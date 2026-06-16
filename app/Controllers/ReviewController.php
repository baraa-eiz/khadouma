<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Core\Flash;
use App\Repositories\ProviderRepository;
use App\Services\ReviewAggregationService;

class ReviewController extends Controller
{
    private ProviderRepository $providerRepo;
    private ReviewAggregationService $aggregationService;

    public function __construct()
    {
        $this->providerRepo = new ProviderRepository();
        $this->aggregationService = new ReviewAggregationService();
    }

    /**
     * Submit a new review for a provider.
     */
    public function submit(Request $request, string $slug): Response
    {
        // 1. Find the provider
        $provider = $this->providerRepo->findBySlug($slug);
        if (!$provider) {
            Flash::error('مزود الخدمة غير موجود.');
            return Response::redirect('/');
        }

        // 2. Validate CSRF
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/provider/' . $slug);
        }

        // 3. Honeypot check
        if (!empty($request->input('email_confirm'))) {
            // Silently ignore or return success to mislead the bot
            Flash::success('شكراً لك! تم إرسال تقييمك بنجاح وهو قيد المراجعة حالياً.');
            return Response::redirect('/provider/' . $slug);
        }

        // 4. Validate Inputs
        $reviewerName = trim($request->input('reviewer_name', ''));
        $rating = (int)$request->input('rating', 0);
        $comment = trim($request->input('comment', ''));

        if (mb_strlen($reviewerName) < 3 || mb_strlen($reviewerName) > 100) {
            Flash::error('يرجى إدخال اسم صحيح (بين 3 و 100 حرف).');
            return Response::redirect('/provider/' . $slug);
        }

        if ($rating < 1 || $rating > 5) {
            Flash::error('الرجاء اختيار تقييم صحيح بين 1 و 5 نجوم.');
            return Response::redirect('/provider/' . $slug);
        }

        if (mb_strlen($comment) < 5) {
            Flash::error('يرجى كتابة تعليق لا يقل عن 5 أحرف.');
            return Response::redirect('/provider/' . $slug);
        }

        // 5. Check Rate Limiting (24 hours per IP & User Agent hash)
        $ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $uaHash = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $oneDayAgo = date('Y-m-d H:i:s', time() - 86400);

        $db = Database::getInstance();
        $exists = $db->fetchColumn(
            "SELECT COUNT(*) FROM `reviews` 
             WHERE `provider_id` = :pid 
               AND `ip_hash` = :ip 
               AND `user_agent_hash` = :ua 
               AND `created_at` > :ago AND `deleted_at` IS NULL",
            [
                'pid' => $provider['id'],
                'ip' => $ipHash,
                'ua' => $uaHash,
                'ago' => $oneDayAgo
            ]
        );

        if ((int)$exists > 0) {
            Flash::error('لقد قمت بإضافة تقييم لهذا المزود مؤخراً. يرجى المحاولة بعد 24 ساعة.');
            return Response::redirect('/provider/' . $slug);
        }

        // 6. Insert pending review
        $db->execute(
            "INSERT INTO `reviews` 
             (`provider_id`, `reviewer_name`, `rating`, `comment`, `status`, `is_approved`, `ip_hash`, `user_agent_hash`) 
             VALUES (:pid, :name, :rating, :comment, 'pending', 0, :ip, :ua)",
            [
                'pid' => $provider['id'],
                'name' => $reviewerName,
                'rating' => $rating,
                'comment' => $comment,
                'ip' => $ipHash,
                'ua' => $uaHash
            ]
        );

        // Note: Approved only affects rating/score, so we don't recalculate aggregation here since it's pending.

        Flash::success('شكراً لك! تم إرسال تقييمك بنجاح وهو قيد المراجعة حالياً.');
        return Response::redirect('/provider/' . $slug);
    }
}
