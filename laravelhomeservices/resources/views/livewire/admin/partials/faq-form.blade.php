{{-- Formulario de una pregunta frecuente (nueva o en edición) dentro de AdminFaqComponent. --}}
<form wire:submit="save" class="space-y-4">
    <x-ui.field label="Sección" name="group" for="{{ $key }}-group">
        <select id="{{ $key }}-group" class="ui-input" wire:model="group">
            @foreach ($groups as $groupOption)
                <option value="{{ $groupOption }}">{{ $groupOption }}</option>
            @endforeach
        </select>
    </x-ui.field>
    <x-ui.field label="Pregunta" name="question" for="{{ $key }}-question">
        <input type="text" id="{{ $key }}-question" class="ui-input @error('question') ui-input-invalid @enderror" wire:model="question" placeholder="Ej.: ¿Atienden los fines de semana?">
    </x-ui.field>
    <x-ui.field label="Respuesta" name="answer" for="{{ $key }}-answer">
        <textarea id="{{ $key }}-answer" rows="4" class="ui-input @error('answer') ui-input-invalid @enderror" wire:model="answer"></textarea>
    </x-ui.field>
    <details class="text-[13px] text-grafito-500">
        <summary class="cursor-pointer">Datos que se actualizan solos</summary>
        <p class="mt-2">Escríbelos tal cual y el sitio pone el valor actual de «Ajustes»:</p>
        <ul class="mt-1 space-y-0.5">
            @foreach ($placeholders as $code => $meaning)
                <li><code class="rounded bg-humo-200 px-1">{{ $code }}</code> {{ $meaning }}</li>
            @endforeach
        </ul>
    </details>
    <div class="flex flex-wrap gap-2">
        <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm">Guardar pregunta</button>
        <button type="button" wire:click="cancel" class="ui-btn ui-btn-ghost ui-btn-sm">Cancelar</button>
    </div>
</form>
