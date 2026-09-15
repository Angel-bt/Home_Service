<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Service;

use App\Models\Service;
use App\Services\ServiceAssetCleaner;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AdminServicesComponent extends Component
{
    use WithPagination;

    public function deleteService($service_id)
    {
        abort_unless(Auth::check() && Auth::user()->utype === 'ADM', 403);

        $service = Service::find($service_id);

        if ($service === null) {
            return;
        }

        app(ServiceAssetCleaner::class)->deleteFiles($service);

        $service->delete();
        session()->flash('message', 'Service has been deleted successfully!');
    }

    public function render()
    {
        $services = Service::query()->with(['category:id,name,slug', 'user:id,name'])->paginate(10);
        return view('livewire.admin.service.admin-services-component', ['services' => $services])->layout('layouts.base');
    }
}
