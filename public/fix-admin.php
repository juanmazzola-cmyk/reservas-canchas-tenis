<?php
// BORRAR DESPUÉS DE USAR
$env = parse_ini_file(__DIR__ . '/../.env');
$pdo = new PDO(
    "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8",
    $env['DB_USERNAME'],
    $env['DB_PASSWORD']
);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId   = (int) $_POST['user_id'];
    $dni      = trim($_POST['dni']);
    $password = trim($_POST['password']);

    if ($dni) {
        $stmt = $pdo->prepare("UPDATE users SET dni = ? WHERE id = ?");
        $stmt->execute([$dni, $userId]);
        $msg .= "<p style='color:green'>✓ DNI actualizado a: {$dni}</p>";
    }
    if ($password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
        $stmt->execute([$hash, $userId]);
        $msg .= "<p style='color:green'>✓ Contraseña actualizada.</p>";
    }
}

$users = $pdo->query("SELECT id, nombre, apellido, rol, dni FROM users ORDER BY rol")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Fix Admin</title></head>
<body style="font-family:sans-serif;max-width:500px;margin:40px auto;padding:20px">
<?= $msg ?>
<h2>DNI actuales en base de datos</h2>
<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;margin-bottom:24px">
<tr><th>ID</th><th>Nombre</th><th>Rol</th><th>DNI guardado</th></tr>
<?php foreach ($users as $u): ?>
<tr>
    <td><?= $u['id'] ?></td>
    <td><?= htmlspecialchars($u['apellido'] . ', ' . $u['nombre']) ?></td>
    <td><?= $u['rol'] ?></td>
    <td><?= $u['dni'] ? htmlspecialchars($u['dni']) : '<em style="color:red">SIN DNI</em>' ?></td>
</tr>
<?php endforeach; ?>
</table>
<h2>Cambiar DNI / contraseña</h2>
<form method="POST">
    <select name="user_id" style="width:100%;padding:8px;margin:6px 0">
        <?php foreach ($users as $u): ?>
        <option value="<?= $u['id'] ?>">[<?= $u['rol'] ?>] <?= htmlspecialchars($u['apellido'] . ', ' . $u['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br>
    <input type="text" name="dni" style="width:100%;padding:8px;margin:6px 0" placeholder="Nuevo DNI (vacío = no cambiar)"><br>
    <input type="password" name="password" style="width:100%;padding:8px;margin:6px 0" placeholder="Nueva contraseña (vacío = no cambiar)"><br>
    <button type="submit" style="background:#0057a8;color:white;padding:10px 24px;border:none;border-radius:6px;cursor:pointer;font-size:16px">Actualizar</button>
</form>
</body></html>
