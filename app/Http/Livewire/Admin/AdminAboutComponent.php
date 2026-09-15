<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;

class AdminAboutComponent extends Component
{
    public function render()
    {
        return view('livewire.admin.admin-about-component')->layout('layouts.base');
    }
}
