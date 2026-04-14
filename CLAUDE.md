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

**Stack:** Laravel 12 + Livewire 4 + Tailwind CSS 4 + Alpine.js + SQLite

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
| `MisTurnos.php` | User's upcoming/past reservations |
| `Pago.php` | Payment screen: MercadoPago flow + bank transfer receipt upload |
| `Admin/Usuarios.php` | User management |
| `Admin/Configuracion.php` | System config (prices, courts, MP credentials, etc.) |
| `Admin/Estadisticas.php` | Statistics + Excel export |
| `Admin/Comprobantes.php` | AI-powered receipt verification queue |

### Payment System

Two payment paths:
1. **MercadoPago**: Creates preference → redirects to MP → callback to `/pago/mp/success|failure|pending` → updates `reservas.estado_pago`
2. **Bank transfer**: User uploads receipt image/PDF → `ComprobanteVerificador` service calls Anthropic Claude API to verify amount/timestamp/account → admin reviews in `/admin/comprobantes`

Payment states on `Reserva`: `PENDIENTE`, `PENDIENTE_REVISION`, `AUTORIZADO`, `PAGO_PARCIAL`, `DRAFT`.

`DRAFT` reservations (created when MP flow starts but not completed) are auto-cancelled when the browser session ends.

### Models

- **User**: roles, WhatsApp (stored without 0/15 prefix, displayed with +54), `forzar_cambio_password`
- **Reserva**: belongs to User and Cancha; tracks `estado_pago`, MP preference ID, receipt path
- **Pago**: payment records linked to Reserva
- **Configuracion**: key-value store for all system settings (retrieved via `Configuracion::get('key')`)
- **Bloqueo**: court blocks with `MotivoBloqueo` enum
- **Cancha**: courts (nombre, activa)

### Database

SQLite at `database/database.sqlite`. Migrations in `database/migrations/`. No seeders needed for production — configuration is managed via admin UI.

### Frontend

- `resources/views/layouts/app.blade.php`: Main layout with bottom nav (role-aware), toast system, PWA install banner, session keep-alive ping every 2 minutes
- `resources/views/livewire/`: Blade templates for each component
- Tailwind 4 configured via `resources/css/app.css` (no `tailwind.config.js` — uses CSS-first config)
- Alpine.js loaded via CDN in layout head

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