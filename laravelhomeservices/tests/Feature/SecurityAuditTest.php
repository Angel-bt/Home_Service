<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use App\Mail\NoticeMail;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\SupabaseLockdown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Lo que salió de la auditoría de seguridad del 11/11/2026.
 */
class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_carry_a_content_security_policy_and_hide_the_php_version(): void
    {
        $response = $this->get('/');

        $policy = (string) $response->headers->get('Content-Security-Policy');
        foreach (["object-src 'none'", "base-uri 'self'", "frame-ancestors 'self'", "form-action 'self'", 'https://fonts.googleapis.com', 'https://images.unsplash.com'] as $piece) {
            $this->assertStringContainsString($piece, $policy);
        }
        $this->assertFalse($response->headers->has('X-Powered-By'));
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
    }

    public function test_the_policy_allows_the_photo_bucket_and_maps_only_when_configured(): void
    {
        $this->assertStringNotContainsString('maps.googleapis.com', SecurityHeaders::policy());

        config([
            'filesystems.disks.s3.url' => 'https://abc.supabase.co/storage/v1/object/public/media',
            'filesystems.disks.s3.endpoint' => 'https://abc.supabase.co/storage/v1/s3',
            'services.google_maps.key' => 'clave',
        ]);
        $policy = SecurityHeaders::policy();

        $this->assertMatchesRegularExpression('#img-src [^;]*https://abc\.supabase\.co#', $policy);
        $this->assertMatchesRegularExpression('#connect-src [^;]*https://abc\.supabase\.co#', $policy, 'las fotos se suben directo al bucket');
        $this->assertMatchesRegularExpression('#script-src [^;]*https://maps\.googleapis\.com#', $policy);
    }

    #[DataProvider('privateFiles')]
    public function test_private_files_are_never_cached_indexed_or_run(string $name, string $contents, bool $sandboxed): void
    {
        Storage::fake('receipts');
        Storage::disk('receipts')->put($name, $contents);
        $provider = User::factory()->create(['utype' => 'SVP']);
        $payment = SubscriptionPayment::create(['user_id' => $provider->id, 'method' => 'transfer', 'periods' => 1, 'amount' => 8, 'status' => SubscriptionPayment::PENDING, 'receipt_path' => $name]);

        $response = $this->actingAs(User::factory()->create(['utype' => 'ADM']))->get(route('admin.payment_receipt', $payment))->assertOk();

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        if ($sandboxed) {
            $this->assertStringStartsWith('sandbox', (string) $response->headers->get('Content-Security-Policy'));
        } else {
            $this->assertFalse($response->headers->has('Content-Security-Policy'), 'Chrome no muestra un PDF con sandbox');
        }
    }

    public static function privateFiles(): array
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        return [
            'imagen' => ['comprobante.png', $png, true],
            'PDF' => ['comprobante.pdf', "%PDF-1.4\n%prueba\n", false],
        ];
    }

    public function test_every_admin_sign_in_is_reported_by_email(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['utype' => 'ADM', 'email' => 'admin@example.test']);
        $customer = User::factory()->create(['utype' => 'CST']);

        $this->post('/login', ['email' => $customer->email, 'password' => 'password']);
        Mail::assertNothingSent();
        auth()->logout();

        $this->withHeader('User-Agent', 'Navegador de prueba')->post('/login', ['email' => $admin->email, 'password' => 'password']);
        Mail::assertSent(NoticeMail::class, fn (NoticeMail $mail) => $mail->hasTo('admin@example.test')
            && $mail->title === 'Ingresaron a tu cuenta de administración'
            && str_contains(implode(' ', $mail->lines), 'Navegador de prueba'));
    }

    public function test_the_admin_is_asked_to_turn_on_two_step_verification(): void
    {
        $admin = User::factory()->create(['utype' => 'ADM']);

        $this->actingAs($admin)->get('/admin/dashboard')->assertSee('Protege tu cuenta con la verificación en dos pasos');

        $admin->forceFill(['two_factor_secret' => encrypt('x'), 'two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin->fresh())->get('/admin/dashboard')->assertDontSee('Protege tu cuenta con la verificación en dos pasos');
    }

    public function test_leaked_passwords_are_rejected_in_production(): void
    {
        // «password» aparece millones de veces en filtraciones; la API solo recibe los 5 primeros caracteres del hash.
        $hash = strtoupper(sha1('password'));
        Http::fake(['api.pwnedpasswords.com/range/' . substr($hash, 0, 5) => Http::response(substr($hash, 5) . ":9659365\r\n")]);

        $this->app->detectEnvironment(fn () => 'production');
        $validator = Validator::make(['password' => 'password'], ['password' => Password::default()]);

        $this->assertTrue($validator->fails());
        $this->assertSame('Esa contraseña apareció en filtraciones de internet. Elige otra distinta.', $validator->errors()->first('password'));
    }

    public function test_the_security_self_check(): void
    {
        $this->artisan('security:check')->assertSuccessful();

        $this->app->detectEnvironment(fn () => 'production');
        config(['app.debug' => true]);
        $this->artisan('security:check')->assertFailed();
    }

    public function test_the_supabase_lockdown_does_nothing_outside_supabase(): void
    {
        $this->assertFalse(SupabaseLockdown::applies());
        $this->assertSame(0, SupabaseLockdown::apply());
    }

    public function test_the_daily_cron_includes_the_security_check(): void
    {
        config(['services.cron.secret' => 'secreto', 'marketplace.visit_counter' => 'database']);
        Storage::fake('local');
        Storage::fake('receipts');

        $this->withToken('secreto')->get('/cron/diario')->assertOk()->assertJson(['security:check' => 0]);
    }

    public static function vercelPaths(): array
    {
        return [
            // [ruta, ¿bloqueada?]
            ['/composer.json', true], ['/composer.lock', true], ['/.env', true], ['/.env.example', true], ['/artisan', true],
            ['/phpunit.xml', true], ['/vendor/autoload.php', true], ['/routes/web.php', true], ['/app/Models/User.php', true],
            ['/README.md', true], ['/docs/06-despliegue-y-operacion.md', true], ['/api/index.php', true], ['/storage/logs/laravel.log', true],
            ['/', false], ['/buscar', false], ['/service/destape', false], ['/gasfiteria/services', false], ['/admin/users', false],
            ['/sprovider/my-services', false], ['/livewire/livewire.min.js', false], ['/livewire/update', false], ['/sitemap.xml', false],
            ['/cron/diario', false], ['/billing/payphone/return', false], ['/user/profile', false], ['/aceptar-terminos', false],
        ];
    }

    #[DataProvider('vercelPaths')]
    public function test_vercel_hides_project_files_but_not_the_site(string $path, bool $blocked): void
    {
        $routes = json_decode((string) file_get_contents(base_path('vercel.json')), true)['routes'];
        $matched = null;
        foreach ($routes as $route) {
            if (preg_match('#^' . $route['src'] . '$#', $path) === 1) {
                $matched = $route;
                break;
            }
        }

        $this->assertNotNull($matched, $path);
        $this->assertSame($blocked, ($matched['status'] ?? null) === 404, $path);
    }
}
