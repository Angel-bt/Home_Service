<?php

namespace App\Http\Livewire;

use Livewire\Component;

class FaqComponent extends Component
{
    public function render()
    {
        $grouped = \App\Models\Faq::orderBy('position')->orderBy('id')->get()->groupBy('group');
        $faqs = [];
        foreach (array_unique([...\App\Models\Faq::GROUPS, ...$grouped->keys()->all()]) as $group) {
            if ($grouped->has($group)) {
                $faqs[$group] = $grouped[$group]->mapWithKeys(fn (\App\Models\Faq $faq) => [$faq->question => $faq->renderedAnswer()])->all();
            }
        }

        return view('livewire.faq-component', ['faqs' => $faqs])->layout('layouts.base')->title('Preguntas frecuentes');
    }
}
