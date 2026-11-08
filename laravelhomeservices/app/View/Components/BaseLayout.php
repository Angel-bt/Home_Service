<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BaseLayout extends Component
{
    // Título de la pestaña; el layout le agrega « · Malakplace».
    public function __construct(public ?string $title = null)
    {
    }

    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('layouts.base');
    }
}
