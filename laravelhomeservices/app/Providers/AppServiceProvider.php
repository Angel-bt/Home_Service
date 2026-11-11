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

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event): void {
            \App\Support\Activity::record(\App\Models\ActivityEvent::LOGIN, ['user' => $event->user]);
        });

        // Re-aplica los guards de rol en cada petición Livewire (acciones, subidas), no solo al cargar la página.
        Livewire::addPersistentMiddleware([
            AuthAdmin::class,
            AuthSprovider::class,
        ]);
    }
}
