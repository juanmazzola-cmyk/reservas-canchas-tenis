<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800">Estadísticas</h2>
    </div>

    {{-- Filtro mes / año --}}
    <div class="bg-white rounded-2xl shadow-sm p-4 flex gap-3">
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
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-500 mb-1">Año</label>
            <select wire:model.live="anio" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-terracota">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
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

    {{-- Ranking jugadores --}}
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <p class="text-sm font-semibold text-gray-700 mb-3">Reservas por jugador</p>
        @if(empty($jugadoresTop))
            <p class="text-sm text-gray-400 text-center py-4">Sin reservas en este período.</p>
        @else
            <div class="space-y-2">
                @foreach($jugadoresTop as $i => $j)
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-400 w-5 text-right">{{ $i + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $j['nombre'] }}</p>
                        <span class="text-[10px] {{ $j['es_socio'] ? 'text-green-600' : 'text-orange-500' }}">
                            {{ $j['es_socio'] ? 'Socio' : 'No socio' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        @php $max = $jugadoresTop[0]['reservas']; @endphp
                        <div class="w-20 bg-gray-100 rounded-full h-1.5">
                            <div class="bg-terracota h-1.5 rounded-full" style="width: {{ round(($j['reservas'] / $max) * 100) }}%"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-700 w-6 text-right">{{ $j['reservas'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
