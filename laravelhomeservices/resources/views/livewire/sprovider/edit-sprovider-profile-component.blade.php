<div>
    @include('livewire.partials.page-header', ['title' => 'Editar perfil'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row portfolioContainer">
                        <div class="col-md-12 profile1">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-md-6">Editar perfil de proveedor</div>
                                        <div class="col-md-6">
                                            <a href="{{ route('sprovider.profile') }}" class="btn btn-info pull-right">Ver mi perfil</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    @if (Session::has('message'))
                                        <div class="alert alert-success" role="alert">{{ Session::get('message') }}</div>
                                    @endif
                                    <form class="form-horizontal" wire:submit.prevent="updateProfile">
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">Foto de perfil:</label>
                                            <div class="col-sm-9">
                                                <input type="file" accept="image/*" class="form-control-file" wire:model="newimage"/>
                                                @error('newimage') <div class="text-danger">{{ $message }}</div> @enderror
                                                @if ($newimage)
                                                    <img src="{{ $newimage->temporaryUrl() }}" width="160" alt=""/>
                                                @elseif ($image)
                                                    <img src="{{ asset('images/sproviders/' . $image) }}" width="160" alt=""/>
                                                @else
                                                    <img src="{{ asset('images/sproviders/default.jpg') }}" width="160" alt=""/>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="whatsapp" class="control-label col-sm-3">WhatsApp:</label>
                                            <div class="col-sm-9">
                                                <input type="tel" id="whatsapp" class="form-control" wire:model.blur="whatsapp" placeholder="0991234567"/>
                                                <small class="text-muted">Los clientes te escribirán a este número.</small>
                                                @error('whatsapp') <div class="text-danger">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-3">Zonas donde trabajas:</label>
                                            <div class="col-sm-9">
                                                @foreach (config('marketplace.zones') as $slug => $label)
                                                    <label class="checkbox-inline" style="margin-right: 15px;">
                                                        <input type="checkbox" value="{{ $slug }}" wire:model="zones"> {{ $label }}
                                                    </label>
                                                @endforeach
                                                @error('zones') <div class="text-danger">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="about" class="control-label col-sm-3">Sobre ti:</label>
                                            <div class="col-sm-9">
                                                <textarea id="about" class="form-control" wire:model.blur="about" placeholder="Experiencia, especialidades, horarios..."></textarea>
                                                @error('about') <div class="text-danger">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="city" class="control-label col-sm-3">Ciudad / parroquia:</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="city" class="form-control" wire:model.blur="city" placeholder="Malacatos"/>
                                                @error('city') <div class="text-danger">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="service_category_id" class="control-label col-sm-3">Categoría principal:</label>
                                            <div class="col-sm-9">
                                                <select id="service_category_id" class="form-control" wire:model="service_category_id">
                                                    <option value="">Selecciona una categoría</option>
                                                    @foreach ($scategories as $scategory)
                                                        <option value="{{ $scategory->id }}">{{ $scategory->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('service_category_id') <div class="text-danger">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="service_location" class="control-label col-sm-3">Referencia de ubicación:</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="service_location" class="form-control" wire:model.blur="service_location" placeholder="Barrio, sector o referencia"/>
                                                @error('service_location') <div class="text-danger">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-offset-3 col-sm-9">
                                                <button type="submit" class="btn btn-success pull-right">Guardar perfil</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
