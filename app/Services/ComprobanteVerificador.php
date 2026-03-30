<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ComprobanteVerificador
{
    public function verificar(string $rutaArchivo, float $importeEsperado, string $aliasEsperado, string $cbuEsperado = '', string $cuentaEsperada = '', string $cuitEsperado = '', ?\Carbon\Carbon $fechaHoraBase = null): array
    {
        $apiKey = config('services.anthropic.key');

        if (!$apiKey) {
            return $this->sinVerificacion('ANTHROPIC_API_KEY no configurada');
        }

        if (!file_exists($rutaArchivo)) {
            return $this->sinVerificacion('Archivo no encontrado');
        }

        $extension = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));
        $base64    = base64_encode(file_get_contents($rutaArchivo));

        $mediaType = match ($extension) {
            'png'        => 'image/png',
            'pdf'        => 'application/pdf',
            default      => 'image/jpeg',
        };

        $importeFormateado = '$' . number_format($importeEsperado, 0, ',', '.');
        $ahora  = $fechaHoraBase ?? now();
        $desde  = $ahora->copy()->subMinutes(30);

        // Ventana válida: el comprobante debe tener una hora dentro de los 30 minutos anteriores al envío
        // Ej: envío a las 16:00 → comprobante válido entre 15:30 y 16:00
        // Maneja el caso medianoche: ej. transferencia a las 23:50, envío a las 00:05
        $fechaHoraDesde = $desde->format('d/m/Y H:i');
        $fechaHoraHasta = $ahora->format('d/m/Y H:i');

        // Pasamos ambas fechas por si la ventana cruza la medianoche
        $fechaHoy  = $ahora->format('d/m/Y');
        $fechaAyer = $desde->format('d/m/Y');
        $horaDesde = $desde->format('H:i');
        $horaHasta = $ahora->format('H:i');

        // Construir línea de identificadores de cuenta
        $identificadores = [];
        if ($aliasEsperado)  $identificadores[] = "Alias: {$aliasEsperado}";
        if ($cbuEsperado)    $identificadores[] = "CBU/CVU: {$cbuEsperado}";
        if ($cuentaEsperada) $identificadores[] = "Cuenta corriente: {$cuentaEsperada}";
        if ($cuitEsperado)   $identificadores[] = "CUIT: {$cuitEsperado}";
        $lineaIdentificadores = implode(' | ', $identificadores) ?: 'No configurado';

        $prompt = <<<PROMPT
Analizá este comprobante de transferencia bancaria argentina.

Datos esperados del pago:
- Importe a pagar: {$importeFormateado}
- Identificadores de la cuenta destino (buscá CUALQUIERA de estos): {$lineaIdentificadores}
- Ventana de tiempo válida: entre {$fechaHoraDesde} y {$fechaHoraHasta} (la transferencia debe haberse hecho dentro de los 30 minutos anteriores al envío del comprobante)

Instrucciones:
1. Determiná si la imagen es un comprobante de transferencia/pago bancario.
2. Buscá la fecha Y hora del comprobante. Verificá si cae dentro de la ventana válida ({$fechaHoraDesde} a {$fechaHoraHasta}). La idea es que la transferencia fue hecha como máximo 30 minutos antes de que el usuario envíe el comprobante. Tené en cuenta que si la ventana cruza la medianoche, la fecha puede ser {$fechaAyer} o {$fechaHoy}. Si el comprobante no muestra hora, verificá solo que la fecha sea {$fechaHoy} o {$fechaAyer}.
3. Buscá el importe transferido y verificá si coincide con {$importeFormateado} (puede estar escrito con o sin puntos/comas).
4. Buscá en el comprobante CUALQUIERA de estos identificadores de cuenta destino: alias, CBU/CVU o número de cuenta corriente. Si encontrás al menos uno y coincide, devolvé alias_ok como true. Si no aparece ninguno, devolvé alias_ok como null (no lo consideres un error). Si aparece alguno pero no coincide, devolvé alias_ok como false.

Respondé ÚNICAMENTE con un objeto JSON válido, sin markdown, sin texto extra:
{"es_comprobante":true/false,"fecha_ok":true/false/null,"hora_ok":true/false/null,"importe_ok":true/false/null,"alias_ok":true/false/null,"fecha_encontrada":"texto o null","hora_encontrada":"texto o null","importe_encontrado":"texto o null","alias_encontrado":"texto encontrado o null","detalle":"breve explicación de 1 línea"}
PROMPT;

        $sourceType = $extension === 'pdf' ? 'document' : 'image';

        $content = [
            [
                'type'   => $sourceType,
                'source' => [
                    'type'       => 'base64',
                    'media_type' => $mediaType,
                    'data'       => $base64,
                ],
            ],
            ['type' => 'text', 'text' => $prompt],
        ];

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-sonnet-4-6',
                'max_tokens' => 400,
                'messages'   => [
                    ['role' => 'user', 'content' => $content],
                ],
            ]);

            if (!$response->successful()) {
                Log::warning('ComprobanteVerificador: error API', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return $this->sinVerificacion('Error al conectar con el servicio de verificación');
            }

            $text = $response->json('content.0.text', '');
            $data = json_decode($text, true);

            if (!is_array($data)) {
                Log::warning('ComprobanteVerificador: respuesta no parseable', ['text' => $text]);
                return $this->sinVerificacion('Respuesta inválida del servicio');
            }

            $esComprobante = $data['es_comprobante'] ?? false;
            $importeOk     = $data['importe_ok']     ?? null;
            $aliasOk       = $data['alias_ok']       ?? null;
            $fechaOk       = $data['fecha_ok']       ?? null;
            $horaOk        = $data['hora_ok']        ?? null;

            // Confirmación automática: fecha + hora + importe + alias/CBU/CUIT encontrado y correcto
            $valido = $esComprobante === true
                && $importeOk === true
                && $fechaOk  === true
                && $horaOk   !== false   // si no aparece hora, se acepta
                && $aliasOk  === true;   // debe aparecer y coincidir

            return array_merge($data, ['valido' => $valido, 'error' => null]);

        } catch (\Throwable $e) {
            Log::error('ComprobanteVerificador: excepción', ['message' => $e->getMessage()]);
            return $this->sinVerificacion('Excepción: ' . $e->getMessage());
        }
    }

    private function sinVerificacion(string $motivo): array
    {
        return [
            'valido'              => false,
            'error'               => $motivo,
            'es_comprobante'      => null,
            'fecha_ok'            => null,
            'hora_ok'             => null,
            'importe_ok'          => null,
            'alias_ok'            => null,
            'fecha_encontrada'    => null,
            'hora_encontrada'     => null,
            'importe_encontrado'  => null,
            'alias_encontrado'    => null,
            'detalle'             => null,
        ];
    }
}
