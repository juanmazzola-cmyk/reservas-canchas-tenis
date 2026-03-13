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
                        @php $todosSocios = collect($r['jugadores'])->every(fn($j) => $j['es_socio']); @endphp
                        @if($r['vencida'])
                            <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded-full">Vencido</span>
                        @elseif(($r['estado'] ?? '') === 'PENDING_REVIEW')
                            <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full font-medium">⏳ Pendiente de autorización</span>
                        @elseif($r['esta_pagado'] && $todosSocios)
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">✓ Autorizado</span>
                        @elseif($r['esta_pagado'])
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">✓ Pagado</span>
                        @else
                            <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full font-medium">⚠ Pendiente de autorización</span>
                        @endif
                    </div>
                </div>

                {{-- Jugadores --}}
                <div class="px-4 py-3">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-2">Jugadores</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($r['jugadores'] as $jug)
                        <div class="flex items-center gap-1.5 bg-blue-50 rounded-full px-3 py-1">
                            <div class="w-5 h-5 bg-[#0057a8] text-white rounded-full flex items-center justify-center text-[10px] font-bold">
                                {{ strtoupper(substr($jug['nombre'], 0, 1)) }}
                            </div>
                            <span class="text-xs text-blue-800">{{ $jug['apellido'] }}</span>
                            @if(!$jug['es_socio'])
                                <span class="text-[10px] text-orange-500">$</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Acciones --}}
                @if(!$r['vencida'])
                <div class="px-4 pb-4 flex gap-2">
                    <button
                        wire:click="confirmarCancelar({{ $r['id'] }})"
                        class="flex-1 border border-red-200 text-red-500 text-xs py-2 rounded-lg font-medium hover:bg-red-50 transition"
                    >
                        Cancelar turno
                    </button>
                </div>
                @endif
            </div>
            @endforeach
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
