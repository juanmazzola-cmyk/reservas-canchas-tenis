<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\Configuracion::first()?->club_name ?? config('app.name', 'Liga Padres Tenis') }}</title>
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0057a8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Ateneo JAM">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
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
    @php
        $cfgHeader = \App\Models\Configuracion::first();
        $clubName = $cfgHeader?->club_name ?? 'Liga Padres Tenis';
        $clubAddress = $cfgHeader?->club_address;
    @endphp
    <header id="app-header" class="bg-verde text-white px-4 py-3 flex justify-between items-center shadow-md sticky top-0 z-50 rounded-b-2xl">
        <div>
            <h1 class="text-lg font-bold leading-tight">🎾 {{ $clubName }}</h1>
            @if($clubAddress)
                <p class="text-[10px] opacity-70 leading-tight pl-7">📍 {{ $clubAddress }}</p>
            @endif
            <p class="text-xs opacity-80 pl-7">Hola, {{ auth()->user()->nombre ?? 'Usuario' }} 👋</p>
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
    @if($cfg && $cfg->notification_text && auth()->user()->rol === 'usuario')
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
    <nav class="fixed bottom-0 left-0 right-0 bg-verde text-gray-900 flex justify-around items-center h-16 z-50 shadow-lg rounded-t-2xl">
        @auth
            @php
                $rol = auth()->user()->rol;
                $cfgUbic = \App\Models\Configuracion::first();
            @endphp

            {{-- Agenda (todos los roles) --}}
            <a href="{{ route('agenda') }}" wire:navigate
               class="relative flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('agenda') ? 'text-yellow-300 font-semibold' : 'text-gray-900 opacity-80 hover:opacity-100' }}">
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
                @php
                    $haySuspendidas   = \App\Models\Reserva::whereJsonContains('jugadores_ids', auth()->id())->where('estado', 'SUSPENDIDA')->exists();
                    $hayPagoPendiente = \App\Models\Pago::where('user_id', auth()->id())->where('estado', 'PENDIENTE')
                        ->whereHas('reserva', fn($q) => $q->where('estado', '!=', 'DRAFT'))->exists();
                @endphp
                <a href="{{ route('mis-turnos') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('mis-turnos') ? 'text-yellow-300 font-semibold' : 'text-gray-900 opacity-80 hover:opacity-100' }}">
                    <div class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="16" y1="2" x2="16" y2="6" stroke-linecap="round"/>
                            <line x1="8" y1="2" x2="8" y2="6" stroke-linecap="round"/>
                            <line x1="3" y1="10" x2="21" y2="10" stroke-linecap="round"/>
                        </svg>
                        @if($haySuspendidas)
                            <span class="absolute -top-2 -right-2 text-base leading-none drop-shadow">⚠️</span>
                        @endif
                        @if($hayPagoPendiente)
                            <span class="absolute -top-2 -left-2 text-base leading-none drop-shadow">💰</span>
                        @endif
                    </div>
                    <span>Mis Turnos</span>
                </a>
                {{-- Ubicacion --}}
                @php
                    $mapsUrl = null;
                    if ($cfgUbic?->club_lat && $cfgUbic?->club_lng) {
                        $mapsUrl = 'https://maps.google.com/?q=' . $cfgUbic->club_lat . ',' . $cfgUbic->club_lng;
                    } elseif ($cfgUbic?->club_address) {
                        $mapsUrl = 'https://maps.google.com/?q=' . urlencode($cfgUbic->club_address);
                    }
                @endphp
                @if($mapsUrl)
                <a href="{{ $mapsUrl }}" target="_blank"
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors text-gray-900 opacity-80 hover:opacity-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8.134 2 5 5.134 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.866-3.134-7-7-7z"/>
                        <circle cx="12" cy="9" r="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Ubicación</span>
                </a>
                @endif
                {{-- Contacto WhatsApp --}}
                @if($cfgUbic?->admin_whatsapp)
                @php
                    $waNumero = preg_replace('/\D/', '', $cfgUbic->admin_whatsapp);
                    $waContacto = 'https://wa.me/549' . ltrim($waNumero, '0');
                @endphp
                <a href="{{ $waContacto }}" target="_blank"
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors text-gray-900 opacity-80 hover:opacity-100">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <span>Contacto admin</span>
                </a>
                @endif
                {{-- Perfil --}}
                <a href="{{ route('perfil') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('perfil') ? 'text-yellow-300 font-semibold' : 'text-gray-900 opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Perfil</span>
                </a>
            @endif

            @if($rol === 'admin')
                {{-- Usuarios --}}
                <a href="{{ route('admin.usuarios') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('admin.usuarios') ? 'text-yellow-300 font-semibold' : 'text-gray-900 opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Usuarios</span>
                </a>
                {{-- Configuracion --}}
                <a href="{{ route('admin.configuracion') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('admin.configuracion') ? 'text-yellow-300 font-semibold' : 'text-gray-900 opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Config</span>
                </a>
                {{-- Estadisticas --}}
                <a href="{{ route('admin.estadisticas') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('admin.estadisticas') ? 'text-yellow-300 font-semibold' : 'text-gray-900 opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Stats</span>
                </a>
                {{-- Comprobantes --}}
                @php $hayEnRevision = \App\Models\Pago::where('estado', 'PENDING_REVIEW')->whereNotNull('comprobante')->exists(); @endphp
                <a href="{{ route('admin.comprobantes') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('admin.comprobantes') ? 'text-yellow-300 font-semibold' : 'text-gray-900 opacity-80 hover:opacity-100' }}">
                    <div class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        @if($hayEnRevision)
                            <span class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-amber-400 rounded-full border border-white"></span>
                        @endif
                    </div>
                    <span>Recibos</span>
                </a>
                {{-- Perfil --}}
                <a href="{{ route('perfil') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('perfil') ? 'text-yellow-300 font-semibold' : 'text-gray-900 opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Perfil</span>
                </a>
            @endif

            @if($rol === 'control')
                {{-- Mis Turnos (turnos de hoy) --}}
                <a href="{{ route('mis-turnos') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('mis-turnos') ? 'text-yellow-300 font-semibold' : 'text-gray-900 opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="16" y1="2" x2="16" y2="6" stroke-linecap="round"/>
                        <line x1="8" y1="2" x2="8" y2="6" stroke-linecap="round"/>
                        <line x1="3" y1="10" x2="21" y2="10" stroke-linecap="round"/>
                    </svg>
                    <span>Mis Turnos</span>
                </a>
                @if($cfgUbic?->admin_whatsapp)
                @php
                    $waNumero = preg_replace('/\D/', '', $cfgUbic->admin_whatsapp);
                    $waContacto = 'https://wa.me/549' . ltrim($waNumero, '0');
                @endphp
                <a href="{{ $waContacto }}" target="_blank"
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors text-gray-900 opacity-80 hover:opacity-100">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <span>Contacto admin</span>
                </a>
                @endif
                <a href="{{ route('perfil') }}" wire:navigate
                   class="flex flex-col items-center gap-0.5 text-xs px-2 py-1 rounded transition-colors {{ request()->routeIs('perfil') ? 'text-yellow-300 font-semibold' : 'text-gray-900 opacity-80 hover:opacity-100' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Perfil</span>
                </a>
            @endif
        @endauth
    </nav>

    <!-- Banner de instalación PWA -->
    <div id="pwa-banner" style="display:none"
         class="fixed bottom-20 left-0 right-0 z-50 bg-[#0057a8] text-white px-4 py-3 shadow-lg">
        <div class="flex items-center justify-between max-w-sm mx-auto gap-3">
            <div class="flex items-center gap-2">
                <img src="/icon-192.png" class="w-10 h-10 rounded-xl" alt="Logo">
                <div>
                    <p class="font-bold text-sm leading-tight">Instalá la app</p>
                    <p class="text-xs opacity-80">Accedé más rápido desde tu celular</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="instalarPWA()" class="bg-white text-[#0057a8] font-bold text-sm px-3 py-1.5 rounded-lg">
                    Instalar
                </button>
                <button onclick="cerrarBanner()" class="text-white opacity-70 text-lg leading-none px-1">✕</button>
            </div>
        </div>
    </div>

    <!-- iOS: instrucciones manuales -->
    <div id="ios-banner" style="display:none"
         class="fixed bottom-20 left-0 right-0 z-50 bg-[#0057a8] text-white px-4 py-3 shadow-lg">
        <div class="max-w-sm mx-auto">
            <div class="flex justify-between items-start mb-1">
                <p class="font-bold text-sm">Instalá la app en tu iPhone</p>
                <button onclick="cerrarBannerIos()" class="text-white opacity-70 text-lg leading-none px-1">✕</button>
            </div>
            <p class="text-xs opacity-90">Tocá <strong>Compartir</strong> ↑ y luego <strong>"Agregar a pantalla de inicio"</strong></p>
        </div>
    </div>

    @livewireScripts
    <script>
        // Keep-alive: ping cada 2 minutos para mantener la sesión activa
        setInterval(function() {
            fetch('/ping', { credentials: 'same-origin' }).catch(function(){});
        }, 2 * 60 * 1000);

        document.addEventListener('limpiarBannerPassword', e => {
            sessionStorage.removeItem('pwd_banner_ok_' + e.detail.userId);
        });

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }

        // Manejar sesión expirada (419) de forma amigable
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    if (status === 419) {
                        preventDefault()
                        window.location.href = '{{ route("login") }}'
                    }
                })
            })
        })

        if (!window.__pwaInit) {
            window.__pwaInit = true;
            window.deferredPrompt = null;

            const banner = document.getElementById('pwa-banner');
            const iosBanner = document.getElementById('ios-banner');
            const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
            const isInStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                window.deferredPrompt = e;
                if (!sessionStorage.getItem('pwa-banner-closed')) {
                    banner.style.display = 'block';
                }
            });

            if (isIos && !isInStandalone && !sessionStorage.getItem('ios-banner-closed')) {
                iosBanner.style.display = 'block';
            }

            window.instalarPWA = function() {
                if (!window.deferredPrompt) return;
                window.deferredPrompt.prompt();
                window.deferredPrompt.userChoice.then(() => {
                    window.deferredPrompt = null;
                    banner.style.display = 'none';
                });
            };

            window.cerrarBanner = function() {
                banner.style.display = 'none';
                sessionStorage.setItem('pwa-banner-closed', '1');
            };

            window.cerrarBannerIos = function() {
                iosBanner.style.display = 'none';
                sessionStorage.setItem('ios-banner-closed', '1');
            };
        }
    </script>
</body>
</html>
