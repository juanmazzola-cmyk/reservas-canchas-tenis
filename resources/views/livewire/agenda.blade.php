<div x-data="{
    hayMas: false,
    stickyTop: 68,
    bodyMaxH: 'calc(100vh - 300px)',
    init() {
        let h = document.getElementById('app-header');
        this.stickyTop = h ? h.offsetHeight : 68;
        this.$nextTick(() => {
            this.chequear();
            this.calcBodyH();
        });
    },
    calcBodyH() {
        let appH  = (document.getElementById('app-header')  || {offsetHeight: 68}).offsetHeight;
        let stickyEl = this.$el.querySelector('#agenda-sticky');
        let stickyH  = stickyEl ? stickyEl.offsetHeight : 130;
        let mainPt   = 16;  // pt-4
        let bottomNav = 80; // pb-20
        this.bodyMaxH = 'max-height:calc(100vh - ' + (appH + stickyH + mainPt + bottomNav) + 'px)';
    },
    chequear() {
        let el = document.getElementById('agenda-body');
        if (!el) return;
        this.hayMas = el.scrollLeft < (el.scrollWidth - el.clientWidth - 2);
    },
    syncHeader() {
        let head = document.getElementById('agenda-head');
        let body = document.getElementById('agenda-body');
        if (head && body) head.scrollLeft = body.scrollLeft;
    }
}" class="max-w-2xl mx-auto">

    @php
        $cfgAnn = \App\Models\Configuracion::first();
        $hayAnuncio = $cfgAnn && $cfgAnn->announcement_enabled && $cfgAnn->announcement_text;
        $minW       = (56 + count($canchas) * 80) . 'px';
        $diaComplBloqueado = collect($bloqueos)->first(fn($b) => $b['hora'] === null && $b['cancha_id'] === null);
    @endphp

    {{-- ═══════════════════════════════════════════════════
         BLOQUE STICKY: anuncio + días + hint + cabecera tabla
    ══════════════════════════════════════════════════════ --}}
    <div id="agenda-sticky" class="sticky z-30 bg-gray-100 -mx-4 px-4 -mt-4 pt-1" :style="'top:' + stickyTop + 'px'">

        {{-- Anuncio global (si está activo) --}}
        @if($hayAnuncio)
        <div class="pulse-bg text-yellow-900 px-4 py-2 text-sm text-center font-medium mb-1 rounded-xl">
            📢 {{ $cfgAnn->announcement_text }}
        </div>
        @endif

        {{-- Banner contraseña temporal --}}
        @if(auth()->user()->must_change_password)
        <div x-data="{ visible: !sessionStorage.getItem('pwd_banner_ok_{{ auth()->id() }}') }" x-show="visible"
            class="-mx-4 bg-yellow-400 text-yellow-900 px-4 py-2 text-sm font-medium flex items-center justify-between gap-2 mb-1">
            <span>🔑 Contraseña temporal.
                <a href="{{ route('perfil') }}" class="underline font-bold"
                   @click.prevent="sessionStorage.setItem('pwd_banner_ok_{{ auth()->id() }}', '1'); window.location.href = '{{ route('perfil') }}'">Cambiala en tu perfil</a>
            </span>
            <button @click="visible = false; sessionStorage.setItem('pwd_banner_ok_{{ auth()->id() }}', '1')"
                class="font-bold text-lg leading-none flex-shrink-0">✕</button>
        </div>
        @endif

        {{-- Acceso rápido: Seguimiento de torneos --}}
        <a href="{{ app()->environment('local') ? 'http://localhost/org-torneo-tenis/public' : 'https://torneos.proyectosia.com.ar' }}"
            class="flex items-center gap-2 w-full bg-[#c0522b] hover:bg-[#a8441f] text-white px-4 py-2 rounded-xl mb-2 transition">
            <span class="text-lg">🏆</span>
            <span class="text-sm font-semibold">Seguimiento de torneos</span>
            <span class="ml-auto font-bold opacity-80 tracking-tighter">&gt;&gt;&gt;&gt;&gt;</span>
        </a>

        {{-- Selector de días --}}
        <div class="flex gap-2 overflow-x-auto pt-1 pb-2 scrollbar-hide items-center">
            <span class="flex-shrink-0 text-sm font-bold text-[#0057a8] pr-1 whitespace-nowrap">Elegí el día</span>
            @foreach($dias as $dia)
            <button
                wire:click="seleccionarDia('{{ $dia['clave'] }}')"
                class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                    {{ $diaSeleccionado === $dia['clave']
                        ? 'bg-[#0057a8] text-white shadow'
                        : 'bg-white text-gray-600 border border-gray-200 hover:border-[#0057a8]' }}"
            >{{ $dia['etiqueta'] }}</button>
            @endforeach
        </div>

        {{-- Indicador más canchas --}}
        <div class="flex justify-end pr-1" style="height:22px">
            <span
                x-bind:style="hayMas ? 'visibility:visible' : 'visibility:hidden'"
                class="inline-flex items-center gap-1 bg-[#0057a8] text-white text-xs font-bold px-3 py-0.5 rounded-full shadow"
            >más canchas →</span>
        </div>

        {{-- Cabecera de tabla (sincronizada horizontalmente con el cuerpo) --}}
        @if(!$diaComplBloqueado)
        <div id="agenda-head" class="-mx-4 overflow-hidden" style="scrollbar-width:none; -ms-overflow-style:none">
            <table class="text-xs border-collapse" style="min-width:{{ $minW }}; table-layout:fixed">
                <colgroup>
                    <col style="width:56px">
                    @foreach($canchas as $c)<col style="width:80px">@endforeach
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-left px-2 py-1.5 text-gray-500 bg-gray-100" style="width:56px">Hora</th>
                        @foreach($canchas as $cancha)
                        @php $canchaBloq = collect($bloqueos)->first(fn($b) => $b['hora'] === null && $b['cancha_id'] == $cancha['id']); @endphp
                        <th class="px-1 py-1.5 bg-gray-100" style="width:80px">
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="text-[9px] text-gray-400 uppercase tracking-wide">Cancha</span>
                                <div class="w-8 h-8 {{ $canchaBloq ? 'bg-gray-500' : 'bg-[#0057a8]' }} text-white rounded-full flex items-center justify-center font-bold text-sm">
                                    {{ $cancha['nombre'] }}
                                </div>
                                @if($canchaBloq && !empty($canchaBloq['razon']))
                                    <span class="text-[9px] text-gray-500 leading-tight text-center">{{ $canchaBloq['razon'] }}</span>
                                @endif
                                @if(auth()->user()->rol === 'admin')
                                    @if($canchaBloq)
                                        <button wire:click="desbloquearCancha({{ $cancha['id'] }})" class="text-[10px] text-yellow-500">🔓</button>
                                    @else
                                        <button wire:click="abrirModalBloqueo('cancha', '', {{ $cancha['id'] }})" class="text-[10px] text-gray-400 hover:text-red-500">🔒</button>
                                    @endif
                                @endif
                            </div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
            </table>
        </div>
        @endif

    </div>{{-- /sticky block --}}

    {{-- ═══════════════════════════════════════════════════
         CONTENIDO: día bloqueado ó grilla de turnos
    ══════════════════════════════════════════════════════ --}}
    @if($diaComplBloqueado)
        <div class="bg-gray-900 text-white rounded-xl p-8 text-center mt-2">
            <div class="text-4xl mb-2">🔒</div>
            <p class="font-bold text-lg">Día bloqueado</p>
            <p class="text-sm text-gray-400 mt-1">{{ $diaComplBloqueado['razon'] ?? 'No hay turnos disponibles' }}</p>
        </div>
    @else
        {{-- Cuerpo de la tabla (solo tbody, scrolleable) --}}
        <div
            id="agenda-body"
            @scroll="chequear(); syncHeader()"
            class="overflow-auto -mx-4"
            :style="bodyMaxH"
        >
            <table class="text-xs border-collapse" style="min-width:{{ $minW }}; table-layout:fixed">
                <colgroup>
                    <col style="width:56px">
                    @foreach($canchas as $c)<col style="width:80px">@endforeach
                </colgroup>
                <tbody>
                    @foreach($horarios as $hora)
                    @php
                        $horaBloq = collect($bloqueos)->first(fn($b) => $b['hora'] === $hora && $b['cancha_id'] === null);
                    @endphp
                    <tr class="border-t border-gray-100 {{ $horaBloq ? 'bg-gray-50' : '' }}">
                        <td class="px-2 py-1 text-gray-500 font-mono text-xs sticky left-0 bg-white z-10"
                            style="{{ $horaBloq ? 'background:#f9fafb' : '' }}">
                            <div class="flex items-center gap-1">
                                <span>{{ $hora }}</span>
                                @if(auth()->user()->rol === 'admin')
                                    @if($horaBloq)
                                        <button wire:click="desbloquearHora('{{ $hora }}')" class="text-yellow-500 hover:text-yellow-600">🔓</button>
                                    @else
                                        <button wire:click="abrirModalBloqueo('hora', '{{ $hora }}')" class="text-gray-300 hover:text-red-400 transition-colors">🔒</button>
                                    @endif
                                @endif
                            </div>
                        </td>
                        @foreach($canchas as $cancha)
                            @php $celda = $this->getCeldaInfo($hora, $cancha['id']); @endphp
                            <td class="px-1 py-0.5">
                                @if($celda['tipo'] === 'vencida')
                                    <div class="rounded bg-gray-100 text-center py-2 text-gray-400 text-[11px]">—</div>

                                @elseif($celda['tipo'] === 'sin_anticipacion')
                                    <div class="rounded bg-gray-50 border border-gray-200 text-center py-2 text-gray-300 text-[11px]">—</div>

                                @elseif($celda['tipo'] === 'bloqueada')
                                    <div class="rounded bg-gray-800 text-center py-1.5 text-white text-[11px] flex flex-col items-center gap-0.5">
                                        <span>🔒</span>
                                        @if(!empty($celda['razon']))
                                            <span class="text-[9px] text-gray-300 leading-tight px-0.5 break-words w-full text-center">{{ $celda['razon'] }}</span>
                                        @endif
                                        @if(auth()->user()->rol === 'admin')
                                            <button wire:click="desbloquear('{{ $hora }}', {{ $cancha['id'] }})"
                                                class="text-yellow-400 text-[10px] underline">quitar</button>
                                        @endif
                                    </div>

                                @elseif($celda['tipo'] === 'ocupada')
                                    @php $puedeVerDetalle = $celda['es_mia'] || auth()->user()->rol === 'admin'; @endphp
                                    @if($puedeVerDetalle)
                                    @php
                                        $esParcial = in_array($celda['estado'] ?? '', ['PARTIAL_PAYMENT', 'PENDING']) && $celda['es_mia'];
                                    @endphp
                                    <button wire:click="seleccionarTurno('{{ $hora }}', {{ $cancha['id'] }})"
                                        class="w-full rounded border text-left px-1.5 py-1 text-[10px] leading-snug transition
                                            {{ $esParcial ? 'bg-amber-100 border-amber-400 hover:bg-amber-200' : ($celda['es_mia'] ? 'bg-blue-50 border-blue-200 hover:bg-blue-100' : 'bg-gray-100 border-gray-200 hover:bg-gray-200') }}">
                                        @foreach($celda['apellidos'] as $ap)
                                            <div class="truncate text-center font-medium {{ str_ends_with($ap, ' *') ? 'text-orange-500' : ($esParcial ? 'text-amber-800' : ($celda['es_mia'] ? 'text-blue-800' : 'text-gray-600')) }}">{{ $ap }}</div>
                                        @endforeach
                                        @if(!$celda['esta_pagado'])
                                            @if($esParcial)
                                                <div class="text-amber-700 font-bold text-[10px] mt-0.5 text-center">Pago pend.</div>
                                            @else
                                                <div class="text-orange-500 font-semibold text-[10px] mt-0.5 text-center">Falta aut.</div>
                                            @endif
                                        @endif
                                    </button>
                                    @elseif(auth()->user()->rol === 'control')
                                    @php
                                        $estadoCelda = $celda['estado'] ?? '';
                                        $labelPago = match(true) {
                                            $celda['esta_pagado']              => null,
                                            $estadoCelda === 'AUTHORIZED'      => null,
                                            $estadoCelda === 'PARTIAL_PAYMENT' => ['txt' => 'Pago parcial', 'cls' => 'text-amber-600'],
                                            $estadoCelda === 'PENDING'         => ['txt' => 'Pago pend.',  'cls' => 'text-amber-600'],
                                            $estadoCelda === 'PENDING_REVIEW'  => ['txt' => 'En revisión',  'cls' => 'text-purple-600'],
                                            default                            => ['txt' => 'Falta pago',   'cls' => 'text-red-500'],
                                        };
                                    @endphp
                                    <div class="w-full rounded border bg-gray-100 border-gray-200 px-1.5 py-1 text-[10px] leading-snug">
                                        @foreach($celda['apellidos'] as $ap)
                                            <div class="truncate text-center font-medium {{ str_ends_with($ap, ' *') ? 'text-orange-500' : 'text-gray-600' }}">{{ $ap }}</div>
                                        @endforeach
                                        @if($labelPago)
                                            <div class="font-semibold text-[10px] mt-0.5 text-center {{ $labelPago['cls'] }}">{{ $labelPago['txt'] }}</div>
                                        @endif
                                    </div>
                                    @else
                                    <div class="w-full rounded border bg-gray-100 border-gray-200 px-1.5 py-1 text-[10px] leading-snug">
                                        @foreach($celda['apellidos'] as $ap)
                                            <div class="truncate text-center font-medium {{ str_ends_with($ap, ' *') ? 'text-orange-500' : 'text-gray-600' }}">{{ $ap }}</div>
                                        @endforeach
                                        @if(!$celda['esta_pagado'])
                                            <div class="text-orange-500 font-semibold text-[10px] mt-0.5 text-center">Falta aut.</div>
                                        @endif
                                    </div>
                                    @endif

                                @else {{-- libre --}}
                                    @php $conflicto = $horasConflicto[$hora] ?? null; @endphp
                                    <div class="flex flex-col gap-0.5">
                                        @if($conflicto && auth()->user()->rol === 'usuario')
                                            <div x-data="{ show: false }" class="relative">
                                                <button @click="show = true; setTimeout(() => show = false, 3500)"
                                                    class="w-full rounded bg-green-50 border border-green-100 text-center py-2 text-green-600 text-[11px] font-medium">
                                                    LIBRE
                                                </button>
                                                <div x-show="show" x-cloak
                                                    class="absolute left-full top-1/2 -translate-y-1/2 ml-1 z-50 bg-red-600 text-white text-[11px] rounded-lg px-2.5 py-2 shadow-xl leading-snug"
                                                    style="width:160px">
                                                    @if($conflicto === 'mismo_horario')
                                                        Vos o tu rival ya tienen reservado en el mismo horario.
                                                    @else
                                                        No podés reservar dos turnos consecutivos.
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif(auth()->user()->rol === 'control')
                                            <div class="w-full rounded bg-gray-50 border border-gray-200 text-center py-2 text-gray-400 text-[11px]">LIBRE</div>
                                        @else
                                            <button wire:click="seleccionarTurno('{{ $hora }}', {{ $cancha['id'] }})"
                                                class="w-full rounded bg-green-50 border border-green-100 hover:bg-[#16a34a] hover:text-white hover:border-[#16a34a] text-center py-2 text-green-600 text-[11px] font-medium transition-colors">
                                                LIBRE
                                            </button>
                                        @endif
                                        @if(auth()->user()->rol === 'admin')
                                            <button wire:click="abrirModalBloqueo('celda', '{{ $hora }}', {{ $cancha['id'] }})"
                                                class="w-full text-[10px] text-gray-300 hover:text-red-400 transition-colors text-center">
                                                🔒
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════
         MODAL: Nueva Reserva (pantalla completa)
    ══════════════════════════════════════════════════════ --}}
    @if($modalReserva)
    @php
        $maxJugadores = $modalTipo === 'single' ? 2 : 4;
        $rivalesNecesarios = $maxJugadores - 1;
        $rivalesAgregados = count($jugadoresSeleccionados) - 1 + count($invitadoApellidos);
        $faltanRivales = $rivalesNecesarios - $rivalesAgregados;
        $totalActual = count($jugadoresSeleccionados) + count($invitadoApellidos);
        $hayInvitadoSinApellido = collect($invitadoApellidos)->contains(fn($a) => trim($a) === '');
        $puedeConfirmar = $totalActual >= $maxJugadores && !$avisoConflicto && !$hayInvitadoSinApellido;
    @endphp
    <div class="fixed left-0 right-0 bottom-0 z-50 flex flex-col bg-white shadow-2xl" :style="'top:' + stickyTop + 'px'">

        {{-- Header azul --}}
        <div class="bg-[#0057a8] text-white px-4 py-3 flex-shrink-0 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-base leading-tight">Reservar turno</h3>
                <p class="text-xs opacity-75">{{ $modalDia }} · {{ $modalHora }} · Cancha {{ $modalCancha }}</p>
            </div>
            <button type="button" wire:click="$set('modalReserva', false)" class="text-white/80 hover:text-white text-sm font-medium border border-white/40 rounded-lg px-3 py-1">← Volver</button>
        </div>

        {{-- Contenido scrolleable --}}
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-4 pb-6">

            {{-- Aviso de conflicto --}}
            @if($avisoConflicto)
            <div class="bg-red-50 border border-red-300 rounded-xl px-4 py-3 flex items-start gap-2">
                <span class="text-red-500 text-lg leading-none mt-0.5">⚠</span>
                <p class="text-sm text-red-700 font-medium">{{ $avisoConflicto }}</p>
            </div>
            @endif

            {{-- Selector Single / Dobles --}}
            <div class="grid grid-cols-2 gap-2">
                <button wire:click="setTipo('single')"
                    class="py-3 rounded-xl border-2 text-center transition-all font-bold text-sm
                        {{ $modalTipo === 'single'
                            ? 'border-[#0057a8] bg-[#0057a8] text-white'
                            : 'border-gray-200 bg-gray-50 text-gray-500' }}">
                    🎾🎾 Single
                    <p class="text-[11px] font-normal mt-0.5 {{ $modalTipo === 'single' ? 'opacity-75' : 'text-gray-400' }}">vos + 1 rival</p>
                </button>
                <button wire:click="setTipo('dobles')"
                    class="py-3 rounded-xl border-2 text-center transition-all font-bold text-sm
                        {{ $modalTipo === 'dobles'
                            ? 'border-[#0057a8] bg-[#0057a8] text-white'
                            : 'border-gray-200 bg-gray-50 text-gray-500' }}">
                    🎾🎾🎾🎾 Dobles
                    <p class="text-[11px] font-normal mt-0.5 {{ $modalTipo === 'dobles' ? 'opacity-75' : 'text-gray-400' }}">vos + 3 rivales</p>
                </button>
            </div>

            {{-- Jugadores seleccionados --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1.5">
                    Jugadores {{ count($jugadoresSeleccionados) }}/{{ $maxJugadores }}
                    @if($faltanRivales > 0)
                        <span class="text-orange-500 normal-case font-normal">— falta{{ $faltanRivales > 1 ? 'n' : '' }} {{ $faltanRivales }} rival{{ $faltanRivales > 1 ? 'es' : '' }}</span>
                    @endif
                </p>
                <div class="space-y-1.5">
                    @foreach($jugadoresSeleccionados as $j)
                    <div class="flex items-center justify-between bg-blue-50 border border-blue-100 rounded-lg px-3 py-2">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-[#0057a8] text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($j['apellido'], 0, 1)) }}
                            </div>
                            <div>
                                <span class="text-sm text-gray-800">{{ $j['nombre'] }} {{ $j['apellido'] }}</span>
                                @if($j['id'] === auth()->id())
                                    <span class="text-[10px] text-blue-400 ml-1">Vos</span>
                                @endif
                                <div class="text-[10px] {{ $j['es_socio'] ? 'text-green-600' : 'text-orange-500' }}">
                                    {{ $j['es_socio'] ? 'Socio' : 'No socio' }}
                                </div>
                            </div>
                        </div>
                        @if($j['id'] !== auth()->id())
                        <button wire:click="quitarJugador({{ $j['id'] }})" class="text-red-400 text-xs font-semibold pl-2">Quitar</button>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Buscador (solo cuando quedan slots libres) --}}
            @if($totalActual < $maxJugadores)
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1.5">
                    Agregar {{ $modalTipo === 'single' ? 'rival registrado' : 'rival / compañero registrado' }}
                </p>
                <div x-data="{ abierto: false }">
                <input
                    type="text"
                    wire:model="busquedaJugador"
                    wire:keyup="buscarJugador"
                    @input="abierto = true"
                    @blur="setTimeout(() => abierto = false, 200)"
                    placeholder="Buscar por nombre o apellido..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"
                />
                @if(!empty($resultadosBusqueda))
                <div x-show="abierto" class="border border-gray-200 rounded-lg mt-1.5 divide-y divide-gray-100 shadow-sm overflow-hidden">
                    @foreach($resultadosBusqueda as $r)
                    <button
                        wire:click="agregarJugador({{ $r['id'] }})"
                        class="w-full text-left px-3 py-2.5 text-sm hover:bg-blue-50 flex items-center justify-between transition-colors">
                        <span class="font-medium text-gray-800">{{ $r['nombre'] }} {{ $r['apellido'] }}</span>
                        <span class="text-xs {{ $r['es_socio'] ? 'text-green-600 bg-green-50' : 'text-orange-500 bg-orange-50' }} px-2 py-0.5 rounded-full">
                            {{ $r['es_socio'] ? 'Socio' : 'No socio' }}
                        </span>
                    </button>
                    @endforeach
                </div>
                @endif
                </div>

                {{-- Aviso + botón de invitado --}}
                <div class="mt-3 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5">
                    <p class="text-xs text-amber-800">
                        <span class="font-semibold">¿El jugador no está registrado?</span>
                        Podés agregarlo como invitado indicando solo su apellido.
                    </p>
                    <button wire:click="agregarInvitado"
                        class="mt-2 w-full text-xs font-semibold text-amber-700 border border-amber-300 bg-white hover:bg-amber-50 rounded-lg py-2 transition-colors">
                        + Agregar invitado
                    </button>
                </div>
            </div>
            @endif

            {{-- Invitados con input de apellido --}}
            @if(!empty($invitadoApellidos))
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1.5">Invitados</p>
                <div class="space-y-2">
                    @foreach($invitadoApellidos as $slot => $apellido)
                    <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        <div class="w-7 h-7 bg-amber-400 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">
                            ?
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] text-amber-600 font-semibold mb-0.5">Invitado {{ $slot }}</p>
                            <input
                                type="text"
                                wire:model.live.debounce.400ms="invitadoApellidos.{{ $slot }}"
                                placeholder="Apellido..."
                                maxlength="40"
                                class="w-full border border-amber-300 bg-white rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                            />
                        </div>
                        <button wire:click="quitarInvitado({{ $slot }})" class="text-red-400 text-xs font-semibold pl-1">Quitar</button>
                    </div>
                    @endforeach
                </div>
                <div class="mt-2 bg-red-50 border border-red-300 rounded-xl px-3 py-2.5 flex items-start gap-2">
                    <span class="text-red-500 text-base leading-none mt-0.5">⚠️</span>
                    <p class="text-xs text-red-700">
                        <span class="font-semibold">Reserva con invitado:</span> tenés que abonar el <strong>total completo de la reserva</strong>. No está disponible el pago dividido.
                    </p>
                </div>
            </div>
            @endif

        </div>

        {{-- Footer confirmar --}}
        <div class="flex-shrink-0 px-4 pt-3 pb-20 border-t border-gray-100 bg-white"
             x-data="{ cargando: false }">
            <button
                type="button"
                wire:click="confirmarReserva"
                @click="cargando = true"
                :disabled="cargando || {{ $puedeConfirmar ? 'false' : 'true' }}"
                class="w-full bg-[#0057a8] text-white py-3.5 rounded-xl text-sm font-bold hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                <span x-show="!cargando">
                    @if($avisoConflicto)
                        No se puede confirmar
                    @elseif($totalActual < $maxJugadores)
                        Completá los jugadores
                    @elseif($hayInvitadoSinApellido)
                        Completá los apellidos de invitados
                    @else
                        CONFIRMAR RESERVA
                    @endif
                </span>
                <span x-show="cargando">Reservando...</span>
            </button>
        </div>

    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════
         MODAL: Detalle reserva
    ══════════════════════════════════════════════════════ --}}
    {{-- ═══════════════════════════════════════════════════
         MODAL: Etiqueta de bloqueo
    ══════════════════════════════════════════════════════ --}}
    @if($modalBloqueo)
    <div class="fixed inset-0 bg-black/60 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-sm shadow-xl p-6">
            <h3 class="font-bold text-base mb-1">Bloquear
                @if($bloqueoTipo === 'celda') celda {{ $bloqueoHora }} — Cancha {{ $bloqueoCancha }}
                @elseif($bloqueoTipo === 'hora') horario {{ $bloqueoHora }}
                @else cancha {{ $bloqueoCancha }}
                @endif
            </h3>
            <p class="text-xs text-gray-500 mb-3">Seleccioná el motivo del bloqueo.</p>
            <div class="grid grid-cols-2 gap-2 mb-4">
                @foreach($motivos as $m)
                <button
                    wire:click="$set('bloqueoMotivoId', {{ $m['id'] }})"
                    class="flex items-center gap-2 px-3 py-2.5 rounded-xl border text-sm font-medium transition
                        {{ $bloqueoMotivoId === $m['id']
                            ? 'border-gray-800 bg-gray-800 text-white'
                            : 'border-gray-200 text-gray-700 hover:border-gray-400' }}"
                >
                    <span class="text-lg">{{ $m['emoji'] }}</span>
                    <span>{{ $m['descripcion'] }}</span>
                </button>
                @endforeach
            </div>
            <div class="flex gap-3">
                <button type="button" wire:click="$set('modalBloqueo', false)"
                    class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg text-sm">
                    Cancelar
                </button>
                <button type="button" wire:click="confirmarBloqueo"
                    @if(!$bloqueoMotivoId) disabled @endif
                    class="flex-1 bg-gray-800 text-white py-2.5 rounded-lg text-sm font-bold hover:bg-gray-900 disabled:opacity-40 disabled:cursor-not-allowed">
                    🔒 Bloquear
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($modalDetalle && $detalleReserva)
    @php
        $dr = $detalleReserva;
        $drJugadores = \App\Models\User::whereIn('id', $dr->jugadores_ids ?? [])->get();
    @endphp
    <div class="fixed inset-0 bg-black/60 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md shadow-xl max-h-[90vh] flex flex-col">
            <div class="bg-[#0057a8] text-white px-6 py-4 rounded-t-2xl flex-shrink-0">
                <h3 class="font-bold text-lg">Detalle del turno</h3>
                <p class="text-sm opacity-80">{{ $dr->dia }} — {{ $dr->hora }} — Cancha {{ $dr->cancha_id }}</p>
            </div>
            <div class="p-6 overflow-y-auto flex-1">
                <div class="mb-4">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-2">Jugadores</p>
                    @foreach($drJugadores as $jug)
                    <div class="flex items-center gap-2 py-1.5 border-b border-gray-100">
                        <div class="w-7 h-7 bg-[#0057a8] text-white rounded-full flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr($jug->nombre, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium">{{ $jug->nombre }} {{ $jug->apellido }}</p>
                            <p class="text-xs text-gray-400">{{ $jug->telefono }}</p>
                        </div>
                        <span class="ml-auto text-xs {{ $jug->es_socio ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }} px-2 py-0.5 rounded-full">
                            {{ $jug->es_socio ? 'Socio' : 'No socio' }}
                        </span>
                    </div>
                    @endforeach
                    @foreach($dr->invitados ?? [] as $inv)
                    <div class="flex items-center gap-2 py-1.5 border-b border-gray-100">
                        <div class="w-7 h-7 bg-amber-400 text-white rounded-full flex items-center justify-center text-xs font-bold">
                            ?
                        </div>
                        <div>
                            <p class="text-sm font-medium">{{ $inv['apellido'] }}</p>
                            <p class="text-xs text-amber-500">Invitado</p>
                        </div>
                        <span class="ml-auto text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">No socio</span>
                    </div>
                    @endforeach
                </div>
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-sm">Estado de pago:</span>
                    @if($dr->esta_pagado)
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">✓ PAGADO</span>
                    @else
                        <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full font-medium">⚠ PENDIENTE</span>
                    @endif
                </div>
                @if(auth()->user()->rol === 'admin')
                <div class="flex flex-col gap-2 mt-4">

                    {{-- Comprobante adjunto --}}
                    @if($dr->comprobante)
                    <div class="bg-purple-50 border border-purple-200 rounded-xl p-3">
                        <p class="text-xs font-semibold text-purple-700 mb-2">📎 Comprobante adjunto</p>
                        @php $ext = pathinfo($dr->comprobante, PATHINFO_EXTENSION); @endphp
                        @if(in_array(strtolower($ext), ['jpg','jpeg','png']))
                            <img src="{{ asset('storage/' . $dr->comprobante) }}"
                                 class="w-full rounded-lg border border-purple-200 max-h-48 object-contain bg-white"
                                 alt="Comprobante">
                        @else
                            <a href="{{ asset('storage/' . $dr->comprobante) }}" target="_blank"
                               class="flex items-center gap-2 text-sm text-purple-700 font-medium underline">
                                📄 Ver PDF
                            </a>
                        @endif
                    </div>
                    @endif

                    @if(!$dr->esta_pagado)
                        @if($dr->estado === 'PENDING_REVIEW')
                        <button wire:click="autorizarPago({{ $dr->id }})"
                            class="w-full bg-[#16a34a] text-white py-3 rounded-xl text-sm font-bold hover:bg-green-700">
                            ✓ Autorizar pago y confirmar reserva
                        </button>
                        @else
                        <button wire:click="marcarPagado({{ $dr->id }})"
                            class="w-full bg-[#16a34a] text-white py-2.5 rounded-lg text-sm font-bold hover:bg-green-700">
                            ✓ Marcar como pagado
                        </button>
                        @endif
                    @endif

                    <button wire:click="cancelarReservaAdmin({{ $dr->id }})"
                        class="w-full bg-red-500 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-red-600">
                        Cancelar reserva
                    </button>
                </div>
                @endif
                <button type="button" wire:click="$set('modalDetalle', false)"
                    class="w-full mt-2 border border-gray-300 text-gray-700 py-2.5 rounded-lg text-sm hover:bg-gray-50">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
