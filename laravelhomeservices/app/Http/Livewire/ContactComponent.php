<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Contact;
use App\Jobs\SendNewLeadNotification;
use Livewire\Component;

class ContactComponent extends Component
{
    public $name;
    public $email;
    public $phone;
    public $message;

    public function updated($fields)
    {
        $this->validateOnly($fields, [
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'message' => 'required'
        ]);
    }

    public function sendMessage(): void
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'message' => 'required'
        ]);

        $contact = new Contact();
        $contact->name = $this->name;
        $contact->email = $this->email;
        $contact->phone = $this->phone;
        $contact->message = $this->message;
        $contact->save();
        SendNewLeadNotification::dispatch($contact);

        session()->flash('message', 'Your message has been send successfully!');
    }

    public function render()
    {
        return view('livewire.contact-component')->layout('layouts.base');
    }
}
