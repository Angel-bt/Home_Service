<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sprovider;

use App\Models\ServiceRequest;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Requests received by a provider. Administrators (also routed here) see every
 * request, including those for services published by the administration.
 */
class SproviderRequestsComponent extends Component
{
    use WithPagination;

    public string $status = '';

    protected $queryString = ['status' => ['except' => '']];

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function changeStatus(int $requestId, string $status): void
    {
        $request = $this->visibleRequests()->findOrFail($requestId);

        try {
            $request->transitionTo($status, Auth::user());
            session()->flash('message', match ($status) {
                ServiceRequest::ACCEPTED => 'Aceptaste la solicitud. Escríbele al cliente para acordar el día y el precio.',
                ServiceRequest::REJECTED => 'Rechazaste la solicitud. El cliente lo verá en su cuenta.',
                ServiceRequest::COMPLETED => '¡Trabajo terminado! Ahora el cliente puede dejarte una reseña.',
                default => 'Solicitud actualizada.',
            });
        } catch (DomainException $exception) {
            session()->flash('error', $exception->getMessage());
        }
    }

    private function visibleRequests(): Builder
    {
        $query = ServiceRequest::query();

        if (Auth::user()->utype !== 'ADM') {
            $query->where('provider_id', Auth::id());
        }

        return $query;
    }

    public function render()
    {
        $requests = $this->visibleRequests()
            ->with(['service:id,name,slug', 'customer:id,name', 'provider:id,name', 'review:id,service_request_id,rating'])
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.sprovider.sprovider-requests-component', [
            'requests' => $requests,
            'isAdmin' => Auth::user()->utype === 'ADM',
            // Cuántas hay en cada estado, para las pestañas del filtro.
            'statusCounts' => $this->visibleRequests()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ])->layout('layouts.base')->title('Solicitudes');
    }
}
