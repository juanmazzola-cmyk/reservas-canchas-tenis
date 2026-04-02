<?php
// BORRAR ESTE ARCHIVO DESPUÉS DE EJECUTAR
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni'] ?? '');
    $userId = (int) ($_POST['user_id'] ?? 0);

    if (!$dni || !$userId) {
        echo '<p style="color:red">Completá todos los campos.</p>';
    } else {
        $user = User::find($userId);
        if (!$user) {
            echo '<p style="color:red">Usuario no encontrado.</p>';
        } elseif (User::where('dni', $dni)->where('id', '!=', $userId)->exists()) {
            echo '<p style="color:red">Ese DNI ya está en uso.</p>';
        } else {
            $user->update(['dni' => $dni]);
            echo '<p style="color:green;font-weight:bold">✓ DNI ' . htmlspecialchars($dni) . ' asignado a ' . htmlspecialchars($user->nombre . ' ' . $user->apellido) . '</p>';
        }
    }
}

$users = User::orderBy('rol')->orderBy('apellido')->get(['id', 'nombre', 'apellido', 'email', 'rol', 'dni']);
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Asignar DNI</title></head>
<body style="font-family:sans-serif;max-width:500px;margin:40px auto;padding:20px">
    <h2>Asignar DNI a usuarios</h2>
    <form method="POST">
        <label>Usuario:</label><br>
        <select name="user_id" style="width:100%;padding:8px;margin:8px 0">
            <?php foreach ($users as $u): ?>
                <option value="<?= $u->id ?>">
                    [<?= $u->rol ?>] <?= htmlspecialchars($u->apellido . ', ' . $u->nombre) ?>
                    <?= $u->dni ? '— DNI: ' . $u->dni : '— SIN DNI' ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <label>DNI:</label><br>
        <input type="text" name="dni" style="width:100%;padding:8px;margin:8px 0" placeholder="Ej: 30123456"><br>
        <button type="submit" style="background:#0057a8;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer">
            Asignar DNI
        </button>
    </form>
</body>
</html>
