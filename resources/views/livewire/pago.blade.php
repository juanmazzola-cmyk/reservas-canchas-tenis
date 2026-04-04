<div class="space-y-4 pb-24">

    @if($noNecesitosPagar)
    {{-- El usuario es socio --}}
    <div class="bg-[#0057a8] text-white rounded-2xl px-4 py-4">
        <p class="text-xs opacity-70 uppercase font-medium mb-1">Tu reserva</p>
        <p class="font-bold text-lg leading-tight">{{ $turno_dia }}</p>
        <p class="text-sm opacity-90">{{ $turno_hora }} · Cancha {{ $turno_cancha }}</p>
    </div>

    @if($todosAutorizados)
    {{-- Reserva confirmada --}}
    <div class="bg-green-50 border border-green-200 rounded-2xl p-6 text-center space-y-2">
        <div class="text-5xl">✅</div>
        <h2 class="text-xl font-bold text-green-700">¡Reserva confirmada!</h2>
        <p class="text-sm text-green-600">Todos los pagos fueron completados.</p>
    </div>
    <a href="{{ route('agenda') }}" class="block text-center bg-terracota text-white py-3 rounded-2xl font-bold text-sm">
        Volver a la agenda
    </a>

    @elseif($socioQuierePagar && $enviado && $miPagoEstado === 'PENDING_REVIEW')
    {{-- Socio pagó pero quedó en revisión --}}
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 text-center space-y-2">
        <div class="text-4xl">⚠️</div>
        <h2 class="text-base font-bold text-yellow-800">Comprobante en revisión</h2>
        <p class="text-xs text-yellow-700 mt-1">No pudimos confirmarlo automáticamente. El club lo revisará.</p>
    </div>
    <a href="{{ route('agenda') }}" class="block text-center bg-terracota text-white py-3 rounded-2xl font-bold text-sm">
        Volver a la agenda
    </a>

    @elseif($socioQuierePagar)
    {{-- Socio eligió pagar: mostrar formulario de pago --}}
    <div class="bg-orange-50 border border-orange-200 rounded-2xl px-4 py-3">
        <div class="flex items-center justify-between">
            <p class="text-xs text-orange-600 font-semibold uppercase">Total a abonar</p>
            <p class="text-2xl font-bold text-orange-600">${{ number_format($totalAPagar, 0, ',', '.') }}</p>
        </div>
        <p class="text-xs text-orange-500 mt-0.5">Estás pagando en nombre de tu/s rival/es no soci{{ $cantNoSocios > 1 ? 'os' : 'o' }}</p>
    </div>

    {{-- Instrucciones --}}
    @if($config->payment_instructions)
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl px-4 py-3">
        <p class="text-xs font-semibold text-yellow-700 uppercase mb-1">📋 Instrucciones</p>
        <p class="text-sm text-yellow-800 leading-relaxed">{{ $config->payment_instructions }}</p>
    </div>
    @endif

    {{-- Formas de pago --}}
    <div class="grid grid-cols-2 gap-3">
        @if($config->payment_alias)
        <div class="bg-white rounded-2xl shadow-sm px-3 py-3 flex flex-col justify-between">
            <p class="text-[10px] font-semibold text-gray-500 uppercase mb-2">Transferencia</p>
            <div class="bg-gray-50 rounded-xl px-3 py-2">
                <p class="text-[10px] text-gray-500 mb-0.5">Alias</p>
                <p class="text-sm font-bold text-gray-800 tracking-wide break-all">{{ $config->payment_alias }}</p>
            </div>
        </div>
        @endif
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

    {{-- Upload --}}
    <div class="bg-white rounded-2xl shadow-sm px-4 py-4">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Adjuntar comprobante</p>
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
        @error('comprobante') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        <div wire:loading wire:target="comprobante" class="text-xs text-gray-400 mt-2 text-center">Cargando archivo...</div>
    </div>

    @if($errorImporte)
    <div class="bg-red-50 border border-red-300 rounded-2xl px-4 py-4 flex items-start gap-3">
        <span class="text-2xl leading-none">❌</span>
        <div>
            <p class="text-sm font-bold text-red-700">Comprobante inválido</p>
            <p class="text-xs text-red-600 mt-1">{{ $errorImporte }}</p>
            <p class="text-xs text-red-500 mt-2">Por favor revisá el comprobante y volvé a intentarlo.</p>
        </div>
    </div>
    @endif

    <div x-data="{ procesando: false }" x-init="$watch('$wire.errorImporte', v => { if (v) procesando = false })">
        <button
            wire:click="enviarComprobante"
            @click="if ($wire.comprobante) procesando = true"
            :disabled="procesando || {{ $comprobante ? 'false' : 'true' }}"
            class="w-full bg-[#0057a8] text-white py-4 rounded-2xl font-bold text-base shadow-md hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
            <span x-show="!procesando">{{ $comprobante ? 'Enviar comprobante y confirmar reserva' : 'Adjuntá el comprobante' }}</span>
            <span x-show="procesando">Verificando comprobante...</span>
        </button>
    </div>
    <button wire:click="$set('socioQuierePagar', false)"
            class="w-full border border-gray-300 text-gray-500 py-3 rounded-2xl text-sm font-medium hover:bg-gray-50 transition-colors">
        ← No, que pague el/la rival
    </button>
    <button wire:click="$set('socioQuierePagar', false)"
            class="w-full text-gray-400 py-2 text-sm hover:text-gray-600 transition-colors">
        ← Volver
    </button>

    @elseif($puedeOfrecerPago)
    {{-- Socio puede elegir --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 space-y-3"
         x-data="{ confirmando: false }">
        <div class="text-center">
            <div class="text-4xl mb-1">💰</div>
            <h2 class="text-base font-bold text-blue-800">¿Cómo querés proceder?</h2>
            <p class="text-xs text-blue-600 mt-1">Vos sos socio/a. El pago de tu/s rival/es no-soci{{ $cantNoSocios > 1 ? 'os' : 'o' }} está pendiente.</p>
        </div>
        <button type="button" wire:click="ofrecerPagar"
                class="w-full bg-[#0057a8] text-white py-3 rounded-xl font-bold text-sm">
            Pagar yo la reserva (${{ number_format($totalAPagar, 0, ',', '.') }})
        </button>
        <button type="button" wire:click="dejarQuePagueRival"
                class="w-full border border-gray-300 text-gray-600 py-3 rounded-xl text-sm font-medium hover:bg-gray-50">
            Dejar que pague el/la rival
        </button>

        {{-- Cancelar con confirmación inline --}}
        <div x-show="!confirmando">
            <button type="button" @click="confirmando = true"
                    class="w-full border border-red-300 text-red-500 py-3 rounded-xl text-sm font-medium hover:bg-red-50 transition-colors">
                Cancelar reserva
            </button>
        </div>
        <div x-show="confirmando" class="bg-red-50 border border-red-200 rounded-xl p-3 space-y-2">
            <p class="text-xs text-red-700 font-semibold text-center">¿Confirmar cancelación? No se puede deshacer.</p>
            <div class="flex gap-2">
                <button type="button" @click="confirmando = false"
                        class="flex-1 border border-gray-300 text-gray-600 py-2.5 rounded-xl text-sm font-medium">
                    No, volver
                </button>
                <button type="button" wire:click="cancelarYVolver"
                        class="flex-1 bg-red-600 text-white py-2.5 rounded-xl text-sm font-bold">
                    Sí, cancelar
                </button>
            </div>
        </div>
    </div>

    @else
    {{-- Socio sin opción de pago (rivals ya pagaron o están en revisión) --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 text-center space-y-2">
        <div class="text-4xl">⏳</div>
        <h2 class="text-base font-bold text-blue-800">Esperando pago de tus rivales</h2>
        <p class="text-xs text-blue-700 mt-1">Tu reserva se confirmará cuando tus rivales completen el pago.</p>
    </div>
    <a href="{{ route('agenda') }}" class="block text-center bg-terracota text-white py-3 rounded-2xl font-bold text-sm">
        Volver a la agenda
    </a>
    @endif

    @elseif($enviado)
    {{-- Resultado después de enviar comprobante --}}
    @if($miPagoEstado === 'AUTHORIZED' && $todosAutorizados)
    <div class="bg-green-50 border border-green-200 rounded-2xl p-6 text-center space-y-2">
        <div class="text-5xl">✅</div>
        <h2 class="text-xl font-bold text-green-700">¡Reserva confirmada!</h2>
        @if(!$hayInvitados && $totalAPagar < $totalReserva)
        <p class="text-sm text-green-600">El total de la reserva ya fue abonado por completo.</p>
        @else
        <p class="text-sm text-green-600">Todos los pagos fueron verificados automáticamente.</p>
        @endif
    </div>

    @elseif($miPagoEstado === 'AUTHORIZED' && !$todosAutorizados)
    <div class="bg-green-50 border border-green-200 rounded-2xl p-5 text-center space-y-2">
        <div class="text-4xl mb-1">✅</div>
        <h2 class="text-base font-bold text-green-700">¡Tu pago fue verificado!</h2>
        <p class="text-xs text-green-600 mt-1">Falta que tu/s rival/es abonen su parte. Te avisaremos cuando la reserva esté confirmada.</p>
    </div>

    @elseif($verificacion && !($verificacion['error'] ?? false))
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 space-y-3">
        <div class="text-center">
            <div class="text-4xl mb-1">⚠️</div>
            <h2 class="text-base font-bold text-yellow-800">Comprobante en revisión</h2>
            <p class="text-xs text-yellow-700 mt-1">No pudimos confirmar el pago automáticamente. El club lo revisará.</p>
        </div>
        @if($waUrl)
        <a href="{{ $waUrl }}" target="_blank"
           class="flex items-center justify-center gap-2 w-full bg-[#25D366] text-white py-3 rounded-xl font-bold text-sm">
            <svg viewBox="0 0 24 24" class="w-5 h-5 fill-white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.117 1.528 5.847L0 24l6.335-1.508A11.933 11.933 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.808 9.808 0 01-5.003-1.366l-.36-.214-3.76.896.952-3.655-.235-.376A9.808 9.808 0 012.182 12C2.182 6.57 6.57 2.182 12 2.182S21.818 6.57 21.818 12 17.43 21.818 12 21.818z"/></svg>
            Notificar al admin por WhatsApp
        </a>
        @endif
        {{-- Detalle de verificación --}}
        <div class="bg-white rounded-xl px-3 py-2 space-y-1.5 text-xs">
            @php
                function iconoCheck($val) {
                    if ($val === true)  return ['✓', 'text-green-600'];
                    if ($val === false) return ['✗', 'text-red-600'];
                    return ['?', 'text-gray-400'];
                }
                [$fi, $fc] = iconoCheck($verificacion['fecha_ok'] ?? null);
                [$hi, $hc] = iconoCheck($verificacion['hora_ok'] ?? null);
                [$ii, $ic] = iconoCheck($verificacion['importe_ok'] ?? null);
                [$ai, $ac] = iconoCheck($verificacion['alias_ok'] ?? null);
            @endphp
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Fecha</span>
                <span class="font-semibold {{ $fc }}">{{ $fi }} @if($verificacion['fecha_encontrada'] ?? null)<span class="font-normal text-gray-500">({{ $verificacion['fecha_encontrada'] }})</span>@endif</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Hora <span class="text-[10px] text-gray-400">(±30 min)</span></span>
                <span class="font-semibold {{ $hc }}">
                    @if(($verificacion['hora_ok'] ?? null) === null)<span class="text-gray-400 font-normal text-[10px]">No aparece</span>
                    @else {{ $hi }} @if($verificacion['hora_encontrada'] ?? null)<span class="font-normal text-gray-500">({{ $verificacion['hora_encontrada'] }})</span>@endif
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Importe</span>
                <span class="font-semibold {{ $ic }}">{{ $ii }} @if($verificacion['importe_encontrado'] ?? null)<span class="font-normal text-gray-500">({{ $verificacion['importe_encontrado'] }})</span>@endif</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Alias</span>
                <span class="font-semibold {{ $ac }}">
                    @if(($verificacion['alias_ok'] ?? null) === null)<span class="text-gray-400 font-normal text-[10px]">No aparece</span>
                    @else {{ $ai }} @if($verificacion['alias_encontrado'] ?? null)<span class="font-normal text-gray-500">({{ $verificacion['alias_encontrado'] }})</span>@endif
                    @endif
                </span>
            </div>
        </div>
        @if(!empty($verificacion['detalle']))
        <p class="text-[10px] text-yellow-700 text-center">{{ $verificacion['detalle'] }}</p>
        @endif
    </div>

    @else
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 text-center space-y-2">
        <div class="text-5xl">📋</div>
        <h2 class="text-base font-bold text-blue-800">Comprobante enviado</h2>
        <p class="text-xs text-blue-700">El club revisará el pago y autorizará tu reserva.</p>
    </div>
    @if($waUrl)
    <a href="{{ $waUrl }}" target="_blank"
       class="flex items-center justify-center gap-2 w-full bg-[#25D366] text-white py-3 rounded-xl font-bold text-sm">
        <svg viewBox="0 0 24 24" class="w-5 h-5 fill-white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.117 1.528 5.847L0 24l6.335-1.508A11.933 11.933 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.808 9.808 0 01-5.003-1.366l-.36-.214-3.76.896.952-3.655-.235-.376A9.808 9.808 0 012.182 12C2.182 6.57 6.57 2.182 12 2.182S21.818 6.57 21.818 12 17.43 21.818 12 21.818z"/></svg>
        Notificar al admin por WhatsApp
    </a>
    @endif
    @endif

    <a href="{{ route('agenda') }}"
       class="block text-center bg-terracota text-white py-3 rounded-2xl font-bold text-sm">
        Volver a la agenda
    </a>

    @else
    {{-- ── FORMULARIO DE PAGO (solo cuando el pago está PENDIENTE) ─────────────────────────────── --}}
    @if($miPagoEstado !== '' && $miPagoEstado !== 'PENDIENTE')
    {{-- Pago ya procesado pero sin redirect aún: evitar mostrar el formulario --}}
    <div class="bg-green-50 border border-green-200 rounded-2xl p-6 text-center space-y-2">
        <div class="text-5xl">✅</div>
        <h2 class="text-base font-bold text-green-700">Tu pago fue recibido</h2>
        <p class="text-xs text-green-600">Redirigiendo...</p>
    </div>
    @else

    {{-- Datos del turno --}}
    <div class="bg-[#0057a8] text-white rounded-2xl px-4 py-4">
        <p class="text-xs opacity-70 uppercase font-medium mb-1">Tu reserva</p>
        <p class="font-bold text-lg leading-tight">{{ $turno_dia }}</p>
        <p class="text-sm opacity-90">{{ $turno_hora }} · Cancha {{ $turno_cancha }}</p>
    </div>

    {{-- Aviso importante: hay invitados --}}
    @if($hayInvitados)
    <div class="bg-amber-50 border border-amber-300 rounded-2xl px-4 py-3 flex items-start gap-3">
        <span class="text-2xl leading-none">⚠️</span>
        <div>
            <p class="text-sm font-bold text-amber-800">Reserva con invitados</p>
            <p class="text-xs text-amber-700 mt-1">Como hay un invitado en la reserva, debés abonar el <strong>total completo</strong>. No está disponible el pago dividido.</p>
        </div>
    </div>
    @endif

    {{-- Jugadores --}}
    <div class="bg-white rounded-2xl shadow-sm px-3 py-2">
        <p class="text-[10px] font-semibold text-gray-500 uppercase mb-1">Jugadores</p>
        <div class="divide-y divide-gray-100">
            @foreach($jugadores as $j)
            <div class="flex items-center justify-between py-1.5">
                <span class="text-xs text-gray-800 font-medium">{{ $j['nombre'] }}</span>
                <div class="flex items-center gap-1.5">
                    @if(!$j['es_socio'] && ($j['ya_pago'] ?? false))
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-green-100 text-green-700">pagó</span>
                    @endif
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-medium
                        {{ $j['es_socio'] ? 'bg-green-100 text-green-700' : ($j['es_invitado'] ?? false ? 'bg-amber-100 text-amber-700' : 'bg-orange-100 text-orange-700') }}">
                        {{ $j['es_socio'] ? 'Socio' : ($j['es_invitado'] ?? false ? 'Invitado' : 'No socio') }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Total a pagar --}}
    @if($totalReserva > 0)
    <div class="bg-orange-50 border border-orange-200 rounded-2xl px-4 py-3">
        <div class="flex items-center justify-between">
            <p class="text-xs text-orange-600 font-semibold uppercase">Total a abonar</p>
            <p class="text-2xl font-bold text-orange-600">${{ number_format($montoRestante, 0, ',', '.') }}</p>
        </div>
        @if($montoYaPagado > 0)
        <p class="text-xs text-orange-500 mt-1">
            Ya se abonaron <strong>${{ number_format($montoYaPagado, 0, ',', '.') }}</strong> de ${{ number_format($totalReserva, 0, ',', '.') }}.
        </p>
        @elseif(!$hayInvitados && $totalReserva > $totalAPagar)
        <p class="text-xs text-orange-500 mt-1.5">
            También podés abonar solo tu parte: <strong>${{ number_format($totalAPagar, 0, ',', '.') }}</strong>. Tu rival deberá abonar la suya desde Mis Turnos.
        </p>
        @else
        <p class="text-xs text-orange-500 mt-0.5">{{ $cantNoSocios }} jugador{{ $cantNoSocios > 1 ? 'es' : '' }} no soci{{ $cantNoSocios > 1 ? 'os' : 'o' }}</p>
        @endif
    </div>
    @else
    <div class="bg-green-50 border border-green-200 rounded-2xl px-4 py-3 text-center">
        <p class="text-sm text-green-700 font-semibold">✓ Sin costo adicional</p>
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
        <p class="text-xs text-yellow-600 mt-1">MercadoPago está procesando el pago.</p>
    </div>
    @elseif($mp_result === 'failure')
    <div class="bg-red-50 border border-red-200 rounded-2xl px-4 py-3 text-center">
        <p class="text-sm font-bold text-red-700">❌ Pago rechazado</p>
        <p class="text-xs text-red-600 mt-1">Podés intentarlo de nuevo o pagar por transferencia.</p>
    </div>
    @endif

    {{-- Instrucciones de pago --}}
    @if($config->payment_instructions)
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl px-4 py-3">
        <p class="text-xs font-semibold text-yellow-700 uppercase mb-1">📋 Instrucciones</p>
        <p class="text-sm text-yellow-800 leading-relaxed">{{ $config->payment_instructions }}</p>
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

    {{-- Error de importe --}}
    @if($errorImporte)
    <div class="bg-red-50 border border-red-300 rounded-2xl px-4 py-4 flex items-start gap-3">
        <span class="text-2xl leading-none">❌</span>
        <div>
            <p class="text-sm font-bold text-red-700">Comprobante inválido</p>
            <p class="text-xs text-red-600 mt-1">{{ $errorImporte }}</p>
            <p class="text-xs text-red-500 mt-2">Por favor revisá el comprobante y volvé a intentarlo.</p>
            @if($pagoDemas)
            <p class="text-xs text-red-500 mt-1">Cualquier duda contactá al administrador.</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Botón enviar --}}
    <div x-data="{ procesando: false }"
         x-init="$watch('$wire.errorImporte', v => { if (v) procesando = false })">
        <button
            wire:click="enviarComprobante"
            @click="if ($wire.comprobante) procesando = true"
            :disabled="procesando || {{ $comprobante ? 'false' : 'true' }}"
            class="w-full bg-[#0057a8] text-white py-4 rounded-2xl font-bold text-base shadow-md
                   hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
            <span x-show="!procesando">
                {{ $comprobante ? 'Enviar comprobante y confirmar reserva' : 'Adjuntá el comprobante' }}
            </span>
            <span x-show="procesando">Verificando comprobante...</span>
        </button>
    </div>

    @endif {{-- miPagoEstado === PENDIENTE --}}
    @endif {{-- outer @else --}}
</div>
