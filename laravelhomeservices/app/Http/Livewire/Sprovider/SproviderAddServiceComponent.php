<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sprovider;

use App\Http\Livewire\Concerns\HandlesServiceForm;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ServicePublicationGate;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class SproviderAddServiceComponent extends Component
{
    use HandlesServiceForm;
    use WithFileUploads;

    public function createService(ServicePublicationGate $publicationGate)
    {
        $this->validate($this->serviceRules());

        $user = Auth::user();

        try {
            $publicationGate->ensureCanPublish($user);
        } catch (DomainException $exception) {
            $this->addError('name', 'Alcanzaste el límite de servicios de tu plan gratuito. Pásate al plan Pro para publicar más.');

            return null;
        }

        $service = new Service();
        $service->user_id = $user->id;
        $this->persistService($service);

        session()->flash('message', 'Tu servicio ya está publicado.');

        return redirect()->route('sprovider.services');
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.sprovider.sprovider-add-service-component', [
            'categories' => ServiceCategory::orderBy('name')->get(),
            'limit' => $user->servicePublicationLimit(),
            'used' => $user->services()->count(),
        ])->layout('layouts.base');
    }
}
