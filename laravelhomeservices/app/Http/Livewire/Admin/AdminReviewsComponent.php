<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Reseñas: ocultar las ofensivas, falsas o con datos de terceros (términos, punto 7). Ocultar se
 * puede deshacer; una reseña oculta no cuenta en el promedio ni se ve en el sitio.
 */
class AdminReviewsComponent extends Component
{
    use WithPagination;

    public string $filter = '';

    public ?int $hidingId = null;

    public string $hideReason = '';

    protected $queryString = ['filter' => ['except' => '']];

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function startHide(int $reviewId): void
    {
        $this->resetErrorBag();
        $this->hidingId = $reviewId;
        $this->hideReason = '';
    }

    public function hide(): void
    {
        abort_unless(Auth::user()?->utype === 'ADM', 403);
        $this->validate(['hideReason' => ['required', 'string', 'max:255']], [], ['hideReason' => 'el motivo']);

        Review::findOrFail($this->hidingId)->forceFill(['hidden_at' => now(), 'hidden_reason' => $this->hideReason])->save();
        $this->hidingId = null;
        session()->flash('message', 'Reseña oculta. Ya no se ve en el sitio ni cuenta en la calificación.');
    }

    public function show(int $reviewId): void
    {
        abort_unless(Auth::user()?->utype === 'ADM', 403);

        Review::findOrFail($reviewId)->forceFill(['hidden_at' => null, 'hidden_reason' => null])->save();
        session()->flash('message', 'La reseña se ve otra vez.');
    }

    public function render()
    {
        return view('livewire.admin.admin-reviews-component', [
            'reviews' => Review::query()
                ->with(['service:id,name,slug,user_id', 'service.user:id,name', 'user:id,name,email'])
                ->when($this->filter === 'low', fn (Builder $query) => $query->where('rating', '<=', 2)->whereNull('hidden_at'))
                ->when($this->filter === 'hidden', fn (Builder $query) => $query->whereNotNull('hidden_at'))
                ->latest()
                ->paginate(20),
            'lowCount' => Review::where('rating', '<=', 2)->whereNull('hidden_at')->count(),
            'hiddenCount' => Review::whereNotNull('hidden_at')->count(),
        ])->layout('layouts.base')->title('Reseñas');
    }
}
