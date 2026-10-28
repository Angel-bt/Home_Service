<?php

declare(strict_types=1);

namespace App\Http\Livewire\Concerns;

/**
 * Zone filter for public listings. The chosen zone is remembered in the session
 * so it carries over between pages and pre-fills the request form.
 */
trait FiltersByZone
{
    public string $zona = '';

    protected function initialiseZone(): void
    {
        if ($this->zona === '') {
            $this->zona = (string) session('zone', '');
        }

        $this->rememberZone();
    }

    public function updatedZona(): void
    {
        $this->rememberZone();

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    private function rememberZone(): void
    {
        if (! array_key_exists($this->zona, config('marketplace.zones'))) {
            $this->zona = '';
        }

        if ($this->zona === '') {
            session()->forget('zone');
        } else {
            session(['zone' => $this->zona]);
        }
    }
}
