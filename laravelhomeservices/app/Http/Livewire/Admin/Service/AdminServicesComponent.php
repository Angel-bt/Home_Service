<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Service;

use App\Http\Livewire\Concerns\ManagesServicesAsAdmin;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class AdminServicesComponent extends Component
{
    use ManagesServicesAsAdmin;
    use WithPagination;

    public string $q = '';

    public string $filter = '';

    protected $queryString = ['q' => ['except' => ''], 'filter' => ['except' => '']];

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $services = Service::query()
            ->with(['category:id,name,slug', 'user:id,name,utype,suspended_at'])
            ->search($this->q)
            ->when($this->filter === 'hidden', fn (Builder $query) => $query->where('status', false))
            ->when($this->filter === 'featured', fn (Builder $query) => $query->where('featured', true))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.service.admin-services-component', [
            'services' => $services,
            'counts' => [
                '' => Service::count(),
                'hidden' => Service::where('status', false)->count(),
                'featured' => Service::where('featured', true)->count(),
            ],
        ])->layout('layouts.base')->title('Servicios');
    }
}
