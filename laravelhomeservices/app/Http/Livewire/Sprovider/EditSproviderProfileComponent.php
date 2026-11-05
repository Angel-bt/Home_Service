<?php

namespace App\Http\Livewire\Sprovider;

use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Services\ServiceAssetCleaner;
use App\Support\WhatsApp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditSproviderProfileComponent extends Component
{
    use WithFileUploads;
    public $service_provider_id;
    public $image;
    public $about;
    public $experience_years;
    public $city;
    public $whatsapp;
    public $service_category_id;
    public $service_location;
    public $zones = [];

    public $newimage;

    public function mount()
    {
        // Los proveedores registrados antes de crear su perfil lo obtienen aquí.
        $sprovider = ServiceProvider::firstOrCreate(['user_id' => Auth::id()]);

        $this->service_provider_id = $sprovider->id;
        $this->image = $sprovider->image;
        $this->about = $sprovider->about;
        $this->experience_years = $sprovider->experience_years;
        $this->city = $sprovider->city;
        $this->whatsapp = $sprovider->whatsapp ?: Auth::user()->phone;
        $this->service_category_id = $sprovider->service_category_id;
        $this->service_location = $sprovider->service_location;
        $this->zones = $sprovider->zones()->pluck('zone')->all();
    }

    protected function rules()
    {
        return [
            'newimage' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'about' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'between:0,70'],
            'city' => ['nullable', 'string', 'max:100'],
            'whatsapp' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) {
                if (WhatsApp::normalize($value) === null) {
                    $fail('El número de WhatsApp no es válido (ej.: 0991234567).');
                }
            }],
            'service_category_id' => ['nullable', 'exists:service_categories,id'],
            'service_location' => ['nullable', 'string', 'max:255'],
            'zones' => ['required', 'array', 'min:1'],
            'zones.*' => [Rule::in(array_keys(config('marketplace.zones')))],
        ];
    }

    protected $messages = [
        'zones.required' => 'Elige al menos una zona donde trabajas.',
        'zones.min' => 'Elige al menos una zona donde trabajas.',
    ];

    public function updateProfile()
    {
        $this->validate();

        $sprovider = ServiceProvider::where('user_id', Auth::id())->firstOrFail();

        if ($this->newimage) {
            app(ServiceAssetCleaner::class)->deleteFile('images/sproviders', $sprovider->image);
            $imageName = now()->timestamp . '_' . Str::random(8) . '.' . $this->newimage->extension();
            \App\Support\Media::store($this->newimage, 'sproviders', $imageName);
            $sprovider->image = $imageName;
            $this->image = $imageName;
            $this->newimage = null;
        }

        $sprovider->about = $this->about;
        $sprovider->experience_years = $this->experience_years === '' ? null : $this->experience_years;
        $sprovider->city = $this->city;
        $sprovider->whatsapp = $this->whatsapp;
        $sprovider->service_category_id = $this->service_category_id ?: null;
        $sprovider->service_location = $this->service_location;
        $sprovider->save();
        $sprovider->syncZones($this->zones);

        session()->flash('message', 'Tu perfil se actualizó correctamente.');
    }

    public function render()
    {
        $scategories = ServiceCategory::orderBy('name')->get();
        return view('livewire.sprovider.edit-sprovider-profile-component', ['scategories' => $scategories])->layout('layouts.base');
    }
}
