<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Http\Request;

class MercadoPagoController extends Controller
{
    public function iniciarPago(Request $request, int $reservaId)
    {
        $reserva = Reserva::findOrFail($reservaId);
        $config  = Configuracion::getConfig();

        if (!$config->mp_access_token) {
            return redirect()->route('pago', $reservaId)->with('error', 'MercadoPago no configurado.');
        }

        \MercadoPago\MercadoPagoConfig::setAccessToken($config->mp_access_token);

        $jugadores   = User::whereIn('id', $reserva->jugadores_ids ?? [])->get();
        $cantNoSocios = $jugadores->where('es_socio', false)->count();
        $total        = $cantNoSocios * (float) $config->non_member_price;

        if ($total <= 0) {
            return redirect()->route('pago', $reservaId)->with('info', 'No hay monto a pagar.');
        }

        $client = new \MercadoPago\Client\Preference\PreferenceClient();

        $preference = $client->create([
            'items' => [[
                'title'       => 'Reserva cancha - ' . $reserva->dia . ' ' . $reserva->hora,
                'quantity'    => 1,
                'unit_price'  => $total,
                'currency_id' => 'ARS',
            ]],
            'back_urls' => [
                'success' => route('mp.success'),
                'failure' => route('mp.failure'),
                'pending' => route('mp.pending'),
            ],
            'auto_return'        => 'approved',
            'external_reference' => (string) $reservaId,
        ]);

        $reserva->update(['mp_preference_id' => $preference->id]);

        return redirect($preference->init_point);
    }

    public function success(Request $request)
    {
        $reservaId = $request->query('external_reference');
        $paymentId = $request->query('payment_id');
        $status    = $request->query('status');

        if ($reservaId) {
            $reserva = Reserva::find($reservaId);
            if ($reserva) {
                $reserva->update([
                    'mp_payment_id' => $paymentId,
                    'mp_status'     => $status,
                    'esta_pagado'   => true,
                    'estado'        => 'AUTHORIZED',
                ]);
            }
        }

        return redirect()->route('pago', $reservaId)->with('mp_result', 'success');
    }

    public function failure(Request $request)
    {
        $reservaId = $request->query('external_reference');
        $paymentId = $request->query('payment_id');

        if ($reservaId) {
            $reserva = Reserva::find($reservaId);
            if ($reserva) {
                $reserva->update([
                    'mp_payment_id' => $paymentId,
                    'mp_status'     => 'rejected',
                ]);
            }
        }

        return redirect()->route('pago', $reservaId)->with('mp_result', 'failure');
    }

    public function pending(Request $request)
    {
        $reservaId = $request->query('external_reference');
        $paymentId = $request->query('payment_id');

        if ($reservaId) {
            $reserva = Reserva::find($reservaId);
            if ($reserva) {
                $reserva->update([
                    'mp_payment_id' => $paymentId,
                    'mp_status'     => 'pending',
                    'estado'        => 'PENDING_REVIEW',
                ]);
            }
        }

        return redirect()->route('pago', $reservaId)->with('mp_result', 'pending');
    }
}
