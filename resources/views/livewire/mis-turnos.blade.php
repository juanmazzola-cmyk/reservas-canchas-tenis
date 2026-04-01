<div>
    <h2 class="text-xl font-bold text-gray-800 mb-4">Mis Turnos</h2>

    @if(empty($reservas))
        <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
            <div class="text-5xl mb-3">📅</div>
            <p class="text-gray-500 font-medium">No tenés turnos reservados</p>
            <a href="{{ route('agenda') }}" class="inline-block mt-4 bg-[#0057a8] text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-blue-700 transition">
                Reservar turno
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($reservas as $r)
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden {{ $r['vencida'] ? 'opacity-60' : '' }}">
                {{-- Header --}}
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-gray-800 capitalize">{{ $r['dia'] }}</p>
                        <p class="text-sm text-gray-500">{{ $r['hora'] }} — Cancha {{ $r['cancha_id'] }}</p>
                    </div>
                    <div class="text-right">
                        @php $todosSocios = collect($r['jugadores'])->every(fn($j) => $j['es_socio'] && !($j['es_invitado'] ?? false)); @endphp
                        @php $suspendida = ($r['estado'] ?? '') === 'SUSPENDIDA'; @endphp
                        <div class="flex flex-col items-end gap-1">
                            {{-- Badge suspensión --}}
                            @if($suspendida)
                                <span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full font-medium">Cancha suspendida</span>
                                @if(!empty($r['suspension_motivo']))
                                    <p class="text-[11px] text-red-500">{{ $r['suspension_motivo'] }}</p>
                                @endif
                            @elseif($r['vencida'])
                                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded-full">Vencido</span>
                            @endif

                            {{-- Badge pago (siempre visible salvo vencida no suspendida) --}}
                            @if($suspendida || !$r['vencida'])
                                @if($r['esta_pagado'] && $todosSocios)
                                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">✓ Autorizada</span>
                                @elseif($r['esta_pagado'])
                                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">✓ Pagada</span>
                                @elseif(($r['estado'] ?? '') === 'PARTIAL_PAYMENT')
                                    @if(($r['mi_pago_estado'] ?? '') === 'AUTHORIZED')
                                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">✓ Tu parte: pagada</span>
                                        <span class="bg-amber-100 text-amber-700 text-xs px-2 py-1 rounded-full font-medium">⏳ Rival: pendiente</span>
                                    @elseif(($r['mi_pago_estado'] ?? '') === 'PENDING_REVIEW')
                                        <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full font-medium">⏳ Tu parte: en revisión</span>
                                    @else
                                        <span class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-full font-medium">💰 Debés abonar tu parte</span>
                                    @endif
                                @elseif(($r['estado'] ?? '') === 'PENDING_REVIEW')
                                    <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full font-medium">⏳ Pendiente de autorización</span>
                                @elseif(!$suspendida)
                                    <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full font-medium">⚠ Pendiente de pago</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Jugadores --}}
                <div class="px-4 py-3">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-2">Jugadores</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($r['jugadores'] as $jug)
                        @php $esInvitado = $jug['es_invitado'] ?? false; @endphp
                        <div class="flex items-center gap-1.5 {{ $esInvitado ? 'bg-amber-50' : 'bg-blue-50' }} rounded-full px-3 py-1">
                            <div class="w-5 h-5 {{ $esInvitado ? 'bg-amber-400' : 'bg-[#0057a8]' }} text-white rounded-full flex items-center justify-center text-[10px] font-bold">
                                {{ $esInvitado ? '?' : strtoupper(substr($jug['nombre'], 0, 1)) }}
                            </div>
                            <span class="text-xs {{ $esInvitado ? 'text-amber-800' : 'text-blue-800' }}">{{ $jug['apellido'] }}</span>
                            @if($esInvitado)
                                <span class="text-[10px] text-amber-500">inv.</span>
                            @elseif(!$jug['es_socio'])
                                <span class="text-[10px] text-orange-500">$</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Acciones --}}
                @if(!$r['vencida'] || ($r['estado'] ?? '') === 'SUSPENDIDA')
                <div class="px-4 pb-4 flex flex-col gap-2">
                    {{-- Botón pagar mi parte (solo cuando el pago del usuario está pendiente) --}}
                    @if(!$r['vencida'] && ($r['mi_pago_estado'] ?? '') === 'PENDIENTE')
                    <a href="{{ route('pago', $r['id']) }}"
                       class="w-full bg-amber-500 hover:bg-amber-600 text-white text-xs py-2.5 rounded-lg font-bold text-center transition">
                        💰 Pagar mi parte — ${{ number_format($r['mi_pago_monto'], 0, ',', '.') }}
                    </a>
                    @endif
                    <div class="flex gap-2">
                        <button
                            wire:click="confirmarReprogramar({{ $r['id'] }})"
                            class="flex-1 border border-blue-200 text-blue-600 text-xs py-2 rounded-lg font-medium hover:bg-blue-50 transition"
                        >
                            Reprogramar
                        </button>
                        @if(!$r['vencida'])
                        <button
                            wire:click="confirmarCancelar({{ $r['id'] }})"
                            class="flex-1 border border-red-200 text-red-500 text-xs py-2 rounded-lg font-medium hover:bg-red-50 transition"
                        >
                            Cancelar turno
                        </button>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @endif

    {{-- MODAL: Reprogramar --}}
    @if($modalReprogramar)
    <div class="fixed inset-0 bg-black/60 z-50 flex items-start justify-center p-4 pt-[8vh]">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-bold text-lg text-gray-800">Reprogramar turno</h3>
                <p class="text-sm text-gray-500">Elegí cancha, día y horario</p>
            </div>

            {{-- Selector de cancha --}}
            <div class="p-4 border-b border-gray-100">
                <p class="text-xs text-gray-500 uppercase font-medium mb-2">Elegí la cancha</p>
                <div class="flex gap-2 flex-wrap">
                    @foreach($canchas as $cancha)
                    <button
                        wire:click="seleccionarCanchaReprogramar({{ $cancha['id'] }})"
                        class="px-3 py-1.5 rounded-full text-sm font-medium transition
                            {{ $reprogramarCancha === $cancha['id']
                                ? 'bg-[#0057a8] text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        Cancha {{ $cancha['nombre'] }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Selector de día --}}
            <div class="p-4 border-b border-gray-100">
                <p class="text-xs text-gray-500 uppercase font-medium mb-2">Elegí el día</p>
                <div class="flex gap-2 flex-wrap">
                    @foreach($dias as $dia)
                    <button
                        wire:click="seleccionarDiaReprogramar('{{ $dia['clave'] }}')"
                        class="px-3 py-1.5 rounded-full text-sm font-medium transition
                            {{ $reprogramarDia === $dia['clave']
                                ? 'bg-[#0057a8] text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        {{ $dia['etiqueta'] }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Selector de hora --}}
            <div class="p-4 border-b border-gray-100">
                <p class="text-xs text-gray-500 uppercase font-medium mb-2">Elegí el horario</p>
                @if(empty($horasDisponibles))
                    <p class="text-sm text-gray-400 text-center py-3">Sin horarios disponibles para este día</p>
                @else
                    <div class="grid grid-cols-4 gap-1.5 max-h-44 overflow-y-auto pr-1">
                        @foreach($horasDisponibles as $hora)
                        <button
                            wire:click="$set('reprogramarHora', '{{ $hora }}')"
                            class="py-1.5 rounded-lg text-xs font-medium transition
                                {{ $reprogramarHora === $hora
                                    ? 'bg-[#0057a8] text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-blue-50 hover:text-[#0057a8]' }}"
                        >
                            {{ $hora }}
                        </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Botones --}}
            <div class="p-4 flex gap-3">
                <button wire:click="$set('modalReprogramar', false)" class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg text-sm">
                    Cancelar
                </button>
                <button
                    wire:click="reprogramarReserva"
                    @if(!$reprogramarHora) disabled @endif
                    class="flex-1 bg-[#0057a8] text-white py-2.5 rounded-lg text-sm font-bold hover:bg-blue-700 transition disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    Confirmar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL: Confirmar cancelar --}}
    @if($modalCancelar)
    <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl p-6">
            <h3 class="font-bold text-lg mb-2">¿Cancelar turno?</h3>
            <p class="text-sm text-gray-600 mb-4">Esta acción no se puede deshacer.</p>
            <div class="flex gap-3">
                <button wire:click="$set('modalCancelar', false)" class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg text-sm">Volver</button>
                <button wire:click="cancelarReserva" class="flex-1 bg-red-500 text-white py-2.5 rounded-lg text-sm font-bold hover:bg-red-600">Cancelar turno</button>
            </div>
        </div>
    </div>
    @endif
</div>
