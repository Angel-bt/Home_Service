<div>
    <x-ui.page title="Tu" accent="ubicación" eyebrow="Ubicación" lede="Busca tu dirección en el mapa o escríbela a mano. La usamos para mostrarte servicios cercanos." />

    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        <x-ui.flash class="mb-8" />
        <form wire:submit="changeLocation" class="grid gap-10 lg:grid-cols-[1.5fr_1fr]">
            <div>
                <label for="autocomplete" class="ui-label">Busca tu dirección</label>
                <input type="text" class="ui-input" id="autocomplete" name="location" wire:ignore placeholder="Ej.: Parque central de Malacatos">
                <div id="map" class="mt-4 h-[400px] overflow-hidden rounded-3xl bg-humo-200" wire:ignore></div>
                @if (! config('services.google_maps.key'))
                    <p class="mt-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-[15px] text-amber-950">
                        <x-site.icon name="info" class="mt-0.5 h-5 w-5 shrink-0" /> El mapa no está disponible por ahora. Llena tu dirección a mano en los campos de la derecha.
                    </p>
                @endif
            </div>
            <div class="ui-card ui-card-pad space-y-5 self-start">
                    <x-ui.field label="Número" for="street_number">
                        <input type="text" id="street_number" class="ui-input" wire:model="streetnumber">
                    </x-ui.field>
                    <x-ui.field label="Calle" for="route">
                        <input type="text" id="route" class="ui-input" wire:model="routes">
                    </x-ui.field>
                    <x-ui.field label="Ciudad" for="locality">
                        <input type="text" id="locality" class="ui-input" wire:model="city">
                    </x-ui.field>
                    <x-ui.field label="Provincia" for="administrative_area_level_1">
                        <input type="text" id="administrative_area_level_1" class="ui-input" wire:model="state">
                    </x-ui.field>
                    <x-ui.field label="País" for="country">
                        <input type="text" id="country" class="ui-input" wire:model="country">
                    </x-ui.field>
                    <x-ui.field label="Código postal" for="postal_code">
                        <input type="text" id="postal_code" class="ui-input" wire:model="zipcode">
                    </x-ui.field>
                <button type="submit" class="ui-btn ui-btn-primary w-full">Guardar ubicación</button>
            </div>
        </form>
    </section>
    @push('scripts')
        @if (config('services.google_maps.key'))
            <script>
                let map, marker, autocomplete;

                function initMap() {
                    // Inicializa el mapa
                    const initialLocation = { lat: -4.2190, lng: -79.2580 }; // Malacatos, Loja
                    map = new google.maps.Map(document.getElementById("map"), {
                        center: initialLocation,
                        zoom: 13,
                    });

                    // Agrega un marcador inicial
                    marker = new google.maps.Marker({
                        position: initialLocation,
                        map: map,
                        draggable: true,
                    });

                    // Autocompletar en el campo de búsqueda
                    autocomplete = new google.maps.places.Autocomplete(document.getElementById("autocomplete"));
                    autocomplete.bindTo("bounds", map);

                    // Escucha cuando se selecciona una ubicación
                    autocomplete.addListener("place_changed", () => {
                        const place = autocomplete.getPlace();

                        if (!place.geometry || !place.geometry.location) {
                            alert("No encontramos detalles de ese lugar. Prueba con otro o llena los campos a mano.");
                            return;
                        }

                        // Centra el mapa y mueve el marcador
                        map.setCenter(place.geometry.location);
                        map.setZoom(15);
                        marker.setPosition(place.geometry.location);

                        // Actualiza los campos del formulario
                        fillLocationFields(place);
                    });

                    // Escucha cuando se mueve el marcador manualmente
                    marker.addListener("dragend", () => {
                        const position = marker.getPosition();
                        const geocoder = new google.maps.Geocoder();
                        geocoder.geocode({ location: position }, (results, status) => {
                            if (status === "OK" && results[0]) {
                                fillLocationFields(results[0]);
                            }
                        });
                    });
                }

                function fillLocationFields(place) {
                    const components = place.address_components || [];
                    const fields = {
                        street_number: "short_name",
                        route: "long_name",
                        locality: "long_name",
                        administrative_area_level_1: "short_name",
                        country: "long_name",
                        postal_code: "short_name",
                    };

                    for (const component of components) {
                        const addressType = component.types[0];
                        if (fields[addressType]) {
                            const field = document.getElementById(addressType);
                            if (field) {
                                field.value = component[fields[addressType]];
                                // Notificar a Livewire para que wire:model reciba el valor.
                                field.dispatchEvent(new Event('input'));
                            }
                        }
                    }
                }
            </script>
            <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initMap" async defer></script>
        @endif
    @endpush
</div>
