<?php

namespace App\Services;

use App\Core\Database;
use App\Repositories\ProviderRepository;

class ReviewAggregationService
{
    private Database $db;
    private ProviderRepository $providerRepo;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->providerRepo = new ProviderRepository();
    }

    /**
     * Recalculates provider stats: rating, reviews_count, and platform_score.
     * 
     * Formula for platform_score (Max 100 points):
     * 1. Detailed description present (>10 chars): +20 points
     * 2. Profile avatar present: +15 points
     * 3. Gallery/Work photos present: +15 points
     * 4. Contact completeness (phone: +5, whatsapp: +5, email: +5): +15 points
     * 5. Verification status:
     *    - 'verified': +25 points
     *    - 'pending_review' / 'documents_uploaded' / 'resubmitted': +10 points
     *    - Others (unverified, rejected): +0 points
     * 6. Approved reviews count (+2 points per review): +10 points max (5+ reviews)
     * 
     * @param int $providerId
     * @return void
     */
    public function recalculateProviderStats(int $providerId): void
    {
        // 1. Calculate average rating and reviews count from approved reviews
        $reviews = $this->db->fetchAll(
            "SELECT rating FROM `reviews` WHERE `provider_id` = :pid AND `status` = 'approved' AND `deleted_at` IS NULL",
            ['pid' => $providerId]
        );

        $reviewsCount = count($reviews);
        $averageRating = 0.00;
        if ($reviewsCount > 0) {
            $totalRating = 0;
            foreach ($reviews as $r) {
                $totalRating += (int)$r['rating'];
            }
            $averageRating = round($totalRating / $reviewsCount, 2);
        }

        // 2. Calculate provider details/completeness for platform score
        $provider = $this->db->fetch(
            "SELECT description_ar, phone, whatsapp, email, verification_status FROM `providers` WHERE `id` = :pid",
            ['pid' => $providerId]
        );

        if (!$provider) {
            return;
        }

        $platformScore = 0;

        // description present: +20 points
        if (!empty($provider['description_ar']) && mb_strlen(trim($provider['description_ar'])) > 10) {
            $platformScore += 20;
        }

        // profile image present: +15 points
        $profileImage = $this->providerRepo->getProviderProfileImage($providerId);
        if (!empty($profileImage)) {
            $platformScore += 15;
        }

        // work photos present (gallery): +15 points
        $workPhotos = $this->providerRepo->getProviderWorkPhotos($providerId);
        if (!empty($workPhotos)) {
            $platformScore += 15;
        }

        // contact completeness: +15 points total (+5 for phone, +5 for whatsapp, +5 for email)
        if (!empty($provider['phone'])) {
            $platformScore += 5;
        }
        if (!empty($provider['whatsapp'])) {
            $platformScore += 5;
        }
        if (!empty($provider['email'])) {
            $platformScore += 5;
        }

        // verification status:
        // 'verified' => 25, 'pending_review'/'documents_uploaded'/'resubmitted' => 10, other => 0
        $vStatus = $provider['verification_status'] ?? 'unverified';
        if ($vStatus === 'verified') {
            $platformScore += 25;
        } elseif (in_array($vStatus, ['pending_review', 'documents_uploaded', 'resubmitted'])) {
            $platformScore += 10;
        }

        // approved reviews: +2 points per approved review, up to +10 max
        $reviewsPoints = $reviewsCount * 2;
        if ($reviewsPoints > 10) {
            $reviewsPoints = 10;
        }
        $platformScore += $reviewsPoints;

        // 3. Update providers table
        $verifiedVal = ($vStatus === 'verified') ? 1 : 0;
        $this->db->execute(
            "UPDATE `providers` SET 
                `rating` = :rating, 
                `reviews_count` = :reviews_count, 
                `platform_score` = :platform_score, 
                `verified` = :verified 
             WHERE `id` = :pid",
            [
                'rating' => $averageRating,
                'reviews_count' => $reviewsCount,
                'platform_score' => $platformScore,
                'verified' => $verifiedVal,
                'pid' => $providerId
            ]
        );
    }
}
