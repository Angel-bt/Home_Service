<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Models\ProviderZone;
use App\Models\ServiceRequest;
use App\Support\SiteSettings;
use App\Support\WhatsApp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Ajustes del sitio: lo que antes había que cambiar en el código o en las variables de entorno.
 */
class AdminSettingsComponent extends Component
{
    /** @var array<string, mixed> */
    public array $values = [];

    /** @var array<string, string> slug => nombre */
    public array $zones = [];

    public string $mainZone = '';

    public string $newZone = '';

    public function mount(): void
    {
        foreach (array_keys(SiteSettings::FIELDS) as $key) {
            $this->values[$key] = SiteSettings::value($key);
        }

        $this->zones = config('marketplace.zones');
        $this->mainZone = (string) config('marketplace.main_zone');
    }

    public function save(): void
    {
        $this->authorizeAdmin();

        $rules = [];
        $attributes = [];
        foreach (SiteSettings::FIELDS as $key => [, , $label, , , $fieldRules]) {
            $rules['values.' . $key] = $fieldRules;
            $attributes['values.' . $key] = mb_strtolower($label);
        }
        $rules['values.contact_phone'][] = function (string $attribute, mixed $value, \Closure $fail): void {
            if (WhatsApp::normalize((string) $value) === null) {
                $fail('Escribe un celular válido, por ejemplo 0991234567.');
            }
        };
        $this->validate($rules, [], $attributes);

        SiteSettings::save($this->values);
        session()->flash('message', 'Ajustes guardados. Ya se ven en todo el sitio.');
    }

    public function saveZones(): void
    {
        $this->authorizeAdmin();

        $this->validate([
            'zones' => ['required', 'array', 'min:1'],
            'zones.*' => ['required', 'string', 'max:40'],
            'mainZone' => ['required', 'in:' . implode(',', array_keys($this->zones))],
        ], ['zones.*.required' => 'Cada zona necesita un nombre.'], ['zones.*' => 'el nombre de la zona', 'mainZone' => 'la zona principal']);

        $zones = array_map(fn ($label) => trim((string) $label), $this->zones);
        SiteSettings::save([SiteSettings::ZONES_KEY => $zones, SiteSettings::MAIN_ZONE_KEY => $this->mainZone]);
        session()->flash('message', 'Zonas guardadas.');
    }

    public function addZone(): void
    {
        $this->authorizeAdmin();

        $this->validate(['newZone' => ['required', 'string', 'max:40']], [], ['newZone' => 'el nombre de la zona']);

        $slug = Str::slug($this->newZone);
        if ($slug === '' || array_key_exists($slug, $this->zones)) {
            $this->addError('newZone', 'Ya hay una zona con ese nombre.');

            return;
        }

        $this->zones[$slug] = trim($this->newZone);
        $this->newZone = '';
        $this->saveZones();
    }

    public function removeZone(string $slug): void
    {
        $this->authorizeAdmin();

        if (count($this->zones) <= 1 || $slug === $this->mainZone) {
            session()->flash('error', 'No puedes quitar la zona principal ni la última zona.');

            return;
        }

        $inUse = ProviderZone::where('zone', $slug)->count() + ServiceRequest::where('zone', $slug)->count();
        if ($inUse > 0) {
            session()->flash('error', 'No se puede quitar «' . $this->zones[$slug] . '»: la usan ' . $inUse . ' proveedores o solicitudes. Puedes cambiarle el nombre.');

            return;
        }

        unset($this->zones[$slug]);
        $this->saveZones();
    }

    public function render()
    {
        $fields = [];
        foreach (SiteSettings::FIELDS as $key => [$group, , $label, $hint, $type]) {
            $fields[$group][$key] = compact('label', 'hint', 'type');
        }

        return view('livewire.admin.admin-settings-component', [
            'groups' => SiteSettings::GROUPS,
            'fields' => $fields,
        ])->layout('layouts.base')->title('Ajustes');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->utype === 'ADM', 403);
    }
}
