<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;

class AdminFaqComponent extends Component
{
    public function render()
    {
        return view('livewire.admin.admin-faq-component')->layout('layouts.base');
    }
}
