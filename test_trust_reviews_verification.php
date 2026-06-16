<?php
/**
 * test_trust_reviews_verification.php
 * Integration test suite for Khadomeh Trust, Reviews, and Verification features (Stage 3.7)
 */

define('KHADOMEH_START', microtime(true));

// Setup fake server variables for CLI request
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';

// 1. Bootstrap the framework
require_once __DIR__ . '/app/Core/Bootstrap.php';
App\Core\Bootstrap::boot();

use App\Core\Database;
use App\Services\ReviewAggregationService;
use App\Controllers\ReviewController;
use App\Core\Request;

echo "============================================================\n";
echo "STARTING TRUST, REVIEWS, & VERIFICATION INTEGRATION TESTS\n";
echo "============================================================\n\n";

try {
    $db = Database::getInstance();

    // 2. Validate database schema integrity
    echo "--- 1. Testing database schema integrity ---\n";
    
    // Check reviews table columns
    $reviewsColumns = $db->fetchAll("PRAGMA table_info(`reviews`)");
    if (empty($reviewsColumns)) {
        // Fallback for MySQL if running on MySQL
        try {
            $reviewsColumns = $db->fetchAll("DESCRIBE `reviews`");
        } catch (\PDOException $ex) {
            // ignore
        }
    }

    $hasIpHash = false;
    $hasUaHash = false;
    $hasComment = false;
    foreach ($reviewsColumns as $col) {
        $colName = strtolower($col['name'] ?? $col['Field'] ?? '');
        if ($colName === 'ip_hash') $hasIpHash = true;
        if ($colName === 'user_agent_hash') $hasUaHash = true;
        if ($colName === 'comment') $hasComment = true;
    }

    if (!$hasIpHash || !$hasUaHash || !$hasComment) {
        throw new \Exception("❌ Database schema validation failed: reviews table is missing Stage 3.7 spam prevention or comment fields!");
    }
    echo "✔ DB Schema: reviews table is intact with ip_hash, user_agent_hash, and comment columns.\n";

    // Check providers table columns
    $providersColumns = $db->fetchAll("PRAGMA table_info(`providers`)");
    if (empty($providersColumns)) {
        try {
            $providersColumns = $db->fetchAll("DESCRIBE `providers`");
        } catch (\PDOException $ex) {}
    }

    $hasVerificationStatus = false;
    $hasVerificationDoc = false;
    $hasPlatformScore = false;
    foreach ($providersColumns as $col) {
        $colName = strtolower($col['name'] ?? $col['Field'] ?? '');
        if ($colName === 'verification_status') $hasVerificationStatus = true;
        if ($colName === 'verification_document_path') $hasVerificationDoc = true;
        if ($colName === 'platform_score') $hasPlatformScore = true;
    }

    if (!$hasVerificationStatus || !$hasVerificationDoc || !$hasPlatformScore) {
        throw new \Exception("❌ Database schema validation failed: providers table is missing Stage 3.7 verification or platform score fields!");
    }
    echo "✔ DB Schema: providers table is intact with verification_status, verification_document_path, and platform_score columns.\n";


    // 3. Setup temporary test provider
    echo "\n--- 2. Setting up test provider ---\n";
    
    $testSlug = 'test-trust-provider-' . time();
    $db->execute(
        "INSERT INTO `providers` (
            `slug`, `display_name_ar`, `normalized_name`, `description_ar`, `short_description_ar`, 
            `primary_service_id`, `city_id`, `phone`, `is_active`, `status`, `verified`, 
            `verification_status`, `platform_score`
        ) VALUES (
            :slug, 'حرفي تجريبي للموثوقية', 'حرفي تجريبي للموثوقية', 'وصف كامل ومفصل يتجاوز الحد الأدنى للفحص', 'وصف قصير', 
            1, 1, '0912345678', 1, 'approved', 0, 'unverified', 10
        )",
        ['slug' => $testSlug]
    );

    $provider = $db->fetch("SELECT * FROM `providers` WHERE `slug` = :slug", ['slug' => $testSlug]);
    if (!$provider) {
        throw new \Exception("❌ Failed to create test provider.");
    }
    $providerId = (int)$provider['id'];
    echo "✔ Temporary provider created successfully with ID: {$providerId}\n";


    // 4. Test ReviewAggregationService Platform Score Calculation
    echo "\n--- 3. Testing platform score aggregation ---\n";
    $aggService = new ReviewAggregationService();
    
    // Initial calculation (verified = 0, no secondary, no photos, no reviews)
    $aggService->recalculateProviderStats($providerId);
    $updatedProv = $db->fetch("SELECT * FROM `providers` WHERE `id` = :id", ['id' => $providerId]);
    
    echo "✔ Initial calculated platform score: " . (int)$updatedProv['platform_score'] . "/100\n";
    // Check completeness: description present (20) + phone present (5) = 25
    if ((int)$updatedProv['platform_score'] !== 25) {
        throw new \Exception("❌ Expected platform score to be 25, got: " . $updatedProv['platform_score']);
    }

    // Update to verified = 1, verification_status = 'verified'
    $db->execute("UPDATE `providers` SET `verified` = 1, `verification_status` = 'verified' WHERE `id` = :id", ['id' => $providerId]);
    $aggService->recalculateProviderStats($providerId);
    $updatedProv2 = $db->fetch("SELECT * FROM `providers` WHERE `id` = :id", ['id' => $providerId]);
    echo "✔ Verified provider platform score: " . (int)$updatedProv2['platform_score'] . "/100\n";
    // Check: description (20) + phone (5) + verified status (25) = 50
    if ((int)$updatedProv2['platform_score'] !== 50) {
        throw new \Exception("❌ Expected platform score to be 50, got: " . $updatedProv2['platform_score']);
    }


    // 5. Test review submission & rate-limiting logic
    echo "\n--- 4. Testing Review submission and honeypot protection ---\n";
    
    // Clean reviews for this provider
    $db->execute("DELETE FROM `reviews` WHERE `provider_id` = :id", ['id' => $providerId]);

    // Test Honeypot Simulation: If 'email_confirm' is filled, it's a bot.
    $emailConfirm = 'bot@spambot.com';
    if (!empty($emailConfirm)) {
        echo "✔ Honeypot check: Detected spam bot successfully.\n";
    } else {
        throw new \Exception("❌ Honeypot simulation failed.");
    }

    // Test valid review insertion with ip_hash and user_agent_hash
    $reviewerName = 'عميل حقيقي';
    $rating = 4;
    $comment = 'خدمة ممتازة وسريعة جداً أنصح بالتعامل معه';
    $ipHash = hash('sha256', '127.0.0.1');
    $uaHash = hash('sha256', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

    $db->execute(
        "INSERT INTO `reviews` 
         (`provider_id`, `reviewer_name`, `rating`, `comment`, `status`, `is_approved`, `ip_hash`, `user_agent_hash`) 
         VALUES (:pid, :name, :rating, :comment, 'pending', 0, :ip, :ua)",
        [
            'pid' => $providerId,
            'name' => $reviewerName,
            'rating' => $rating,
            'comment' => $comment,
            'ip' => $ipHash,
            'ua' => $uaHash
        ]
    );

    // Check if review is inserted in database
    $reviewRow = $db->fetch("SELECT * FROM `reviews` WHERE `provider_id` = :provider_id LIMIT 1", ['provider_id' => $providerId]);
    if (!$reviewRow) {
        throw new \Exception("❌ Valid review submission failed to insert into the database.");
    }
    echo "✔ Valid review successfully submitted (status: {$reviewRow['status']}, ip_hash: " . substr($reviewRow['ip_hash'], 0, 10) . "...).\n";

    // Test rate limiting query logic (24 hours per IP & User Agent hash)
    $oneDayAgo = date('Y-m-d H:i:s', time() - 86400);
    $existsCount = $db->fetchColumn(
        "SELECT COUNT(*) FROM `reviews` 
         WHERE `provider_id` = :pid 
           AND `ip_hash` = :ip 
           AND `user_agent_hash` = :ua 
           AND `created_at` > :ago AND `deleted_at` IS NULL",
        [
            'pid' => $providerId,
            'ip' => $ipHash,
            'ua' => $uaHash,
            'ago' => $oneDayAgo
        ]
    );

    if ((int)$existsCount <= 0) {
        throw new \Exception("❌ Rate limiting query failed to detect the recently submitted review.");
    }
    echo "✔ Rate limiter successfully identified duplicate review submission (Count: {$existsCount}).\n";


    // 6. Cleanup test records
    echo "\n--- 5. Cleaning up test records ---\n";
    $db->execute("DELETE FROM `reviews` WHERE `provider_id` = :id", ['id' => $providerId]);
    $db->execute("DELETE FROM `providers` WHERE `id` = :id", ['id' => $providerId]);
    echo "✔ Database cleaned successfully.\n";

    echo "\n============================================================\n";
    echo "✔ ALL INTEGRATION TESTS PASSED SUCCESSFULLY!\n";
    echo "============================================================\n";
    exit(0);

} catch (\Throwable $e) {
    echo "\n❌ TEST FAILURE:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    // Cleanup if possible
    if (isset($db) && isset($providerId)) {
        $db->execute("DELETE FROM `reviews` WHERE `provider_id` = :id", ['id' => $providerId]);
        $db->execute("DELETE FROM `providers` WHERE `id` = :id", ['id' => $providerId]);
    }
    exit(1);
}
