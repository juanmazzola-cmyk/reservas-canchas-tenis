<div class="max-w-2xl mx-auto space-y-4">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Configuración del Club</h2>

    <form wire:submit="guardar" class="space-y-4">

        {{-- General --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">General</h3>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del club</label>
                    <input type="text" wire:model="club_name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('club_name') border-red-400 @enderror"/>
                    @error('club_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div x-data="{
                    obtenerGPS() {
                        if (!navigator.geolocation) { alert('Tu dispositivo no soporta geolocalización.'); return; }
                        navigator.geolocation.getCurrentPosition(
                            pos => {
                                $wire.set('club_lat', pos.coords.latitude.toFixed(7));
                                $wire.set('club_lng', pos.coords.longitude.toFixed(7));
                            },
                            () => alert('No se pudo obtener la ubicación. Verificá los permisos.')
                        );
                    }
                }">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dirección del club</label>
                    <input type="text" wire:model="club_address" placeholder="Ej: Av. Siempreviva 742, Buenos Aires"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
                    <p class="text-[10px] text-gray-400 mt-1">Texto de referencia (opcional si cargás coordenadas).</p>

                    <div class="mt-2 flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-[10px] font-medium text-gray-500 mb-1">Latitud</label>
                            <input type="text" wire:model="club_lat" placeholder="-34.6037"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
                        </div>
                        <div class="flex-1">
                            <label class="block text-[10px] font-medium text-gray-500 mb-1">Longitud</label>
                            <input type="text" wire:model="club_lng" placeholder="-58.3816"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
                        </div>
                        <button type="button" @click="obtenerGPS()"
                            class="flex items-center gap-1.5 bg-gray-800 text-white text-xs font-medium px-3 py-2 rounded-lg hover:bg-gray-700 transition-colors whitespace-nowrap">
                            📍 Usar mi GPS
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Las coordenadas se usan para el botón de ubicación en la app. Tocá "Usar mi GPS" desde el club para mayor precisión.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Cantidad de canchas</label>
                        <input type="number" wire:model.live="court_count" min="1" max="20"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('court_count') border-red-400 @enderror"/>
                        @error('court_count') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Horas de anticipación máx.</label>
                        <input type="number" wire:model="advance_booking_limit_hours" min="1"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('advance_booking_limit_hours') border-red-400 @enderror"/>
                        @error('advance_booking_limit_hours') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Ventana para enviar comprobante (minutos)</label>
                    <input type="number" wire:model="payment_window_minutes" min="5"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('payment_window_minutes') border-red-400 @enderror"/>
                    <p class="text-[10px] text-gray-400 mt-0.5">El comprobante debe tener una hora dentro de esta ventana antes del momento en que se sube. Por defecto: 30 min.</p>
                    @error('payment_window_minutes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Nombres de canchas --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Nombres de canchas</h3>
            <div class="grid grid-cols-2 gap-3">
                @for($i = 0; $i < $court_count; $i++)
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cancha {{ $i + 1 }}</label>
                    <input type="text" wire:model="cancha_names.{{ $i }}"
                        placeholder="{{ $i + 1 }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
                </div>
                @endfor
            </div>
            <p class="text-xs text-gray-400 mt-2">Si lo dejás vacío, se usa el número de orden.</p>
        </div>

        {{-- Horarios --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Horarios disponibles</h3>

            <div class="flex flex-wrap gap-2 mb-3">
                @foreach($horarios as $slot)
                <div class="flex items-center gap-1 bg-blue-50 border border-blue-100 rounded-full px-3 py-1">
                    <span class="text-xs font-mono text-blue-800">{{ $slot }}</span>
                    <button type="button" wire:click="quitarSlot('{{ $slot }}')" class="text-blue-300 hover:text-red-500 transition ml-1">×</button>
                </div>
                @endforeach
                @if(empty($horarios))
                    <p class="text-sm text-gray-400">No hay horarios configurados.</p>
                @endif
            </div>

            <div class="flex gap-2">
                <input type="text" wire:model="nuevoSlot" placeholder="HH:MM (ej: 08:00)"
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] font-mono @error('nuevoSlot') border-red-400 @enderror"/>
                <button type="button" wire:click="agregarSlot"
                    class="bg-[#0057a8] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    + Agregar
                </button>
            </div>
            @error('nuevoSlot') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Pagos --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Pagos</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Precio para no socios ($)</label>
                    <input type="number" wire:model="non_member_price" min="0" step="0.01"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('non_member_price') border-red-400 @enderror"/>
                    @error('non_member_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alias de pago</label>
                    <input type="text" wire:model="payment_alias" placeholder="Ej: ateneo.jam.tenis"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">CBU / CVU</label>
                    <input type="text" wire:model="payment_cbu" placeholder="22 dígitos"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] font-mono"/>
                    <p class="text-[10px] text-gray-400 mt-0.5">Se usa para verificar comprobantes donde no aparece el alias.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Número de cuenta corriente</label>
                    <input type="text" wire:model="payment_cuenta" placeholder="Ej: 123-456789/0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] font-mono"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">CUIT</label>
                    <input type="text" wire:model="payment_cuit" placeholder="Ej: 20-12345678-9"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] font-mono"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Link de MercadoPago</label>
                    <input type="url" wire:model="payment_link" placeholder="https://mpago.la/..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('payment_link') border-red-400 @enderror"/>
                    @error('payment_link') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Instrucciones de pago</label>
                    <textarea wire:model="payment_instructions" rows="3" placeholder="Cómo pagar, a qué nombre, etc."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] resize-none"></textarea>
                </div>

                <div class="border-t border-gray-100 pt-3 mt-1">
                    <p class="text-xs font-semibold text-[#009EE3] uppercase mb-2">Mercado Pago</p>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Access Token</label>
                            <input type="password" wire:model="mp_access_token" placeholder="APP_USR-..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#009EE3] font-mono"/>
                            <p class="text-xs text-gray-400 mt-0.5">Credencial privada de tu cuenta MP (nunca la compartas).</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Public Key</label>
                            <input type="text" wire:model="mp_public_key" placeholder="APP_USR-..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#009EE3] font-mono"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contacto --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Contacto y Avisos</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">WhatsApp del admin</label>
                    <input type="text" wire:model="admin_whatsapp" placeholder="Ej: 1112345678 (sin 0, sin 15)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] @error('admin_whatsapp') border-red-400 @enderror"/>
                    @error('admin_whatsapp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Texto del anuncio global</label>
                    <textarea wire:model="announcement_text" rows="2" placeholder="Mensaje que verán todos los usuarios..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] resize-none"></textarea>
                </div>
                <div class="flex items-center gap-3 bg-yellow-50 rounded-lg px-4 py-3">
                    <button
                        type="button"
                        wire:click="$set('announcement_enabled', {{ $announcement_enabled ? 'false' : 'true' }})"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $announcement_enabled ? 'bg-yellow-400' : 'bg-gray-200' }}"
                    >
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $announcement_enabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                    <label class="text-sm text-gray-700">
                        Anuncio activo
                        <span class="text-xs text-gray-500 block">Mostrar aviso amarillo en la app</span>
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notificación al entrar</label>
                    <textarea wire:model="notification_text" rows="2" placeholder="Si hay texto, aparece un popup al usuario cuando abre la app. Borrarlo para desactivar."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] resize-none"></textarea>
                    <p class="text-xs text-gray-400 mt-1">Si cambiás el texto, el popup vuelve a aparecer para todos.</p>
                </div>
            </div>
        </div>

    </form>

    {{-- Bloquear días completos --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-1 pb-2 border-b border-gray-100">Bloquear días completos</h3>
        <p class="text-xs text-gray-400 mb-3">Bloqueá un día entero para que no se puedan hacer reservas.</p>
        <div class="space-y-2">
            @foreach($proximosDias as $dia)
            <div wire:key="dia-{{ $dia['fecha'] }}" class="flex items-center gap-2">
                <button wire:click="toggleBloqueo('{{ $dia['fecha'] }}')"
                    class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors
                        {{ $dia['bloqueado']
                            ? 'bg-red-500 text-white hover:bg-red-600'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $dia['bloqueado'] ? '🔒' : '' }} {{ $dia['etiqueta'] }}
                </button>
                @if($dia['bloqueado'])
                <div class="flex-1 flex gap-1">
                    <input type="text"
                        value="{{ $dia['razon'] }}"
                        placeholder="Motivo del bloqueo..."
                        class="flex-1 border border-red-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-red-400"/>
                    <button type="button"
                        @click="$wire.guardarMotivo('{{ $dia['fecha'] }}', $el.previousElementSibling.value)"
                        class="flex-shrink-0 text-xs bg-red-100 text-red-600 px-2.5 py-1.5 rounded-lg hover:bg-red-200 font-medium">✓</button>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- WhatsApp Masivo --}}
    <div class="bg-white rounded-2xl shadow-sm p-5" x-data="{ mensaje: '', mostrar: false }">
        <h3 class="font-semibold text-gray-800 mb-1 pb-2 border-b border-gray-100">WhatsApp Masivo</h3>
        <p class="text-xs text-gray-400 mb-3">Escribí el mensaje y abrí cada contacto para enviar por WhatsApp.</p>

        <textarea x-model="mensaje" rows="3" placeholder="Escribí el mensaje a enviar..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8] resize-none mb-3"></textarea>

        <button type="button" @click="mostrar = !mostrar"
            class="w-full text-sm bg-[#25D366] text-white px-4 py-2 rounded-lg font-medium hover:bg-green-600 transition">
            <span x-text="mostrar ? 'Ocultar contactos' : 'Ver contactos ({{ count($usuariosConTelefono) }})'"></span>
        </button>

        <div x-show="mostrar" class="mt-3 space-y-2">
            @forelse($usuariosConTelefono as $u)
            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $u['apellido'] }}, {{ $u['nombre'] }}</p>
                    <p class="text-xs text-gray-400">{{ $u['telefono'] }}</p>
                </div>
                <a :href="`https://wa.me/{{ preg_replace('/\D/', '', $u['telefono']) }}?text=${encodeURIComponent(mensaje)}`"
                   target="_blank"
                   class="flex-shrink-0 bg-[#25D366] text-white text-xs px-3 py-1.5 rounded-lg hover:bg-green-600 font-medium">
                    Enviar
                </a>
            </div>
            @empty
            <p class="text-xs text-gray-400 text-center py-2">No hay usuarios con teléfono registrado.</p>
            @endforelse
        </div>
    </div>

    {{-- Verificación IA --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-1 pb-2 border-b border-gray-100">Verificación IA de comprobantes</h3>
        <p class="text-xs text-gray-400 mb-4">Estadísticas de comprobantes analizados automáticamente por Claude.</p>

        {{-- Alerta de vencimiento --}}
        @if($statsIA['alertaVencimiento'])
        <div class="bg-red-50 border border-red-300 rounded-xl px-4 py-3 mb-4 flex items-start gap-2">
            <span class="text-lg leading-none">⚠️</span>
            <div>
                <p class="text-sm font-bold text-red-700">¡Los créditos de la API vencen en {{ $statsIA['diasRestantes'] }} día{{ $statsIA['diasRestantes'] !== 1 ? 's' : '' }}!</p>
                <p class="text-xs text-red-600 mt-0.5">Recargá créditos en console.anthropic.com y actualizá la fecha abajo.</p>
            </div>
        </div>
        @endif

        {{-- Contadores --}}
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-blue-50 rounded-xl px-3 py-3 text-center">
                <p class="text-2xl font-bold text-blue-700">{{ $statsIA['total'] }}</p>
                <p class="text-[10px] text-blue-500 font-medium uppercase mt-0.5">Total verificados</p>
            </div>
            <div class="bg-green-50 rounded-xl px-3 py-3 text-center">
                <p class="text-2xl font-bold text-green-700">{{ $statsIA['confirmadas'] }}</p>
                <p class="text-[10px] text-green-500 font-medium uppercase mt-0.5">Confirmados auto</p>
            </div>
            <div class="bg-yellow-50 rounded-xl px-3 py-3 text-center">
                <p class="text-2xl font-bold text-yellow-700">{{ $statsIA['revision'] }}</p>
                <p class="text-[10px] text-yellow-500 font-medium uppercase mt-0.5">A revisión manual</p>
            </div>
        </div>

        {{-- Costo estimado --}}
        <div class="bg-gray-50 rounded-xl px-4 py-3 flex items-center justify-between mb-4">
            <div>
                <p class="text-xs font-semibold text-gray-600">Costo estimado acumulado</p>
                <p class="text-[10px] text-gray-400 mt-0.5">~$0.002 USD por verificación</p>
            </div>
            <p class="text-lg font-bold text-gray-700">~${{ number_format($statsIA['costoEstimado'], 3) }} USD</p>
        </div>

        {{-- Fecha de carga de créditos --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Fecha de carga de créditos</label>
            <input type="date" wire:model="anthropic_credits_date"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0057a8]"/>
            @if($statsIA['vencimiento'])
            <p class="text-[10px] mt-1 {{ $statsIA['alertaVencimiento'] ? 'text-red-500 font-semibold' : 'text-gray-400' }}">
                Vence el {{ $statsIA['vencimiento']->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                · {{ $statsIA['diasRestantes'] >= 0 ? $statsIA['diasRestantes'] . ' días restantes' : 'VENCIDO' }}
            </p>
            @else
            <p class="text-[10px] text-gray-400 mt-1">Registrá la fecha en que cargaste los créditos para recibir alerta de vencimiento.</p>
            @endif
        </div>

        @if($statsIA['total'] === 0)
        <p class="text-xs text-gray-400 text-center mt-3">Aún no se verificó ningún comprobante.</p>
        @endif
    </div>

    <button wire:click="guardar" wire:loading.attr="disabled"
        class="w-full bg-[#0057a8] text-white py-3 rounded-xl text-sm font-bold hover:bg-blue-700 transition disabled:opacity-60">
        <span wire:loading.remove wire:target="guardar">Guardar configuración</span>
        <span wire:loading wire:target="guardar">Guardando...</span>
    </button>
</div>
