<?php
// ELIMINAR ESTE ARCHIVO DESPUÉS DE USARLO

$envFile = __DIR__ . '/../.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if ($line && !str_starts_with($line, '#') && str_contains($line, '=')) {
        [$key, $val] = explode('=', $line, 2);
        $env[trim($key)] = trim($val);
    }
}

$host = $env['DB_HOST'] ?? 'localhost';
$db   = $env['DB_DATABASE'] ?? '';
$user = $env['DB_USERNAME'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $log = [];

    // Crear tabla audits si no existe
    $existe = $pdo->query("SHOW TABLES LIKE 'audits'")->fetchColumn();
    if ($existe) {
        $log[] = "✓ La tabla <code>audits</code> ya existe, no se tocó.";
    } else {
        $pdo->exec("CREATE TABLE `audits` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `user_type` varchar(255) DEFAULT NULL,
          `user_id` bigint unsigned DEFAULT NULL,
          `event` varchar(255) NOT NULL,
          `auditable_type` varchar(255) NOT NULL,
          `auditable_id` bigint unsigned NOT NULL,
          `old_values` text,
          `new_values` text,
          `url` text,
          `ip_address` varchar(45) DEFAULT NULL,
          `user_agent` varchar(1023) DEFAULT NULL,
          `tags` varchar(255) DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `audits_user_id_user_type_index` (`user_id`, `user_type`),
          KEY `audits_auditable_type_auditable_id_index` (`auditable_type`, `auditable_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $log[] = "✓ Tabla <code>audits</code> creada.";
    }

    // Registrar en migrations para que Laravel no la vuelva a correr
    $migrationName = '2026_06_30_182715_create_audits_table';
    $yaRegistrada = $pdo->query("SELECT COUNT(*) FROM migrations WHERE migration = '$migrationName'")->fetchColumn();
    if ($yaRegistrada) {
        $log[] = "✓ Migración ya registrada en la tabla <code>migrations</code>.";
    } else {
        $batch = $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations")->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migrationName, $batch]);
        $log[] = "✓ Migración registrada en <code>migrations</code> (batch $batch).";
    }

    foreach ($log as $line) echo $line . "<br>";
    echo "<br><strong>BORRÁ ESTE ARCHIVO del servidor.</strong>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
