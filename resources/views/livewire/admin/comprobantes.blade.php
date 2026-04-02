<div class="space-y-4 pb-24">
    <h2 class="text-xl font-bold text-gray-800">Comprobantes de pago</h2>
    <p class="text-xs text-gray-500 -mt-2">Reservas vigentes con comprobantes subidos</p>

    @if($reservas->isEmpty())
    <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
        <div class="text-5xl mb-3">🧾</div>
        <p class="text-gray-500 font-medium">No hay comprobantes para revisar</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($reservas as $item)
        @php
            $r      = $item['reserva'];
            $pagos  = $item['pagos'];
            $hayPendienteRevision = $pagos->contains(fn($p) => $p->estado === 'PENDING_REVIEW');
            $todosAutorizados     = $pagos->every(fn($p) => $p->estado === 'AUTHORIZED');
        @endphp
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            {{-- Header reserva --}}
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="font-bold text-gray-800 capitalize text-sm">{{ $r->dia }}</p>
                    <p class="text-xs text-gray-500">{{ $r->hora }} — Cancha {{ $r->cancha_id }}</p>
                </div>
                <div>
                    @if($hayPendienteRevision)
                        <span class="bg-amber-100 text-amber-700 text-xs px-2 py-1 rounded-full font-medium">⚠️ En revisión</span>
                    @elseif($todosAutorizados)
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">✓ Autorizado</span>
                    @else
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full font-medium">Parcial</span>
                    @endif
                </div>
            </div>

            {{-- Lista de comprobantes --}}
            <div class="divide-y divide-gray-50">
                @foreach($pagos as $pago)
                <div class="px-4 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">
                            {{ $pago->user?->nombre }} {{ $pago->user?->apellido }}
                        </p>
                        <p class="text-xs text-gray-500">${{ number_format($pago->monto, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        {{-- Badge estado --}}
                        @if($pago->estado === 'AUTHORIZED')
                            <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">✓ OK</span>
                        @elseif($pago->estado === 'PENDING_REVIEW')
                            <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Revisar</span>
                        @else
                            <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Pendiente</span>
                        @endif
                        {{-- Botón ver comprobante --}}
                        <button
                            wire:click="abrirModal({{ $pago->id }})"
                            class="flex items-center gap-1 bg-[#0057a8] text-white text-xs px-3 py-1.5 rounded-lg font-medium hover:bg-blue-700 transition">
                            🧾 Ver
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Modal comprobante --}}
    @if($modalPago)
    <div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4"
         wire:click.self="cerrarModal">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="font-bold text-sm text-gray-800">
                        {{ $modalPago->user?->nombre }} {{ $modalPago->user?->apellido }}
                    </p>
                    <p class="text-xs text-gray-500">${{ number_format($modalPago->monto, 0, ',', '.') }}</p>
                </div>
                <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">✕</button>
            </div>
            <div class="p-3">
                @php
                    $ext  = strtolower(pathinfo($modalPago->comprobante, PATHINFO_EXTENSION));
                    $url  = asset('storage/' . $modalPago->comprobante);
                @endphp
                @if($ext === 'pdf')
                    <div class="text-center py-6">
                        <p class="text-5xl mb-3">📄</p>
                        <a href="{{ $url }}" target="_blank"
                           class="inline-block bg-[#0057a8] text-white px-5 py-2.5 rounded-xl text-sm font-bold">
                            Abrir PDF
                        </a>
                    </div>
                @else
                    <img src="{{ $url }}" alt="Comprobante"
                         class="w-full rounded-xl object-contain max-h-[65vh]">
                @endif

                {{-- Detalle IA si existe --}}
                @if($modalPago->verificacion_ia && !($modalPago->verificacion_ia['error'] ?? false))
                @php $v = $modalPago->verificacion_ia; @endphp
                <div class="mt-3 bg-gray-50 rounded-xl px-3 py-2 space-y-1 text-xs">
                    <p class="font-semibold text-gray-600 uppercase text-[10px] mb-1">Verificación IA</p>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Fecha</span>
                        <span class="{{ ($v['fecha_ok'] ?? null) === true ? 'text-green-600' : 'text-red-500' }} font-medium">
                            {{ ($v['fecha_ok'] ?? null) === true ? '✓' : '✗' }} {{ $v['fecha_encontrada'] ?? '–' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Importe</span>
                        <span class="{{ ($v['importe_ok'] ?? null) === true ? 'text-green-600' : 'text-red-500' }} font-medium">
                            {{ ($v['importe_ok'] ?? null) === true ? '✓' : '✗' }} {{ $v['importe_encontrado'] ?? '–' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Alias</span>
                        <span class="{{ ($v['alias_ok'] ?? null) === true ? 'text-green-600' : 'text-red-500' }} font-medium">
                            {{ ($v['alias_ok'] ?? null) === true ? '✓' : '✗' }} {{ $v['alias_encontrado'] ?? '–' }}
                        </span>
                    </div>
                    @if($v['detalle'] ?? null)
                    <p class="text-[10px] text-gray-400 pt-1 border-t border-gray-200">{{ $v['detalle'] }}</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
