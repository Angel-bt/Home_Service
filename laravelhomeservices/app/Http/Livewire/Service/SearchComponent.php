<?php

declare(strict_types=1);

namespace App\Http\Livewire\Service;

use App\Http\Livewire\Concerns\FiltersByZone;
use App\Models\ActivityEvent;
use App\Models\Service;
use App\Support\Activity;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class SearchComponent extends Component
{
    use FiltersByZone;
    use WithPagination;

    public string $q = '';

    // La búsqueda va cambiando mientras se escribe: «plo», «plom», «plomero» son una sola fila.
    #[Locked]
    public ?int $searchEventId = null;

    #[Locked]
    public string $searchEventTerm = '';

    protected $queryString = [
        'q' => ['except' => ''],
        'zona' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->q = trim($this->q);
        $this->initialiseZone();
        $this->recordSearch();
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function updatedQ(): void
    {
        $this->recordSearch();
    }

    public function clearFilters(): void
    {
        $this->q = '';
        $this->zona = '';
        $this->updatedZona();
    }

    private function recordSearch(): void
    {
        $term = Activity::normalizeTerm($this->q);

        // Dos letras no dicen qué buscaba, y borrar letras no es una búsqueda nueva.
        if (mb_strlen($term) < 3 || ($this->searchEventId !== null && str_starts_with($this->searchEventTerm, $term))) {
            return;
        }

        try {
            $results = Service::query()->active()->search($this->q)->inZone($this->zona)->count();

            if ($this->searchEventId !== null && str_starts_with($term, $this->searchEventTerm)) {
                ActivityEvent::whereKey($this->searchEventId)->update(['term' => $term, 'results' => $results]);
            } else {
                $this->searchEventId = Activity::record(ActivityEvent::SEARCH, ['term' => $term, 'results' => $results])?->id;
            }

            $this->searchEventTerm = $term;
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function render()
    {
        $services = Service::query()
            ->active()
            ->with(['category:id,name,slug', 'user:id,name,utype', 'user.serviceProvider'])
            ->withAvg('reviews', 'rating')
            ->search($this->q)
            ->inZone($this->zona)
            ->orderByDesc('featured')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.service.search-component', [
            'services' => $services,
            'zones' => config('marketplace.zones'),
        ])->layout('layouts.base')->title('Buscar servicios');
    }
}
