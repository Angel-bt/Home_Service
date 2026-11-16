<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Models\ProviderZone;
use App\Models\ServiceRequest;
use App\Support\AdminLog;
use App\Support\SiteSettings;
use App\Support\WhatsApp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Ajustes del sitio: lo que antes había que cambiar en el código o en las variables de entorno.
 * La misma pantalla sirve para «Textos y aviso» (AdminContentComponent), con otros grupos.
 */
class AdminSettingsComponent extends Component
{
    /** Qué grupos de SiteSettings::GROUPS muestra esta pantalla. */
    protected string $page = 'ajustes';

    /** @var array<string, mixed> */
    public array $values = [];

    /** @var array<string, string> slug => nombre */
    public array $zones = [];

    public string $mainZone = '';

    public string $newZone = '';

    public function mount(): void
    {
        foreach (array_keys(SiteSettings::fieldsFor($this->page)) as $key) {
            $value = SiteSettings::value($key);
            $this->values[$key] = SiteSettings::FIELDS[$key][6] === 'bool' ? (bool) $value : $value;
        }

        $this->zones = config('marketplace.zones');
        $this->mainZone = (string) config('marketplace.main_zone');
    }

    public function save(): void
    {
        $this->authorizeAdmin();

        $fields = SiteSettings::fieldsFor($this->page);
        $rules = [];
        $attributes = [];
        foreach ($fields as $key => [, , $label, , , $fieldRules]) {
            $rules['values.' . $key] = $fieldRules;
            $attributes['values.' . $key] = mb_strtolower($label);
        }

        if (isset($fields['contact_phone'])) {
            $rules['values.contact_phone'][] = function (string $attribute, mixed $value, \Closure $fail): void {
                if (WhatsApp::normalize((string) $value) === null) {
                    $fail('Escribe un celular válido, por ejemplo 0991234567.');
                }
            };
        }
        if (isset($fields['pay_cash'])) {
            $rules['values.pay_cash'][] = function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $value && ! ($this->values['pay_transfer'] ?? false)) {
                    $fail('Deja al menos una forma de pago activa; si no, nadie podrá pasarse al plan Pro.');
                }
            };
        }
        $this->validate($rules, [], $attributes);

        // Un campo vacío no pasa por reglas propias: el aviso sin texto se revisa aparte.
        if (isset($fields['announcement_text']) && ($this->values['announcement_enabled'] ?? false) && blank($this->values['announcement_text'] ?? null)) {
            $this->addError('values.announcement_text', 'Escribe el texto del aviso, o desmarca «Mostrar el aviso».');

            return;
        }

        SiteSettings::save(array_intersect_key($this->values, $fields));
        AdminLog::record($this->page === 'textos' ? 'Cambió los textos del sitio' : 'Cambió los ajustes', null, implode(', ', array_map(fn ($key) => $fields[$key][2], array_keys($fields))));
        session()->flash('message', 'Guardado. Ya se ve en todo el sitio.');
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
        AdminLog::record('Cambió las zonas', null, implode(', ', $zones));
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
        foreach (SiteSettings::fieldsFor($this->page) as $key => [$group, , $label, $hint, $type]) {
            $fields[$group][$key] = compact('label', 'hint', 'type');
        }

        return view('livewire.admin.admin-settings-component', [
            'groups' => array_filter(SiteSettings::GROUPS, fn (array $group) => $group[2] === $this->page),
            'fields' => $fields,
            'page' => $this->page,
        ])->layout('layouts.base')->title($this->page === 'textos' ? 'Textos y aviso' : 'Ajustes');
    }

    protected function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->utype === 'ADM', 403);
    }
}
