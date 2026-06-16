<?php

/**
 * test_smoke.php
 * Khadomeh Core Framework CLI Smoke Tests
 */

define('KHADOMEH_START', microtime(true));

// Setup fake server variables for CLI request
$_SERVER['REQUEST_URI'] = '/health';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';

require_once __DIR__ . '/app/Core/Bootstrap.php';

try {
    // 1. Boot system
    App\Core\Bootstrap::boot();
    echo "✔ Bootstrap completed.\n";

    // 2. Test Helper loading
    if (function_exists('e') && function_exists('url') && function_exists('normalize_arabic') && function_exists('get_placeholder_svg')) {
        echo "✔ Helpers loaded successfully.\n";
    } else {
        throw new \Exception("Helpers failed to load.");
    }

    // 3. Test Routing & Request/Response instantiation
    $request = new App\Core\Request();
    $router = new App\Core\Router();
    echo "✔ Request and Router instantiated.\n";

    // 4. Test Session & CSRF
    $token = App\Core\CSRF::getToken();
    if (!empty($token)) {
        echo "✔ Session & CSRF Token created: {$token}\n";
    } else {
        throw new \Exception("CSRF Token generation failed.");
    }

    // 5. Test Database Connection
    $db = App\Core\Database::getInstance();
    $conn = $db->getConnection();
    echo "✔ Database connection established.\n";

    // 6. Test Transactions (Begin & Rollback)
    $db->beginTransaction();
    $db->query("SELECT 1");
    $db->rollBack();
    echo "✔ Transaction Begin and Rollback verified.\n";

    // 7. Test View Rendering
    $rendered = App\Core\View::render('health_template', ['testVar' => 'render_ok']);
    if ($rendered === 'render_ok') {
        echo "✔ View rendering functional.\n";
    } else {
        throw new \Exception("View rendering produced unexpected output: " . $rendered);
    }

    // 8. Test Storage Write & Delete
    $storageFile = 'secure_uploads/smoke_test.txt';
    $writeOk = App\Core\Storage::put($storageFile, 'smoke_test_content');
    if ($writeOk && App\Core\Storage::get($storageFile) === 'smoke_test_content') {
        App\Core\Storage::delete($storageFile);
        echo "✔ Storage Write/Read/Delete verified.\n";
    } else {
        throw new \Exception("Storage test failed.");
    }

    // 9. Test Cache Write & Read & Delete
    $cacheKey = 'smoke_cache_key';
    $cacheVal = ['a' => 1, 'b' => 'test'];
    App\Core\Cache::set($cacheKey, $cacheVal, 5);
    $cacheRead = App\Core\Cache::get($cacheKey);
    if ($cacheRead === $cacheVal) {
        App\Core\Cache::delete($cacheKey);
        echo "✔ Cache Write/Read/Delete verified.\n";
    } else {
        throw new \Exception("Cache test failed.");
    }

    // 10. Test Logger Write
    App\Core\Logger::info('Smoke test log entry.');
    $logFile = App\Core\Storage::path('logs/app.log');
    if (file_exists($logFile) && filesize($logFile) > 0) {
        echo "✔ Logger write verified.\n";
    } else {
        throw new \Exception("Logger failed to write.");
    }

    echo "\n=== ALL SMOKE TESTS PASSED SUCCESSFULLY ===\n";
    exit(0);

} catch (\Throwable $e) {
    echo "\n✖ SMOKE TEST FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
