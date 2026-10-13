{{-- Aviso informativo de fichas de luz (requiere propiedad $avisoFichasLuz en el componente) --}}
<div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-5 relative">
        <button type="button" wire:click="$set('avisoFichasLuz', false)"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl leading-none" aria-label="Cerrar">×</button>
        <p class="text-3xl text-center mb-2">⚡</p>
        <p class="text-sm text-gray-700 text-center">No olvides adquirir fichas de luz, disponibles en la entrada o en Secretaría.</p>
        <button type="button" wire:click="$set('avisoFichasLuz', false)"
            class="mt-4 w-full bg-[#0057a8] text-white py-2.5 rounded-lg text-sm font-bold hover:bg-blue-700 transition">
            Cerrar
        </button>
    </div>
</div>
