<div>
    <x-ui.panel title="Editar" accent="perfil" help="Solo son obligatorios tu WhatsApp y tus zonas. Lo demás ayuda a que los clientes confíen en ti.">
        <form wire:submit="updateProfile" class="space-y-6">
            <section class="ui-card ui-card-pad">
                <h2 class="lp-title text-2xl">Cómo te contactan</h2>
                <div class="mt-6 space-y-5">
                    <x-ui.field label="Tu número de WhatsApp" name="whatsapp" hint="Los clientes te escribirán a este número. Ej.: 0991234567">
                        <input type="tel" id="whatsapp" class="ui-input max-w-sm @error('whatsapp') ui-input-invalid @enderror" wire:model.blur="whatsapp" placeholder="0991234567" inputmode="tel">
                    </x-ui.field>
                    <fieldset>
                        <legend class="ui-label">¿En qué zonas trabajas?</legend>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach (config('marketplace.zones') as $slug => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-humo-300 bg-white px-4 py-3 text-[15px] transition has-[:checked]:border-noche-900 has-[:checked]:bg-humo-50">
                                    <input type="checkbox" class="ui-check" value="{{ $slug }}" wire:model="zones"> {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('zones')
                            <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
                        @enderror
                        <p class="ui-hint">Marca todas las que quieras. Solo te mostraremos a clientes de esas zonas.</p>
                    </fieldset>
                </div>
            </section>

            <section class="ui-card ui-card-pad">
                <h2 class="lp-title text-2xl">Quién eres</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Tu foto" name="newimage" for="photo" class="sm:col-span-2" hint="Una foto clara de tu cara o trabajando. JPG, PNG o WebP de hasta 4 MB." optional>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <img src="{{ $newimage ? $newimage->temporaryUrl() : asset('images/sproviders/' . ($image ?: 'default.jpg')) }}" alt="Tu foto" class="h-24 w-24 shrink-0 rounded-full object-cover">
                            <div class="flex-1">
                                <input type="file" id="photo" accept="image/*" class="ui-file" wire:model="newimage">
                                <p wire:loading wire:target="newimage" class="ui-hint">Subiendo foto…</p>
                            </div>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Cuéntanos sobre ti" name="about" for="about" class="sm:col-span-2" hint="Experiencia, especialidades y horarios, en dos líneas." optional>
                        <textarea id="about" maxlength="255" class="ui-input @error('about') ui-input-invalid @enderror" wire:model.blur="about" placeholder="Soy gasfitero con 10 años de experiencia. Atiendo de lunes a sábado."></textarea>
                    </x-ui.field>
                    <x-ui.field label="Oficio principal" name="service_category_id" optional>
                        <select id="service_category_id" class="ui-input @error('service_category_id') ui-input-invalid @enderror" wire:model="service_category_id">
                            <option value="">Elige un oficio</option>
                            @foreach ($scategories as $scategory)
                                <option value="{{ $scategory->id }}">{{ $scategory->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Ciudad o parroquia" name="city" optional>
                        <input type="text" id="city" class="ui-input @error('city') ui-input-invalid @enderror" wire:model.blur="city" placeholder="Malacatos">
                    </x-ui.field>
                    <x-ui.field label="Referencia de ubicación" name="service_location" class="sm:col-span-2" optional>
                        <input type="text" id="service_location" class="ui-input @error('service_location') ui-input-invalid @enderror" wire:model.blur="service_location" placeholder="Barrio, sector o referencia">
                    </x-ui.field>
                </div>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" wire:loading.attr="disabled" wire:target="updateProfile, newimage">
                    <span wire:loading.remove wire:target="updateProfile">Guardar perfil</span>
                    <span wire:loading wire:target="updateProfile">Guardando…</span>
                </button>
                <a href="{{ route('sprovider.dashboard') }}" class="ui-btn ui-btn-ghost ui-btn-lg">Volver al panel</a>
            </div>
        </form>
    </x-ui.panel>
</div>
