<?php

namespace App\Http\Livewire\Admin;

use App\Models\Contact;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Admin home: what needs attention first, then the numbers of the platform.
 */
class AdminDashboardComponent extends Component
{
    public function resetVisits(): void
    {
        abort_unless(Auth::user()->utype === 'ADM', 403);

        Visit::counter()->update(['visits' => 0]);
        session()->forget('visit_counted');
        session()->flash('message', 'El contador de visitas volvió a cero.');
    }

    public function render()
    {
        $pendingPayments = SubscriptionPayment::where('status', SubscriptionPayment::PENDING)->count();
        // Más de un día sin respuesta: el cliente puede estar quedándose sin atención.
        $staleRequests = ServiceRequest::where('status', ServiceRequest::PENDING)->where('created_at', '<', now()->subDay())->count();
        $unansweredContacts = Contact::whereNull('answered_at')->count();
        $pendingDocuments = \App\Models\ProviderDocument::where('status', \App\Models\ProviderDocument::PENDING)->count();
        $lowReviews = \App\Models\Review::where('rating', '<=', 2)->whereNull('hidden_at')->where('created_at', '>=', now()->subDays(7))->count();
        $incompleteProviders = ServiceProvider::query()
            ->where(fn ($query) => $query->whereNull('whatsapp')->orWhere('whatsapp', '')->orWhereDoesntHave('zones'))
            ->count();

        return view('livewire.admin.admin-dashboard-component', [
            'tasks' => array_values(array_filter([
                $pendingPayments > 0 ? [
                    'icon' => 'card',
                    'title' => $pendingPayments === 1 ? '1 pago del plan Pro por revisar' : $pendingPayments . ' pagos del plan Pro por revisar',
                    'text' => 'Confirma que recibiste el dinero para activar el plan del proveedor.',
                    'action' => 'Revisar pagos',
                    'href' => route('admin.payments'),
                ] : null,
                $staleRequests > 0 ? [
                    'icon' => 'clock',
                    'title' => $staleRequests === 1 ? '1 solicitud lleva más de un día sin respuesta' : $staleRequests . ' solicitudes llevan más de un día sin respuesta',
                    'text' => 'Escríbele al proveedor para recordarle, o al cliente para ofrecerle otra opción.',
                    'action' => 'Ver solicitudes',
                    'href' => route('admin.service_requests', ['status' => ServiceRequest::PENDING]),
                ] : null,
                $pendingDocuments > 0 ? [
                    'icon' => 'shield',
                    'title' => $pendingDocuments === 1 ? '1 documento de verificación por revisar' : $pendingDocuments . ' documentos de verificación por revisar',
                    'text' => 'Un proveedor espera su sello. Revísalo pronto: el archivo se borra apenas decides.',
                    'action' => 'Revisar documentos',
                    'href' => route('admin.verifications'),
                ] : null,
                $unansweredContacts > 0 ? [
                    'icon' => 'mail',
                    'title' => $unansweredContacts === 1 ? '1 mensaje sin responder' : $unansweredContacts . ' mensajes sin responder',
                    'text' => 'Personas que escribieron desde la página de contacto. Respóndeles y márcalos como respondidos.',
                    'action' => 'Leer mensajes',
                    'href' => route('admin.contacts'),
                ] : null,
                $lowReviews > 0 ? [
                    'icon' => 'star',
                    'title' => $lowReviews === 1 ? '1 reseña de 1 o 2 estrellas esta semana' : $lowReviews . ' reseñas de 1 o 2 estrellas esta semana',
                    'text' => 'Puede ser una queja real: vale la pena hablar con el proveedor. Ocúltala solo si es ofensiva o falsa.',
                    'action' => 'Ver reseñas',
                    'href' => route('admin.reviews', ['filter' => 'low']),
                ] : null,
                $incompleteProviders > 0 ? [
                    'icon' => 'users',
                    'title' => $incompleteProviders === 1 ? '1 proveedor con el perfil incompleto' : $incompleteProviders . ' proveedores con el perfil incompleto',
                    'text' => 'Sin WhatsApp o sin zonas los clientes no los encuentran. Vale la pena avisarles.',
                    'action' => 'Ver proveedores',
                    'href' => route('admin.service_providers'),
                ] : null,
            ])),
            'totalCustomers' => User::where('utype', 'CST')->count(),
            'totalProviders' => User::where('utype', 'SVP')->count(),
            'totalServices' => Service::count(),
            'activeRequests' => ServiceRequest::whereIn('status', [ServiceRequest::PENDING, ServiceRequest::ACCEPTED])->count(),
            'completedRequests' => ServiceRequest::where('status', ServiceRequest::COMPLETED)->count(),
            'totalCategories' => ServiceCategory::count(),
            'visitCount' => Visit::counter()->visits,
            'topCategories' => ServiceCategory::withCount('services')->orderByDesc('services_count')->take(5)->get(),
        ])->layout('layouts.base')->title('Administración');
    }
}
