{{-- Avisos tras una acción (session message/error) y resumen de errores de validación.
     Va dentro de cada vista Livewire (el layout no se vuelve a pintar en /livewire/update). <x-ui.panel> ya lo incluye. --}}
@php $errorCount = isset($errors) ? $errors->count() : 0; @endphp
@if (session('message') || session('error') || $errorCount > 0)
    <div {{ $attributes->merge(['class' => 'space-y-3']) }}>
        @if (session('message'))
            <div data-flash class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-[15px] text-emerald-900" role="status">
                <x-site.icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" />
                <p>{{ session('message') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div data-flash class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-[15px] text-red-900" role="alert">
                <x-site.icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-red-700" />
                <div>
                    <p class="font-medium">{{ session('error') }}</p>
                    <p class="mt-1 text-[14px] text-red-800/80">Si no sabes cómo seguir, escríbenos por WhatsApp al {{ config('marketplace.contact_phone') }} y te ayudamos.</p>
                </div>
            </div>
        @endif
        @if ($errorCount > 0)
            <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-[15px] text-amber-950" role="alert">
                <x-site.icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-700" />
                <p>
                    {{ $errorCount === 1 ? 'Falta corregir un dato. Está marcado en rojo,' : 'Faltan corregir ' . $errorCount . ' datos. Están marcados en rojo,' }}
                    con lo que hay que cambiar.
                </p>
            </div>
        @endif
    </div>
@endif
