<?php
/**
 * test_user_portal_foundation.php
 * Khadomeh User Portal Foundation Integration Tests
 */

define('KHADOMEH_START', microtime(true));

// Setup environment variables before bootstrap
putenv("DEV_USER_LOGIN=true");
putenv("DEV_USER_PASSWORD=user_pass_123");
$_ENV['DEV_USER_LOGIN'] = 'true';
$_ENV['DEV_USER_PASSWORD'] = 'user_pass_123';

// Setup fake server variables for CLI request
$_SERVER['REQUEST_URI'] = '/user/dashboard';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once __DIR__ . '/app/Core/Bootstrap.php';

$userId = null;

try {
    // 1. Boot system
    App\Core\Bootstrap::boot();
    echo "✔ Bootstrap completed.\n";

    $db = App\Core\Database::getInstance();

    // 2. Validate users table exists and has correct columns
    $tableInfo = [];
    $columns = [];
    try {
        $tableInfo = $db->fetchAll("PRAGMA table_info(user_accounts)");
        $columns = array_column($tableInfo, 'name');
    } catch (\Exception $ex) {
        // ignore SQLite specific error
    }
    if (empty($tableInfo)) {
        try {
            $tableInfo = $db->fetchAll("DESCRIBE user_accounts");
            $columns = array_column($tableInfo, 'Field');
        } catch (\Exception $ex) {
            throw new \Exception("Could not describe user_accounts table: " . $ex->getMessage());
        }
    }

    $requiredColumns = [
        'id', 'public_id', 'display_name', 'email', 'phone', 
        'avatar', 'city_id', 'area_id', 'default_address', 
        'preferred_contact_method', 'preferred_language', 'timezone',
        'marketing_opt_in', 'status', 
        'created_at', 'updated_at', 'deleted_at'
    ];

    foreach ($requiredColumns as $col) {
        if (!in_array($col, $columns)) {
            throw new \Exception("Missing column in user_accounts table: {$col}");
        }
    }
    echo "✔ user_accounts table columns verified.\n";

    // 3. Test UsersValidation (Valid Case)
    $validation = new App\Modules\Users\UsersValidation(new App\Modules\Users\UsersRepository());
    $validData = [
        'display_name' => 'محمد السوري',
        'email' => 'testuser@example.com',
        'phone' => '0931234567',
        'city_id' => '1',
        'area_id' => '1',
        'default_address' => 'حي الميدان، دمشق',
        'preferred_contact_method' => 'phone',
        'preferred_language' => 'ar',
        'timezone' => 'Asia/Damascus',
        'marketing_opt_in' => '1',
        'status' => 'active'
    ];

    [$errors, $dto] = $validation->validate($validData);
    if (!empty($errors)) {
        throw new \Exception("Validation failed for valid data: " . json_encode($errors));
    }
    echo "✔ UsersValidation parsed valid data successfully.\n";

    // 4. Test UsersValidation (Invalid Cases)
    $invalidData = [
        'display_name' => 'A', // too short
        'email' => 'invalid-email',
        'phone' => '123456' // invalid Syrian format
    ];
    [$errorsInvalid, $dtoInvalid] = $validation->validate($invalidData);
    if (empty($errorsInvalid)) {
        throw new \Exception("Validation did not catch errors for invalid data!");
    }
    echo "✔ UsersValidation correctly rejected invalid data.\n";

    // 5. Test UsersRepository and UsersService CRUD
    $repo = new App\Modules\Users\UsersRepository();
    $service = new App\Modules\Users\UsersService($repo);

    // Create a new user
    $userId = $service->createUser($dto, 1, '127.0.0.1');
    if (!$userId) {
        throw new \Exception("Failed to create user using service.");
    }
    echo "✔ User created via service. ID: {$userId}\n";

    // Fetch the created user
    $user = $service->getUserById($userId);
    if (!$user || $user->display_name !== 'محمد السوري') {
        throw new \Exception("Failed to fetch correct user from service.");
    }
    echo "✔ User fetched and display_name matches.\n";

    // Verify profile completeness score (should be high since email, phone, city, area are set)
    if ($user->completion_score < 80) {
        throw new \Exception("Unexpected profile completeness score: {$user->completion_score}");
    }
    echo "✔ Profile completeness score calculated correctly: {$user->completion_score}%\n";

    // Test Dev Login configuration and Auth Service
    $authService = new App\Services\Auth\UserAuthService($repo);
    
    // Simulate Dev Authentication via email
    $devUser = $authService->authenticateDev('testuser@example.com', 'user_pass_123');
    if (!$devUser) {
        throw new \Exception("Dev login attempt failed using email 'testuser@example.com' and dev password.");
    }
    echo "✔ Auth service dev login attempt via email verified.\n";

    // Simulate Dev Authentication via phone
    $devUserPhone = $authService->authenticateDev('0931234567', 'user_pass_123');
    if (!$devUserPhone) {
        throw new \Exception("Dev login attempt failed using phone '0931234567' and dev password.");
    }
    echo "✔ Auth service dev login attempt via phone verified.\n";

    // Test session lifecycle
    $authService->loginUser($devUser);
    if (App\Core\Session::get('user_id') !== $userId) {
        throw new \Exception("Failed to populate session correctly on login.");
    }
    echo "✔ User session initialized successfully.\n";

    $authService->logoutUser();
    if (App\Core\Session::get('user_id') !== null) {
        throw new \Exception("Failed to clear session on logout.");
    }
    echo "✔ User session cleared successfully.\n";

    // Test Change User Status
    $statusOk = $service->toggleUserStatus($userId, 'suspended', 1, '127.0.0.1');
    $user = $service->getUserById($userId);
    if (!$statusOk || $user->status !== 'suspended') {
        throw new \Exception("Failed to toggle user status.");
    }
    echo "✔ User status toggled to suspended.\n";

    // Test Soft Delete & Restore
    $deleteOk = $service->softDeleteUser($userId, 1, '127.0.0.1');
    $user = $service->getUserById($userId);
    if (!$deleteOk || $user->deleted_at === null) {
        throw new \Exception("Failed to soft-delete user.");
    }
    echo "✔ User soft-deleted successfully.\n";

    $restoreOk = $service->restoreUser($userId, 1, '127.0.0.1');
    $user = $service->getUserById($userId);
    if (!$restoreOk || $user->deleted_at !== null) {
        throw new \Exception("Failed to restore soft-deleted user.");
    }
    echo "✔ User restored successfully.\n";

    // Cleanup test record
    if ($userId !== null) {
        $db->execute("DELETE FROM `audit_logs` WHERE `entity_type` = 'users' AND `entity_id` = :id", ['id' => $userId]);
        $db->execute("DELETE FROM `user_accounts` WHERE `id` = :id", ['id' => $userId]);
        echo "✔ Cleaned up test user and related audit logs.\n";
    }

    echo "\n=== ALL USER PORTAL FOUNDATION TESTS PASSED SUCCESSFULLY ===\n";
    exit(0);

} catch (\Throwable $e) {
    // Attempt cleanup even on failure
    if (isset($db) && $userId !== null) {
        try {
            $db->execute("DELETE FROM `audit_logs` WHERE `entity_type` = 'users' AND `entity_id` = :id", ['id' => $userId]);
            $db->execute("DELETE FROM `user_accounts` WHERE `id` = :id", ['id' => $userId]);
        } catch (\Throwable $ex) {}
    }
    echo "\n✖ TEST FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
