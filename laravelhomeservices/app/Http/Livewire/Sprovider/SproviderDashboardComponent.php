<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sprovider;

use App\Models\ActivityEvent;
use App\Models\Review;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SproviderDashboardComponent extends Component
{
    public function render()
    {
        $user = Auth::user();
        $profile = $user->serviceProvider()->withCount('zones')->first();
        $requests = ServiceRequest::where('provider_id', $user->id);

        $servicesCount = $user->services()->count();

        // Lo que Malakplace le trajo este mes: es la razón para volver y para seguir publicando.
        $interest = ActivityEvent::query()
            ->where('provider_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('event, count(*) as total')
            ->groupBy('event')
            ->pluck('total', 'event');

        return view('livewire.sprovider.sprovider-dashboard-component', [
            'servicesCount' => $servicesCount,
            'monthViews' => (int) ($interest[ActivityEvent::SERVICE_VIEW] ?? 0),
            'monthContacts' => (int) ($interest[ActivityEvent::WHATSAPP_CLICK] ?? 0),
            'verified' => $profile?->isVerified() ?? false,
            'pendingCount' => (clone $requests)->where('status', ServiceRequest::PENDING)->count(),
            'acceptedCount' => (clone $requests)->where('status', ServiceRequest::ACCEPTED)->count(),
            'completedCount' => (clone $requests)->where('status', ServiceRequest::COMPLETED)->count(),
            'rating' => Review::whereIn('service_id', $user->services()->select('id'))->avg('rating'),
            'profileIncomplete' => $profile === null || $profile->zones_count === 0 || ! $profile->whatsapp,
            // "Primeros pasos": se marcan solos según lo que el proveedor ya hizo.
            'steps' => [
                [
                    'done' => $profile !== null && $profile->zones_count > 0 && (bool) $profile->whatsapp,
                    'title' => 'Pon tu WhatsApp y las zonas donde trabajas',
                    'text' => 'Sin esto los clientes no pueden escribirte ni encontrarte cuando filtran por zona.',
                    'action' => 'Completar perfil',
                    'href' => route('sprovider.edit_profile'),
                ],
                [
                    'done' => $profile !== null && (bool) $profile->image && (bool) $profile->about,
                    'title' => 'Sube tu foto y cuenta quién eres',
                    'text' => 'Una foto tuya y dos líneas sobre tu experiencia dan confianza: la gente contrata a personas.',
                    'action' => 'Agregar foto',
                    'href' => route('sprovider.edit_profile'),
                ],
                [
                    'done' => $servicesCount > 0,
                    'title' => 'Publica tu primer servicio',
                    'text' => 'Ponle un nombre claro (ej.: «Instalación de calefones»), un precio de referencia y una buena foto.',
                    'action' => 'Publicar servicio',
                    'href' => route('sprovider.add_service'),
                ],
            ],
            'limit' => $user->servicePublicationLimit(),
            'isPro' => $user->isPro(),
            'latestRequests' => (clone $requests)->with('service:id,name,slug')
                ->orderByRaw("case when status = 'pending' then 0 when status = 'accepted' then 1 else 2 end")
                ->latest()->take(5)->get(),
        ])->layout('layouts.base')->title('Mi panel');
    }
}
