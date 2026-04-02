<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId   = (int) ($_POST['user_id'] ?? 0);
    $dni      = trim($_POST['dni'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($userId) {
        $data = [];
        if ($dni)      $data['dni']      = $dni;
        if ($password) $data['password'] = Hash::make($password);

        if ($data) {
            DB::table('users')->where('id', $userId)->update($data);
            echo '<p style="color:green;font-weight:bold">✓ Actualizado correctamente.</p>';
        } else {
            echo '<p style="color:orange">No ingresaste ningún valor para cambiar.</p>';
        }
    }
}

$users = DB::table('users')->orderBy('rol')->get(['id', 'nombre', 'apellido', 'rol', 'dni']);
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Fix Admin</title></head>
<body style="font-family:sans-serif;max-width:500px;margin:40px auto;padding:20px">
<h2>Usuarios y DNI actuales</h2>
<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;margin-bottom:30px">
<tr><th>ID</th><th>Nombre</th><th>Rol</th><th>DNI guardado</th></tr>
<?php foreach ($users as $u): ?>
<tr>
    <td><?= $u->id ?></td>
    <td><?= htmlspecialchars($u->apellido . ', ' . $u->nombre) ?></td>
    <td><?= $u->rol ?></td>
    <td><?= $u->dni ? htmlspecialchars($u->dni) : '<em style="color:red">SIN DNI</em>' ?></td>
</tr>
<?php endforeach; ?>
</table>

<h2>Corregir DNI y/o contraseña</h2>
<form method="POST">
    <label>Usuario:</label><br>
    <select name="user_id" style="width:100%;padding:8px;margin:8px 0">
        <?php foreach ($users as $u): ?>
        <option value="<?= $u->id ?>">[<?= $u->rol ?>] <?= htmlspecialchars($u->apellido . ', ' . $u->nombre) ?></option>
        <?php endforeach; ?>
    </select><br>
    <label>Nuevo DNI (dejá vacío para no cambiar):</label><br>
    <input type="text" name="dni" style="width:100%;padding:8px;margin:8px 0" placeholder="Solo números, ej: 30123456"><br>
    <label>Nueva contraseña (dejá vacío para no cambiar):</label><br>
    <input type="password" name="password" style="width:100%;padding:8px;margin:8px 0"><br>
    <button type="submit" style="background:#0057a8;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer">Actualizar</button>
</form>
</body></html>
