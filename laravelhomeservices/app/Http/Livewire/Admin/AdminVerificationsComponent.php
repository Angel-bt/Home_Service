<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Models\ProviderDocument;
use App\Services\ProviderVerification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Revisión de documentos de proveedores. Al aprobar o rechazar, el archivo se borra.
 */
class AdminVerificationsComponent extends Component
{
    use WithPagination;

    public string $status = ProviderDocument::PENDING;

    public ?int $rejectingId = null;

    public string $rejectReason = '';

    protected $queryString = ['status' => ['except' => ProviderDocument::PENDING]];

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function approve(int $documentId, ProviderVerification $verification): void
    {
        abort_unless(Auth::user()->utype === 'ADM', 403);

        $verification->approve(ProviderDocument::findOrFail($documentId), Auth::user());
        session()->flash('message', 'Sello otorgado. El archivo ya se borró y el proveedor recibió un correo.');
    }

    public function startReject(int $documentId): void
    {
        $this->resetErrorBag();
        $this->rejectingId = $documentId;
        $this->rejectReason = '';
    }

    public function reject(ProviderVerification $verification): void
    {
        abort_unless(Auth::user()->utype === 'ADM', 403);

        $this->validate(['rejectReason' => ['required', 'string', 'max:255']], [], ['rejectReason' => 'el motivo']);

        $verification->reject(ProviderDocument::findOrFail($this->rejectingId), Auth::user(), $this->rejectReason);
        $this->rejectingId = null;
        session()->flash('message', 'Listo. El archivo ya se borró y el proveedor recibió el motivo por correo.');
    }

    public function revoke(int $documentId, ProviderVerification $verification): void
    {
        abort_unless(Auth::user()->utype === 'ADM', 403);

        $document = ProviderDocument::with('provider')->findOrFail($documentId);
        $verification->withdraw($document->provider, $document->type);
        session()->flash('message', 'Quitamos ese sello del perfil del proveedor.');
    }

    public function render()
    {
        return view('livewire.admin.admin-verifications-component', [
            'documents' => ProviderDocument::query()
                ->with(['provider.user:id,name,email,phone', 'reviewer:id,name'])
                ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
                ->latest()
                ->paginate(20),
            'pendingCount' => ProviderDocument::where('status', ProviderDocument::PENDING)->count(),
            'types' => ProviderDocument::TYPES,
        ])->layout('layouts.base')->title('Verificaciones');
    }
}
