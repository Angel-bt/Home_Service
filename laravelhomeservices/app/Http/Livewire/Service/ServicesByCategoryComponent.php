<?php

namespace App\Http\Livewire\Service;

use App\Http\Livewire\Concerns\FiltersByZone;
use App\Models\Service;
use App\Models\ServiceCategory;
use Livewire\Component;
use Livewire\WithPagination;

class ServicesByCategoryComponent extends Component
{
    use FiltersByZone;
    use WithPagination;

    public $category_slug;

    protected $queryString = ['zona' => ['except' => '']];

    public function mount($category_slug)
    {
        $this->category_slug = $category_slug;
        $this->initialiseZone();

        if (ServiceCategory::where('slug', $category_slug)->exists()) {
            \App\Support\Activity::record(\App\Models\ActivityEvent::CATEGORY_VIEW, ['term' => $category_slug]);
        }
    }

    public function render()
    {
        $scategory = ServiceCategory::where('slug', $this->category_slug)->firstOrFail();
        $services = Service::query()
            ->active()
            ->where('service_category_id', $scategory->id)
            ->with(['category:id,name,slug', 'user:id,name,utype', 'user.serviceProvider'])
            ->withAvg('reviews', 'rating')
            ->inZone($this->zona)
            ->orderByDesc('featured')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.service.services-by-category-component', ['scategory' => $scategory, 'services' => $services])->layout('layouts.base')->title($scategory->name);
    }
}
