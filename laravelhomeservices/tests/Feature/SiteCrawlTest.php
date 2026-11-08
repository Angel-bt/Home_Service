<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoMarketplaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Recorre todos los enlaces internos que ve cada rol, con los datos de demostración, y falla con
 * cualquier página que no exista o que dé error, cualquier imagen local que no exista y cualquier
 * imagen sin dirección. Así un enlace roto no llega a producción.
 */
class SiteCrawlTest extends TestCase
{
    use RefreshDatabase;

    private const MAX_PAGES = 250;

    /** Rutas que no se visitan: cierran sesión, salen del sitio o son para máquinas. */
    private const SKIP = ['/logout', '/livewire/', '/billing/payphone', '/cron/', '/increment-visit', '/reset-visits'];

    public static function roles(): array
    {
        return [
            'visitante' => [null],
            'cliente' => ['cliente@proyetech.test'],
            'proveedor' => ['proveedor@proyetech.test'],
            'administración' => ['admin@proyetech.test'],
        ];
    }

    #[DataProvider('roles')]
    public function test_every_link_works_for_each_role(?string $email): void
    {
        $this->seed(DemoMarketplaceSeeder::class);
        User::query()->update(['terms_version' => config('marketplace.terms_version')]);

        if ($email !== null) {
            $this->actingAs(User::where('email', $email)->firstOrFail());
        }

        $queue = ['/'];
        if ($email !== null) {
            $queue[] = route(User::where('email', $email)->firstOrFail()->dashboardRoute(), [], false);
        }

        $seen = [];
        $problems = [];
        $base = rtrim(config('app.url'), '/');

        while ($queue !== [] && count($seen) < self::MAX_PAGES) {
            $path = array_shift($queue);
            if (isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;

            $response = $this->get($path);
            $status = $response->getStatusCode();

            if ($status >= 400) {
                $problems[] = "{$status} en {$path}";

                continue;
            }

            if ($response->isRedirect()) {
                $target = $this->internalPath((string) $response->headers->get('Location'), $base);
                if ($target !== null && ! isset($seen[$target])) {
                    $queue[] = $target;
                }

                continue;
            }

            $content = (string) $response->getContent();
            if (! str_contains((string) $response->headers->get('Content-Type'), 'html')) {
                continue;
            }

            if ($path !== '/' && str_contains($content, '<title>Malakplace · Servicios para tu hogar en Malacatos</title>')) {
                $problems[] = "pestaña sin título propio en {$path}";
            }

            if (preg_match('/<img[^>]+src=""/', $content) === 1) {
                $problems[] = "imagen sin dirección en {$path}";
            }

            preg_match_all('/<img[^>]+src="([^"]+)"/', $content, $images);
            foreach (array_unique($images[1]) as $src) {
                $local = $this->internalPath(html_entity_decode($src), $base);
                if ($local !== null && ! is_file(public_path(strtok($local, '?')))) {
                    $problems[] = "imagen que no existe ({$local}) en {$path}";
                }
            }

            preg_match_all('/href="([^"#][^"]*)"/', $content, $links);
            foreach (array_unique($links[1]) as $href) {
                $target = $this->internalPath(html_entity_decode($href), $base);
                if ($target !== null && ! isset($seen[$target]) && ! $this->skipped($target) && ! str_starts_with($target, '/images/') && ! str_starts_with($target, '/css/') && ! str_starts_with($target, '/js/')) {
                    $queue[] = $target;
                }
            }
        }

        $this->assertSame([], array_values(array_unique($problems)), 'Páginas visitadas: ' . count($seen));
        $this->assertGreaterThan(15, count($seen));
    }

    public function test_the_sitemap_lists_public_pages_and_active_services_only(): void
    {
        $this->seed(DemoMarketplaceSeeder::class);
        $hidden = \App\Models\Service::query()->firstOrFail();
        $hidden->forceFill(['status' => false])->save();
        $visible = \App\Models\Service::query()->where('status', true)->firstOrFail();

        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('home.service_details', ['service_slug' => $visible->slug]), false)
            ->assertDontSee(route('home.service_details', ['service_slug' => $hidden->slug]), false)
            ->assertDontSee('/admin/', false);
    }

    private function internalPath(string $url, string $base): ?string
    {
        if ($url === '' || preg_match('/^(mailto:|tel:|javascript:|data:)/', $url) === 1) {
            return null;
        }

        if (str_starts_with($url, $base)) {
            $url = substr($url, strlen($base)) ?: '/';
        } elseif (preg_match('#^https?://#', $url) === 1 || str_starts_with($url, '//')) {
            return null;
        }

        if (! str_starts_with($url, '/')) {
            return null;
        }

        $url = strtok($url, '#') ?: '/';

        return $url;
    }

    private function skipped(string $path): bool
    {
        foreach (self::SKIP as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
