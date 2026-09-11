<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class VisitCounter extends Component
{
    public $visits;

    public function mount()
    {
         // Verifica si el usuario ya ha contado su visita en esta sesión
         if (!session()->has('visited')) {
            $this->incrementCounter();
            session()->put('visited', true); // Marca la sesión como visitada
        }
        
        $this->visits = Cache::get('visits', 0);
    }

    public function incrementCounter()
    {
        $visits = Cache::increment('visits', 1); // Incrementa la caché
        $this->visits = $visits;
    }

    public function render()
    {
        return view('livewire.visit-counter');
    }
}
