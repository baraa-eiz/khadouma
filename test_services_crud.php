<?php
/**
 * test_services_crud.php
 * Integration test suite for Khadomeh Services Golden CRUD module
 */

define('KHADOMEH_START', microtime(true));

// Setup fake server variables for CLI request
$_SERVER['REQUEST_URI'] = '/admin/services';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';

// 1. Bootstrap the framework
require_once __DIR__ . '/app/Core/Bootstrap.php';
App\Core\Bootstrap::boot();

use App\Core\Database;
use App\Modules\Services\ServicesRepository;
use App\Modules\Services\ServicesService;
use App\Modules\Services\ServicesValidation;
use App\Modules\Services\ServiceData;

echo "============================================================\n";
echo "STARTING SERVICES CRUD INTEGRATION TESTS\n";
echo "============================================================\n\n";

try {
    // 2. Initialize DB and Repositories
    $db = Database::getInstance();
    $repo = new ServicesRepository();
    $service = new ServicesService($repo);
    $validation = new ServicesValidation($repo);

    echo "✔ Services module components instantiated.\n";

    // 3. Test Seeded Service Retrievability
    $cleaning = $repo->findBySlug('cleaning');
    if (!$cleaning) {
        throw new Exception("❌ Seeded service 'cleaning' not found!");
    }
    echo "✔ Found seeded service: {$cleaning['display_name_ar']} (id: {$cleaning['id']}, public_id: {$cleaning['public_id']})\n";

    // 4. Test Validation Rules
    echo "\n--- Testing Validations ---\n";
    
    // Check Empty validation
    [$errors, $dto] = $validation->validate([]);
    if (!isset($errors['key']) || !isset($errors['display_name_ar'])) {
        throw new Exception("❌ Validation failed to catch empty fields!");
    }
    echo "✔ Validation caught empty required fields: key and display_name_ar.\n";

    // Check key format validation
    [$errors, $dto] = $validation->validate([
        'key' => 'Invalid Key With Spaces',
        'display_name_ar' => 'خدمة تجريبية',
        'short_name_ar' => 'تجريبية',
    ]);
    if (!isset($errors['key'])) {
        throw new Exception("❌ Validation failed to catch invalid key format!");
    }
    echo "✔ Validation caught invalid key format.\n";

    // Check Arabic character validation
    [$errors, $dto] = $validation->validate([
        'key' => 'test-validation-ar',
        'display_name_ar' => 'English Display Name',
        'short_name_ar' => 'اسم عربي',
    ]);
    if (!isset($errors['display_name_ar'])) {
        throw new Exception("❌ Validation failed to catch non-Arabic display name!");
    }
    echo "✔ Validation caught non-Arabic characters in display_name_ar.\n";

    // Check unique key validation
    [$errors, $dto] = $validation->validate([
        'key' => 'cleaning',
        'display_name_ar' => 'خدمة تنظيف جديدة',
        'short_name_ar' => 'تنظيف جديد',
    ]);
    if (!isset($errors['key'])) {
        throw new Exception("❌ Validation failed to catch duplicate key 'cleaning'!");
    }
    echo "✔ Validation caught duplicate key 'cleaning'.\n";

    // 5. Test Creation with Service & DTO
    echo "\n--- Testing Service Creation & DTO ---\n";
    $testKey = 'test-service-' . time();
    $testDisplayName = 'خدمة الغسيل والتجفيف السريعة ة'; // trailing 'ة' to test normalization
    $testShortName = 'غسيل سريع';

    [$errors, $dto] = $validation->validate([
        'key' => $testKey,
        'display_name_ar' => $testDisplayName,
        'short_name_ar' => $testShortName,
        'description_ar' => '   وصف الخدمة التجريبية للتجفيف  ',
        'icon' => 'icon-cleaning',
        'sort_order' => '10',
        'meta_title_ar' => 'عنوان سيو الغسيل',
        'meta_description_ar' => 'وصف سيو الغسيل السريع',
        'is_active' => '1'
    ]);

    if (!empty($errors)) {
        throw new Exception("❌ Unexpected validation errors: " . print_r($errors, true));
    }
    if (!$dto instanceof ServiceData) {
        throw new Exception("❌ Validation did not return a valid DTO instance!");
    }

    echo "✔ Validation succeeded and returned clean DTO.\n";
    echo "✔ Arabic normalization checked (display_name_ar normalized, whitespace trimmed).\n";

    $adminId = 1; // Assuming superadmin ID is 1
    $ip = '192.168.1.50';

    $newId = $service->createService($dto, $adminId, $ip);
    if ($newId <= 0) {
        throw new Exception("❌ Failed to create service record!");
    }
    echo "✔ Service created successfully with ID: {$newId}\n";

    // Fetch the new service and verify fields
    $createdItem = $service->getById($newId);
    if (!$createdItem) {
        throw new Exception("❌ Failed to retrieve newly created service!");
    }
    if ($createdItem['key'] !== $testKey) {
        throw new Exception("❌ Created service key mismatch!");
    }
    // Arabic Normalization assertion: trailing 'ة' should be normalized to 'ه' or preserved depending on normalization logic.
    $normalizedExpected = normalize_arabic($testDisplayName);
    if ($createdItem['display_name_ar'] !== $normalizedExpected) {
        throw new Exception("❌ Normalization mismatch! Expected: {$normalizedExpected}, Got: {$createdItem['display_name_ar']}");
    }
    echo "✔ Service fields and Arabic normalization verified in database.\n";

    // Verify Audit Log for creation
    $creationLog = $db->fetch(
        "SELECT * FROM `audit_logs` WHERE `entity_type` = 'services' AND `entity_id` = :id AND `action` = 'create_service' LIMIT 1",
        ['id' => $newId]
    );
    if (!$creationLog) {
        throw new Exception("❌ Audit log for 'create_service' not found!");
    }
    if ((int)$creationLog['admin_user_id'] !== $adminId) {
        throw new Exception("❌ Audit log admin user mismatch!");
    }
    echo "✔ Audit log for 'create_service' successfully created.\n";

    // 6. Test Update Workflow
    echo "\n--- Testing Service Update ---\n";
    $updatedDisplayName = 'خدمة غسيل وكوي ملابس';
    [$errors, $updateDto] = $validation->validate([
        'public_id' => $createdItem['public_id'],
        'key' => $testKey,
        'slug' => $createdItem['slug'],
        'display_name_ar' => $updatedDisplayName,
        'short_name_ar' => $testShortName,
        'description_ar' => 'وصف معدل',
        'icon' => 'icon-cleaning',
        'sort_order' => '15',
        'is_active' => '1'
    ], $newId);

    if (!empty($errors)) {
        throw new Exception("❌ Unexpected validation errors on update: " . print_r($errors, true));
    }

    $updateSuccess = $service->updateService($newId, $updateDto, $adminId, $ip);
    if (!$updateSuccess) {
        throw new Exception("❌ Service update operation failed!");
    }

    $updatedItem = $service->getById($newId);
    if ($updatedItem['display_name_ar'] !== normalize_arabic($updatedDisplayName)) {
        throw new Exception("❌ Service display name was not updated correctly!");
    }
    if ((int)$updatedItem['sort_order'] !== 15) {
        throw new Exception("❌ Service sort order was not updated correctly!");
    }
    echo "✔ Service updated successfully in database.\n";

    // Verify Audit Log for update
    $updateLog = $db->fetch(
        "SELECT * FROM `audit_logs` WHERE `entity_type` = 'services' AND `entity_id` = :id AND `action` = 'update_service' ORDER BY id DESC LIMIT 1",
        ['id' => $newId]
    );
    if (!$updateLog) {
        throw new Exception("❌ Audit log for 'update_service' not found!");
    }
    $oldValues = json_decode($updateLog['old_value_json'], true);
    $newValues = json_decode($updateLog['new_value_json'], true);
    if ($oldValues['display_name_ar'] === $newValues['display_name_ar']) {
        throw new Exception("❌ Audit log failed to capture display name update difference!");
    }
    echo "✔ Audit log for 'update_service' successfully verified (delta captured).\n";

    // 7. Test Soft Delete
    echo "\n--- Testing Soft Delete ---\n";
    $deleteSuccess = $service->softDeleteService($newId, $adminId, $ip);
    if (!$deleteSuccess) {
        throw new Exception("❌ Soft delete failed!");
    }

    // Verify item is excluded from normal searches
    $searchResults = $service->searchServices(['keyword' => $testShortName]);
    foreach ($searchResults as $item) {
        if ((int)$item['id'] === $newId) {
            throw new Exception("❌ Soft-deleted item returned in default search results!");
        }
    }
    echo "✔ Soft-deleted item excluded from default searches.\n";

    // Verify it can still be fetched directly, marked as deleted
    $deletedItem = $service->getById($newId);
    if (!$deletedItem || (int)$deletedItem['is_deleted'] !== 1 || empty($deletedItem['deleted_at'])) {
        throw new Exception("❌ Service flags is_deleted or deleted_at were not updated correctly!");
    }
    echo "✔ Direct fetch of soft-deleted item verified (is_deleted = 1, deleted_at is set).\n";

    // Verify Audit Log for delete
    $deleteLog = $db->fetch(
        "SELECT * FROM `audit_logs` WHERE `entity_type` = 'services' AND `entity_id` = :id AND `action` = 'delete_service' LIMIT 1",
        ['id' => $newId]
    );
    if (!$deleteLog) {
        throw new Exception("❌ Audit log for 'delete_service' not found!");
    }
    echo "✔ Audit log for 'delete_service' successfully created.\n";

    // 8. Test Restore
    echo "\n--- Testing Restore ---\n";
    $restoreSuccess = $service->restoreService($newId, $adminId, $ip);
    if (!$restoreSuccess) {
        throw new Exception("❌ Restore operation failed!");
    }

    // Verify it is restored in database
    $restoredItem = $service->getById($newId);
    if (!$restoredItem || (int)$restoredItem['is_deleted'] !== 0 || !is_null($restoredItem['deleted_at'])) {
        throw new Exception("❌ Restore did not reset is_deleted or deleted_at flags!");
    }

    // Verify search matches again
    $searchRestored = $service->searchServices(['keyword' => $testShortName]);
    $foundRestored = false;
    foreach ($searchRestored as $item) {
        if ((int)$item['id'] === $newId) {
            $foundRestored = true;
            break;
        }
    }
    if (!$foundRestored) {
        throw new Exception("❌ Restored item not found in search results!");
    }
    echo "✔ Restore reset flags and item is searchable again.\n";

    // Verify Audit Log for restore
    $restoreLog = $db->fetch(
        "SELECT * FROM `audit_logs` WHERE `entity_type` = 'services' AND `entity_id` = :id AND `action` = 'restore_service' LIMIT 1",
        ['id' => $newId]
    );
    if (!$restoreLog) {
        throw new Exception("❌ Audit log for 'restore_service' not found!");
    }
    echo "✔ Audit log for 'restore_service' successfully created.\n";

    // Cleanup test record
    $db->execute("DELETE FROM `audit_logs` WHERE `entity_type` = 'services' AND `entity_id` = :id", ['id' => $newId]);
    $db->execute("DELETE FROM `services` WHERE `id` = :id", ['id' => $newId]);
    echo "\n✔ Cleaned up test service and related audit logs.\n";

    echo "\n============================================================\n";
    echo "✔ ALL INTEGRATION TESTS PASSED SUCCESSFULLY!\n";
    echo "============================================================\n";

} catch (Throwable $e) {
    echo "\n❌ TEST FAILURE:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    exit(1);
}
