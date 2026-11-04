<?php

namespace App\Http\Livewire\Admin\Service;

use App\Models\Slider;
use App\Services\ServiceAssetCleaner;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;

class AdminEditSlideComponent extends Component
{
    use WithFileUploads;
    public $slide_id;
    public $title;
    public $image;
    public $status;
    public $newimage;

    public function mount($slide_id)
    {
        $slide = Slider::findOrFail($slide_id);
        $this->slide_id = $slide->id;
        $this->title = $slide->title;
        $this->image = $slide->image;
        $this->status = $slide->status;
    }

    public function updated($fields)
    {
        $this->validateOnly($fields, [
            'title' => 'required'
        ]);

        if ($this->newimage) {
            $this->validateOnly($fields, [
                'newimage' => 'required|image|mimes:png,jpg,jpeg,webp|max:4096'
            ]);
        }
    }

    public function updateSlide()
    {
        $this->validate([
            'title' => 'required'
        ]);

        if ($this->newimage) {
            $this->validate([
                'newimage' => 'required|image|mimes:png,jpg,jpeg,webp|max:4096'
            ]);
        }

        $slide = Slider::findOrFail($this->slide_id);
        $slide->title = $this->title;

        if ($this->newimage) {
            app(ServiceAssetCleaner::class)->deleteFile('images/slider', $slide->image);
            $imageName = Carbon::now()->timestamp . '.' . $this->newimage->extension();
            \App\Support\Media::store($this->newimage, 'slider', $imageName);
            $slide->image = $imageName;
        }

        $slide->status = $this->status;
        $slide->save();

        session()->flash('message', 'La diapositiva se actualizó correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.service.admin-edit-slide-component')->layout('layouts.base');
    }
}
