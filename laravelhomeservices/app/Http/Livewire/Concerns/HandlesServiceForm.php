<?php

declare(strict_types=1);

namespace App\Http\Livewire\Concerns;

use App\Models\Service;
use App\Services\ServiceAssetCleaner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Shared fields, validation and persistence for the service create/edit forms
 * used by administrators and service providers.
 */
trait HandlesServiceForm
{
    public $service_id;
    public $name;
    public $slug;
    public $tagline;
    public $service_category_id;
    public $price;
    public $discount;
    public $discount_type;
    public $description;
    public $inclusion;
    public $exclusion;

    // New uploads (create) and current file names (edit).
    public $image;
    public $thumbnail;
    public $newimage;
    public $newthumbnail;

    public function generateSlug(): void
    {
        // Dos proveedores pueden llamar igual a su servicio: el enlace se numera solo para no mostrarles un error.
        $base = Str::slug((string) $this->name, '-');
        $slug = $base;

        for ($n = 2; $base !== '' && Service::where('slug', $slug)->when($this->service_id, fn ($query) => $query->whereKeyNot($this->service_id))->exists(); $n++) {
            $slug = $base . '-' . $n;
        }

        $this->slug = $slug;
    }

    public function updated($field): void
    {
        $this->validateOnly($field, $this->serviceRules());
    }

    /**
     * @return array<string, mixed>
     */
    protected function serviceRules(): array
    {
        // El enlace sale del nombre: se crea solo y no se le pide a nadie.
        if (blank($this->slug)) {
            $this->generateSlug();
        }

        $creating = $this->service_id === null;
        $image = ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096'];

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'slug' => [blank($this->name) ? 'nullable' : 'required', 'alpha_dash', 'max:150', Rule::unique('services', 'slug')->ignore($this->service_id)],
            'tagline' => ['required', 'string', 'max:255'],
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'price' => ['required', 'numeric', 'min:0', 'max:100000'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'required_with:discount', 'in:fixed,percent'],
            'description' => ['required', 'string'],
            'inclusion' => ['required', 'string'],
            'exclusion' => ['required', 'string'],
        ];

        if ($creating) {
            $rules['image'] = array_merge(['required'], $image);
            $rules['thumbnail'] = array_merge(['required'], $image);
        } else {
            $rules['newimage'] = array_merge(['nullable'], $image);
            $rules['newthumbnail'] = array_merge(['nullable'], $image);
        }

        return $rules;
    }

    protected function fillFromService(Service $service): void
    {
        $this->service_id = $service->id;
        $this->name = $service->name;
        $this->slug = $service->slug;
        $this->tagline = $service->tagline;
        $this->service_category_id = $service->service_category_id;
        $this->price = $service->price;
        $this->discount = $service->discount;
        $this->discount_type = $service->discount_type;
        $this->description = $service->description;
        $this->image = $service->image;
        $this->thumbnail = $service->thumbnail;
        $this->inclusion = str_replace('|', "\n", (string) $service->inclusion);
        $this->exclusion = str_replace('|', "\n", (string) $service->exclusion);
    }

    /**
     * Copy the validated form into the service, store uploads and save it.
     */
    protected function persistService(Service $service): Service
    {
        $service->name = $this->name;
        $service->slug = $this->slug;
        $service->tagline = $this->tagline;
        $service->service_category_id = $this->service_category_id;
        $service->price = $this->price;
        $service->discount = $this->discount === '' ? null : $this->discount;
        $service->discount_type = $this->discount_type === '' || $service->discount === null ? null : $this->discount_type;
        $service->description = $this->description;
        $service->inclusion = $this->joinLines($this->inclusion);
        $service->exclusion = $this->joinLines($this->exclusion);

        $cleaner = app(ServiceAssetCleaner::class);
        $thumbnail = $service->exists ? $this->newthumbnail : $this->thumbnail;
        $image = $service->exists ? $this->newimage : $this->image;

        if ($thumbnail instanceof UploadedFile) {
            $cleaner->deleteFile('images/services/thumbnails', $service->thumbnail);
            $service->thumbnail = $this->storeServiceUpload($thumbnail, 'services/thumbnails');
        }

        if ($image instanceof UploadedFile) {
            $cleaner->deleteFile('images/services', $service->image);
            $service->image = $this->storeServiceUpload($image, 'services');
        }

        $service->save();

        return $service;
    }

    private function storeServiceUpload(UploadedFile $file, string $directory): string
    {
        $fileName = now()->timestamp . '_' . Str::random(8) . '.' . $file->extension();
        // Disco explícito: sin él, Livewire usa su disco de subidas temporales.
        $file->storeAs($directory, $fileName, config('filesystems.default'));

        return $fileName;
    }

    private function joinLines(?string $text): string
    {
        $lines = array_filter(array_map('trim', preg_split('/\R/', trim((string) $text)) ?: []));

        return implode('|', $lines);
    }
}
