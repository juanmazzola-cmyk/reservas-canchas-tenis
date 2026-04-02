<div class="w-full max-w-sm flex flex-col min-h-screen sm:min-h-0 sm:my-8 sm:rounded-2xl sm:shadow-xl sm:border sm:border-gray-200 sm:overflow-hidden">
    <div class="flex flex-col flex-1">

        <!-- Header azul -->
        <div class="bg-[#0057a8] text-white text-center py-8 sm:py-5 px-6">
            <div class="text-4xl sm:text-3xl mb-1">🎾</div>
            <h1 class="text-2xl font-bold">{{ $clubName }}</h1>
            <p class="text-sm opacity-80 mt-1">Reserva tus turnos fácil y rápido</p>
        </div>

        <!-- Fotos de canchas (reemplazá con fotos propias si querés) -->
        <div class="flex overflow-hidden h-48 sm:h-32">
            <img src="https://images.unsplash.com/photo-1554068865-24cecd4e34b8?w=200&h=240&fit=crop&q=80"
                 alt="Cancha de polvo de ladrillo"
                 class="w-1/3 object-cover">
            <img src="https://images.unsplash.com/photo-1587280501635-68a0e82cd5ff?w=200&h=240&fit=crop&q=80"
                 alt="Cancha de polvo de ladrillo"
                 class="w-1/3 object-cover border-x-2 border-white">
            <img src="https://images.unsplash.com/photo-1542144582-1ba00456b5e3?w=200&h=240&fit=crop&q=80"
                 alt="Cancha de polvo de ladrillo"
                 class="w-1/3 object-cover">
        </div>

        <!-- Banner primera vez -->
        <div x-data="{ visible: false, init() { if (!localStorage.getItem('ya_ingrese')) this.visible = true; } }" x-init="init()" x-cloak>
            <div x-show="visible" class="mx-4 mt-4 bg-amber-400 rounded-2xl p-4 text-center shadow-md">
                <p class="text-amber-900 font-bold text-base leading-tight">¿Primera vez que ingresás?</p>
                <a href="{{ route('registro') }}"
                   @click="localStorage.setItem('ya_ingrese', '1'); visible = false"
                   class="inline-block mt-3 bg-amber-900 text-white font-bold text-sm px-6 py-2.5 rounded-full hover:bg-amber-800 transition">
                    Registrarme ahora
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="p-6 sm:p-5 flex-shrink-0">
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    placeholder="tu@email.com"
                    autocomplete="email"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"
                />
            </div>

            <div class="mb-5">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <div class="relative" x-data="{ show: false }">
                    <input
                        id="password"
                        name="password"
                        :type="show ? 'text' : 'password'"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"
                    />
                    <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button
                type="submit"
                class="w-full bg-[#16a34a] hover:bg-green-700 text-white font-bold py-3 rounded-lg transition-colors text-sm tracking-wide">
                INGRESAR
            </button>

            </form>

            <div class="mt-5 text-center space-y-3">
                <p class="text-sm text-gray-500">
                    <a href="{{ route('olvide-password') }}" class="text-[#0057a8] hover:underline">Olvidé mi contraseña</a>
                </p>
                <p class="text-sm text-gray-600">
                    ¿No tenés cuenta?
                    <a href="{{ route('registro') }}" class="text-[#0057a8] font-medium hover:underline">Registrate</a>
                </p>
            </div>
            <p class="text-center text-xs text-gray-400 mt-2">{{ $clubName }} © {{ date('Y') }}</p>
        </div>
    </div>
</div>
