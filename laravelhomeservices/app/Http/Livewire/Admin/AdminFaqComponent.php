<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Models\Faq;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Preguntas frecuentes: agregar, editar, ordenar y quitar, sin tocar código.
 */
class AdminFaqComponent extends Component
{
    public ?int $editingId = null;

    public string $group = 'Para clientes';

    public string $question = '';

    public string $answer = '';

    public function create(string $group): void
    {
        $this->resetErrorBag();
        $this->editingId = 0;
        $this->group = in_array($group, Faq::GROUPS, true) ? $group : Faq::GROUPS[0];
        $this->question = '';
        $this->answer = '';
    }

    public function edit(int $faqId): void
    {
        $this->resetErrorBag();
        $faq = Faq::findOrFail($faqId);
        $this->editingId = $faq->id;
        $this->group = $faq->group;
        $this->question = $faq->question;
        $this->answer = $faq->answer;
    }

    public function cancel(): void
    {
        $this->editingId = null;
    }

    public function save(): void
    {
        $this->authorizeAdmin();

        $data = $this->validate([
            'group' => ['required', Rule::in(Faq::GROUPS)],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:3000'],
        ], [], ['group' => 'la sección', 'question' => 'la pregunta', 'answer' => 'la respuesta']);

        if ($this->editingId) {
            Faq::findOrFail($this->editingId)->update($data);
        } else {
            Faq::create($data + ['position' => (int) Faq::max('position') + 1]);
        }

        $this->editingId = null;
        session()->flash('message', 'Pregunta guardada. Ya se ve en la página de preguntas frecuentes.');
    }

    public function move(int $faqId, int $direction): void
    {
        $this->authorizeAdmin();

        $faq = Faq::findOrFail($faqId);
        $siblings = Faq::where('group', $faq->group)->orderBy('position')->orderBy('id')->get()->values();
        $index = $siblings->search(fn (Faq $item) => $item->id === $faq->id);
        $target = $siblings->get($index + ($direction < 0 ? -1 : 1));

        if ($target === null) {
            return;
        }

        // Reasigna posiciones correlativas y cambia las dos de lugar.
        $siblings->each(fn (Faq $item, int $i) => $item->position = $i);
        [$faq->position, $target->position] = [$target->position, $faq->position];
        $siblings->each->save();
    }

    public function delete(int $faqId): void
    {
        $this->authorizeAdmin();

        Faq::whereKey($faqId)->delete();
        session()->flash('message', 'Pregunta eliminada.');
    }

    public function render()
    {
        return view('livewire.admin.admin-faq-component', [
            'groups' => Faq::GROUPS,
            'faqs' => Faq::orderBy('position')->orderBy('id')->get()->groupBy('group'),
            'placeholders' => Faq::PLACEHOLDERS,
        ])->layout('layouts.base')->title('Preguntas frecuentes');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->utype === 'ADM', 403);
    }
}
