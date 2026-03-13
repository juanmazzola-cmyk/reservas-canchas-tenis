<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Liga Padres Tenis') }}</title>
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
    @livewireScripts
</body>
</html>
