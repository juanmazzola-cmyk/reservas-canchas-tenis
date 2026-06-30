<?php
// Script temporal — ejecutar en producción y borrar con commit inmediato
// Crea la tabla audits para owen-it/laravel-auditing

$env = parse_ini_file(__DIR__ . '/../.env');
$dsn = 'mysql:host=' . $env['DB_HOST'] . ';port=' . ($env['DB_PORT'] ?? 3306) . ';dbname=' . $env['DB_DATABASE'];
$pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "CREATE TABLE IF NOT EXISTS `audits` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$pdo->exec($sql);

// Registrar en migrations para que Laravel no intente correrla de nuevo
$check = $pdo->query("SELECT COUNT(*) FROM migrations WHERE migration = '2026_06_30_182715_create_audits_table'")->fetchColumn();
if (!$check) {
    $pdo->exec("INSERT INTO migrations (migration, batch) VALUES ('2026_06_30_182715_create_audits_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations m2))");
}

echo 'OK: tabla audits creada y migración registrada.';
