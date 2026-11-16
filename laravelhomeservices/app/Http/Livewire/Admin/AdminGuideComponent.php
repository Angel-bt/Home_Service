<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use Livewire\Component;

/**
 * Guía del administrador: cómo hacer cada tarea desde el panel, sin tocar código.
 */
class AdminGuideComponent extends Component
{
    public function render()
    {
        return view('livewire.admin.admin-guide-component')->layout('layouts.base')->title('Guía del administrador');
    }
}
