<div class="max-w-sm mx-auto space-y-4 pb-4">

    {{-- Carnet --}}
    <div class="bg-gradient-to-br from-[#0057a8] to-[#003f7a] rounded-2xl shadow-xl overflow-hidden text-white">

        {{-- Header --}}
        <div class="flex items-center gap-2 px-5 pt-4 pb-3 border-b border-white/20">
            <span class="text-xl">🎾</span>
            <div>
                <p class="text-[10px] uppercase tracking-widest opacity-70">Liga Padres Tenis</p>
                <p class="text-sm font-bold leading-tight">Carnet de Socio</p>
            </div>
        </div>

        {{-- Cuerpo --}}
        <div class="flex gap-4 px-5 py-4">

            {{-- Foto --}}
            <div class="flex-shrink-0">
                <label class="cursor-pointer block">
                    <div class="w-24 h-28 rounded-xl overflow-hidden bg-white/10 border-2 border-white/30 flex items-center justify-center relative group">
                        @if($fotoUrl)
                            <img src="{{ $fotoUrl }}" alt="Foto" class="w-full h-full object-cover"/>
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <span class="text-xs text-white font-semibold">Cambiar</span>
                            </div>
                        @else
                            <div class="text-center px-2">
                                <svg class="w-8 h-8 mx-auto opacity-50 mb-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                                </svg>
                                <p class="text-[10px] opacity-70 leading-tight">Tocá para agregar foto</p>
                            </div>
                        @endif
                    </div>
                    <input type="file" wire:model="foto" accept="image/*" capture="user" class="hidden"/>
                </label>
                <div wire:loading wire:target="foto" class="text-[10px] text-center mt-1 opacity-70">Subiendo...</div>
            </div>

            {{-- Datos --}}
            <div class="flex-1 space-y-2">
                <div>
                    <p class="text-[10px] uppercase tracking-wider opacity-60">Nombre</p>
                    <p class="font-bold text-base leading-tight">{{ $user->apellido }}, {{ $user->nombre }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wider opacity-60">DNI</p>
                    <p class="font-semibold text-sm">{{ number_format($user->dni, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wider opacity-60">Nro. Socio</p>
                    <p class="font-semibold text-sm">{{ $user->nro_socio ?? '—' }}</p>
                </div>
                @if($user->grupo_sanguineo)
                <div>
                    <p class="text-[10px] uppercase tracking-wider opacity-60">Grupo sanguíneo</p>
                    <p class="font-semibold text-sm">🩸 {{ $user->grupo_sanguineo }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- QR --}}
        <div class="border-t border-white/20 px-5 py-4 flex items-center gap-4">
            <div id="qr-code" class="bg-white rounded-lg p-1.5 flex-shrink-0"></div>
            <div>
                <p class="text-xs font-semibold">Mostrá este QR al control</p>
                <p class="text-[10px] opacity-60 mt-0.5 leading-relaxed">El personal escanea el QR desde la app y ve tu identidad en pantalla al instante.</p>
            </div>
        </div>
    </div>

    {{-- Instrucción foto --}}
    @if(!$fotoUrl)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
        <p class="font-semibold mb-1">📷 Agregá tu foto</p>
        <p class="text-xs">Tocá el recuadro de la foto para tomar una selfie. Es necesaria para que el personal pueda verificar tu identidad.</p>
    </div>
    @endif

</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    function renderQR() {
        const el = document.getElementById('qr-code');
        if (!el) return;
        el.innerHTML = '';
        new QRCode(el, {
            text: '{{ route('verificar-socio', $user->id) }}',
            width: 90,
            height: 90,
            colorDark: '#003f7a',
            colorLight: '#ffffff',
        });
    }

    function initQR() {
        if (typeof QRCode !== 'undefined') {
            renderQR();
        } else {
            const t = setInterval(() => {
                if (typeof QRCode !== 'undefined') {
                    clearInterval(t);
                    renderQR();
                }
            }, 50);
        }
    }

    document.addEventListener('DOMContentLoaded', initQR);
    document.addEventListener('livewire:navigated', initQR);
</script>
@endpush
</div>
