<?php

declare(strict_types=1);

namespace App\Http\Livewire\Customer;

use App\Models\Review;
use App\Models\ServiceRequest;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerDashboardComponent extends Component
{
    use WithPagination;

    // Formulario de reseña para la solicitud seleccionada.
    public ?int $reviewingId = null;
    public int $rating = 5;
    public string $comment = '';

    public function cancelRequest(int $requestId): void
    {
        $request = ServiceRequest::where('customer_id', Auth::id())->findOrFail($requestId);

        try {
            $request->transitionTo(ServiceRequest::CANCELLED, Auth::user());
            session()->flash('message', 'Cancelaste la solicitud.');
        } catch (DomainException $exception) {
            session()->flash('error', $exception->getMessage());
        }
    }

    public function startReview(int $requestId): void
    {
        $this->resetErrorBag();
        $this->reviewingId = $requestId;
        $this->rating = 5;
        $this->comment = '';
    }

    public function saveReview(): void
    {
        $this->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $request = ServiceRequest::where('customer_id', Auth::id())->findOrFail($this->reviewingId);

        if (! $request->canBeReviewedBy(Auth::user())) {
            $this->addError('comment', 'Solo puedes calificar una vez los trabajos completados.');

            return;
        }

        Review::create([
            'service_id' => $request->service_id,
            'service_request_id' => $request->id,
            'user_id' => Auth::id(),
            'rating' => $this->rating,
            'comment' => $this->comment === '' ? null : $this->comment,
        ]);

        \App\Support\Activity::record(\App\Models\ActivityEvent::REVIEW_CREATED, ['service_id' => $request->service_id, 'provider_id' => $request->provider_id]);

        $this->reviewingId = null;
        session()->flash('message', '¡Gracias por tu reseña!');
    }

    public function render()
    {
        return view('livewire.customer.customer-dashboard-component', [
            'requests' => ServiceRequest::where('customer_id', Auth::id())
                ->with(['service:id,name,slug', 'provider:id,name,phone,utype', 'provider.serviceProvider:id,user_id,whatsapp', 'review'])
                ->latest()
                ->paginate(10),
        ])->layout('layouts.base')->title('Mis solicitudes');
    }
}
