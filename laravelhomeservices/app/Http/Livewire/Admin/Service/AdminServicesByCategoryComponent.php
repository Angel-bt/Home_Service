<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Service;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ServiceAssetCleaner;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AdminServicesByCategoryComponent extends Component
{
    use \App\Http\Livewire\Concerns\ManagesServicesAsAdmin;
    use WithPagination;
    public $category_slug;

    public function mount($category_slug)
    {
        $this->category_slug = $category_slug;
    }



    public function render()
    {
        $category = ServiceCategory::where('slug', $this->category_slug)->firstOrFail();
        $services = Service::query()->with(['category:id,name,slug', 'user:id,name,utype,suspended_at'])->where('service_category_id', $category->id)->latest()->paginate(10);
        return view('livewire.admin.service.admin-services-by-category-component', ['category_name' => $category->name, 'services' => $services])->layout('layouts.base')->title($category->name);
    }
}
