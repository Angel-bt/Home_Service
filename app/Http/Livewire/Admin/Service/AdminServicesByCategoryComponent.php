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
    use WithPagination;
    public $category_slug;

    public function mount($category_slug)
    {
        $this->category_slug = $category_slug;
    }

    public function deleteService($service_id)
    {
        abort_unless(Auth::check() && Auth::user()->utype === 'ADM', 403);

        $service = Service::find($service_id);

        if ($service === null) {
            return;
        }

        app(ServiceAssetCleaner::class)->deleteFiles($service);

        $service->delete();
        session()->flash('message', 'Service has been deleted successfully!');
    }


    public function render()
    {
        $category = ServiceCategory::where('slug', $this->category_slug)->first();
        $services = Service::query()->with(['category:id,name,slug', 'user:id,name'])->where('service_category_id', $category->id)->paginate(10);
        return view('livewire.admin.service.admin-services-by-category-component', ['category_name' => $category->name, 'services' => $services])->layout('layouts.base');
    }
}
