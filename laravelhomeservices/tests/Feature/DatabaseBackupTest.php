<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Services\DatabaseBackup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_backup_is_encrypted_and_holds_every_table(): void
    {
        $disk = Storage::fake('backups');
        $user = User::factory()->create(['email' => 'secreto@ejemplo.test']);
        Service::factory()->create(['user_id' => $user->id, 'service_category_id' => $this->category()]);
        DB::table('sessions')->insert(['id' => 'x', 'payload' => 'y', 'last_activity' => 1]);

        $copy = (new DatabaseBackup)->create();

        $disk->assertExists($copy['name']);
        $this->assertStringNotContainsString('secreto@ejemplo.test', $disk->get($copy['name']));
        $this->assertSame(1, $copy['tables']['users']);
        $this->assertSame(1, $copy['tables']['services']);
        $this->assertArrayHasKey('migrations', $copy['tables']);
        $this->assertArrayNotHasKey('sessions', $copy['tables']);
        $this->assertSame('secreto@ejemplo.test', (new DatabaseBackup)->read($copy['name'])['tables']['users'][0]['email']);
    }

    public function test_restoring_brings_back_exactly_what_was_saved(): void
    {
        Storage::fake('backups');
        $owner = User::factory()->create();
        $service = Service::factory()->create(['user_id' => $owner->id, 'name' => 'Plomería', 'service_category_id' => $this->category()]);
        $name = $owner->name;
        $copy = (new DatabaseBackup)->create();

        // Un desastre: se borra un servicio, se cambia otro dato y aparece una cuenta que no estaba.
        $service->delete();
        $owner->update(['name' => 'Cambiado']);
        User::factory()->create();

        $restored = (new DatabaseBackup)->restore($copy['name']);

        $this->assertSame(1, $restored['services']);
        $this->assertSame(1, User::count());
        $this->assertSame($name, User::first()->name);
        $this->assertSame('Plomería', Service::first()->name);
        // Lo nuevo sigue funcionando después de restaurar.
        $this->assertNotNull(User::factory()->create()->id);
    }

    public function test_test_restore_never_touches_the_real_data(): void
    {
        Storage::fake('backups');
        User::factory()->count(2)->create();
        $copy = (new DatabaseBackup)->create();
        User::factory()->create();

        $this->assertSame(2, (new DatabaseBackup)->testRestore($copy['name'])['users']);
        $this->assertSame(3, User::count());
    }

    public function test_old_backups_are_deleted_but_never_the_newest(): void
    {
        $disk = Storage::fake('backups');
        $disk->put('malakplace-2020-01-01-060000.copia', 'vieja');
        $disk->put('otra-cosa.txt', 'no es una copia');

        $copy = (new DatabaseBackup)->create();

        $disk->assertMissing('malakplace-2020-01-01-060000.copia');
        $disk->assertExists('otra-cosa.txt');
        $this->assertSame([$copy['name']], array_column((new DatabaseBackup)->list(), 'name'));
    }

    public function test_a_backup_made_with_another_key_is_reported_clearly(): void
    {
        $disk = Storage::fake('backups');
        $disk->put('malakplace-2026-01-01-060000.copia', 'esto no está cifrado con nuestra clave');

        $this->expectExceptionMessage('No se pudo abrir la copia');

        (new DatabaseBackup)->read('malakplace-2026-01-01-060000.copia');
    }

    public function test_the_command_records_its_result_for_the_system_page(): void
    {
        Storage::fake('backups');
        User::factory()->create();

        $this->artisan('backup:run --test-restore')->assertSuccessful();

        $status = Cache::get('system:last_backup');
        $this->assertTrue($status['ok']);
        $this->assertNotNull($status['restore_tested_at']);
        $this->assertSame($status['at'], $status['last_ok']);

        config(['filesystems.disks.backups' => ['driver' => 'no-existe']]);
        Storage::forgetDisk('backups');

        $this->artisan('backup:run')->assertFailed();
        $failed = Cache::get('system:last_backup');
        $this->assertFalse($failed['ok']);
        $this->assertSame($status['last_ok'], $failed['last_ok']);
        $this->assertSame($status['restore_tested_at'], $failed['restore_tested_at']);
    }

    public function test_the_daily_cron_makes_a_backup(): void
    {
        $disk = Storage::fake('backups');
        Storage::fake('local');
        Storage::fake('receipts');
        config(['services.cron.secret' => 'secreto', 'marketplace.visit_counter' => 'database']);

        $this->withToken('secreto')->get('/cron/diario')->assertOk()->assertJson(['backup:run' => 0]);

        $this->assertCount(1, $disk->files());
    }

    public function test_restore_command_only_tests_unless_told_otherwise(): void
    {
        Storage::fake('backups');
        $this->artisan('backup:restore')->assertFailed();

        User::factory()->create();
        (new DatabaseBackup)->create();
        User::factory()->create();

        $this->artisan('backup:restore')->assertSuccessful();
        $this->artisan('backup:restore --list')->assertSuccessful();
        $this->artisan('backup:restore --real')->expectsConfirmation('¿Seguro? Escribe «yes» para continuar', 'no')->assertFailed();
        $this->assertSame(2, User::count());

        $this->artisan('backup:restore --real')->expectsConfirmation('¿Seguro? Escribe «yes» para continuar', 'yes')->assertSuccessful();
        $this->assertSame(1, User::count());
    }

    private function category(): int
    {
        return (int) DB::table('service_categories')->insertGetId(['name' => 'Hogar', 'slug' => 'hogar', 'image' => 'hogar.png']);
    }
}
