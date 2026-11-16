<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

/**
 * Textos de la portada, aviso destacado y lo que se ve en Google y al compartir.
 * Misma lógica que Ajustes, con los grupos de la pantalla 'textos'.
 */
class AdminContentComponent extends AdminSettingsComponent
{
    protected string $page = 'textos';
}
