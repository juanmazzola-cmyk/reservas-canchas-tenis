<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Liga Padres Tenis') }}</title>
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0057a8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Ateneo JAM">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32.png">
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
</head>
<body class="bg-white sm:bg-gray-100 min-h-screen flex items-stretch sm:items-center justify-center">
    {{ $slot }}

    <!-- Banner de instalación PWA -->
    <div id="pwa-banner" style="display:none"
         class="fixed bottom-0 left-0 right-0 z-50 bg-[#0057a8] text-white px-4 py-3 shadow-lg">
        <div class="flex items-center justify-between max-w-sm mx-auto gap-3">
            <div class="flex items-center gap-2">
                <img src="/icons/icon-192.png" class="w-10 h-10 rounded-xl" alt="Logo">
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
         class="fixed bottom-0 left-0 right-0 z-50 bg-[#0057a8] text-white px-4 py-3 shadow-lg">
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
        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }

        let deferredPrompt = null;
        const banner = document.getElementById('pwa-banner');
        const iosBanner = document.getElementById('ios-banner');

        // Detectar iOS
        const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
        const isInStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (!sessionStorage.getItem('pwa-banner-closed')) {
                banner.style.display = 'block';
            }
        });

        // Mostrar banner iOS solo en registro
        if (isIos && !isInStandalone && !sessionStorage.getItem('ios-banner-closed')) {
            iosBanner.style.display = 'block';
        }

        function instalarPWA() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(() => {
                deferredPrompt = null;
                banner.style.display = 'none';
            });
        }

        function cerrarBanner() {
            banner.style.display = 'none';
            sessionStorage.setItem('pwa-banner-closed', '1');
        }

        function cerrarBannerIos() {
            iosBanner.style.display = 'none';
            sessionStorage.setItem('ios-banner-closed', '1');
        }
    </script>
</body>
</html>
