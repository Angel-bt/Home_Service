<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Para cuentas que aún no aceptaron la versión vigente de los términos (ver EnsureTermsAccepted).
 */
class AcceptTermsComponent extends Component
{
    public bool $terms = false;

    public bool $publication = false;

    public function accept()
    {
        $user = Auth::user();
        $isProvider = $user->utype === 'SVP';

        $this->validate([
            'terms' => ['accepted'],
            'publication' => $isProvider ? ['accepted'] : ['nullable'],
        ], [
            'terms.accepted' => 'Para seguir usando Malakplace debes aceptar los términos y la privacidad.',
            'publication.accepted' => 'Para ofrecer servicios debes aceptar que estos datos se publiquen en tu perfil. Si no estás de acuerdo, puedes eliminar tu cuenta.',
        ]);

        $user->acceptTerms($isProvider);

        return redirect()->intended(route($user->dashboardRoute()));
    }

    public function render()
    {
        return view('livewire.accept-terms-component')->layout('layouts.base')->title('Términos de uso');
    }
}
