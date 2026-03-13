<div class="space-y-4 pb-24">

    @if($enviado)
    {{-- Confirmación --}}
    <div class="bg-green-50 border border-green-200 rounded-2xl p-6 text-center space-y-3">
        <div class="text-5xl">✅</div>
        <h2 class="text-xl font-bold text-green-700">¡Comprobante enviado!</h2>
        <p class="text-sm text-green-600">Tu reserva está pendiente de autorización por el club.</p>
    </div>

    <a href="{{ route('agenda') }}"
       class="block text-center bg-terracota text-white py-3 rounded-2xl font-bold text-sm">
        Volver a la agenda
    </a>

    @else

    {{-- Datos del turno --}}
    <div class="bg-[#0057a8] text-white rounded-2xl px-4 py-4">
        <p class="text-xs opacity-70 uppercase font-medium mb-1">Tu reserva</p>
        <p class="font-bold text-lg leading-tight">{{ $turno_dia }}</p>
        <p class="text-sm opacity-90">{{ $turno_hora }} · Cancha {{ $turno_cancha }}</p>
    </div>

    {{-- Jugadores --}}
    <div class="bg-white rounded-2xl shadow-sm px-3 py-2">
        <p class="text-[10px] font-semibold text-gray-500 uppercase mb-1">Jugadores</p>
        <div class="divide-y divide-gray-100">
            @foreach($jugadores as $j)
            <div class="flex items-center justify-between py-1.5">
                <span class="text-xs text-gray-800 font-medium">{{ $j['nombre'] }}</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-medium
                    {{ $j['es_socio'] ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                    {{ $j['es_socio'] ? 'Socio' : 'No socio' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Total a pagar --}}
    @if($totalAPagar > 0)
    <div class="bg-orange-50 border border-orange-200 rounded-2xl px-4 py-3 flex items-center justify-between">
        <div>
            <p class="text-xs text-orange-600 font-semibold uppercase">Total a abonar</p>
            <p class="text-xs text-orange-500 mt-0.5">{{ $cantNoSocios }} jugador{{ $cantNoSocios > 1 ? 'es' : '' }} no soci{{ $cantNoSocios > 1 ? 'os' : 'o' }}</p>
        </div>
        <p class="text-2xl font-bold text-orange-600">${{ number_format($totalAPagar, 0, ',', '.') }}</p>
    </div>
    @else
    <div class="bg-green-50 border border-green-200 rounded-2xl px-4 py-3 text-center">
        <p class="text-sm text-green-700 font-semibold">✓ Todos los jugadores son socios — sin costo adicional</p>
    </div>
    @endif

    {{-- Resultado de pago MP --}}
    @if($mp_result === 'success')
    <div class="bg-green-50 border border-green-200 rounded-2xl px-4 py-3 text-center">
        <p class="text-sm font-bold text-green-700">✅ Pago aprobado por MercadoPago</p>
        <p class="text-xs text-green-600 mt-1">Tu reserva fue confirmada automáticamente.</p>
    </div>
    @elseif($mp_result === 'pending')
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl px-4 py-3 text-center">
        <p class="text-sm font-bold text-yellow-700">⏳ Pago pendiente</p>
        <p class="text-xs text-yellow-600 mt-1">MercadoPago está procesando el pago. Te notificaremos cuando se confirme.</p>
    </div>
    @elseif($mp_result === 'failure')
    <div class="bg-red-50 border border-red-200 rounded-2xl px-4 py-3 text-center">
        <p class="text-sm font-bold text-red-700">❌ Pago rechazado</p>
        <p class="text-xs text-red-600 mt-1">Podés intentarlo de nuevo o pagar por transferencia.</p>
    </div>
    @endif

    {{-- Formas de pago --}}
    <div class="grid grid-cols-2 gap-3">

        {{-- Transferencia --}}
        @if($config->payment_alias)
        <div class="bg-white rounded-2xl shadow-sm px-3 py-3 flex flex-col justify-between">
            <p class="text-[10px] font-semibold text-gray-500 uppercase mb-2">Transferencia</p>
            <div class="bg-gray-50 rounded-xl px-3 py-2">
                <p class="text-[10px] text-gray-500 mb-0.5">Alias</p>
                <p class="text-sm font-bold text-gray-800 tracking-wide break-all">{{ $config->payment_alias }}</p>
            </div>
        </div>
        @endif

        {{-- Mercado Pago --}}
        @if($config->mp_access_token && $totalAPagar > 0)
        <a href="{{ route('mp.iniciar', $reservaId) }}"
           class="bg-[#009EE3] rounded-2xl shadow-sm px-3 py-3 flex flex-col items-center justify-center gap-2 hover:bg-[#0082c0] transition-colors no-underline">
            <svg viewBox="0 0 48 48" class="w-10 h-10 fill-white flex-shrink-0" xmlns="http://www.w3.org/2000/svg">
                <path d="M24 4C12.95 4 4 12.95 4 24s8.95 20 20 20 20-8.95 20-20S35.05 4 24 4zm-2 28.5l-7-7 2.12-2.12L22 28.26l8.88-8.88L33 21.5l-11 11z"/>
            </svg>
            <p class="text-white font-bold text-xs text-center leading-tight">Pagar con<br>Mercado Pago</p>
        </a>
        @else
        <div class="bg-[#009EE3] rounded-2xl shadow-sm px-3 py-3 flex flex-col items-center justify-center gap-2 opacity-50">
            <svg viewBox="0 0 48 48" class="w-10 h-10 fill-white flex-shrink-0" xmlns="http://www.w3.org/2000/svg">
                <path d="M24 4C12.95 4 4 12.95 4 24s8.95 20 20 20 20-8.95 20-20S35.05 4 24 4zm-2 28.5l-7-7 2.12-2.12L22 28.26l8.88-8.88L33 21.5l-11 11z"/>
            </svg>
            <p class="text-white font-bold text-xs text-center leading-tight">Mercado Pago<br><span class="font-normal opacity-80 text-[10px]">Próximamente</span></p>
        </div>
        @endif

    </div>

    {{-- Instrucciones de pago --}}
    @if($config->payment_instructions)
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl px-4 py-3">
        <p class="text-xs font-semibold text-yellow-700 uppercase mb-1">📋 Instrucciones</p>
        <p class="text-sm text-yellow-800 leading-relaxed">{{ $config->payment_instructions }}</p>
    </div>
    @endif

    {{-- Subir comprobante --}}
    <div class="bg-white rounded-2xl shadow-sm px-4 py-4">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Adjuntar comprobante de pago</p>

        <label class="block w-full cursor-pointer">
            <div class="border-2 border-dashed border-gray-300 rounded-xl px-4 py-6 text-center hover:border-[#0057a8] transition-colors">
                @if($comprobante)
                    <p class="text-sm text-green-600 font-medium">✓ {{ $comprobante->getClientOriginalName() }}</p>
                    <p class="text-xs text-gray-400 mt-1">Tocá para cambiar</p>
                @else
                    <p class="text-3xl mb-2">📎</p>
                    <p class="text-sm text-gray-500 font-medium">Tocá para adjuntar</p>
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG o PDF · máx. 5 MB</p>
                @endif
            </div>
            <input type="file" wire:model="comprobante" accept=".jpg,.jpeg,.png,.pdf" class="hidden"/>
        </label>

        @error('comprobante')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror

        <div wire:loading wire:target="comprobante" class="text-xs text-gray-400 mt-2 text-center">
            Cargando archivo...
        </div>
    </div>

    {{-- Botón volver (solo si no adjuntó comprobante) --}}
    @if(!$comprobante)
    <button
        wire:click="cancelarYVolver"
        wire:loading.attr="disabled" wire:target="cancelarYVolver"
        class="w-full border border-gray-300 text-gray-600 py-3 rounded-2xl text-sm font-medium hover:bg-gray-50 transition-colors">
        ← Volver (cancela la reserva)
    </button>
    @endif

    {{-- Botón enviar --}}
    <button
        x-data="{ waUrl: @js($waUrl) }"
        @click="if(waUrl) window.open(waUrl, '_blank')"
        wire:click="enviarComprobante"
        wire:loading.attr="disabled" wire:target="enviarComprobante"
        @if(!$comprobante) disabled @endif
        class="w-full bg-[#0057a8] text-white py-4 rounded-2xl font-bold text-base shadow-md
               hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
        <span wire:loading.remove wire:target="enviarComprobante">
            @if(!$comprobante) Adjuntá el comprobante @else Enviar comprobante y confirmar reserva @endif
        </span>
        <span wire:loading wire:target="enviarComprobante">Enviando...</span>
    </button>

    @endif
</div>
