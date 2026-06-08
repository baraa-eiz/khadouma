<?php
/**
 * import_seed.php
 * Re-create database schema and populate seed data
 */

require_once dirname(__DIR__) . '/app/Core/Bootstrap.php';
App\Core\Bootstrap::boot();

try {
    $db = App\Core\Database::getInstance();
    $pdo = $db->getConnection();

    echo "Resetting database...\n";

    // Disable foreign key checks to safely drop/recreate tables
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // Load schema
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("schema.sql not found at " . $schemaFile);
    }
    $schemaSql = file_get_contents($schemaFile);
    $pdo->exec($schemaSql);
    echo "✔ Schema loaded successfully.\n";

    // Load seed
    $seedFile = __DIR__ . '/seed.sql';
    if (!file_exists($seedFile)) {
        throw new Exception("seed.sql not found at " . $seedFile);
    }
    $seedSql = file_get_contents($seedFile);
    $pdo->exec($seedSql);
    echo "✔ Seed data loaded successfully.\n";

    // Enable foreign key checks back
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n=== DATABASE SEEDING COMPLETED SUCCESSFULLY ===\n";
    exit(0);
} catch (Throwable $e) {
    echo "✖ DATABASE SEEDING FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}
