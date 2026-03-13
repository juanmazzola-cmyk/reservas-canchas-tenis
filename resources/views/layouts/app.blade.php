<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\Configuracion::first()?->club_name ?? config('app.name', 'Liga Padres Tenis') }}</title>
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
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        .bounce-in { animation: bounceIn 0.5s ease; }
        @keyframes bounceIn {
            0% { transform: translateY(-20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        .slide-up { animation: slideUp 0.4s ease; }
        @keyframes slideUp {
            0% { transform: translateY(10px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        .pulse-bg { animation: pulseBg 1s ease-in-out infinite; }
        @keyframes pulseBg {
            0%, 100% { background-color: #f59e0b; opacity: 1; }
            50% { background-color: #fde68a; opacity: 0.7; }
        }
        .pulso-flechas { animation: pulsoFlechas 1.4s ease-in-out infinite; }
        @keyframes pulsoFlechas {
            0%, 100% { opacity: 1; filter: brightness(1); }
            50% { opacity: 0.35; filter: brightness(0.5); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen pb-20">

    <!-- Header -->
    @php $clubName = \App\Models\Configuracion::first()?->club_name ?? 'Liga Padres Tenis'; @endphp
    <header id="app-header" class="bg-verde text-white px-4 py-3 flex justify-between items-center shadow-md sticky top-0 z-50 rounded-b-2xl">
        <div>
            <h1 class="text-lg font-bold">🎾 {{ $clubName }}</h1>
            <p class="text-xs opacity-80">Hola, {{ auth()->user()->nombre ?? 'Usuario' }} 👋</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-xs border border-white px-3 py-1 rounded hover:bg-white hover:text-verde transition-colors">
                Salir
            </button>
        </form>
    </header>

    <!-- Aviso Global -->
    @php
        $cfg = null;
        try {
            $cfg = \App\Models\Configuracion::first();
        } catch (\Exception $e) {}
    @endphp
    @if($cfg && $cfg->announcement_enabled && $cfg->announcement_text && !request()->routeIs('agenda'))
    <div class="pulse-bg text-yellow-900 px-4 py-2 text-sm text-center font-medium bounce-in mx-4 mt-2 rounded-xl">
        📢 {{ $cfg->announcement_text }}
    </div>
    @endif

    <!-- Popup Notificación al entrar -->
    @if($cfg && $cfg->notification_text)
    <div x-data="{
            open: false,
            hash: '{{ md5($cfg->notification_text) }}_{{ auth()->id() }}',
            init() {
                const seen = localStorage.getItem('notif_seen');
                if (seen !== this.hash) this.open = true;
            },
            dismiss() {
                localStorage.setItem('notif_seen', this.hash);
                this.open = false;
            }
        }" x-init="init()" x-cloak>
        <div x-show="open" class="fixed inset-0 bg-black/60 z-[70] flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl p-6">
                <h3 class="font-bold text-lg mb-3 text-[#0057a8]">📢 Aviso</h3>
                <p class="text-sm text-gray-700 mb-5">{{ $cfg->notification_text }}</p>
                <button @click="dismiss()" class="w-full bg-[#0057a8] text-white py-2.5 rounded-lg text-sm font-bold hover:bg-blue-700">Entendido</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Banner: recordar cambiar contraseña (solo fuera de la agenda, en agenda va dentro del sticky) -->
    @if(auth()->user()?->must_change_password && !request()->routeIs('agenda'))
    <div x-data="{ visible: !sessionStorage.getItem('pwd_banner_ok_{{ auth()->id() }}') }" x-show="visible"
        class="bg-yellow-400 text-yellow-900 px-4 py-2.5 text-sm font-medium flex items-center justify-between gap-2">
        <span>🔑 Estás usando una contraseña temporal.
            <a href="{{ route('perfil') }}" class="underline font-bold"
               @click.prevent="sessionStorage.setItem('pwd_banner_ok_{{ auth()->id() }}', '1'); window.location.href = '{{ route('perfil') }}'">Cambiala desde tu perfil</a>
        </span>
        <button @click="visible = false; sessionStorage.setItem('pwd_banner_ok_{{ auth()->id() }}', '1')"
            class="text-yellow-800 hover:text-yellow-900 font-bold text-lg leading-none flex-shrink-0">✕</button>
    </div>
    @endif

    <!-- Toast Notifications -->
    <div
        x-data="{
            toasts: [],
            addToast(msg, type = 'success') {
                const id = Date.now();
                this.toasts.push({ id, msg, type });
                setTimeout(() => this.removeToast(id), 3500);
            },
            removeToast(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
        }"
        @toast.window="addToast($event.detail.message, $event.detail.type ?? 'success')"
        class="fixed top-20 right-4 z-[100] flex flex-col gap-2 max-w-xs w-full pointer-events-none"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                class="slide-up rounded-lg shadow-lg px-4 py-3 text-sm font-medium pointer-events-auto cursor-pointer"
                :class="{
                    'bg-green-500 text-white': toast.type === 'success',
                    'bg-red-500 text-white': toast.type === 'error',
                    'bg-yellow-400 text-yellow-900': toast.type === 'warning',
                    'bg-blue-500 text-white': toast.type === 'info',
                }"
                x-text="toast.msg"
                @click="removeToast(toast.id)"
            ></div>
        </template>
    </div>

    <!-- Contenido -->
    <main class="max-w-screen-xl mx-auto px-4 pt-4">
        {{ $slot }}
    </main>

    <!-- Bottom Nav -->
    <nav class="fixed bottom-0 left-0 right-0 bg-verde text-white flex justify-around items-center h-16 z-50 shadow-lg rounded-t-2xl">
        @auth
            @php $rol = auth()->user()->rol; @endphp

            {{-- Agenda (todos los roles) --}}
            <a href="{{ route('agenda') }}" wire:navigate
               class="relative flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('agenda') ? 'text-yellow-300' : 'text-white opacity-80 hover:opacity-100' }}">
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    @if($rol === 'admin')
                        @livewire('nav-badge')
                    @endif
                </div>
                <span>Agenda</span>
            </a>

            @if($rol === 'usuario')
                {{-- Mis Turnos --}}
                <a href="{{ route('mis-turnos') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('mis-turnos') ? 'text-yellow-300' : 'text-white opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="16" y1="2" x2="16" y2="6" stroke-linecap="round"/>
                        <line x1="8" y1="2" x2="8" y2="6" stroke-linecap="round"/>
                        <line x1="3" y1="10" x2="21" y2="10" stroke-linecap="round"/>
                    </svg>
                    <span>Mis Turnos</span>
                </a>
                {{-- Perfil --}}
                <a href="{{ route('perfil') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('perfil') ? 'text-yellow-300' : 'text-white opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Perfil</span>
                </a>
            @endif

            @if($rol === 'admin')
                {{-- Usuarios --}}
                <a href="{{ route('admin.usuarios') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('admin.usuarios') ? 'text-yellow-300' : 'text-white opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Usuarios</span>
                </a>
                {{-- Configuracion --}}
                <a href="{{ route('admin.configuracion') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('admin.configuracion') ? 'text-yellow-300' : 'text-white opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Config</span>
                </a>
                {{-- Estadisticas --}}
                <a href="{{ route('admin.estadisticas') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('admin.estadisticas') ? 'text-yellow-300' : 'text-white opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Stats</span>
                </a>
                {{-- Perfil --}}
                <a href="{{ route('perfil') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('perfil') ? 'text-yellow-300' : 'text-white opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Perfil</span>
                </a>
            @endif

            @if($rol === 'control')
                {{-- Solo Agenda, ya agregada arriba --}}
                <a href="{{ route('perfil') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('perfil') ? 'text-yellow-300' : 'text-white opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Perfil</span>
                </a>
            @endif
        @endauth
    </nav>

    @livewireScripts
    <script>
        document.addEventListener('limpiarBannerPassword', e => {
            sessionStorage.removeItem('pwd_banner_ok_' + e.detail.userId);
        });
    </script>
</body>
</html>
