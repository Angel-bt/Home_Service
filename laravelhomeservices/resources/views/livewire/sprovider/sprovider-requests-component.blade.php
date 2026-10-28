<div>
    @include('livewire.partials.page-header', ['title' => $isAdmin ? 'Solicitudes de servicio' : 'Solicitudes recibidas'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row portfolioContainer">
                        <div class="col-md-12 profile1">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-md-6">{{ $isAdmin ? 'Todas las solicitudes' : 'Clientes que pidieron tus servicios' }}</div>
                                        <div class="col-md-6">
                                            <select class="form-control pull-right" style="max-width: 220px;" wire:model="status">
                                                <option value="">Todos los estados</option>
                                                @foreach (\App\Models\ServiceRequest::LABELS as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    @if (Session::has('message'))
                                        <div class="alert alert-success">{{ Session::get('message') }}</div>
                                    @endif
                                    @if (Session::has('error'))
                                        <div class="alert alert-danger">{{ Session::get('error') }}</div>
                                    @endif
                                    @if ($requests->isEmpty())
                                        <p class="text-center">No hay solicitudes por ahora.</p>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Servicio</th>
                                                        @if ($isAdmin)
                                                            <th>Proveedor</th>
                                                        @endif
                                                        <th>Cliente</th>
                                                        <th>Zona / dirección</th>
                                                        <th>Para el</th>
                                                        <th>Mensaje</th>
                                                        <th>Estado</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($requests as $request)
                                                        <tr wire:key="request-{{ $request->id }}">
                                                            <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                                            <td>
                                                                @if ($request->service)
                                                                    <a href="{{ route('home.service_details', ['service_slug' => $request->service->slug]) }}">{{ $request->service->name }}</a>
                                                                @endif
                                                            </td>
                                                            @if ($isAdmin)
                                                                <td>{{ $request->provider?->name ?? '—' }}</td>
                                                            @endif
                                                            <td>
                                                                {{ $request->name }}<br>
                                                                @if ($wa = \App\Support\WhatsApp::link($request->phone, 'Hola ' . $request->name . ', te escribo por tu solicitud de "' . ($request->service?->name ?? 'servicio') . '" en Proyetech.'))
                                                                    <a href="{{ $wa }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> {{ $request->phone }}</a>
                                                                @else
                                                                    {{ $request->phone }}
                                                                @endif
                                                            </td>
                                                            <td>{{ $request->zoneLabel() }}@if ($request->address)<br><small>{{ $request->address }}</small>@endif</td>
                                                            <td>{{ $request->preferred_date?->format('d/m/Y') ?? 'A convenir' }}</td>
                                                            <td style="max-width: 220px;">{{ $request->message }}</td>
                                                            <td>@include('livewire.partials.request-status', ['request' => $request])</td>
                                                            <td style="white-space: nowrap;">
                                                                @if ($request->status === 'pending')
                                                                    <button class="btn btn-xs btn-success" wire:click="changeStatus({{ $request->id }}, 'accepted')">Aceptar</button>
                                                                    <button class="btn btn-xs btn-default" onclick="confirm('¿Rechazar esta solicitud?') || event.stopImmediatePropagation()" wire:click="changeStatus({{ $request->id }}, 'rejected')">Rechazar</button>
                                                                @elseif ($request->status === 'accepted')
                                                                    <button class="btn btn-xs btn-primary" wire:click="changeStatus({{ $request->id }}, 'completed')">Marcar completada</button>
                                                                @endif
                                                            </td>
                                                        </tr>
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
            </div>
        </div>
    </section>
</div>
