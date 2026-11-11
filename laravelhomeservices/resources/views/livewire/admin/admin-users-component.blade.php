<div>
    <x-ui.panel title="Usuarios" help="Todas las cuentas del sitio. Busca a alguien, escríbele, cambia su tipo de cuenta o suspéndelo si incumple las reglas.">
        <div class="space-y-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <x-site.icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-grafito-400" />
                    <input type="search" class="ui-input pl-11" wire:model.live.debounce.400ms="q" placeholder="Buscar por nombre, correo o celular" aria-label="Buscar usuarios">
                </div>
                <div class="-mx-5 flex gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0" role="group" aria-label="Filtrar por tipo">
                    @foreach (['' => ['Todos', $total], 'CST' => ['Clientes', $counts['CST'] ?? 0], 'SVP' => ['Proveedores', $counts['SVP'] ?? 0], 'ADM' => ['Administración', $counts['ADM'] ?? 0], 'suspended' => ['Suspendidos', $suspendedCount]] as $value => [$label, $count])
                        <button type="button" wire:click="$set('role', '{{ $value }}')" @if ($role === $value) aria-pressed="true" @endif
                                class="shrink-0 rounded-full border px-4 py-2 text-[14px] transition {{ $role === $value ? 'border-noche-900 bg-noche-900 text-white' : 'border-humo-300 bg-white text-grafito-600 hover:border-grafito-400' }}">
                            {{ $label }} <span class="{{ $role === $value ? 'text-white/60' : 'text-grafito-400' }}">{{ $count }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            @if ($users->isEmpty())
                <x-ui.empty icon="users" title="No encontramos cuentas">
                    {{ $q !== '' ? 'Prueba con otra parte del nombre, del correo o del celular.' : 'No hay cuentas con este filtro.' }}
                </x-ui.empty>
            @else
                <div class="space-y-3">
                    @foreach ($users as $user)
                        @php
                            $isMe = $user->id === auth()->id();
                            $wa = $user->phone ? \App\Support\WhatsApp::link((string) $user->phone, 'Hola ' . $user->name . ', te escribimos de Malakplace.') : null;
                            $lastSeen = $user->last_seen ? \Illuminate\Support\Carbon::parse($user->last_seen) : null;
                        @endphp
                        <article wire:key="user-{{ $user->id }}" class="ui-card overflow-hidden {{ $user->suspended_at ? '!border-red-200' : '' }}">
                            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <h2 class="flex flex-wrap items-center gap-2 text-[17px] font-medium">
                                        <span class="min-w-0 break-words">{{ $user->name }}</span>
                                        @if ($isMe)<span class="text-[13px] font-normal text-grafito-400">(tú)</span>@endif
                                        <x-ui.status :status="$user->utype === 'ADM' ? 'accepted' : ($user->utype === 'SVP' ? 'approved' : 'initiated')" :label="$roles[$user->utype] ?? $user->utype" />
                                        @if ($user->isPro())<x-ui.status status="pending" label="Pro" />@endif
                                        @if ($user->suspended_at)<x-ui.status status="rejected" label="Suspendido" />@endif
                                    </h2>
                                    <p class="mt-1 break-words text-[14px] text-grafito-500">{{ $user->email }}@if ($user->phone) · {{ $user->phone }}@endif</p>
                                    <p class="mt-1 text-[13px] text-grafito-400">
                                        Desde el {{ $user->created_at?->format('d/m/Y') }}
                                        · {{ $lastSeen ? 'Último ingreso ' . $lastSeen->diffForHumans() : 'Sin ingresos registrados' }}
                                        @if ($user->utype === 'SVP') · {{ $user->services_count }} {{ $user->services_count === 1 ? 'servicio' : 'servicios' }}@endif
                                    </p>
                                    @if ($user->suspended_at && $user->suspension_reason)
                                        <p class="mt-2 text-[14px] text-red-900">Motivo: {{ $user->suspension_reason }}</p>
                                    @endif
                                </div>
                                <div class="flex shrink-0 flex-wrap gap-2">
                                    @if ($wa)
                                        <a href="{{ $wa }}" target="_blank" rel="noopener" class="ui-btn ui-btn-whatsapp ui-btn-sm"><x-site.icon name="whatsapp" class="h-4 w-4" /> Escribir</a>
                                    @endif
                                    <a href="mailto:{{ $user->email }}" class="ui-btn ui-btn-secondary ui-btn-sm"><x-site.icon name="mail" class="h-4 w-4" /> Correo</a>
                                </div>
                            </div>

                            @unless ($isMe)
                                <div class="flex flex-wrap items-center gap-2 border-t border-humo-200 bg-humo-50 px-5 py-3" x-data="{ open: false }">
                                    <div class="relative">
                                        <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="open = ! open" @click.outside="open = false" :aria-expanded="open">Cambiar tipo <x-site.icon name="chevron-down" class="h-4 w-4" /></button>
                                        <div x-cloak x-show="open" x-transition class="absolute left-0 z-20 mt-2 w-60 overflow-hidden rounded-2xl border border-humo-300 bg-white py-1 shadow-xl">
                                            @foreach ($roles as $value => $label)
                                                @continue($value === $user->utype)
                                                <button type="button" class="block w-full px-4 py-2.5 text-left text-[14px] hover:bg-humo-100"
                                                        wire:click="changeRole({{ $user->id }}, '{{ $value }}')"
                                                        wire:confirm="{{ $value === 'ADM' ? '¿Darle acceso de administración a ' . $user->name . '? Podrá ver y cambiar todo el sitio.' : '¿Cambiar a ' . $user->name . ' a ' . mb_strtolower($label) . '?' }}"
                                                        @click="open = false">Hacerlo {{ mb_strtolower($label) }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @if ($user->suspended_at)
                                        <button type="button" wire:click="reactivate({{ $user->id }})" class="ui-btn ui-btn-success ui-btn-sm">Reactivar</button>
                                    @elseif ($suspendingId !== $user->id)
                                        <button type="button" wire:click="startSuspend({{ $user->id }})" class="ui-btn ui-btn-ghost ui-btn-sm">Suspender</button>
                                    @endif
                                    @if ($user->utype !== 'ADM')
                                        <button type="button" wire:click="deleteUser({{ $user->id }})" wire:confirm="¿Eliminar la cuenta de {{ $user->name }}? Se borran sus servicios, solicitudes y documentos. No se puede deshacer." class="ui-btn ui-btn-ghost ui-btn-sm text-red-800 sm:ml-auto">Eliminar cuenta</button>
                                    @endif
                                </div>
                            @endunless

                            @if ($suspendingId === $user->id)
                                <form wire:submit="suspend" class="space-y-4 border-t border-humo-200 p-5">
                                    <x-ui.field label="¿Por qué lo suspendes?" name="suspendReason" for="suspend-{{ $user->id }}" hint="La persona verá este motivo al intentar entrar.">
                                        <input type="text" id="suspend-{{ $user->id }}" class="ui-input @error('suspendReason') ui-input-invalid @enderror" wire:model="suspendReason" placeholder="Ej.: varios clientes reportaron que no llegó al trabajo">
                                    </x-ui.field>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm">Suspender cuenta</button>
                                        <button type="button" wire:click="$set('suspendingId', null)" class="ui-btn ui-btn-ghost ui-btn-sm">Cancelar</button>
                                    </div>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
                {{ $users->links() }}
            @endif
        </div>
    </x-ui.panel>
</div>
