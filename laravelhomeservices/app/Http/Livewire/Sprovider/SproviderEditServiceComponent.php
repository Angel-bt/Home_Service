<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sprovider;

use App\Http\Livewire\Concerns\HandlesServiceForm;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class SproviderEditServiceComponent extends Component
{
    use HandlesServiceForm;
    use WithFileUploads;

    public function mount(string $service_slug): void
    {
        $this->fillFromService($this->ownedService($service_slug, 'slug'));
    }

    public function updateService(): void
    {
        $this->validate($this->serviceRules());

        $service = $this->persistService($this->ownedService($this->service_id, 'id'));

        $this->image = $service->image;
        $this->thumbnail = $service->thumbnail;
        $this->newimage = null;
        $this->newthumbnail = null;
        session()->flash('message', 'Tu servicio se actualizó correctamente.');
    }

    /**
     * Providers can only load and save their own services.
     */
    private function ownedService($value, string $column): Service
    {
        return Service::where($column, $value)->where('user_id', Auth::id())->firstOrFail();
    }

    public function render()
    {
        return view('livewire.sprovider.sprovider-edit-service-component', [
            'categories' => ServiceCategory::orderBy('name')->get(),
        ])->layout('layouts.base')->title('Editar servicio');
    }
}
