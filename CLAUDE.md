# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Initial setup
composer run setup

# Development (runs PHP server + queue + logs + Vite concurrently)
composer run dev

# Frontend only
npm run dev
npm run build

# Tests
composer run test
php artisan test --filter TestName

# Database
php artisan migrate
php artisan migrate:fresh --seed

# Code style (Laravel Pint)
./vendor/bin/pint
```

When running under XAMPP (no `php artisan serve`), Apache serves from `public/` via `.htaccess`.

## Architecture

**Stack:** Laravel 12 + Livewire 4 + Tailwind CSS 4 + Alpine.js + MySQL (localhost y producción)

This is a tennis court reservation system for a club. All UI is built with **Livewire components** — there are no traditional controllers for page rendering (only route → Livewire component full-page). The app is mobile-first and installable as a PWA.

### Request Flow

```
Route (web.php) → Livewire Full-Page Component → Blade Template
```

AJAX/reactive behavior happens entirely through Livewire's wire: directives. Alpine.js handles purely client-side UI state (modals, transitions).

### Roles

Three roles: `admin`, `control`, `usuario`. Enforced in Livewire components via `$this->authorize()` or manual `auth()->user()->rol` checks. Middleware `auth` is applied on most routes.

### Key Livewire Components (`app/Livewire/`)

| Component | Purpose |
|-----------|---------|
| `Agenda.php` | Main reservation calendar — most complex component (~31KB). Handles multi-court/multi-day grid, slot selection, creating/editing reservas. |
| `MisTurnos.php` | User's upcoming/past reservations. Solo el `creador_id` puede cancelar o reprogramar — los otros jugadores de la reserva no tienen esa opción. |
| `Pago.php` | Payment screen: MercadoPago flow + bank transfer receipt upload |
| `Carnet.php` | Membership card for socios: selfie upload, DNI, nro_socio, QR code. Only visible to `es_socio = true`. |
| `VerificarSocio.php` | Verification screen shown after scanning a socio's QR. Shows photo, name, DNI, nro_socio, validity. |
| `EscanearCarnet.php` | QR scanner for `control`/`admin` roles. Uses `html5-qrcode` CDN. **Requires HTTPS** — camera blocked on plain HTTP. |
| `Admin/Usuarios.php` | User management |
| `Admin/Configuracion.php` | System config (prices, courts, MP credentials, etc.) |
| `Admin/Estadisticas.php` | Statistics: reservas del período, usuarios, pagos autorizados (desglose no-socios / con invitados), uso por cancha con top horarios. Tiene tres granularidades: **día** (default, filtra por número de día + mes del campo `dia`), **mes** (filtra por mes del campo `dia`) y **año** (filtra por `created_at` del año completo). El campo `dia` es un string como "lun 02 jun" sin año — el año se infiere de `created_at`. |
| `Admin/Comprobantes.php` | Receipt verification queue — muestra solo pagos con `estado_autorizacion = 'pendiente_admin'`. El admin autoriza desde la grilla de Agenda (`autorizarPago()`). El botón "Autorizar pago" aparece cuando hay pagos `pendiente_admin` para esa reserva (independientemente del `estado` de la reserva — puede ser `PENDING_REVIEW` o `PARTIAL_PAYMENT`). |
| `NavBadge.php` | Badge en nav del admin (reservas pendientes de pago + socios nuevos) |

### Payment System

Two payment paths:
1. **MercadoPago**: Creates preference → redirects to MP → callback to `/pago/mp/success|failure|pending` → updates `reservas.estado_pago`
2. **Bank transfer**: User uploads receipt image/PDF → `ComprobanteVerificador` service calls Anthropic Claude API to verify amount/timestamp/account → admin reviews in `/admin/comprobantes`. Verification result stored in `Pago.verificacion_ia` (not in `Reserva`). `fecha_ok` y `hora_ok` son permisivos (null = aceptado) porque algunos bancos como BNA+ no muestran fecha/hora en el PDF.

**Criterios de rechazo en `enviarComprobante()`:**
- **Hard reject** (borra el archivo, muestra error, el usuario debe reintentar): archivo no es un comprobante bancario válido / importe encontrado pero no coincide / alias/CBU no coincide.
- **Pendiente admin** (`estado_autorizacion = 'pendiente_admin'`): datos ilegibles — fecha, hora, importe o alias no se pueden leer. La reserva queda guardada y el admin la revisa en `/admin/comprobantes`.
- **Aprobado IA** (`estado_autorizacion = 'aprobado_ia'`): todo verificado correctamente, pago AUTHORIZED automáticamente.

States on `Reserva.estado`: `DRAFT`, `AUTHORIZED`, `PENDING`, `PENDING_REVIEW`, `PARTIAL_PAYMENT`. States on `Pago.estado`: `PENDIENTE`, `AUTHORIZED`, `PENDING_REVIEW`. `Pago.estado_autorizacion`: `aprobado_ia`, `rechazado_ia`, `pendiente_admin`, `aprobado_admin`. `Reserva.esta_pagado` is a boolean shortcut.

**`autorizarPago()` en `Agenda.php`**: después de marcar el pago como `aprobado_admin`, cuenta los pagos restantes con `estado = 'PENDIENTE'` para esa reserva. Si quedan → `PARTIAL_PAYMENT` / `esta_pagado = false`. Si no quedan → `AUTHORIZED` / `esta_pagado = true`. Mismo criterio que `Pago.php` usa para el flujo automático de IA/MP.

**`cobrarManualJugador(reservaId, userId)` en `Agenda.php`**: registra un pago cobrado fuera del sistema (efectivo o transferencia en el ingreso). Accesible para `admin` y `control`. Hace `firstOrCreate` del Pago con `monto = non_member_price` de Configuración, lo marca `AUTHORIZED / aprobado_admin`, y aplica la misma lógica de cierre que `autorizarPago()`. El modal no se cierra para permitir cobrar a múltiples jugadores en una sola sesión. En la blade, el modal de detalle muestra un botón **Cobrar** (amber) para cada no-socio sin pago autorizado; al cobrar cambia a **✓ Pagó** (verde).

`DRAFT` reservations (created when MP flow starts but not completed) are auto-cancelled when the browser session ends.

### Models

- **User**: roles, WhatsApp (stored without 0/15 prefix, displayed with +54), `forzar_cambio_password`, `es_socio`, `nro_socio`, `grupo_sanguineo` (nullable, optional — A+/A-/B+/B-/AB+/AB-/O+/O-), `foto_carnet` (nullable, path in `storage/public/fotos-carnet/`)
- **Reserva**: `cancha_id` (integer), `jugadores_ids` (array of user IDs), `invitados` (array of `{slot, apellido}` for non-registered guests), `creador_id`, `esta_pagado`, `estado`, MP fields
- **Pago**: `reserva_id`, `user_id`, `monto`, `estado`, `estado_autorizacion`, `motivo_rechazo`, `autorizado_por`, `autorizado_at`, `verificacion_ia` (JSON del resultado IA), `comprobante` (path). When a reserva has invitados, ONE Pago is created for the creator covering all non-socios + guests. Without invitados, one Pago per non-socio.
- **Configuracion**: single-row config table, retrieved via `Configuracion::getConfig()`. Key fields: `court_count`, `cancha_names` (array), `slots` (array of time strings), `non_member_price`, `carnet_enabled` (boolean, habilita/deshabilita el sistema de carnets)
- **Bloqueo**: court blocks with `MotivoBloqueo` enum
- **Notification** (Laravel built-in): notificaciones en app para admins. Actualmente: `SocioRegistrado` — se dispara cuando un usuario se registra como socio de tenis. Se muestra como badge en el ícono "Usuarios" del nav y como panel en `Admin/Usuarios`.

### Database

- **Localhost:** MySQL en XAMPP (base de datos `liga_padres_tenis`, host 127.0.0.1:3306, user `root` sin password)
- **Producción:** MySQL en DonWeb

Migrations en `database/migrations/`. No seeders para producción — configuración vía admin UI.

**Regla importante:** toda migración que use `Schema::table` para agregar columnas debe incluir un guard `Schema::hasColumn()` antes de ejecutar el cambio. Esto es necesario porque la BD de producción puede tener columnas aplicadas manualmente que el sistema de migraciones no registró.

```php
if (!Schema::hasColumn('tabla', 'columna')) {
    Schema::table('tabla', function (Blueprint $table) {
        // ...
    });
}
```

**Correr migraciones en producción:** DonWeb no tiene terminal. Se usa un script PHP temporal en `public/` que lee el `.env` directamente y ejecuta el `ALTER TABLE` con PDO. Se accede por navegador y se borra con un commit inmediato. Ver historial de commits para el patrón exacto (`migrar-*.php`).

**Limpiar caché en producción:** Si hay errores de "ruta no definida" tras un deploy, es porque DonWeb tiene caché vieja. Usar script temporal `public/limpiar-cache.php` que corre `route:clear`, `config:clear`, `view:clear` vía Artisan. Ver historial de commits para el patrón.

### Frontend

- `resources/views/layouts/app.blade.php`: Main layout with bottom nav (role-aware, horizontally scrollable), toast system, PWA install banner, session keep-alive ping every 2 minutes. Includes `@stack('scripts')` before `</body>` for page-specific JS.
- `resources/views/livewire/`: Blade templates for each component
- Tailwind 4 configured via `resources/css/app.css` (no `tailwind.config.js` — uses CSS-first config)
- Alpine.js loaded via CDN in layout head
- JS libs loaded via CDN in specific views using `@push('scripts')`: `qrcodejs` (carnet QR), `html5-qrcode` (scanner)
- **Livewire navigate**: scripts in views must listen to `livewire:navigated` (not only `DOMContentLoaded`) to re-initialize after SPA navigation. Scanner must also listen to `livewire:navigate` to stop the camera before leaving the page.
- **CDN race condition**: when using `@push('scripts')` with a CDN lib + `livewire:navigated`, the lib may not be loaded yet when the event fires. Fix: poll with `setInterval` until `typeof LibName !== 'undefined'` before calling it (see `carnet.blade.php`).
- **Cámara en localhost**: `getUserMedia` (html5-qrcode) requiere HTTPS. En `http://192.168.x.x` Chrome bloquea la cámara sin mostrar diálogo de permisos. El scanner de carnet solo funciona en producción (`https://ateneo.proyectosia.com.ar`).

### WhatsApp Links

All WhatsApp links use prefix `54` (country code, no `+`). Phone numbers stored without leading 0 or 15. Display format: `+54 {number}`.

### Environment

Key `.env` values beyond standard Laravel:
```
MERCADOPAGO_ACCESS_TOKEN=
ANTHROPIC_API_KEY=        # For receipt AI verification
```

Production uses `.env.production`. Deploy is automatic via push to `main` branch (DonWeb pulls from GitHub). Domain: `ateneo.proyectosia.com.ar`.

## Instruciones de trabajo
Piensa antes de actuar. Lee los archivos antes de escribir código.

Edita solo lo que cambia, no reescribas archivos enteros.

No releas archivos en la misma sesión salvo que te lo pida.

Cuando muestres código, incluí solo el bloque modificado con comentarios indicando dónde va, no el archivo completo.

Sin preámbulos, sin resumenes al final, respondé directo al punto.

Testea antes de dar por terminado.

Seguí el patrón Livewire full-page. No generes controllers ni routes web tradicionales salvo que te lo pida explícitamente.