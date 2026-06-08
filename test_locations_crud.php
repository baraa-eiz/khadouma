<?php
/**
 * test_locations_crud.php
 * Integration test suite for Khadomeh Locations module (Cities & Areas)
 */

define('KHADOMEH_START', microtime(true));

// Setup fake server variables for CLI request
$_SERVER['REQUEST_URI'] = '/admin/cities';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';

// 1. Bootstrap the framework
require_once __DIR__ . '/app/Core/Bootstrap.php';
App\Core\Bootstrap::boot();

use App\Core\Database;
use App\Modules\Locations\CitiesRepository;
use App\Modules\Locations\CitiesService;
use App\Modules\Locations\CitiesValidation;
use App\Modules\Locations\CityData;
use App\Modules\Locations\AreasRepository;
use App\Modules\Locations\AreasService;
use App\Modules\Locations\AreasValidation;
use App\Modules\Locations\AreaData;

echo "============================================================\n";
echo "STARTING LOCATIONS CRUD INTEGRATION TESTS (CITIES & AREAS)\n";
echo "============================================================\n\n";

try {
    $db = Database::getInstance();
    $cityRepo = new CitiesRepository();
    $cityService = new CitiesService($cityRepo);
    $cityValidation = new CitiesValidation($cityRepo);

    $areaRepo = new AreasRepository();
    $areaService = new AreasService($areaRepo);
    $areaValidation = new AreasValidation($areaRepo, $cityRepo);

    echo "✔ Locations module components instantiated.\n";

    // --------------------------------------------------------
    // CITIES TESTING
    // --------------------------------------------------------
    echo "\n--- [Testing Cities CRUD] ---\n";

    // 1. Test Seeded City
    $damascus = $cityRepo->findBySlug('damascus');
    if (!$damascus) {
        throw new Exception("❌ Seeded city 'damascus' not found!");
    }
    echo "✔ Found seeded city: {$damascus['display_name_ar']} (id: {$damascus['id']}, public_id: {$damascus['public_id']})\n";

    // 2. Test City Validation Rules
    [$errors, $dto] = $cityValidation->validate([]);
    if (!isset($errors['key']) || !isset($errors['display_name_ar'])) {
        throw new Exception("❌ City validation failed to catch empty fields!");
    }
    echo "✔ City validation caught empty required fields.\n";

    [$errors, $dto] = $cityValidation->validate([
        'key' => 'Invalid City Key',
        'display_name_ar' => 'دمشق تجربة',
    ]);
    if (!isset($errors['key'])) {
        throw new Exception("❌ City validation failed to catch invalid key format!");
    }
    echo "✔ City validation caught invalid key format.\n";

    [$errors, $dto] = $cityValidation->validate([
        'key' => 'damascus',
        'display_name_ar' => 'دمشق',
    ]);
    if (!isset($errors['key']) || !isset($errors['display_name_ar'])) {
        throw new Exception("❌ City validation failed to catch duplicate key and name!");
    }
    echo "✔ City validation caught duplicate key and name constraints.\n";

    // 3. Test City Creation
    $testCityKey = 'test-city-' . time();
    $testCityName = 'مدينة تجريبية جديدة';
    [$errors, $cityDto] = $cityValidation->validate([
        'key' => $testCityKey,
        'display_name_ar' => $testCityName,
        'display_name_en' => 'Test City',
        'sort_order' => '50',
        'is_active' => '1',
    ]);

    if (!empty($errors)) {
        throw new Exception("❌ City validation failed on valid data: " . print_r($errors, true));
    }
    if (!$cityDto instanceof CityData) {
        throw new Exception("❌ City validation did not return a valid DTO instance!");
    }

    $adminId = 1;
    $ip = '127.0.0.1';
    $newCityId = $cityService->createCity($cityDto, $adminId, $ip);
    if ($newCityId <= 0) {
        throw new Exception("❌ Failed to create city record!");
    }
    echo "✔ City created successfully with ID: {$newCityId}\n";

    // Verify Audit Log for city creation
    $cityCreateLog = $db->fetch(
        "SELECT * FROM `audit_logs` WHERE `entity_type` = 'cities' AND `entity_id` = :id AND `action` = 'create_city' LIMIT 1",
        ['id' => $newCityId]
    );
    if (!$cityCreateLog) {
        throw new Exception("❌ Audit log for 'create_city' not found!");
    }
    echo "✔ Audit log for 'create_city' verified.\n";

    // 4. Test City Update
    [$errors, $cityUpdateDto] = $cityValidation->validate([
        'public_id' => $cityDto->public_id,
        'key' => $testCityKey,
        'slug' => $testCityKey,
        'display_name_ar' => 'مدينة تجريبية معدلة',
        'display_name_en' => 'Test City Edited',
        'sort_order' => '60',
        'is_active' => '1',
    ], $newCityId);

    $updateSuccess = $cityService->updateCity($newCityId, $cityUpdateDto, $adminId, $ip);
    if (!$updateSuccess) {
        throw new Exception("❌ City update operation failed!");
    }
    $updatedCity = $cityService->getById($newCityId);
    if ($updatedCity['display_name_ar'] !== normalize_arabic('مدينة تجريبية معدلة')) {
        throw new Exception("❌ City display name update failed!");
    }
    echo "✔ City updated successfully in database.\n";

    // Verify Audit Log for city update
    $cityUpdateLog = $db->fetch(
        "SELECT * FROM `audit_logs` WHERE `entity_type` = 'cities' AND `entity_id` = :id AND `action` = 'update_city' ORDER BY id DESC LIMIT 1",
        ['id' => $newCityId]
    );
    if (!$cityUpdateLog) {
        throw new Exception("❌ Audit log for 'update_city' not found!");
    }
    echo "✔ Audit log for 'update_city' verified.\n";

    // --------------------------------------------------------
    // AREAS TESTING
    // --------------------------------------------------------
    echo "\n--- [Testing Areas CRUD] ---\n";

    // 1. Test Area Validation Rules
    [$errors, $dto] = $areaValidation->validate([]);
    if (!isset($errors['city_id']) || !isset($errors['key']) || !isset($errors['display_name_ar'])) {
        throw new Exception("❌ Area validation failed to catch empty fields!");
    }
    echo "✔ Area validation caught empty fields.\n";

    [$errors, $dto] = $areaValidation->validate([
        'city_id' => 99999, // Non-existent city
        'key' => 'mezzeh',
        'display_name_ar' => 'المزة الجديدة',
    ]);
    if (!isset($errors['city_id'])) {
        throw new Exception("❌ Area validation failed to catch non-existent city association!");
    }
    echo "✔ Area validation caught invalid city association.\n";

    // 2. Test Area Creation in Damascus
    $testAreaKey = 'test-area-' . time();
    $testAreaName = 'منطقة تجريبية';
    [$errors, $areaDto] = $areaValidation->validate([
        'city_id' => $damascus['id'],
        'key' => $testAreaKey,
        'display_name_ar' => $testAreaName,
        'display_name_en' => 'Test Area',
        'sort_order' => '10',
        'is_active' => '1',
    ]);

    if (!empty($errors)) {
        throw new Exception("❌ Area validation failed on valid data: " . print_r($errors, true));
    }
    if (!$areaDto instanceof AreaData) {
        throw new Exception("❌ Area validation did not return a valid DTO instance!");
    }

    $newAreaId = $areaService->createArea($areaDto, $adminId, $ip);
    if ($newAreaId <= 0) {
        throw new Exception("❌ Failed to create area record!");
    }
    echo "✔ Area created successfully in Damascus with ID: {$newAreaId}\n";

    // Verify Audit Log for area creation
    $areaCreateLog = $db->fetch(
        "SELECT * FROM `audit_logs` WHERE `entity_type` = 'areas' AND `entity_id` = :id AND `action` = 'create_area' LIMIT 1",
        ['id' => $newAreaId]
    );
    if (!$areaCreateLog) {
        throw new Exception("❌ Audit log for 'create_area' not found!");
    }
    echo "✔ Audit log for 'create_area' verified.\n";

    // 3. Test Scoped Uniqueness constraint (Area slugs must be unique within their city only)
    $duplicateSlugKey = 'dup-slug-' . time();
    $duplicateSlugName = 'منطقة مكررة الاسم';

    // Create a first area in Damascus with a slug
    [$errors, $a1Dto] = $areaValidation->validate([
        'city_id' => $damascus['id'],
        'key' => $duplicateSlugKey,
        'slug' => $duplicateSlugKey,
        'display_name_ar' => $duplicateSlugName,
        'is_active' => '1',
    ]);
    if (!empty($errors)) {
        throw new Exception("❌ Failed to validate first area: " . print_r($errors, true));
    }
    $a1Id = $areaService->createArea($a1Dto, $adminId, $ip);

    // Try to create a second area in Damascus with the same slug -> MUST FAIL!
    [$errors, $a2Dto] = $areaValidation->validate([
        'city_id' => $damascus['id'],
        'key' => $duplicateSlugKey,
        'slug' => $duplicateSlugKey,
        'display_name_ar' => $duplicateSlugName,
        'is_active' => '1',
    ]);
    if (!isset($errors['slug']) && !isset($errors['key'])) {
        throw new Exception("❌ Area uniqueness check failed! Allowed duplicate slug inside the same city.");
    }
    echo "✔ Area validation correctly blocked duplicate slug inside the same city.\n";

    // Try to create the same slug in a DIFFERENT city (using the newly created testCity) -> MUST SUCCEED!
    [$errors, $a3Dto] = $areaValidation->validate([
        'city_id' => $newCityId,
        'key' => $duplicateSlugKey,
        'slug' => $duplicateSlugKey,
        'display_name_ar' => $duplicateSlugName,
        'is_active' => '1',
    ]);
    if (!empty($errors)) {
        throw new Exception("❌ Area uniqueness check failed! Blocked duplicate slug in a different city: " . print_r($errors, true));
    }
    $a3Id = $areaService->createArea($a3Dto, $adminId, $ip);
    echo "✔ Area uniqueness check verified (duplicate slug allowed in a different city).\n";

    // 4. Test Soft Delete & Restore
    // Soft delete Area
    $delAreaSuccess = $areaService->softDeleteArea($newAreaId, $adminId, $ip);
    if (!$delAreaSuccess) {
        throw new Exception("❌ Area soft delete failed!");
    }
    $deletedArea = $areaService->getById($newAreaId);
    if ((int)$deletedArea['is_deleted'] !== 1 || empty($deletedArea['deleted_at'])) {
        throw new Exception("❌ Area soft delete flags not updated correctly!");
    }
    echo "✔ Area soft delete verified.\n";

    // Restore Area
    $resAreaSuccess = $areaService->restoreArea($newAreaId, $adminId, $ip);
    if (!$resAreaSuccess) {
        throw new Exception("❌ Area restore failed!");
    }
    $restoredArea = $areaService->getById($newAreaId);
    if ((int)$restoredArea['is_deleted'] !== 0 || !is_null($restoredArea['deleted_at'])) {
        throw new Exception("❌ Area restore flags not reset correctly!");
    }
    echo "✔ Area restore verified.\n";

    // Soft delete City
    $delCitySuccess = $cityService->softDeleteCity($newCityId, $adminId, $ip);
    if (!$delCitySuccess) {
        throw new Exception("❌ City soft delete failed!");
    }
    $deletedCity = $cityService->getById($newCityId);
    if ((int)$deletedCity['is_deleted'] !== 1 || empty($deletedCity['deleted_at'])) {
        throw new Exception("❌ City soft delete flags not updated correctly!");
    }
    echo "✔ City soft delete verified.\n";

    // Restore City
    $resCitySuccess = $cityService->restoreCity($newCityId, $adminId, $ip);
    if (!$resCitySuccess) {
        throw new Exception("❌ City restore failed!");
    }
    $restoredCity = $cityService->getById($newCityId);
    if ((int)$restoredCity['is_deleted'] !== 0 || !is_null($restoredCity['deleted_at'])) {
        throw new Exception("❌ City restore flags not reset correctly!");
    }
    echo "✔ City restore verified.\n";

    // --------------------------------------------------------
    // CLEANUP
    // --------------------------------------------------------
    echo "\n--- [Cleaning Up Test Records] ---\n";
    $db->execute("DELETE FROM `audit_logs` WHERE `entity_type` = 'areas' AND `entity_id` IN (:a1, :a2, :a3)", ['a1' => $newAreaId, 'a2' => $a1Id, 'a3' => $a3Id]);
    $db->execute("DELETE FROM `areas` WHERE `id` IN (:a1, :a2, :a3)", ['a1' => $newAreaId, 'a2' => $a1Id, 'a3' => $a3Id]);
    
    $db->execute("DELETE FROM `audit_logs` WHERE `entity_type` = 'cities' AND `entity_id` = :c1", ['c1' => $newCityId]);
    $db->execute("DELETE FROM `cities` WHERE `id` = :c1", ['c1' => $newCityId]);
    echo "✔ Test records and audit logs cleaned up successfully.\n";

    echo "\n============================================================\n";
    echo "✔ ALL LOCATIONS INTEGRATION TESTS PASSED SUCCESSFULLY!\n";
    echo "============================================================\n";

} catch (Throwable $e) {
    echo "\n❌ LOCATIONS TEST FAILURE:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    exit(1);
}
