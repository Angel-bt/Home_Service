{{-- Cuando una acción de Livewire falla (sin internet, sesión vencida, error del servidor) explica qué pasó en vez de
     mostrar el aviso en inglés de Livewire. Con APP_DEBUG se deja la pantalla de error normal para poder depurar. --}}
<div x-data="{ message: null, timer: null, show(text) { this.message = text; clearTimeout(this.timer); this.timer = setTimeout(() => this.message = null, 9000); } }"
     @ui-error.window="show($event.detail)" x-cloak x-show="message" x-transition
     class="fixed bottom-6 left-1/2 z-[85] flex w-[calc(100%-2rem)] max-w-md -translate-x-1/2 items-start gap-3 rounded-2xl bg-red-900 px-5 py-4 text-[15px] text-white shadow-2xl" role="alert">
    <x-site.icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-red-200" />
    <span class="flex-1" x-text="message"></span>
    <button type="button" @click="message = null" class="text-white/60 hover:text-white" aria-label="Cerrar aviso"><x-site.icon name="close" class="h-5 w-5" /></button>
</div>
<script>
    document.addEventListener('livewire:init', function () {
        var debug = @json((bool) config('app.debug'));
        var help = @json('Intenta de nuevo. Si sigue pasando, escríbenos por WhatsApp al ' . config('marketplace.contact_phone') . '.');
        var submitted = false;
        document.addEventListener('submit', function () { submitted = true; });
        function reveal(el) {
            var top = el.getBoundingClientRect().top;
            if (top < 90 || top > window.innerHeight - 90) window.scrollTo({ top: window.scrollY + top - 120, behavior: 'smooth' });
        }
        Livewire.hook('commit', function (commit) {
            commit.succeed(function () {
                setTimeout(function () {
                    var firstError = submitted ? document.querySelector('main .ui-error') : null;
                    var notice = document.querySelector('main [data-flash]');
                    if (firstError || notice) reveal(firstError || notice);
                    submitted = false;
                }, 60);
            });
        });
        Livewire.hook('request', function (request) {
            request.fail(function (failure) {
                // La página estuvo abierta mucho tiempo: al recargar vuelve a funcionar.
                if (failure.status === 419) { failure.preventDefault(); window.location.reload(); return; }
                if (debug) return;
                failure.preventDefault();
                var message = {
                    403: 'No tienes permiso para hacer esto.',
                    413: 'El archivo pesa demasiado. Prueba con uno más liviano.',
                    429: 'Hiciste muchos intentos seguidos. Espera un minuto y prueba otra vez.',
                }[failure.status] || (failure.status ? 'Algo salió mal y no se guardó. ' + help : 'Parece que no hay internet. Revisa tu conexión e intenta de nuevo.');
                window.dispatchEvent(new CustomEvent('ui-error', { detail: message }));
            });
        });
    });
</script>
