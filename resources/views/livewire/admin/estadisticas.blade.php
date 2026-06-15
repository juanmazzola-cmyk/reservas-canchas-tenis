<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800">Estadísticas</h2>
    </div>

    {{-- Filtro granularidad + período --}}
    <div class="bg-white rounded-2xl shadow-sm p-4 space-y-3">
        {{-- Toggle Día / Mes / Año --}}
        <div class="flex gap-2">
            <button wire:click="$set('granularidad', 'dia')"
                    class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors {{ $granularidad === 'dia' ? 'bg-terracota text-white' : 'bg-gray-100 text-gray-600' }}">
                Día
            </button>
            <button wire:click="$set('granularidad', 'mes')"
                    class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors {{ $granularidad === 'mes' ? 'bg-terracota text-white' : 'bg-gray-100 text-gray-600' }}">
                Mes
            </button>
            <button wire:click="$set('granularidad', 'anio')"
                    class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors {{ $granularidad === 'anio' ? 'bg-terracota text-white' : 'bg-gray-100 text-gray-600' }}">
                Año
            </button>
        </div>

        {{-- Selectores según granularidad --}}
        <div class="flex gap-3">
            @if($granularidad === 'dia')
            @php $diasEnMes = \Carbon\Carbon::create($anio, $mes, 1)->daysInMonth; @endphp
            <div class="w-20">
                <label class="block text-xs font-medium text-gray-500 mb-1">Día</label>
                <select wire:model.live="dia" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-terracota">
                    @for($d = 1; $d <= $diasEnMes; $d++)
                        <option value="{{ $d }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                    @endfor
                </select>
            </div>
            @endif

            @if($granularidad !== 'anio')
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Mes</label>
                <select wire:model.live="mes" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-terracota">
                    <option value="1">Enero</option>
                    <option value="2">Febrero</option>
                    <option value="3">Marzo</option>
                    <option value="4">Abril</option>
                    <option value="5">Mayo</option>
                    <option value="6">Junio</option>
                    <option value="7">Julio</option>
                    <option value="8">Agosto</option>
                    <option value="9">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
            </div>
            @endif

            <div class="{{ $granularidad === 'anio' ? 'flex-1' : 'w-24' }}">
                <label class="block text-xs font-medium text-gray-500 mb-1">Año</label>
                <select wire:model.live="anio" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-terracota">
                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    {{-- Cards principales --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-terracota text-white rounded-2xl p-4 col-span-2">
            <p class="text-xs opacity-80 uppercase font-medium">Reservas del período</p>
            <p class="text-4xl font-bold mt-1">{{ $totalPeriodo }}</p>
        </div>

        <div class="bg-orange-50 border border-orange-100 rounded-2xl p-4">
            <p class="text-xs text-orange-600 uppercase font-medium">Pendientes de pago</p>
            <p class="text-3xl font-bold text-orange-600 mt-1">{{ $pendientesPago }}</p>
        </div>

        <div class="bg-green-50 border border-green-100 rounded-2xl p-4">
            <p class="text-xs text-green-700 uppercase font-medium">Pagadas</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ $pagadas }}</p>
        </div>
    </div>

    {{-- Usuarios --}}
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <p class="text-sm font-semibold text-gray-700 mb-3">Usuarios registrados</p>
        <div class="flex items-center justify-around mb-3">
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-800">{{ $totalUsuarios }}</p>
                <p class="text-xs text-gray-500">Total</p>
            </div>
            <div class="h-12 w-px bg-gray-100"></div>
            <div class="text-center">
                <p class="text-2xl font-bold text-green-600">{{ $totalSocios }}</p>
                <p class="text-xs text-gray-500">Socios</p>
            </div>
            <div class="h-12 w-px bg-gray-100"></div>
            <div class="text-center">
                <p class="text-2xl font-bold text-orange-500">{{ $totalNoSocios }}</p>
                <p class="text-xs text-gray-500">No socios</p>
            </div>
        </div>
        @if($totalUsuarios > 0)
        @php $pct = round(($totalSocios / $totalUsuarios) * 100); @endphp
        <div class="flex rounded-full overflow-hidden h-2">
            <div class="bg-green-500" style="width: {{ $pct }}%"></div>
            <div class="bg-orange-400 flex-1"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1">
            <span>{{ $pct }}% socios</span>
            <span>{{ 100 - $pct }}% no socios</span>
        </div>
        @endif
    </div>

    {{-- Pagos del período --}}
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <p class="text-sm font-semibold text-gray-700 mb-3">Pagos del período</p>

        {{-- Total autorizado --}}
        <div class="bg-green-50 border border-green-100 rounded-xl p-3 mb-3">
            <p class="text-xs text-green-700 uppercase font-medium">Total autorizado</p>
            <p class="text-2xl font-bold text-green-700 mt-0.5">${{ number_format($montoAutorizado, 0, ',', '.') }}</p>
        </div>

        {{-- Pendiente revisión --}}
        @if($montoPendienteRevision > 0)
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-3 mb-3">
            <p class="text-xs text-yellow-700 uppercase font-medium">Pendiente de revisión</p>
            <p class="text-xl font-bold text-yellow-700 mt-0.5">${{ number_format($montoPendienteRevision, 0, ',', '.') }}</p>
        </div>
        @endif

        {{-- Desglose --}}
        <div class="grid grid-cols-2 gap-2">
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-[10px] text-gray-500 uppercase font-medium leading-tight">No socios<br>sin invitados</p>
                <p class="text-xl font-bold text-gray-800 mt-1">${{ number_format($montoNoSocios, 0, ',', '.') }}</p>
            </div>
            <div class="bg-orange-50 border border-orange-100 rounded-xl p-3">
                <p class="text-[10px] text-orange-600 uppercase font-medium leading-tight">Con<br>invitados</p>
                <p class="text-xl font-bold text-orange-600 mt-1">${{ number_format($montoConInvitados, 0, ',', '.') }}</p>
                @if($cantInvitados > 0)
                <p class="text-[10px] text-orange-400 mt-0.5">{{ $cantInvitados }} {{ $cantInvitados === 1 ? 'invitado' : 'invitados' }}</p>
                @endif
            </div>
        </div>

        @if($montoAutorizado === 0.0 && $montoPendienteRevision === 0.0)
            <p class="text-sm text-gray-400 text-center py-2 mt-2">Sin pagos en este período.</p>
        @endif
    </div>

    {{-- Estadísticas por cancha --}}
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <p class="text-sm font-semibold text-gray-700 mb-3">Uso por cancha</p>
        @php $maxCancha = collect($estadisticasCanchas)->max('total'); @endphp
        @if($maxCancha === 0)
            <p class="text-sm text-gray-400 text-center py-4">Sin reservas en este período.</p>
        @else
            <div class="space-y-4">
                @foreach($estadisticasCanchas as $c)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $c['nombre'] }}</span>
                        <span class="text-sm font-bold text-gray-800">{{ $c['total'] }} {{ $c['total'] === 1 ? 'turno' : 'turnos' }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2 mb-2">
                        <div class="bg-terracota h-2 rounded-full transition-all" style="width: {{ round(($c['total'] / $maxCancha) * 100) }}%"></div>
                    </div>
                    @if(!empty($c['horas']))
                    <div class="flex flex-wrap gap-1">
                        @foreach($c['horas'] as $hora => $cant)
                        <span class="text-[10px] bg-gray-100 text-gray-600 rounded-md px-1.5 py-0.5">{{ $hora }} <span class="font-bold text-gray-800">×{{ $cant }}</span></span>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
