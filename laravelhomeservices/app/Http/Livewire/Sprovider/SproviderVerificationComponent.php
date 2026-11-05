<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sprovider;

use App\Http\Livewire\Concerns\ThrottlesActions;
use App\Models\ProviderDocument;
use App\Models\ServiceProvider;
use App\Services\ProviderVerification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * El proveedor entrega documentos por voluntad propia, uno a la vez, y puede retirarlos.
 */
class SproviderVerificationComponent extends Component
{
    use ThrottlesActions;
    use WithFileUploads;

    public ?string $uploading = null;

    public $document;

    public bool $consent = false;

    public function startUpload(string $type): void
    {
        abort_unless(array_key_exists($type, ProviderDocument::TYPES), 404);

        $this->resetErrorBag();
        $this->reset(['document', 'consent']);
        $this->uploading = $type;
    }

    public function cancelUpload(): void
    {
        $this->resetErrorBag();
        $this->reset(['uploading', 'document', 'consent']);
    }

    public function submit(ProviderVerification $verification): void
    {
        $this->validate([
            'uploading' => ['required', Rule::in(array_keys(ProviderDocument::TYPES))],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'consent' => ['accepted'],
        ], [
            'document.required' => 'Elige la foto o el PDF del documento.',
            'consent.accepted' => 'Marca la casilla para autorizarnos a revisarlo. Sin tu permiso no podemos hacerlo.',
        ], ['document' => 'el documento']);

        if (! $this->withinRateLimit('provider-document', (string) Auth::id(), 10, 3600, 'document')) {
            return;
        }

        $verification->submit($this->provider(), $this->uploading, $this->document);

        // La copia temporal de Livewire también es el documento: no debe quedar esperando la limpieza automática.
        try {
            $this->document->delete();
        } catch (\Throwable $exception) {
            report($exception);
        }

        $this->reset(['uploading', 'document', 'consent']);
        session()->flash('message', 'Recibimos tu documento. Te avisaremos por correo apenas lo revisemos.');
    }

    public function withdraw(string $type, ProviderVerification $verification): void
    {
        abort_unless(array_key_exists($type, ProviderDocument::TYPES), 404);

        $verification->withdraw($this->provider(), $type);
        session()->flash('message', 'Listo: borramos ese documento y el sello ya no aparece en tu perfil.');
    }

    public function render()
    {
        $provider = $this->provider();
        $latest = $provider->documents()->orderByDesc('id')->get()->unique('type')->keyBy('type');

        return view('livewire.sprovider.sprovider-verification-component', [
            'provider' => $provider,
            'types' => ProviderDocument::TYPES,
            'latest' => $latest,
            'portal' => config('marketplace.verification.background_portal'),
        ])->layout('layouts.base')->title('Verificarme');
    }

    private function provider(): ServiceProvider
    {
        return ServiceProvider::firstOrCreate(['user_id' => Auth::id()]);
    }
}
