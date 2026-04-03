<div class="w-full max-w-sm mx-auto min-h-screen sm:min-h-0 sm:my-8" x-data="{ showPass: false }">
    <div class="bg-white sm:rounded-2xl sm:shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-[#0057a8] text-white px-4 pt-2 pb-3">
            <div class="mb-1">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1 text-white/70 hover:text-white text-sm transition">
                    ← Volver
                </a>
            </div>
            <div class="flex items-center justify-center gap-2">
                <span class="text-2xl">🎾</span>
                <h1 class="text-lg font-bold">Crear cuenta</h1>
            </div>
        </div>

        <!-- Form -->
        <div class="p-6">
            <form wire:submit="registrar" class="space-y-4">

                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input
                        type="text"
                        wire:model="nombre"
                        placeholder="Tu nombre"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('nombre') border-red-400 @enderror"
                    />
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Apellido -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellido *</label>
                    <input
                        type="text"
                        wire:model="apellido"
                        placeholder="Tu apellido"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('apellido') border-red-400 @enderror"
                    />
                    @error('apellido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- DNI -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DNI *</label>
                    <input
                        type="number"
                        inputmode="numeric"
                        wire:model="dni"
                        placeholder="Ej: 30123456"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('dni') border-red-400 @enderror"
                    />
                    @error('dni') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                    <input
                        type="tel"
                        wire:model="telefono"
                        placeholder="Ej: 1123456789"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('telefono') border-red-400 @enderror"
                    />
                    <p class="text-red-500 text-xs mt-1">Sin el 0 y sin el 15</p>
                    @error('telefono') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input
                        type="email"
                        wire:model="email"
                        placeholder="tu@email.com"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('email') border-red-400 @enderror"
                    />
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña *</label>
                    <div class="relative">
                        <input
                            :type="showPass ? 'text' : 'password'"
                            wire:model="password"
                            placeholder="Mínimo 6 caracteres"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('password') border-red-400 @enderror"
                        />
                        <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Es Socio -->
                <div x-data="{ socio: @entangle('es_socio') }">
                    <p class="text-sm font-medium text-gray-700 mb-2">¿Sos socio del club?</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div wire:click="$set('es_socio', false)" class="cursor-pointer rounded-xl px-3 py-3 text-center transition-all"
                             :class="!socio ? 'border-2 border-orange-400 bg-orange-50 text-orange-700' : 'border border-gray-200 bg-white text-gray-500'">
                            <div class="text-2xl mb-1">👤</div>
                            <p class="font-bold text-sm leading-tight">No soy socio</p>
                        </div>
                        <div wire:click="$set('es_socio', true)" class="cursor-pointer rounded-xl px-3 py-3 text-center transition-all"
                             :class="socio ? 'border-2 border-[#16a34a] bg-green-50 text-green-700' : 'border border-gray-200 bg-white text-gray-500'">
                            <div class="text-2xl mb-1">⭐</div>
                            <p class="font-bold text-sm leading-tight">Sí, soy socio</p>
                        </div>
                    </div>
                </div>

                <!-- Botón -->
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full bg-[#16a34a] hover:bg-green-700 text-white font-bold py-3 rounded-lg transition-colors text-sm tracking-wide disabled:opacity-60"
                >
                    <span wire:loading.remove>CREAR CUENTA</span>
                    <span wire:loading>Creando cuenta...</span>
                </button>
            </form>

        </div>
    </div>
</div>
