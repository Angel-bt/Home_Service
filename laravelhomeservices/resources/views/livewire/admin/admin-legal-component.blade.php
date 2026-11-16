<div>
    <x-ui.panel title="Textos" accent="legales" help="Términos, privacidad y lo que acepta cada persona al registrarse. Antes de un cambio de fondo, que lo revise un abogado.">
        <x-slot:actions>
            <a href="{{ route('home.terms') }}" target="_blank" class="ui-btn ui-btn-secondary"><x-site.icon name="eye" class="h-4 w-4" /> Ver términos</a>
        </x-slot:actions>

        <div class="space-y-6">
            <p class="rounded-2xl bg-humo-100 px-5 py-4 text-[15px] text-grafito-600">Versión vigente: <span class="font-medium text-grafito">desde el {{ $version }}</span>. Si publicas una nueva, cada cuenta la aceptará la próxima vez que entre.</p>

            <div class="-mx-5 flex gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0" role="group" aria-label="Elegir texto">
                @foreach ($docs as $key => [$title])
                    <button type="button" wire:click="show('{{ $key }}')" @if ($doc === $key) aria-pressed="true" @endif
                            class="shrink-0 rounded-full border px-4 py-2 text-[14px] transition {{ $doc === $key ? 'border-noche-900 bg-noche-900 text-white' : 'border-humo-300 bg-white text-grafito-600 hover:border-grafito-400' }}">
                        {{ $title }}
                    </button>
                @endforeach
            </div>

            <section class="ui-card ui-card-pad" wire:key="doc-{{ $doc }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="lp-title text-2xl">{{ $current[0] }}</h2>
                        <p class="mt-1 max-w-2xl text-[14px] text-grafito-500">{{ $current[1] }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click="$toggle('preview')" class="ui-btn ui-btn-secondary ui-btn-sm">{{ $preview ? 'Volver a editar' : 'Vista previa' }}</button>
                        <button type="button" wire:click="restoreOriginal" wire:confirm="¿Cargar el texto original? Lo que escribiste en este texto se reemplaza (todavía no se guarda)." class="ui-btn ui-btn-ghost ui-btn-sm">Texto original</button>
                    </div>
                </div>

                @if ($preview)
                    <div class="ui-prose mt-6 rounded-2xl border border-humo-200 bg-white p-6">{{ $rendered }}</div>
                @else
                    <label for="legal-text" class="sr-only">{{ $current[0] }}</label>
                    <textarea id="legal-text" rows="{{ $current[2] === 'text' ? 4 : 22 }}" wire:model="texts.{{ $doc }}" class="ui-input mt-6 font-mono text-[14px] leading-relaxed @error('texts.' . $doc) ui-input-invalid @enderror"></textarea>
                    @error('texts.' . $doc)
                        <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
                    @enderror
                    @if ($current[2] === 'markdown')
                        <details class="mt-3 text-[13px] text-grafito-500">
                            <summary class="cursor-pointer">Cómo dar formato</summary>
                            <ul class="mt-2 space-y-1">
                                <li><code class="rounded bg-humo-200 px-1">## Título</code> para un título · <code class="rounded bg-humo-200 px-1">**texto**</code> para negrita</li>
                                <li><code class="rounded bg-humo-200 px-1">- algo</code> al empezar la línea para una lista · una línea en blanco separa párrafos</li>
                                <li>Los correos se vuelven enlaces solos. Datos que se actualizan solos:
                                    @foreach ($placeholders as $code => $meaning)<code class="rounded bg-humo-200 px-1">{{ $code }}</code> {{ $meaning }}@if (! $loop->last), @endif @endforeach
                                </li>
                            </ul>
                        </details>
                    @endif
                @endif
            </section>

            <div class="flex flex-col gap-3 rounded-2xl border border-humo-300 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[14px] text-grafito-500">Se guardan todos los textos de esta página a la vez.</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="saveCorrection" class="ui-btn ui-btn-secondary">Guardar corrección</button>
                    <button type="button" wire:click="publishVersion" wire:confirm="¿Publicar una nueva versión? Todas las cuentas tendrán que aceptarla la próxima vez que entren. Úsalo solo para cambios de fondo." class="ui-btn ui-btn-primary">Publicar nueva versión</button>
                </div>
            </div>
        </div>
    </x-ui.panel>
</div>
