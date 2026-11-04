<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Livewire\ContactComponent;
use App\Jobs\SendNewLeadNotification;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\ServiceVisitCounter;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

/**
 * What the app needs on a host without disk, Redis, queue worker or scheduler (Vercel).
 */
class ServerlessDeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploads_in_progress_never_live_in_the_public_folder(): void
    {
        $disk = config('livewire.temporary_file_upload.disk');

        $this->assertSame('uploads', $disk);
        $this->assertStringStartsWith(storage_path(), config("filesystems.disks.{$disk}.root"));
    }

    public function test_images_come_from_public_locally_and_from_the_bucket_on_object_storage(): void
    {
        $this->assertStringEndsWith('/images/services/nueva.jpg', Media::url('services', 'nueva.jpg'));

        config([
            'filesystems.default' => 's3',
            'filesystems.disks.s3' => ['driver' => 's3', 'key' => 'k', 'secret' => 's', 'region' => 'auto', 'bucket' => 'media', 'url' => 'https://media.example.test'],
        ]);

        $this->assertSame('https://media.example.test/services/nueva.jpg', Media::url('services', 'nueva.jpg'));
        // Las imágenes que vienen con el código siguen saliendo de public/.
        $this->assertStringEndsWith('/images/sproviders/default.jpg', Media::url('sproviders', 'default.jpg'));
    }

    public function test_receipts_use_a_private_bucket_when_configured(): void
    {
        $config = require config_path('filesystems.php');
        $this->assertSame('local', $config['disks']['receipts']['driver']);

        $this->assertArrayNotHasKey('url', $this->receiptsDiskWithBucket());
    }

    public function test_media_delete_removes_from_the_bucket_but_not_files_shipped_with_the_code(): void
    {
        config(['filesystems.default' => 's3', 'filesystems.disks.s3.driver' => 's3']);
        $disk = Storage::fake('s3');
        config(['filesystems.disks.s3.driver' => 's3']);
        $disk->put('services/nueva.jpg', 'x');
        $disk->put('sproviders/default.jpg', 'x');

        Media::delete('services', 'nueva.jpg');
        Media::delete('sproviders', 'default.jpg');

        $disk->assertMissing('services/nueva.jpg');
        $disk->assertExists('sproviders/default.jpg');
        $this->assertFileExists(public_path('images/sproviders/default.jpg'));
    }

    public function test_visits_are_counted_without_redis(): void
    {
        config(['marketplace.visit_counter' => 'database']);

        $owner = User::factory()->create(['utype' => 'SVP']);
        $category = new ServiceCategory();
        $category->forceFill(['name' => 'Jardinería', 'slug' => 'jardineria', 'image' => 'j.png'])->save();
        $service = new Service();
        $service->forceFill([
            'user_id' => $owner->id, 'name' => 'Poda', 'slug' => 'poda', 'tagline' => 'x', 'service_category_id' => $category->id,
            'price' => 20, 'image' => 'i.png', 'thumbnail' => 't.png', 'views' => 5,
        ])->save();

        $counter = app(ServiceVisitCounter::class);
        $counter->increment($service);

        $this->assertSame(6, $counter->total($service));
        $this->assertSame([], $counter->pendingServiceIds());
        $this->get(route('home.service_details', ['service_slug' => 'poda']))->assertOk()->assertSee('7 visitas');
    }

    public function test_search_ignores_case_and_accents(): void
    {
        $owner = User::factory()->create(['utype' => 'SVP']);
        $category = new ServiceCategory();
        $category->forceFill(['name' => 'Gasfitería', 'slug' => 'gasfiteria', 'image' => 'g.png'])->save();
        $service = new Service();
        $service->forceFill([
            'user_id' => $owner->id, 'name' => 'Destape de tuberías', 'slug' => 'destape', 'tagline' => 'x', 'service_category_id' => $category->id,
            'price' => 20, 'image' => 'i.png', 'thumbnail' => 't.png',
        ])->save();

        $this->assertSame(1, Service::search('DESTAPE')->count());
        $this->assertSame(0, Service::search('nada')->count());

        if (Service::query()->getConnection()->getDriverName() === 'pgsql') {
            $this->assertSame(1, Service::search('TUBERIAS')->count());
            $this->assertSame(1, Service::search('tuberías')->count());
            $this->assertSame(1, Service::search('gasfiteria')->count());
        }
    }

    public function test_registration_survives_a_mail_failure(): void
    {
        config(['queue.default' => 'sync', 'mail.default' => 'failover', 'mail.mailers.failover' => ['transport' => 'smtp', 'host' => '127.0.0.1', 'port' => 1, 'timeout' => 1]]);

        $this->post('/register', [
            'name' => 'Ana', 'email' => 'ana@example.test', 'phone' => '0991234567', 'registeras' => 'SVP',
            'password' => 'una-clave-larga', 'password_confirmation' => 'una-clave-larga',
        ])->assertRedirect(route('sprovider.dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ana@example.test', 'utype' => 'SVP']);
    }

    public function test_contact_message_is_kept_when_the_mail_fails(): void
    {
        $this->mock(\Illuminate\Contracts\Bus\Dispatcher::class, function ($mock): void {
            $mock->shouldReceive('dispatch')->andThrow(new RuntimeException('smtp caído'));
        });

        Livewire::test(ContactComponent::class)
            ->set('name', 'Ana')->set('email', 'ana@example.test')->set('phone', '0991234567')->set('message', 'Hola')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->assertSee('Tu mensaje fue enviado');

        $this->assertDatabaseHas('contacts', ['email' => 'ana@example.test']);
    }

    public function test_password_reset_explains_when_the_mail_server_is_down(): void
    {
        $user = User::factory()->create();
        config(['mail.default' => 'caido', 'mail.mailers.caido' => ['transport' => 'smtp', 'host' => '127.0.0.1', 'port' => 1, 'timeout' => 1]]);

        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect('/forgot-password')
            ->assertSessionHasErrors('email');
    }

    public function test_daily_cron_requires_the_secret(): void
    {
        $this->get('/cron/diario')->assertNotFound();

        config(['services.cron.secret' => 'secreto', 'marketplace.visit_counter' => 'database']);

        $this->get('/cron/diario')->assertNotFound();
        $this->withToken('otro')->get('/cron/diario')->assertNotFound();
        Storage::fake('local');
        Storage::fake('receipts');
        $this->withToken('secreto')->get('/cron/diario')->assertOk()->assertJson(['billing:expire-plans' => 0]);
    }

    public function test_storage_check_passes_and_cleans_up_after_itself(): void
    {
        $media = Storage::fake('local');
        $receipts = Storage::fake('receipts');
        config([
            'filesystems.disks.local' => ['driver' => 'local', 'root' => $media->path('')],
            'filesystems.disks.receipts' => ['driver' => 'local', 'root' => $receipts->path('')],
        ]);

        $this->artisan('storage:check')->assertSuccessful();

        $this->assertSame([], $media->allFiles());
        $this->assertSame([], $receipts->allFiles());
    }

    public function test_mail_check_fails_without_sending_when_the_server_is_unreachable(): void
    {
        $this->artisan('mail:check')->assertSuccessful();

        config(['mail.default' => 'caido', 'mail.mailers.caido' => ['transport' => 'smtp', 'host' => '127.0.0.1', 'port' => 1, 'timeout' => 1]]);

        $this->artisan('mail:check')->assertFailed();
    }

    public function test_daily_cron_fails_loudly_when_storage_is_broken(): void
    {
        config([
            'services.cron.secret' => 'secreto',
            'marketplace.visit_counter' => 'database',
            'filesystems.disks.receipts' => ['driver' => 'not-a-driver'],
        ]);

        $this->withToken('secreto')->get('/cron/diario')->assertStatus(500);
    }

    public function test_real_client_ip_is_used_behind_a_trusted_proxy(): void
    {
        config(['app.trusted_proxies' => '*']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1', 'HTTP_X_FORWARDED_FOR' => '186.5.5.5', 'HTTP_X_FORWARDED_PROTO' => 'https'])
            ->get('/ip-de-prueba-inexistente');

        $this->assertSame('186.5.5.5', request()->ip());
        $this->assertTrue(request()->isSecure());
    }

    /**
     * @return array<string, mixed>
     */
    private function receiptsDiskWithBucket(): array
    {
        $_ENV['RECEIPTS_BUCKET'] = $_SERVER['RECEIPTS_BUCKET'] = 'privado';

        try {
            \Illuminate\Support\Env::getRepository()->set('RECEIPTS_BUCKET', 'privado');
            $disk = (require config_path('filesystems.php'))['disks']['receipts'];
        } finally {
            \Illuminate\Support\Env::getRepository()->clear('RECEIPTS_BUCKET');
            unset($_ENV['RECEIPTS_BUCKET'], $_SERVER['RECEIPTS_BUCKET']);
        }

        $this->assertSame('s3', $disk['driver']);
        $this->assertSame('privado', $disk['bucket']);

        return $disk;
    }
}
