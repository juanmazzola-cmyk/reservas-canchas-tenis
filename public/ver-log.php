<?php
// ELIMINAR ESTE ARCHIVO DESPUÉS DE USARLO
// Uso: /ver-log.php?k=mwspjbt9kocdlf2aq4virgh3
if (($_GET['k'] ?? '') !== 'mwspjbt9kocdlf2aq4virgh3') {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

$archivo = __DIR__ . '/../storage/logs/laravel.log';
if (!file_exists($archivo)) {
    exit('No existe storage/logs/laravel.log');
}

// Leer solo los últimos 3 MB (el log de producción puede ser grande)
$fh = fopen($archivo, 'r');
$tamano = filesize($archivo);
fseek($fh, max(0, $tamano - 3 * 1024 * 1024));
$contenido = stream_get_contents($fh);
fclose($fh);

// Agrupar en entradas: cada una empieza con "[YYYY-MM-DD HH:MM:SS]"
$entradas = preg_split('/\n(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\])/', $contenido);

$lineas = [];
foreach ($entradas as $entrada) {
    $renglones = explode("\n", $entrada);
    if (!preg_match('/^\[\d{4}-\d{2}-\d{2}/', $renglones[0])) {
        continue;
    }
    if (!preg_match('/parseable|ComprobanteVerificador|error|exception/i', $renglones[0])) {
        continue;
    }
    // Encabezado + continuación (ej. el JSON de la IA en la línea siguiente), sin stacktrace
    foreach ($renglones as $renglon) {
        if (str_starts_with($renglon, '[stacktrace]') || str_starts_with($renglon, '[previous exception]')) {
            break;
        }
        if (trim($renglon) === '') {
            continue;
        }
        $lineas[] = mb_substr(sanitizar($renglon), 0, 1500);
    }
}

echo implode("\n", array_slice($lineas, -100));
echo "\n\n--- Fin. BORRÁ ESTE ARCHIVO. ---";

function sanitizar(string $l): string
{
    // SQL con bindings (puede traer nombres, DNIs, teléfonos, hashes)
    $l = preg_replace('/SQL: .*?\)(?= \{|$)/', 'SQL: [omitido])', $l);
    // Emails
    $l = preg_replace('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', '[email]', $l);
    // Hashes bcrypt y API keys
    $l = preg_replace('/\$2[aby]\$\d{2}\$[.\/A-Za-z0-9]{53}/', '[hash]', $l);
    $l = preg_replace('/sk-ant-[A-Za-z0-9_-]+/', '[api-key]', $l);
    $l = preg_replace('/(Bearer|x-api-key"?:?\s*"?)[A-Za-z0-9._-]{16,}/i', '$1[token]', $l);
    // Secuencias de 7+ dígitos (DNI, teléfono, CBU/CVU): se dejan solo los últimos 4
    $l = preg_replace_callback('/\d{7,}/', fn ($m) => str_repeat('*', strlen($m[0]) - 4) . substr($m[0], -4), $l);
    return $l;
}
