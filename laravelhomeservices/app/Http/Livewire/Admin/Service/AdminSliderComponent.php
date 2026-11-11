<?php

namespace App\Http\Livewire\Admin\Service;

use App\Models\Slider;
use App\Services\ServiceAssetCleaner;
use Livewire\Component;
use Livewire\WithPagination;

class AdminSliderComponent extends Component
{
    use WithPagination;

    public function deleteSlide($slide_id)
    {
        // Segunda barrera: la ruta ya exige ADM, pero la acción también lo comprueba.
        abort_unless(\Illuminate\Support\Facades\Auth::user()?->utype === 'ADM', 403);

        $slide = Slider::find($slide_id);

        if ($slide === null) {
            return;
        }

        app(ServiceAssetCleaner::class)->deleteFile('images/slider', $slide->image);
        $slide->delete();
        session()->flash('message', 'La diapositiva se eliminó.');
    }
    public function render()
    {
        $slides = Slider::paginate(10);
        return view('livewire.admin.service.admin-slider-component', ['slides' => $slides])->layout('layouts.base')->title('Imágenes de portada');
    }
}
