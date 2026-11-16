<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Support\AdminLog;
use App\Support\Content;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Textos legales: términos, privacidad, resumen del registro y permiso de publicación.
 * «Guardar corrección» cambia el texto sin molestar a nadie (erratas, datos). «Publicar nueva
 * versión» además cambia la versión vigente: todas las cuentas aceptan de nuevo al entrar.
 */
class AdminLegalComponent extends Component
{
    /** clave en SiteSettings::TEXTS => [título, explicación, formato] */
    public const DOCS = [
        'legal_terms' => ['Términos de uso', 'La página «Términos». Es lo que acepta cada persona al crear su cuenta.', 'markdown'],
        'legal_privacy' => ['Política de privacidad', 'La página «Privacidad»: qué datos se guardan, para qué y cómo pedir que se borren.', 'markdown'],
        'legal_summary_all' => ['Resumen al registrarse', 'Lo que todos leen antes de crear la cuenta. Cada bloque: un título en **negrita** y, en la línea siguiente, el texto.', 'markdown'],
        'legal_summary_provider' => ['Resumen: solo proveedores', 'Se agrega al resumen cuando la persona elige «Ofrezco servicios».', 'markdown'],
        'legal_summary_customer' => ['Resumen: solo clientes', 'Se agrega al resumen cuando la persona elige «Busco servicios».', 'markdown'],
        'legal_consent' => ['Permiso de publicación', 'La casilla que marca un proveedor para que su perfil se publique. Sin marcarla no puede registrarse.', 'text'],
    ];

    public string $doc = 'legal_terms';

    /** @var array<string, string> */
    public array $texts = [];

    public bool $preview = false;

    public function mount(): void
    {
        foreach (array_keys(self::DOCS) as $key) {
            $this->texts[$key] = (string) SiteSettings::value($key);
        }
    }

    public function show(string $doc): void
    {
        abort_unless(array_key_exists($doc, self::DOCS), 404);

        $this->doc = $doc;
        $this->preview = false;
    }

    public function restoreOriginal(): void
    {
        $defaults = require config_path('content.php');
        $configKey = substr(SiteSettings::TEXTS[$this->doc], strlen('content.'));
        $this->texts[$this->doc] = (string) data_get($defaults, $configKey);
        session()->flash('message', 'Se cargó el texto original. Todavía no está guardado: revisa y guarda si quieres usarlo.');
    }

    public function saveCorrection(): void
    {
        $this->authorizeAdmin();
        $this->validateTexts();

        SiteSettings::save($this->texts);
        AdminLog::record('Corrigió los textos legales', self::DOCS[$this->doc][0]);
        session()->flash('message', 'Corrección guardada. Nadie tiene que volver a aceptar.');
    }

    public function publishVersion(): void
    {
        $this->authorizeAdmin();
        $this->validateTexts();

        $version = $this->nextVersion((string) config('marketplace.terms_version'));
        SiteSettings::save($this->texts + ['terms_version' => $version]);
        AdminLog::record('Publicó una nueva versión de los términos', $version);
        session()->flash('message', 'Nueva versión publicada (' . Content::versionDate($version) . '). Cada cuenta la aceptará la próxima vez que entre.');
    }

    public function render()
    {
        $text = $this->texts[$this->doc] ?? '';

        return view('livewire.admin.admin-legal-component', [
            'docs' => self::DOCS,
            'current' => self::DOCS[$this->doc],
            'rendered' => self::DOCS[$this->doc][2] === 'markdown' ? Content::markdown($text) : e(Content::fill($text)),
            'version' => Content::versionDate((string) config('marketplace.terms_version')),
            'placeholders' => Content::PLACEHOLDERS,
        ])->layout('layouts.base')->title('Textos legales');
    }

    private function validateTexts(): void
    {
        $rules = [];
        $attributes = [];
        foreach (self::DOCS as $key => [$title]) {
            $rules['texts.' . $key] = ['required', 'string', 'max:' . ($key === 'legal_consent' ? 600 : 30000)];
            $attributes['texts.' . $key] = mb_strtolower($title);
        }

        $this->validate($rules, ['texts.*.required' => 'Este texto no puede quedar vacío.'], $attributes);
    }

    /**
     * La fecha de hoy; si ya hubo una versión hoy, la siguiente («2026-11-13-2»).
     */
    private function nextVersion(string $current): string
    {
        $today = now()->format('Y-m-d');

        if (! str_starts_with($current, $today)) {
            return $today;
        }

        $number = (int) (substr($current, 11) ?: 1);

        return $today . '-' . ($number + 1);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->utype === 'ADM', 403);
    }
}
