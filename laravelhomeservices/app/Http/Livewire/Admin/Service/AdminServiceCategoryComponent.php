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
        // Segunda barrera: la ruta ya exige ADM, pero la acción también lo comprueba.
        abort_unless(\Illuminate\Support\Facades\Auth::user()?->utype === 'ADM', 403);

        $scategory = ServiceCategory::find($id);

        if ($scategory === null) {
            return;
        }

        app(ServiceAssetCleaner::class)->deleteFile('images/categories', $scategory->image);

        $scategory->delete();
        session()->flash('message', 'La categoría se eliminó.');
    }

    public function render()
    {
        $scategories = ServiceCategory::withCount('services')->orderBy('name')->paginate(10);
        return view('livewire.admin.service.admin-service-category-component', ['scategories' => $scategories])->layout('layouts.base')->title('Categorías');
    }
}
