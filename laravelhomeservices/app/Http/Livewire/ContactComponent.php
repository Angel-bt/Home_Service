<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Contact;
use App\Http\Livewire\Concerns\ThrottlesActions;
use App\Jobs\SendNewLeadNotification;
use Livewire\Component;

class ContactComponent extends Component
{
    use ThrottlesActions;

    public $name;
    public $email;
    public $phone;
    public $message;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:2000',
        ];
    }

    public function updated($fields)
    {
        $this->validateOnly($fields);
    }

    public function sendMessage(): void
    {
        $this->validate();

        if (! $this->withinRateLimit('contact', (string) request()->ip(), 5, 600, 'message')) {
            return;
        }

        $contact = new Contact();
        $contact->name = $this->name;
        $contact->email = $this->email;
        $contact->phone = $this->phone;
        $contact->message = $this->message;
        $contact->save();

        // Sin cola (QUEUE_CONNECTION=sync) el aviso sale ahora: si falla, el mensaje ya está guardado en el panel.
        try {
            SendNewLeadNotification::dispatch($contact);
        } catch (\Throwable $exception) {
            report($exception);
        }

        session()->flash('message', 'Tu mensaje fue enviado. Te responderemos pronto.');
    }

    public function render()
    {
        return view('livewire.contact-component')->layout('layouts.base')->title('Contacto');
    }
}
