<?php

namespace App\Http\Livewire\Admin\Service;

use App\Http\Livewire\Concerns\HandlesServiceForm;
use App\Models\Service;
use App\Models\ServiceCategory;
use Livewire\Component;
use Livewire\WithFileUploads;

class AdminEditServiceComponent extends Component
{
    use HandlesServiceForm;
    use WithFileUploads;

    public $featured;

    public function mount($service_slug)
    {
        $service = Service::where('slug', $service_slug)->firstOrFail();
        $this->fillFromService($service);
        $this->featured = $service->featured;
    }

    public function updateService()
    {
        // Segunda barrera: la ruta ya exige ADM, pero la acción también lo comprueba.
        abort_unless(\Illuminate\Support\Facades\Auth::user()?->utype === 'ADM', 403);

        $this->validate($this->serviceRules());

        $service = Service::findOrFail($this->service_id);
        $service->featured = (bool) $this->featured;
        $this->persistService($service);

        $this->image = $service->image;
        $this->thumbnail = $service->thumbnail;
        $this->newimage = null;
        $this->newthumbnail = null;
        session()->flash('message', 'El servicio se actualizó correctamente.');
    }

    public function render()
    {
        $categories = ServiceCategory::orderBy('name')->get();
        return view('livewire.admin.service.admin-edit-service-component', ['categories' => $categories])->layout('layouts.base')->title('Editar servicio');
    }
}
