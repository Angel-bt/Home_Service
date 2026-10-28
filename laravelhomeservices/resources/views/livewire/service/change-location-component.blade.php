<div>
    <div class="section-title-01 honmob">
        <div class="bg_parallax image_01_parallax"></div>
        <div class="opacy_bg_02">
            <div class="container">
                <h1>Cambiar ubicación</h1>
                <div class="crumbs">
                    <ul>
                        <li><a href="/">Inicio</a></li>
                        <li>/</li>
                        <li>Cambiar ubicación</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="content-central">
        <div class="semiboxshadow text-center">
            <img src="img/img-theme/shp.png" class="img-responsive" alt="">
        </div>
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row">
                        <form wire:submit.prevent='changeLocation'>
                            @if (Session::has('message'))
                                <div class="alert alert-success" role="alert">{{ Session::get('message') }}</div>
                            @endif
                            <div class="col-md-8">
                                <h3>Busca tu ubicación</h3>
                                <p class="lead">
                                </p>
                                <input type="text" class="form-control" id="autocomplete" name="location" wire:ignore
                                    placeholder="Buscar ubicación...">
                                <div id="map" style="height: 400px; margin-top: 20px;" wire:ignore></div>
                                @if (! config('services.google_maps.key'))
                                    <div class="alert alert-warning" role="alert" style="margin-top: 10px;">
                                        The map is unavailable (GOOGLE_MAPS_API_KEY is not set). You can still fill in your location manually.
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <aside class="addlocation">
                                    <h4>Tu ubicación<input type="submit" class="btn btn-primary pull-right"
                                            name="submit" value="Guardar ubicación"></h4>
                                    <address>
                                        <div class="form-group">
                                            <label for="streetnumber" class="col-form-label">Número:</label>
                                            <input type="text" class="form-control" id="street_number" wire:model='streetnumber'
                                                name="streetnumber">
                                        </div>
                                        <div class="form-group">
                                            <label for="routes" class="col-form-label">Calle:</label>
                                            <input type="text" class="form-control" id="route" name="routes" wire:model='routes'>
                                        </div>
                                        <div class="form-group">
                                            <label for="city" class="col-form-label">Ciudad:</label>
                                            <input type="text" class="form-control" id="locality" name="city" wire:model='city'>
                                        </div>
                                        <div class="form-group">
                                            <label for="state" class="col-form-label">Provincia:</label>
                                            <input type="text" class="form-control" id="administrative_area_level_1" wire:model='state'
                                                name="state">
                                        </div>
                                        <div class="form-group">
                                            <label for="country" class="col-form-label">País:</label>
                                            <input type="text" class="form-control" id="country" name="country" wire:model='country'>
                                        </div>
                                        <div class="form-group">
                                            <label for="pincode" class="col-form-label">Código postal:</label>
                                            <input type="text" class="form-control" id="postal_code" name="pincode" wire:model='zipcode'>
                                        </div>
                                    </address>
                                </aside>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="section-twitter content_resalt border-top">
            <i class="fa fa-twitter icon-big"></i>
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                            alert("No details available for the selected location.");
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
