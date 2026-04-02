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
