<div class="max-w-sm mx-auto pt-4">

    @if(!$socio)
        <div class="bg-red-50 border border-red-200 rounded-2xl p-8 text-center">
            <p class="text-4xl mb-3">❌</p>
            <p class="font-bold text-red-700 text-lg">Socio no encontrado</p>
            <p class="text-sm text-red-500 mt-1">El código QR no corresponde a ningún usuario.</p>
        </div>

    @elseif(!$socio->es_socio)
        <div class="bg-red-50 border border-red-200 rounded-2xl p-8 text-center">
            <p class="text-4xl mb-3">⛔</p>
            <p class="font-bold text-red-700 text-lg">No es socio</p>
            <p class="text-sm text-red-500 mt-1">{{ $socio->nombre }} {{ $socio->apellido }} no figura como socio de tenis.</p>
        </div>

    @else
        <div class="bg-green-50 border-2 border-green-400 rounded-2xl overflow-hidden shadow-lg">

            {{-- Estado --}}
            <div class="bg-green-500 text-white text-center py-3 px-4">
                <p class="text-2xl font-bold">✅ SOCIO VÁLIDO</p>
            </div>

            {{-- Foto + datos --}}
            <div class="flex gap-4 p-5">
                <div class="flex-shrink-0">
                    @if($socio->foto_carnet)
                        <img src="{{ Storage::url($socio->foto_carnet) }}" alt="Foto"
                             class="w-24 h-28 rounded-xl object-cover border-2 border-green-200"/>
                    @else
                        <div class="w-24 h-28 rounded-xl bg-gray-100 border-2 border-gray-200 flex items-center justify-center text-3xl font-bold text-gray-400">
                            {{ strtoupper(substr($socio->nombre, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="space-y-2">
                    <div>
                        <p class="text-[10px] text-gray-500 uppercase tracking-wider">Nombre</p>
                        <p class="font-bold text-gray-800 text-lg leading-tight">{{ $socio->apellido }}, {{ $socio->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-500 uppercase tracking-wider">DNI</p>
                        <p class="font-semibold text-gray-700">{{ number_format($socio->dni, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-500 uppercase tracking-wider">Nro. Socio</p>
                        <p class="font-semibold text-gray-700">{{ $socio->nro_socio ?? '—' }}</p>
                    </div>
                    @if($socio->grupo_sanguineo)
                    <div>
                        <p class="text-[10px] text-gray-500 uppercase tracking-wider">Grupo sanguíneo</p>
                        <p class="font-semibold text-gray-700">🩸 {{ $socio->grupo_sanguineo }}</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    @endif

</div>
