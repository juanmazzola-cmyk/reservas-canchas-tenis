<div class="max-w-sm mx-auto pt-4 space-y-4">

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="bg-[#0057a8] text-white px-6 py-4">
            <h2 class="font-bold text-lg">Escanear carnet</h2>
            <p class="text-sm opacity-80">Apuntá la cámara al QR del socio</p>
        </div>

        <div class="p-4">
            <div id="qr-reader" class="w-full rounded-xl overflow-hidden"></div>
            <p id="qr-status" class="text-center text-sm text-gray-400 mt-3">Iniciando cámara...</p>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    function initScanner() {
        const status = document.getElementById('qr-status');
        const readerEl = document.getElementById('qr-reader');
        if (!status || !readerEl) return;

        if (window._qrScanner) {
            try { window._qrScanner.stop(); } catch(e) {}
            window._qrScanner = null;
        }

        readerEl.innerHTML = '';
        const scanner = new Html5Qrcode('qr-reader');
        window._qrScanner = scanner;

        scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            function (decodedText) {
                scanner.stop().then(() => {
                    window._qrScanner = null;
                    window.location.href = decodedText;
                });
            },
            function () {}
        ).then(() => {
            status.textContent = 'Cámara activa — buscando QR...';
        }).catch(() => {
            status.textContent = 'No se pudo acceder a la cámara. Verificá los permisos.';
        });
    }

    function stopScanner() {
        if (window._qrScanner) {
            try { window._qrScanner.stop(); } catch(e) {}
            window._qrScanner = null;
        }
    }

    document.addEventListener('DOMContentLoaded', initScanner);
    document.addEventListener('livewire:navigated', initScanner);
    document.addEventListener('livewire:navigate', stopScanner);
</script>
@endpush
