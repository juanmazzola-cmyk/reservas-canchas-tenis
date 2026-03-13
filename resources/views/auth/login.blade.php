<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - Liga Padres Tenis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        verde: '#16a34a',
                        azul: '#0057a8',
                        terracota: '#c0522b',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-[#0057a8] text-white text-center py-8 px-6">
                <div class="text-5xl mb-2">🎾</div>
                <h1 class="text-2xl font-bold">Liga Padres Tenis</h1>
                <p class="text-sm opacity-80 mt-1">Reserva tus turnos fácil y rápido</p>
            </div>

            <!-- Form -->
            <div class="p-6">
                @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
                @endif

                @if(session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
                    {{ session('status') }}
                </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" x-data="{ showPass: false }">
                    @csrf

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="tu@email.com"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] focus:border-transparent transition"
                        />
                    </div>

                    <!-- Password -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <div class="relative">
                            <input
                                :type="showPass ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] focus:border-transparent transition"
                            />
                            <button
                                type="button"
                                @click="showPass = !showPass"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Botón ingresar -->
                    <button
                        type="submit"
                        class="w-full bg-[#16a34a] hover:bg-green-700 text-white font-bold py-3 rounded-lg transition-colors text-sm tracking-wide"
                    >
                        INGRESAR
                    </button>
                </form>

                <!-- Links -->
                <div class="mt-5 text-center space-y-2">
                    <p class="text-xs text-gray-500">
                        <a href="#" class="text-[#0057a8] hover:underline">Olvidé mi contraseña</a>
                    </p>
                    <p class="text-xs text-gray-600">
                        ¿No tenés cuenta?
                        <a href="{{ route('registro') }}" class="text-[#0057a8] font-medium hover:underline">Registrate</a>
                    </p>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">Liga Padres Tenis © {{ date('Y') }}</p>
    </div>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
