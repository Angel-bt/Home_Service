<?php

namespace App\Http\Livewire\Admin\Service;

use App\Models\Slider;
use Carbon\Carbon;
use Illuminate\Database\DBAL\TimestampType;
use Livewire\Component;
use Livewire\WithFileUploads;

class AdminAddSlideComponent extends Component
{
    use WithFileUploads;
    public $title;
    public $image;
    public $status = 0;

    public function updated($fields)
    {
        $this->validateOnly($fields, [
            'title' => 'required',
            'image' => 'required|image|mimes:png,jpg,jpeg,webp|max:4096'
        ]);
    }

    public function addNewSlide()
    {
        $this->validate([
            'title' => 'required',
            'image' => 'required|image|mimes:png,jpg,jpeg,webp|max:4096'
        ]);

        $slide = new Slider();
        $slide->title = $this->title;

        $imageName = Carbon::now()->timestamp . '.' . $this->image->extension();
        \App\Support\Media::store($this->image, 'slider', $imageName);
        $slide->image = $imageName;

        $slide->status = $this->status;
        $slide->save();

        session()->flash('message', 'La diapositiva se creó correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.service.admin-add-slide-component')->layout('layouts.base');
    }
}
