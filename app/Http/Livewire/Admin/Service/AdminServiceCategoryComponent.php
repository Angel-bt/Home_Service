<?php

namespace App\Http\Livewire\Admin\Service;

use App\Models\ServiceCategory;
use App\Services\ServiceAssetCleaner;
use Livewire\Component;
use Livewire\WithPagination;

class AdminServiceCategoryComponent extends Component
{
    use WithPagination;

    public function deleteServiceCategory($id)
    {
        $scategory = ServiceCategory::find($id);

        if ($scategory === null) {
            return;
        }

        app(ServiceAssetCleaner::class)->deleteFile('images/categories', $scategory->image);

        $scategory->delete();
        session()->flash('message', 'Category has been deleted successfully!');
    }

    public function render()
    {
        $scategories = ServiceCategory::paginate(10);
        return view('livewire.admin.service.admin-service-category-component', ['scategories' => $scategories])->layout('layouts.base');
    }
}
