<div class="max-w-2xl mx-auto space-y-4 pb-24">
    <h2 class="text-xl font-bold text-gray-800 mb-2">Prueba de verificación IA</h2>
    <p class="text-xs text-gray-500 -mt-2">Probá comprobantes de distintos bancos sin afectar ninguna reserva.</p>

    {{-- Resultado --}}
    @if($resultado)
        @php
            $valido = $resultado['valido'] ?? false;
            $error  = $resultado['error'] ?? null;
            function ic($val) {
                if ($val === true)  return ['✓', 'text-green-600 font-bold'];
                if ($val === false) return ['✗', 'text-red-600 font-bold'];
                return ['—', 'text-gray-400'];
            }
            [$fi, $fc] = ic($resultado['fecha_ok']   ?? null);
            [$hi, $hc] = ic($resultado['hora_ok']    ?? null);
            [$ii, $ic2] = ic($resultado['importe_ok'] ?? null);
            [$ai, $ac] = ic($resultado['alias_ok']   ?? null);
        @endphp

        <div class="rounded-2xl border-2 p-5 {{ $valido ? 'bg-green-50 border-green-300' : 'bg-yellow-50 border-yellow-300' }}">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-4xl">{{ $valido ? '✅' : '⚠️' }}</span>
                <div>
                    <p class="font-bold text-lg {{ $valido ? 'text-green-700' : 'text-yellow-800' }}">
                        {{ $valido ? 'Comprobante válido — se confirmaría automáticamente' : 'No se confirmaría automáticamente' }}
                    </p>
                    @if($error)
                        <p class="text-xs text-red-600 mt-0.5">{{ $error }}</p>
                    @elseif(!$valido)
                        <p class="text-xs text-yellow-700 mt-0.5">Iría a revisión manual del admin.</p>
                    @endif
                </div>
            </div>

            @if(!$error)
            <div class="bg-white rounded-xl divide-y divide-gray-100 text-sm">
                <div class="flex items-center justify-between px-4 py-2.5">
                    <span class="text-gray-600">¿Es comprobante?</span>
                    <span class="{{ ($resultado['es_comprobante'] ?? false) ? 'text-green-600 font-bold' : 'text-red-600 font-bold' }}">
                        {{ ($resultado['es_comprobante'] ?? false) ? '✓ Sí' : '✗ No parece un comprobante' }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-4 py-2.5">
                    <div>
                        <span class="text-gray-600">Fecha</span>
                        @if($resultado['fecha_encontrada'] ?? null)
                            <span class="text-xs text-gray-400 ml-1">({{ $resultado['fecha_encontrada'] }})</span>
                        @endif
                    </div>
                    <span class="{{ $fc }}">{{ $fi }}</span>
                </div>
                <div class="flex items-center justify-between px-4 py-2.5">
                    <div>
                        <span class="text-gray-600">Hora
                        @if($resultado['hora_encontrada'] ?? null)
                            <span class="text-xs text-gray-400">(enviado dentro de los 30 min)</span>
                        @endif
                    </span>
                        @if($resultado['hora_encontrada'] ?? null)
                            <span class="text-xs text-gray-400 ml-1">({{ $resultado['hora_encontrada'] }})</span>
                        @endif
                    </div>
                    <span class="{{ $hc }}">
                        {{ ($resultado['hora_ok'] ?? null) === null ? '— no aparece' : $hi }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-4 py-2.5">
                    <div>
                        <span class="text-gray-600">Importe</span>
                        @if($resultado['importe_encontrado'] ?? null)
                            <span class="text-xs text-gray-400 ml-1">({{ $resultado['importe_encontrado'] }})</span>
                        @endif
                    </div>
                    <span class="{{ $ic2 }}">{{ $ii }}</span>
                </div>
                <div class="flex items-center justify-between px-4 py-2.5">
                    <div>
                        <span class="text-gray-600">Alias / CBU / Cuenta / CUIT</span>
                        @if($resultado['alias_encontrado'] ?? null)
                            <span class="text-xs text-gray-400 ml-1">({{ $resultado['alias_encontrado'] }})</span>
                        @endif
                    </div>
                    <span class="{{ $ac }}">
                        {{ ($resultado['alias_ok'] ?? null) === null ? '— no aparece' : $ai }}
                    </span>
                </div>
            </div>

            @if($resultado['detalle'] ?? null)
            <p class="text-xs text-gray-500 mt-3 px-1">💬 {{ $resultado['detalle'] }}</p>
            @endif
            @endif
        </div>

        <button wire:click="limpiar"
            class="w-full border border-gray-300 text-gray-600 py-3 rounded-2xl text-sm font-medium hover:bg-gray-50 transition-colors">
            ← Probar otro comprobante
        </button>

    @else

    {{-- Formulario --}}
    <div class="bg-white rounded-2xl shadow-sm p-5 space-y-4">

        {{-- Comprobante --}}
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Comprobante a analizar</p>
            <label class="block w-full cursor-pointer">
                <div class="border-2 border-dashed border-gray-300 rounded-xl px-4 py-5 text-center hover:border-[#0057a8] transition-colors">
                    @if($comprobante)
                        <p class="text-sm text-green-600 font-medium">✓ {{ $comprobante->getClientOriginalName() }}</p>
                        <p class="text-xs text-gray-400 mt-1">Tocá para cambiar</p>
                    @else
                        <p class="text-2xl mb-1">📎</p>
                        <p class="text-sm text-gray-500 font-medium">Tocá para adjuntar</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG o PDF · máx. 5 MB</p>
                    @endif
                </div>
                <input type="file" wire:model="comprobante" accept=".jpg,.jpeg,.png,.pdf" class="hidden"/>
            </label>
            @error('comprobante') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Fecha y hora simulada --}}
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Hora de envío simulada</label>
            <input type="datetime-local" wire:model="fechaHoraSimulada"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
            <p class="text-[10px] text-gray-400 mt-1">Es la hora en que el jugador <strong>envía</strong> el comprobante (no la hora del recibo). Poné una hora igual o hasta 30 minutos después de la hora que figura en el comprobante.</p>
            @error('fechaHoraSimulada') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Importe --}}
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Importe esperado ($)</label>
            <input type="number" wire:model="importe" min="1" placeholder="Ej: 7500"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
            @error('importe') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Identificadores --}}
        <div class="border-t border-gray-100 pt-4 space-y-3">
            <p class="text-xs font-semibold text-gray-500 uppercase">Identificadores de cuenta (editables para prueba)</p>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Alias</label>
                <input type="text" wire:model="alias" placeholder="ateneo.jam.tenis"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">CBU / CVU</label>
                <input type="text" wire:model="cbu" placeholder="22 dígitos"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Cuenta corriente</label>
                <input type="text" wire:model="cuenta" placeholder="123-456789/0"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">CUIT</label>
                <input type="text" wire:model="cuit" placeholder="20-12345678-9"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
            </div>
        </div>
    </div>

    <button wire:click="verificar"
        wire:loading.attr="disabled" wire:target="verificar"
        @if(!$comprobante) disabled @endif
        class="w-full bg-[#0057a8] text-white py-4 rounded-2xl font-bold text-base shadow-md
               hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
        <span wire:loading.remove wire:target="verificar">
            {{ !$comprobante ? 'Adjuntá el comprobante' : 'Analizar con IA' }}
        </span>
        <span wire:loading wire:target="verificar">Analizando...</span>
    </button>

    @endif
</div>
