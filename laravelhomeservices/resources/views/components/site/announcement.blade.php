{{-- Aviso destacado que la administración activa en «Textos y aviso». Aparece abajo en todas las páginas;
     cada persona puede cerrarlo y no vuelve a ver ese mismo texto (se recuerda en su navegador). --}}
@if (config('content.announcement.enabled') && filled(config('content.announcement.text')))
    @php
        $text = (string) config('content.announcement.text');
        $link = config('content.announcement.link');
        $key = 'aviso-' . substr(sha1($text . $link), 0, 12);
    @endphp
    <div x-data="{ open: (() => { try { return localStorage.getItem(@js($key)) !== '1'; } catch (e) { return true; } })() }"
         x-show="open" x-cloak role="status"
         class="fixed inset-x-3 bottom-3 z-[70] mx-auto flex max-w-2xl items-center gap-3 rounded-2xl bg-noche-900 px-5 py-4 text-[15px] text-white shadow-2xl sm:bottom-5">
        <x-site.icon name="info" class="h-5 w-5 shrink-0 text-oro-300" />
        <p class="min-w-0 flex-1">
            {{ \App\Support\Content::fill($text) }}
            @if ($link)
                <a href="{{ $link }}" class="ml-1 font-medium text-oro-300 underline underline-offset-4 hover:text-oro-200">Más información</a>
            @endif
        </p>
        <button type="button" class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-white/70 hover:bg-white/10 hover:text-white" aria-label="Cerrar aviso"
                @click="open = false; try { localStorage.setItem(@js($key), '1'); } catch (e) {}">
            <x-site.icon name="close" class="h-4 w-4" />
        </button>
    </div>
@endif
