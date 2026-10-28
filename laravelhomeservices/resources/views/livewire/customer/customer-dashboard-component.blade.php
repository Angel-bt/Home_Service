<div>
    @include('livewire.partials.page-header', ['title' => 'Mis solicitudes'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    @if (Session::has('message'))
                        <div class="alert alert-success">{{ Session::get('message') }}</div>
                    @endif
                    @if (Session::has('error'))
                        <div class="alert alert-danger">{{ Session::get('error') }}</div>
                    @endif
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Servicios que solicitaste
                            <a href="{{ route('search') }}" class="btn btn-primary btn-sm pull-right">Buscar servicios</a>
                        </div>
                        <div class="panel-body">
                            @if ($requests->isEmpty())
                                <p class="text-center">Aún no has solicitado servicios. <a href="{{ route('home.service_categories') }}">Explora las categorías</a>.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Servicio</th>
                                                <th>Proveedor</th>
                                                <th>Zona</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($requests as $request)
                                                <tr wire:key="customer-request-{{ $request->id }}">
                                                    <td>{{ $request->created_at->format('d/m/Y') }}</td>
                                                    <td>
                                                        @if ($request->service)
                                                            <a href="{{ route('home.service_details', ['service_slug' => $request->service->slug]) }}">{{ $request->service->name }}</a>
                                                        @else
                                                            Servicio eliminado
                                                        @endif
                                                    </td>
                                                    <td>{{ $request->provider?->name ?? 'Proyetech' }}</td>
                                                    <td>{{ $request->zoneLabel() }}</td>
                                                    <td>@include('livewire.partials.request-status', ['request' => $request])</td>
                                                    <td>
                                                        @if (in_array($request->status, ['pending', 'accepted'], true))
                                                            <button class="btn btn-xs btn-default" onclick="confirm('¿Cancelar esta solicitud?') || event.stopImmediatePropagation()" wire:click="cancelRequest({{ $request->id }})">Cancelar</button>
                                                        @elseif ($request->status === 'completed' && ! $request->review)
                                                            <button class="btn btn-xs btn-warning" wire:click="startReview({{ $request->id }})">Calificar</button>
                                                        @elseif ($request->review)
                                                            <span class="text-warning">{{ str_repeat('★', $request->review->rating) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if ($reviewingId === $request->id)
                                                    <tr wire:key="review-form-{{ $request->id }}">
                                                        <td colspan="6">
                                                            <form wire:submit.prevent="saveReview" class="form-inline">
                                                                <label for="rating-{{ $request->id }}">Calificación:</label>
                                                                <select id="rating-{{ $request->id }}" class="form-control" wire:model.defer="rating">
                                                                    @for ($i = 5; $i >= 1; $i--)
                                                                        <option value="{{ $i }}">{{ str_repeat('★', $i) }} ({{ $i }})</option>
                                                                    @endfor
                                                                </select>
                                                                <input type="text" class="form-control" style="min-width: 300px;" wire:model.defer="comment" placeholder="Cuéntanos cómo fue el trabajo (opcional)">
                                                                <button type="submit" class="btn btn-success">Enviar reseña</button>
                                                                <button type="button" class="btn btn-link" wire:click="$set('reviewingId', null)">Cancelar</button>
                                                                @error('rating') <div class="text-danger">{{ $message }}</div> @enderror
                                                                @error('comment') <div class="text-danger">{{ $message }}</div> @enderror
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                {{ $requests->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
