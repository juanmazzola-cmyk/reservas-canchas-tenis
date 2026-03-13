<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Configuracion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario admin
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'nombre'   => 'Admin',
                'apellido' => 'Sistema',
                'email'    => 'admin@admin.com',
                'password' => Hash::make('admin123'),
                'telefono' => '1100000000',
                'es_socio' => true,
                'rol'      => 'admin',
            ]
        );

        // Configuración inicial
        Configuracion::updateOrCreate(
            ['id' => 1],
            [
                'club_name'                   => 'Liga Padres Tenis',
                'court_count'                 => 4,
                'slots'                       => [
                    '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
                    '11:00', '11:30', '12:00', '12:30', '13:00', '13:30',
                    '14:00', '14:30', '15:00', '15:30', '16:00', '16:30',
                    '17:00', '17:30', '18:00', '18:30', '19:00', '19:30',
                    '20:00', '20:30', '21:00', '21:30',
                ],
                'non_member_price'            => 7500.00,
                'payment_alias'               => 'liga.padres.tenis',
                'payment_link'                => null,
                'payment_instructions'        => 'Transferí al alias liga.padres.tenis y enviá el comprobante desde la sección "Mis Turnos".',
                'advance_booking_limit_hours' => 96,
                'admin_whatsapp'              => null,
                'announcement_text'           => null,
                'announcement_enabled'        => false,
            ]
        );
    }
}
