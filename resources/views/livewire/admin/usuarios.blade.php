<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Usuarios</h2>
        <span class="text-sm text-gray-500">{{ count($usuarios) }} usuarios</span>
    </div>

    {{-- Buscador --}}
    <div class="relative mb-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="busqueda"
            placeholder="Buscar por nombre, apellido o email..."
            autocomplete="off"
            class="w-full border border-gray-200 bg-white rounded-xl px-4 py-2.5 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"
        />
        <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>

    {{-- Lista --}}
    <div class="space-y-2">
        @forelse($usuarios as $u)
        <div class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-3">
            {{-- Avatar --}}
            <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-white text-sm
                {{ $u['rol'] === 'admin' ? 'bg-[#c0522b]' : ($u['rol'] === 'control' ? 'bg-[#0057a8]' : 'bg-[#16a34a]') }}">
                {{ strtoupper(substr($u['nombre'], 0, 1)) }}
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm">{{ $u['apellido'] }}, {{ $u['nombre'] }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $u['email'] }}</p>
                @if($u['dni'])
                    <p class="text-xs text-gray-400">DNI: {{ $u['dni'] }}</p>
                @else
                    <p class="text-xs text-red-400 font-medium">Sin DNI</p>
                @endif
                @if($u['telefono'])
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <p class="text-xs text-gray-400">{{ $u['telefono'] }}</p>
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $u['telefono']) }}" target="_blank"
                            class="flex-shrink-0">
                            <svg viewBox="0 0 24 24" class="w-4 h-4 fill-[#25D366]" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </a>
                    </div>
                @endif
                <div class="flex gap-1.5 mt-1 flex-wrap">
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-medium
                        {{ $u['rol'] === 'admin' ? 'bg-red-100 text-red-700' : ($u['rol'] === 'control' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ $u['rol'] }}
                    </span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full {{ $u['es_socio'] ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                        {{ $u['es_socio'] ? '✓ Socio' : 'No socio' }}
                    </span>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="flex flex-col gap-1.5">
                <button wire:click="abrirEditar({{ $u['id'] }})"
                    class="text-xs bg-[#0057a8] text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition">
                    Editar
                </button>
                @if($u['id'] !== auth()->id())
                <button wire:click="confirmarEliminar({{ $u['id'] }})"
                    class="text-xs border border-red-200 text-red-500 px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                    Eliminar
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl p-8 text-center text-gray-400">
            No se encontraron usuarios.
        </div>
        @endforelse
    </div>

    {{-- MODAL: Editar usuario --}}
    @if($modalEditar)
    <div class="fixed inset-0 bg-black/60 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md shadow-xl max-h-[90vh] flex flex-col">
            <div class="bg-[#0057a8] text-white px-6 py-4 rounded-t-2xl flex-shrink-0">
                <h3 class="font-bold text-lg">Editar usuario</h3>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre *</label>
                        <input type="text" wire:model="editNombre" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('editNombre') border-red-400 @enderror"/>
                        @error('editNombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Apellido *</label>
                        <input type="text" wire:model="editApellido" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('editApellido') border-red-400 @enderror"/>
                        @error('editApellido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">DNI</label>
                    <input type="text" inputmode="numeric" wire:model="editDni" placeholder="Ej: 30123456" autocomplete="off" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('editDni') border-red-400 @enderror"/>
                    @error('editDni') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email *</label>
                    <input type="email" wire:model="editEmail" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('editEmail') border-red-400 @enderror"/>
                    @error('editEmail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Teléfono</label>
                    <input type="tel" wire:model="editTelefono" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Rol</label>
                    <select wire:model="editRol" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]">
                        <option value="usuario">Usuario</option>
                        <option value="control">Control</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-4 py-3">
                    <input type="checkbox" wire:model="editEsSocio" id="editEsSocio" class="w-4 h-4 text-[#16a34a] rounded"/>
                    <label for="editEsSocio" class="text-sm text-gray-700">Es socio del club</label>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nueva contraseña (dejar vacío para no cambiar)</label>
                    <input type="password" wire:model="editPassword" placeholder="Nueva contraseña" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('editPassword') border-red-400 @enderror"/>
                    @error('editPassword') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-3 px-6 py-4 border-t border-gray-100 flex-shrink-0">
                <button wire:click="$set('modalEditar', false)" class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg text-sm hover:bg-gray-50">Cancelar</button>
                <button wire:click="guardarEdicion" wire:loading.attr="disabled" wire:target="guardarEdicion" class="flex-1 bg-[#0057a8] text-white py-2.5 rounded-lg text-sm font-bold hover:bg-blue-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="guardarEdicion">Guardar</span>
                    <span wire:loading wire:target="guardarEdicion">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL: Confirmar eliminar --}}
    @if($modalEliminar)
    <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl p-6">
            <h3 class="font-bold text-lg mb-2">¿Eliminar usuario?</h3>
            <p class="text-sm text-gray-600 mb-4">Esta acción no se puede deshacer.</p>
            <div class="flex gap-3">
                <button wire:click="$set('modalEliminar', false)" class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg text-sm">Cancelar</button>
                <button wire:click="eliminarUsuario" class="flex-1 bg-red-500 text-white py-2.5 rounded-lg text-sm font-bold hover:bg-red-600">Eliminar</button>
            </div>
        </div>
    </div>
    @endif
</div>
