<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Service;

use App\Http\Livewire\Concerns\HandlesServiceForm;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ServicePublicationGate;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class AdminAddServiceComponent extends Component
{
    use HandlesServiceForm;
    use WithFileUploads;

    public function createService(ServicePublicationGate $publicationGate): void
    {
        $this->validate($this->serviceRules());

        $user = Auth::user();
        abort_if($user === null, 403);

        try {
            $publicationGate->ensureCanPublish($user);
        } catch (DomainException $exception) {
            $this->addError('name', $exception->getMessage());

            return;
        }

        $service = new Service();
        $service->user_id = $user->id;
        $this->persistService($service);

        $this->reset();
        session()->flash('message', 'El servicio se creó correctamente.');
    }

    public function render()
    {
        $categories = ServiceCategory::orderBy('name')->get();
        return view('livewire.admin.service.admin-add-service-component', ['categories' => $categories])->layout('layouts.base');
    }
}
