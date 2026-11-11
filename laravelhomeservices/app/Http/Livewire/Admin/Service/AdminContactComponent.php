<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Service;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Mensajes de la página de contacto: se marcan como respondidos para saber qué falta.
 */
class AdminContactComponent extends Component
{
    use WithPagination;

    public string $filter = 'pending';

    protected $queryString = ['filter' => ['except' => 'pending']];

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function toggleAnswered(int $contactId): void
    {
        $this->authorizeAdmin();

        $contact = Contact::findOrFail($contactId);
        $contact->answered_at = $contact->answered_at ? null : now();
        $contact->save();
        session()->flash('message', $contact->answered_at ? 'Marcado como respondido.' : 'Volvió a la lista de mensajes sin responder.');
    }

    public function deleteContact(int $contactId): void
    {
        $this->authorizeAdmin();

        Contact::whereKey($contactId)->delete();
        session()->flash('message', 'Mensaje eliminado.');
    }

    public function render()
    {
        return view('livewire.admin.service.admin-contact-component', [
            'contacts' => Contact::query()
                ->when($this->filter === 'pending', fn (Builder $query) => $query->whereNull('answered_at'))
                ->when($this->filter === 'answered', fn (Builder $query) => $query->whereNotNull('answered_at'))
                ->latest()
                ->paginate(20),
            'pendingCount' => Contact::whereNull('answered_at')->count(),
        ])->layout('layouts.base')->title('Mensajes');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->utype === 'ADM', 403);
    }
}
