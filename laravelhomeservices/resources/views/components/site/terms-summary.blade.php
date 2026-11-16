{{-- Lo esencial de los términos, en palabras simples, antes de crear una cuenta. Necesita `as` (CST|SVP) en el x-data del padre.
     Los textos se editan en /admin/legal. --}}
<div class="max-h-64 space-y-3 overflow-y-auto rounded-2xl border border-humo-300 bg-humo-50 p-4 text-[14px] leading-relaxed text-grafito-600 [&_p+p]:mt-3 [&_strong]:block [&_strong]:font-medium [&_strong]:text-grafito" tabindex="0" aria-label="Resumen de los términos">
    <div>{{ \App\Support\Content::markdown(config('content.legal.summary_all')) }}</div>
    <div x-show="as === 'SVP'" x-cloak>{{ \App\Support\Content::markdown(config('content.legal.summary_provider')) }}</div>
    <div x-show="as !== 'SVP'">{{ \App\Support\Content::markdown(config('content.legal.summary_customer')) }}</div>
</div>
