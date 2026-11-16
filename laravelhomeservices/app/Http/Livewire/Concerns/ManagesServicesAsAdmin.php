<?php

declare(strict_types=1);

namespace App\Http\Livewire\Concerns;

use App\Models\Service;
use App\Services\ServiceAssetCleaner;
use Illuminate\Support\Facades\Auth;

/**
 * Acciones de la lista de servicios del admin (todos y por categoría), usadas por
 * livewire.admin.service.partials.service-rows.
 */
trait ManagesServicesAsAdmin
{
    public function deleteService($service_id): void
    {
        $this->authorizeServiceAdmin();

        $service = Service::find($service_id);
        if ($service === null) {
            return;
        }

        app(ServiceAssetCleaner::class)->deleteFiles($service);
        $service->delete();
        \App\Support\AdminLog::record('Eliminó un servicio', $service->name);
        session()->flash('message', 'El servicio se eliminó.');
    }

    public function toggleVisible(int $serviceId): void
    {
        $this->authorizeServiceAdmin();

        $service = Service::findOrFail($serviceId);
        $service->status = ! $service->status;
        $service->save();
        \App\Support\AdminLog::record($service->status ? 'Mostró un servicio' : 'Ocultó un servicio', $service->name);
        session()->flash('message', $service->status ? '«' . $service->name . '» se ve otra vez en el sitio.' : '«' . $service->name . '» quedó oculto: nadie lo ve en el sitio.');
    }

    public function toggleFeatured(int $serviceId): void
    {
        $this->authorizeServiceAdmin();

        $service = Service::findOrFail($serviceId);
        $service->featured = ! $service->featured;
        $service->save();
        \App\Support\AdminLog::record($service->featured ? 'Destacó un servicio' : 'Quitó el destacado de un servicio', $service->name);
        session()->flash('message', $service->featured ? '«' . $service->name . '» ahora sale primero en las listas.' : '«' . $service->name . '» ya no está destacado.');
    }

    private function authorizeServiceAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->utype === 'ADM', 403);
    }
}
