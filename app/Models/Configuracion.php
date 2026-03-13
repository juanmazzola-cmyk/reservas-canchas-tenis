<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracion';

    protected $fillable = [
        'club_name',
        'court_count',
        'cancha_names',
        'slots',
        'non_member_price',
        'payment_alias',
        'payment_link',
        'payment_instructions',
        'advance_booking_limit_hours',
        'admin_whatsapp',
        'announcement_text',
        'announcement_enabled',
        'notification_text',
        'mp_access_token',
        'mp_public_key',
    ];

    protected function casts(): array
    {
        return [
            'slots' => 'array',
            'cancha_names' => 'array',
            'announcement_enabled' => 'boolean',
            'non_member_price' => 'decimal:2',
        ];
    }

    public static function getConfig(): self
    {
        return self::first() ?? self::create([
            'club_name' => 'Liga Padres Tenis',
            'court_count' => 4,
            'slots' => [
                '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
                '11:00', '11:30', '12:00', '12:30', '13:00', '13:30',
                '14:00', '14:30', '15:00', '15:30', '16:00', '16:30',
                '17:00', '17:30', '18:00', '18:30', '19:00', '19:30',
                '20:00', '20:30', '21:00', '21:30',
            ],
            'non_member_price' => 7500,
            'advance_booking_limit_hours' => 96,
            'announcement_enabled' => false,
        ]);
    }
}
