<?php

declare(strict_types=1);

namespace App\Http\Livewire\Service;

use App\Http\Livewire\Concerns\FiltersByZone;
use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

class SearchComponent extends Component
{
    use FiltersByZone;
    use WithPagination;

    public string $q = '';

    protected $queryString = [
        'q' => ['except' => ''],
        'zona' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->q = trim($this->q);
        $this->initialiseZone();
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->q = '';
        $this->zona = '';
        $this->updatedZona();
    }

    public function render()
    {
        $services = Service::query()
            ->active()
            ->with(['category:id,name,slug', 'user:id,name,utype'])
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
