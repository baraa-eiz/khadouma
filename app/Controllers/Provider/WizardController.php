<?php

namespace App\Controllers\Provider;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Repositories\ProviderAccountRepository;
use App\Repositories\ProviderDraftRepository;
use App\Repositories\ProviderRepository;
use App\Modules\Locations\CitiesRepository;
use App\Modules\Locations\AreasRepository;
use App\Modules\Services\ServicesRepository;

class WizardController extends Controller
{
    private ProviderAccountRepository $accountRepo;
    private ProviderDraftRepository $draftRepo;
    private ProviderRepository $providerRepo;
    private CitiesRepository $citiesRepo;
    private AreasRepository $areasRepo;
    private ServicesRepository $servicesRepo;

    public function __construct()
    {
        $this->accountRepo = new ProviderAccountRepository();
        $this->draftRepo = new ProviderDraftRepository();
        $this->providerRepo = new ProviderRepository();
        $this->citiesRepo = new CitiesRepository();
        $this->areasRepo = new AreasRepository();
        $this->servicesRepo = new ServicesRepository();
    }

    /**
     * Display the 5-step onboarding wizard.
     */
    public function index(Request $request): Response
    {
        $accountId = Session::get('provider_account_id');
        $account = $this->accountRepo->find($accountId);

        if (!$account) {
            Session::destroy();
            return Response::redirect('/provider/login');
        }

        // Get latest draft
        $draft = $this->draftRepo->getLatestDraftForAccount($accountId);
        if (!$draft) {
            // Re-create one if somehow missing
            $draftId = $this->draftRepo->create([
                'provider_account_id' => $accountId,
                'status' => 'draft',
                'display_name_ar' => $account['display_name'],
                'email' => $account['email']
            ]);
            $draft = $this->draftRepo->find($draftId);
        }

        // Check lock-on-submit status
        if ($draft['status'] === 'pending_review') {
            Flash::warning('الملف بانتظار مراجعة الإدارة حالياً ولا يمكن تعديله.');
            return Response::redirect('/provider/dashboard');
        }

        // Load reference data for dropdowns/checkboxes
        $cities = $this->citiesRepo->search(['is_active' => 1, 'is_deleted' => 0], 'sort_order', 'ASC', 100);
        $services = $this->servicesRepo->search(['is_active' => 1, 'is_deleted' => 0], 'sort_order', 'ASC', 100);
        $areas = $this->areasRepo->search(['is_active' => 1, 'is_deleted' => 0], 'sort_order', 'ASC', 500);

        return $this->render('provider/wizard', [
            'account' => $account,
            'draft' => $draft,
            'cities' => $cities,
            'services' => $services,
            'areas' => $areas
        ]);
    }

    /**
     * Handle AJAX autosave for steps.
     */
    public function saveStep(Request $request): Response
    {
        $accountId = Session::get('provider_account_id');
        $draft = $this->draftRepo->getLatestDraftForAccount($accountId);

        if (!$draft || $draft['status'] === 'pending_review') {
            return Response::json([
                'success' => false,
                'message' => 'غير مسموح بالتعديل في الوقت الحالي.'
            ], 403);
        }

        $step = (int)$request->input('step', 1);
        $data = $draft; // Load current draft state

        if ($step === 1) {
            // Step 1: Identity & Core
            $data['display_name_ar'] = trim($request->input('display_name_ar', $draft['display_name_ar']));
            
            // Auto-slugify display name if empty or manually updated
            $slugInput = trim($request->input('slug', $draft['slug']));
            if (empty($slugInput) && !empty($data['display_name_ar'])) {
                $data['slug'] = slugify($data['display_name_ar']);
            } else {
                $data['slug'] = slugify($slugInput);
            }
            
            $data['business_type'] = trim($request->input('business_type', $draft['business_type']));
            $data['phone'] = phone_format($request->input('phone', $draft['phone']));
            $data['whatsapp'] = phone_format($request->input('whatsapp', $draft['whatsapp']));
            $data['email'] = trim($request->input('email', $draft['email']));
        } 
        elseif ($step === 2) {
            // Step 2: Location & Category
            $data['city_id'] = $request->input('city_id') !== '' ? (int)$request->input('city_id') : null;
            $data['primary_service_id'] = $request->input('primary_service_id') !== '' ? (int)$request->input('primary_service_id') : null;
            
            $secServices = $request->input('secondary_services', []);
            $data['secondary_services_json'] = is_array($secServices) ? array_map('intval', $secServices) : [];
            
            $covAreas = $request->input('coverage_areas', []);
            $data['coverage_areas_json'] = is_array($covAreas) ? array_map('intval', $covAreas) : [];
        } 
        elseif ($step === 3) {
            // Step 3: Professional Details
            $data['description_ar'] = trim($request->input('description_ar', $draft['description_ar']));
            $data['short_description_ar'] = trim($request->input('short_description_ar', $draft['short_description_ar']));
            $data['years_experience'] = $request->input('years_experience') !== '' ? (int)$request->input('years_experience') : 0;
            $data['starting_price'] = $request->input('starting_price') !== '' ? (float)$request->input('starting_price') : null;
            $data['price_unit'] = trim($request->input('price_unit', $draft['price_unit']));
            $data['website'] = trim($request->input('website', $draft['website']));
            $data['working_hours'] = trim($request->input('working_hours', $draft['working_hours']));
            
            $social = $request->input('social_links', []);
            $data['social_links'] = is_array($social) ? $social : [];
        } 
        elseif ($step === 4) {
            // Step 4: Media Gallery & Uploads
            $uploadDir = dirname(dirname(dirname(__DIR__))) . '/public/uploads/providers/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Handle logo delete
            if ($request->input('delete_logo') === '1') {
                if ($draft['logo_path'] && file_exists(dirname(dirname(dirname(__DIR__))) . '/' . $draft['logo_path'])) {
                    @unlink(dirname(dirname(dirname(__DIR__))) . '/' . $draft['logo_path']);
                }
                $data['logo_path'] = null;
            }

            // Handle specific gallery photo delete
            $deletePhotoPath = $request->input('delete_photo_path');
            if ($deletePhotoPath) {
                $currentPhotos = $draft['work_photos_json'] ?? [];
                if (($key = array_search($deletePhotoPath, $currentPhotos)) !== false) {
                    unset($currentPhotos[$key]);
                    if (file_exists(dirname(dirname(dirname(__DIR__))) . '/' . $deletePhotoPath)) {
                        @unlink(dirname(dirname(dirname(__DIR__))) . '/' . $deletePhotoPath);
                    }
                }
                $data['work_photos_json'] = array_values($currentPhotos);
            }

            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['logo'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $newLogoName = 'logo_account_' . $accountId . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $uploadDir . $newLogoName)) {
                        // Delete old logo if any
                        if ($draft['logo_path'] && file_exists(dirname(dirname(dirname(__DIR__))) . '/' . $draft['logo_path'])) {
                            @unlink(dirname(dirname(dirname(__DIR__))) . '/' . $draft['logo_path']);
                        }
                        $data['logo_path'] = 'public/uploads/providers/' . $newLogoName;
                    }
                }
            }

            // Handle gallery photo upload
            if (isset($_FILES['gallery_photos'])) {
                $currentPhotos = $data['work_photos_json'] ?? [];
                $files = $_FILES['gallery_photos'];
                
                // If single file uploaded (not multiple HTML attribute), normalize to array
                if (!is_array($files['name'])) {
                    $files = [
                        'name' => [$files['name']],
                        'type' => [$files['type']],
                        'tmp_name' => [$files['tmp_name']],
                        'error' => [$files['error']],
                        'size' => [$files['size']]
                    ];
                }

                foreach ($files['name'] as $i => $name) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $newPhotoName = 'work_account_' . $accountId . '_' . $i . '_' . time() . '.' . $ext;
                            if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $newPhotoName)) {
                                $currentPhotos[] = 'public/uploads/providers/' . $newPhotoName;
                            }
                        }
                    }
                }
                $data['work_photos_json'] = $currentPhotos;
            }
        } 
        elseif ($step === 5) {
            // Step 5: SEO
            $data['meta_title_ar'] = trim($request->input('meta_title_ar', $draft['meta_title_ar']));
            $data['meta_description_ar'] = trim($request->input('meta_description_ar', $draft['meta_description_ar']));
        }

        // Save progress
        $this->draftRepo->update($draft['id'], $data);

        // Calculate new completeness score
        $updatedDraft = $this->draftRepo->find($draft['id']);
        $score = $this->calculateScore($updatedDraft);

        return Response::json([
            'success' => true,
            'message' => 'تم حفظ التقدم تلقائياً.',
            'completeness_score' => $score,
            'draft' => $updatedDraft
        ]);
    }

    /**
     * Submit draft for admin review.
     */
    public function submitReview(Request $request): Response
    {
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/provider/wizard');
        }

        $accountId = Session::get('provider_account_id');
        $draft = $this->draftRepo->getLatestDraftForAccount($accountId);

        if (!$draft || $draft['status'] === 'pending_review') {
            Flash::error('الملف غير متوفر للمراجعة حالياً.');
            return Response::redirect('/provider/dashboard');
        }

        // Validate mandatory fields
        $errors = [];
        if (empty($draft['display_name_ar'])) $errors[] = 'الاسم التجاري مطلوب.';
        if (empty($draft['slug'])) $errors[] = 'الرابط الفريد (Slug) مطلوب.';
        if (empty($draft['phone'])) $errors[] = 'رقم الهاتف مطلوب.';
        if (empty($draft['city_id'])) $errors[] = 'يرجى اختيار المدينة.';
        if (empty($draft['primary_service_id'])) $errors[] = 'يرجى اختيار الخدمة الرئيسية.';
        if (empty($draft['short_description_ar'])) $errors[] = 'الوصف المختصر مطلوب.';
        if (empty($draft['description_ar'])) $errors[] = 'الوصف التفصيلي مطلوب.';
        if (!isset($draft['years_experience']) || (int)$draft['years_experience'] < 0) $errors[] = 'سنوات الخبرة مطلوبة ويجب أن لا تكون سالبة.';
        if (empty($draft['starting_price'])) $errors[] = 'السعر الافتتاحي مطلوب.';
        
        // Check uniqueness of slug
        $excludeProviderId = $draft['provider_id'];
        $slugExists = $this->providerRepo->existsBySlug($draft['slug'], $excludeProviderId);
        
        if ($slugExists) {
            $errors[] = 'الرابط الفريد (Slug) مستخدم بالفعل من قبل مزود آخر. يرجى تعديل الاسم التجاري.';
        }

        if (!empty($errors)) {
            Flash::error('تعذر الإرسال! يرجى إكمال الحقول الإلزامية التالية: <br>' . implode('<br>', $errors));
            return Response::redirect('/provider/wizard');
        }

        // Mark draft as pending review
        $draft['status'] = 'pending_review';
        $this->draftRepo->update($draft['id'], $draft);

        Flash::success('تم إرسال ملفك الشخصي بنجاح! سيقوم مسؤول النظام بمراجعته ونشره قريباً.');
        return Response::redirect('/provider/dashboard');
    }

    /**
     * Local helper to calculate draft completeness score.
     */
    private function calculateScore(array $draft): int
    {
        $score = 0;
        if (!empty($draft['logo_path'])) $score += 15;
        if (!empty($draft['coverage_areas_json'])) $score += 15;
        if (!empty($draft['description_ar'])) $score += 15;
        if (!empty($draft['short_description_ar'])) $score += 10;
        if (!empty($draft['whatsapp'])) $score += 10;
        if (!empty($draft['email'])) $score += 10;
        if (!empty($draft['work_photos_json'])) $score += 15;
        if (!empty($draft['years_experience']) && (int)$draft['years_experience'] > 0) $score += 5;
        if (!empty($draft['secondary_services_json'])) $score += 5;
        return $score;
    }
}
