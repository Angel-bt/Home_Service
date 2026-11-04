{{-- Lógica de <x-ui.photo-input>: achica la foto en el navegador antes de subirla (las del celular pesan más que el
     límite del servidor) y la sube con $wire.upload. Va antes de @livewireScripts para que Alpine ya la encuentre. --}}
<script>
    window.uiPhoto = function (options) {
        function loadImage(file) {
            return new Promise(function (resolve, reject) {
                var image = new Image();
                image.onload = function () { resolve(image); };
                image.onerror = reject;
                image.src = URL.createObjectURL(file);
            });
        }

        async function shrink(file, maxSide) {
            var image = await loadImage(file);
            var scale = Math.min(1, maxSide / Math.max(image.naturalWidth, image.naturalHeight));
            var canvas = document.createElement('canvas');
            canvas.width = Math.round(image.naturalWidth * scale);
            canvas.height = Math.round(image.naturalHeight * scale);
            var context = canvas.getContext('2d');
            context.fillStyle = '#fff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            URL.revokeObjectURL(image.src);
            var blob = await new Promise(function (resolve) { canvas.toBlob(resolve, 'image/jpeg', 0.82); });
            if (!blob) throw new Error('canvas');
            return new File([blob], 'foto.jpg', { type: 'image/jpeg' });
        }

        return {
            status: 'idle',
            progress: 0,
            preview: null,
            fileName: null,
            message: null,

            async pick(event) {
                var file = event.target.files[0];
                if (!file) return;
                this.message = null;
                this.preview = null;
                this.fileName = null;
                this.busy(true);

                try {
                    if (options.allowPdf && file.type === 'application/pdf') {
                        this.fileName = file.name;
                        await this.send(options.model, file, 0, 1);
                    } else if (file.type.indexOf('image/') !== 0 && !/\.(heic|heif)$/i.test(file.name)) {
                        this.fail(options.allowPdf ? 'Ese archivo no sirve. Elige una foto o un PDF.' : 'Ese archivo no es una foto. Elige una imagen.');
                    } else {
                        this.status = 'working';
                        var main = await shrink(file, options.maxSide || 1600);
                        this.preview = URL.createObjectURL(main);
                        if (options.thumb) {
                            await this.send(options.thumb, await shrink(file, 700), 0, 2);
                            await this.send(options.model, main, 1, 2);
                        } else {
                            await this.send(options.model, main, 0, 1);
                        }
                    }
                    if (this.status !== 'error') this.status = 'done';
                } catch (error) {
                    this.preview = null;
                    this.fail(error === 'upload'
                        ? 'No se pudo subir. Revisa tu conexión e inténtalo otra vez.'
                        : 'No pudimos leer esa foto. Prueba con otra, o con una captura de pantalla.');
                }

                event.target.value = '';
                this.busy(false);
            },

            send(model, file, index, total) {
                var self = this;
                this.status = 'uploading';
                return new Promise(function (resolve, reject) {
                    self.$wire.upload(model, file, resolve, function () { reject('upload'); }, function (event) {
                        self.progress = Math.round(((index + event.detail.progress / 100) / total) * 100);
                    });
                });
            },

            fail(text) {
                this.status = 'error';
                this.message = text;
            },

            // Mientras sube, el botón de guardar queda inactivo. La marca va en <html>: Livewire reescribe las clases del formulario al actualizarlo.
            busy(active) {
                var root = document.documentElement;
                var count = Number(root.dataset.uploads || 0) + (active ? 1 : -1);
                root.dataset.uploads = count;
                root.classList.toggle('ui-uploading', count > 0);
            },
        };
    };
</script>
