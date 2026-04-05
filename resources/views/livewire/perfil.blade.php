<div class="max-w-lg mx-auto space-y-4">

    {{-- Card principal --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="bg-[#0057a8] text-white px-6 py-5 flex items-center gap-4">
            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center text-2xl font-bold">
                {{ strtoupper(substr(auth()->user()->nombre, 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-lg">{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</p>
                <p class="text-sm opacity-80">{{ auth()->user()->email }}</p>
                <div class="flex gap-2 mt-1">
                    <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full capitalize">{{ auth()->user()->rol }}</span>
                    @if(auth()->user()->es_socio)
                        <span class="text-xs bg-green-400/30 px-2 py-0.5 rounded-full">✓ Socio</span>
                    @else
                        <span class="text-xs bg-orange-400/30 px-2 py-0.5 rounded-full">No socio</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Editar datos</h3>
            <form wire:submit="guardarPerfil" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                        <input type="text" wire:model="nombre" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('nombre') border-red-400 @enderror"/>
                        @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Apellido</label>
                        <input type="text" wire:model="apellido" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('apellido') border-red-400 @enderror"/>
                        @error('apellido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">DNI</label>
                    <input type="number" inputmode="numeric" wire:model="dni" placeholder="Ej: 30123456" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('dni') border-red-400 @enderror"/>
                    @error('dni') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if(auth()->user()->es_socio)
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Número de socio</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm flex items-center justify-between">
                        @if(auth()->user()->nro_socio)
                            <span class="font-semibold text-gray-700">{{ auth()->user()->nro_socio }}</span>
                        @else
                            <span class="text-gray-400 italic">Sin asignar — contactá al administrador</span>
                        @endif
                        <span class="text-[10px] text-gray-400 bg-gray-200 px-2 py-0.5 rounded-full ml-2 shrink-0">No editable</span>
                    </div>
                </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Teléfono</label>
                    <input type="tel" wire:model="telefono" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" wire:model="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('email') border-red-400 @enderror"/>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" class="w-full bg-[#0057a8] text-white py-2.5 rounded-lg text-sm font-bold hover:bg-blue-700 transition disabled:opacity-60">
                    <span wire:loading.remove>Guardar cambios</span>
                    <span wire:loading>Guardando...</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Aviso para no socios --}}
    @if(!auth()->user()->es_socio)
    @php
        $config = \App\Models\Configuracion::getConfig();
        $waAdmin = $config->admin_whatsapp ? preg_replace('/\D/', '', $config->admin_whatsapp) : null;
        $user = auth()->user();
        $waMsg = urlencode("Hola, soy {$user->nombre} {$user->apellido} (DNI: {$user->dni}). Me hice socio del club y quiero que actualicen mi perfil.");
    @endphp
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <div class="flex items-start gap-3">
            <span class="text-2xl">⭐</span>
            <div class="flex-1">
                <p class="font-semibold text-gray-800 text-sm">¿Te hiciste socio del club?</p>
                <p class="text-xs text-gray-500 mt-1">Avisale al administrador para que actualice tu perfil y accedas a los beneficios de socio.</p>
                @if($waAdmin)
                <a href="https://wa.me/{{ $waAdmin }}?text={{ $waMsg }}" target="_blank"
                   class="mt-3 flex items-center justify-center gap-2 w-full bg-[#25D366] text-white py-2.5 rounded-xl text-sm font-bold hover:bg-green-600 transition">
                    <svg viewBox="0 0 24 24" class="w-4 h-4 fill-white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Avisar al administrador por WhatsApp
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Cambiar contraseña --}}
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Cambiar contraseña</h3>
        <form wire:submit="cambiarPassword" class="space-y-3" x-data="{ showOld: false, showNew: false }">

            @if(!auth()->user()->must_change_password)
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Contraseña actual</label>
                <div class="relative">
                    <input :type="showOld ? 'text' : 'password'" wire:model="passwordActual"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('passwordActual') border-red-400 @enderror"/>
                    <button type="button" @click="showOld = !showOld" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error('passwordActual') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nueva contraseña</label>
                <div class="relative">
                    <input :type="showNew ? 'text' : 'password'" wire:model="passwordNuevo"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('passwordNuevo') border-red-400 @enderror"/>
                    <button type="button" @click="showNew = !showNew" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error('passwordNuevo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Confirmar nueva contraseña</label>
                <input type="password" wire:model="passwordConfirm"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('passwordConfirm') border-red-400 @enderror"/>
                @error('passwordConfirm') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled" class="w-full bg-gray-800 text-white py-2.5 rounded-lg text-sm font-bold hover:bg-gray-900 transition disabled:opacity-60">
                <span wire:loading.remove>Cambiar contraseña</span>
                <span wire:loading>Actualizando...</span>
            </button>
        </form>
    </div>

</div>
