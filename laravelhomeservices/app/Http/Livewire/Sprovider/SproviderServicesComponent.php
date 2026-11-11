<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sprovider;

use App\Models\Service;
use App\Services\ServiceAssetCleaner;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SproviderServicesComponent extends Component
{
    use WithPagination;

    public function deleteService(int $serviceId, ServiceAssetCleaner $cleaner): void
    {
        $service = Service::where('id', $serviceId)->where('user_id', Auth::id())->first();

        if ($service === null) {
            return;
        }

        $cleaner->deleteFiles($service);
        $service->delete();
        session()->flash('message', 'El servicio se eliminó.');
    }

    public function togglePause(int $serviceId): void
    {
        $service = Service::where('id', $serviceId)->where('user_id', Auth::id())->firstOrFail();
        $service->paused_at = $service->paused_at ? null : now();
        $service->save();

        session()->flash('message', $service->paused_at
            ? '«' . $service->name . '» quedó en pausa: los clientes no lo ven hasta que lo reanudes.'
            : '«' . $service->name . '» se ve otra vez en el sitio.');
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.sprovider.sprovider-services-component', [
            'services' => $user->services()->with('category:id,name')->withCount('requests')->withAvg('reviews', 'rating')->latest()->paginate(10),
            'canPublish' => $user->canPublishService(),
            'limit' => $user->servicePublicationLimit(),
        ])->layout('layouts.base')->title('Mis servicios');
    }
}
