#!/usr/bin/env php
<?php
/**
 * ElectroMech Portfolio — CLI Installer
 *
 * Usage:
 *   php install.php
 *
 * This script:
 *  1. Creates database tables from schema.sql
 *  2. Sets the admin password securely
 *  3. Creates required directories
 */

define('ROOT_PATH', __DIR__);
require ROOT_PATH . '/app/bootstrap.php';

echo "\n";
echo "╔══════════════════════════════════════╗\n";
echo "║   ElectroMech Portfolio Installer    ║\n";
echo "╚══════════════════════════════════════╝\n\n";

// Check .env
if (!is_file(ROOT_PATH . '/.env')) {
    echo "⚠  .env file not found. Copy .env.example to .env and configure it first.\n";
    exit(1);
}

try {
    $db = \App\Core\Database::getInstance();
    echo "✓  Database connection OK\n";
} catch (\Exception $e) {
    echo "✗  Database connection FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Run schema
$sql = file_get_contents(ROOT_PATH . '/database/schema.sql');
try {
    $pdo = $db->getPdo();
    // Split by semicolons (simple approach for this schema)
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if (empty($stmt) || str_starts_with($stmt, '--')) continue;
        try { $pdo->exec($stmt); } catch (\PDOException $e) {
            // Ignore duplicate/existing table errors
            if ($e->getCode() != '42S01') echo "  ⚠  " . $e->getMessage() . "\n";
        }
    }
    echo "✓  Database schema created\n";
} catch (\Exception $e) {
    echo "✗  Schema error: " . $e->getMessage() . "\n";
}

// Create directories
$dirs = [
    ROOT_PATH . '/public/uploads/projects',
    ROOT_PATH . '/public/uploads/cv',
    ROOT_PATH . '/public/assets/images',
    ROOT_PATH . '/storage/cache',
    ROOT_PATH . '/storage/logs',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✓  Created: {$dir}\n";
    }
}

// Set admin password interactively
echo "\n── Admin Setup ──────────────────────────\n";
echo "Enter admin email [admin@example.com]: ";
$email = trim(fgets(STDIN)) ?: 'admin@example.com';

echo "Enter admin name [Admin]: ";
$name = trim(fgets(STDIN)) ?: 'Admin';

echo "Enter admin password (min 12 chars): ";
// Hide input on Unix
system('stty -echo');
$password = trim(fgets(STDIN));
system('stty echo');
echo "\n";

if (strlen($password) < 12) {
    echo "⚠  Password too short. Using random password.\n";
    $password = bin2hex(random_bytes(16));
    echo "   Generated password: {$password}\n";
    echo "   ⚠  SAVE THIS PASSWORD — it won't be shown again!\n";
}

$hash = \App\Core\Security::hashPassword($password);

try {
    $existing = $db->fetchOne("SELECT id FROM admins WHERE email = ?", [$email]);
    if ($existing) {
        $db->execute("UPDATE admins SET name=?, password_hash=? WHERE email=?", [$name, $hash, $email]);
        echo "✓  Admin updated: {$email}\n";
    } else {
        $db->insert('admins', ['name'=>$name, 'email'=>$email, 'password_hash'=>$hash, 'role'=>'superadmin']);
        echo "✓  Admin created: {$email}\n";
    }
} catch (\Exception $e) {
    echo "✗  Admin creation failed: " . $e->getMessage() . "\n";
}

echo "\n✅  Installation complete!\n";
echo "   Admin panel: " . config('app.app.base_url') . "/admin\n";
echo "   Email:       {$email}\n\n";