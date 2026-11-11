<?php

namespace App\Providers;

use App\Http\Middleware\AuthAdmin;
use App\Http\Middleware\AuthSprovider;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Ajustes que la administración cambia en /admin/settings (reemplazan config/*.php).
        \App\Support\SiteSettings::apply();

        // Fechas relativas ("hace 3 días") en el idioma de la app.
        Carbon::setLocale(config('app.locale'));

        // Rechaza contraseñas que aparecen en filtraciones (consulta anónima a Have I Been Pwned:
        // solo viajan los 5 primeros caracteres del hash). En local y en pruebas no sale a internet.
        \Illuminate\Validation\Rules\Password::defaults(fn () => app()->isProduction()
            ? \Illuminate\Validation\Rules\Password::min(8)->uncompromised()
            : \Illuminate\Validation\Rules\Password::min(8));

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event): void {
            \App\Support\Activity::record(\App\Models\ActivityEvent::LOGIN, ['user' => $event->user]);

            // La cuenta de administración puede cambiar todo el sitio: cada ingreso se avisa por correo,
            // así un acceso que no fuiste tú se nota de inmediato.
            if ($event->user->utype === 'ADM') {
                $request = request();
                \App\Support\Notice::send($event->user->email, 'Ingresaron a tu cuenta de administración', [
                    'Alguien ingresó a tu cuenta de administración de Malakplace el ' . now()->format('d/m/Y \\a \\l\\a\\s H:i') . '.',
                    'Desde: ' . ($request->ip() ?? 'desconocido') . ' · ' . \Illuminate\Support\Str::limit((string) $request->userAgent(), 120),
                    'Si fuiste tú, no hagas nada. Si no fuiste tú, cambia tu contraseña ahora y cierra las otras sesiones desde «Cuenta y contraseña».',
                ], 'Revisar mi cuenta', route('profile.show'));
            }
        });

        // Re-aplica los guards de rol en cada petición Livewire (acciones, subidas), no solo al cargar la página.
        Livewire::addPersistentMiddleware([
            AuthAdmin::class,
            AuthSprovider::class,
        ]);
    }
}
