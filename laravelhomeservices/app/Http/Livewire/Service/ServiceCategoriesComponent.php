<?php

namespace App\Http\Livewire\Service;

use App\Models\ServiceCategory;
use Livewire\Component;

class ServiceCategoriesComponent extends Component
{
    public function render()
    {
        // Solo cuentan los servicios publicados; los oficios con más servicios van primero.
        $scategories = ServiceCategory::withCount(['services' => fn ($query) => $query->where('status', true)])
            ->orderByDesc('services_count')
            ->orderBy('name')
            ->get();
        return view('livewire.service.service-categories-component', ['scategories' => $scategories])->layout('layouts.base')->title('Todos los oficios');
    }
}
