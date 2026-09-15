<?php

namespace App\Http\Livewire\Sprovider;

use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditSproviderProfileComponent extends Component
{
    use WithFileUploads;
    public $service_provider_id;
    public $image;
    public $about;
    public $city;
    public $service_category_id;
    public $service_location;

    public $newimage;

    public function mount()
    {
        $sprovider = ServiceProvider::where('user_id', Auth::user()->id)->first();

        // Check if $sprovider is null
        if ($sprovider) {
            $this->service_provider_id = $sprovider->id;
            $this->image = $sprovider->image;
            $this->about = $sprovider->about;
            $this->city = $sprovider->city;
            $this->service_category_id = $sprovider->service_category_id;
            $this->service_location = $sprovider->service_location;
        } else {
            // Handle the case where no ServiceProvider record exists for the user
            // You can initialize default values or redirect the user
            $this->service_provider_id = null;
            $this->image = null;
            $this->about = null;
            $this->city = null;
            $this->service_category_id = null;
            $this->service_location = null;
        }
    }

    public function updateProfile()
    {
        $sprovider = ServiceProvider::where('user_id', Auth::user()->id)->first();

        // Check if $sprovider is null
        if (!$sprovider) {
            session()->flash('error', 'No service provider record found for this user.');
            return;
        }

        if ($this->newimage) {
            $imageName = Carbon::now()->timestamp . '.' . $this->newimage->extension();
            $this->newimage->storeAs('sproviders', $imageName);
            $sprovider->image = $imageName;
        }

        $sprovider->about = $this->about;
        $sprovider->city = $this->city;
        $sprovider->service_category_id = $this->service_category_id;
        $sprovider->service_location = $this->service_location;
        $sprovider->save();

        session()->flash('message', 'Profile has been updated successfully!');
    }

    public function render()
    {
        $scategories = ServiceCategory::all();
        return view('livewire.sprovider.edit-sprovider-profile-component', ['scategories' => $scategories])->layout('layouts.base');
    }
}