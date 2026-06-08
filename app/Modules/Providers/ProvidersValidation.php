<?php
/**
 * ProvidersValidation.php
 * Khadomeh Providers Form Validation and Normalization Pipeline
 */

namespace App\Modules\Providers;

use App\Repositories\ProviderRepository;

class ProvidersValidation
{
    private ProviderRepository $repo;

    public function __construct(ProviderRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Normalize, trim, and validate input data.
     * Returns an array: [array $errors, ?ProviderData $dto]
     */
    public function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        // 1. Preprocessing, Trim and Arabic Normalization
        $displayName = isset($data['display_name_ar']) ? normalize_arabic((string)$data['display_name_ar']) : '';
        $businessType = isset($data['business_type']) ? trim((string)$data['business_type']) : 'individual';
        $phone = isset($data['phone']) ? trim((string)$data['phone']) : '';
        $whatsapp = isset($data['whatsapp']) ? trim((string)$data['whatsapp']) : '';
        
        $cityId = isset($data['city_id']) ? (int)$data['city_id'] : 0;
        $primaryServiceId = isset($data['primary_service_id']) ? (int)$data['primary_service_id'] : 0;
        
        $shortDesc = isset($data['short_description_ar']) ? normalize_arabic((string)$data['short_description_ar']) : '';
        $desc = isset($data['description_ar']) ? normalize_arabic((string)$data['description_ar']) : '';
        
        $yearsExp = isset($data['years_experience']) ? trim((string)$data['years_experience']) : '0';
        $startingPrice = isset($data['starting_price']) && trim((string)$data['starting_price']) !== '' ? trim((string)$data['starting_price']) : '';
        $priceUnit = isset($data['price_unit']) ? trim((string)$data['price_unit']) : 'hour';
        
        $verified = isset($data['verified']) ? (bool)$data['verified'] : false;
        $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : true;
        $sortWeight = isset($data['sort_weight']) ? trim((string)$data['sort_weight']) : '0';
        $status = isset($data['status']) ? trim((string)$data['status']) : 'approved';
        
        // Handle slug creation
        $slug = isset($data['slug']) && trim($data['slug']) !== '' 
            ? slugify((string)$data['slug']) 
            : slugify($displayName);

        // Arrays mapping
        $areas = isset($data['areas']) && is_array($data['areas']) ? $data['areas'] : [];
        $services = isset($data['services']) && is_array($data['services']) ? $data['services'] : [];
        
        $logo = isset($data['logo']) ? trim((string)$data['logo']) : null;
        $workPhotos = isset($data['work_photos']) && is_array($data['work_photos']) ? $data['work_photos'] : [];
        
        $metaTitle = isset($data['meta_title_ar']) ? normalize_arabic((string)$data['meta_title_ar']) : '';
        $metaDesc = isset($data['meta_description_ar']) ? normalize_arabic((string)$data['meta_description_ar']) : '';

        // 2. Required Fields Validation
        if ($displayName === '') {
            $errors['display_name_ar'][] = 'اسم مزود الخدمة الكامل (بالعربية) مطلوب.';
        }
        if ($slug === '' || $slug === 'n-a') {
            $errors['slug'][] = 'الرابط اللطيف (Slug) مطلوب.';
        }
        if ($phone === '') {
            $errors['phone'][] = 'رقم الهاتف مطلوب.';
        }
        if ($cityId <= 0) {
            $errors['city_id'][] = 'المدينة مطلوبة.';
        }
        if ($primaryServiceId <= 0) {
            $errors['primary_service_id'][] = 'الخدمة الأساسية مطلوبة.';
        }

        // 3. Length Validations
        if ($displayName !== '' && mb_strlen($displayName) > 150) {
            $errors['display_name_ar'][] = 'يجب ألا يتجاوز اسم مزود الخدمة 150 حرفاً.';
        }
        if ($slug !== '' && mb_strlen($slug) > 150) {
            $errors['slug'][] = 'يجب ألا يتجاوز الرابط اللطيف 150 حرفاً.';
        }
        if ($phone !== '' && mb_strlen($phone) > 30) {
            $errors['phone'][] = 'يجب ألا يتجاوز رقم الهاتف 30 حرفاً.';
        }
        if ($whatsapp !== '' && mb_strlen($whatsapp) > 30) {
            $errors['whatsapp'][] = 'يجب ألا يتجاوز رقم الواتساب 30 حرفاً.';
        }
        if ($shortDesc !== '' && mb_strlen($shortDesc) > 255) {
            $errors['short_description_ar'][] = 'يجب ألا يتجاوز الوصف القصير 255 حرفاً.';
        }
        if ($metaTitle !== '' && mb_strlen($metaTitle) > 255) {
            $errors['meta_title_ar'][] = 'يجب ألا يتجاوز عنوان الميتا 255 حرفاً.';
        }

        // 4. Format & Content validation
        if ($slug !== '' && !preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors['slug'][] = 'يجب أن يحتوي الرابط اللطيف على أحرف إنجليزية صغيرة، أرقام، وشرطات (-) فقط.';
        }
        if ($displayName !== '' && !preg_match('/[\x{0600}-\x{06FF}]/u', $displayName)) {
            $errors['display_name_ar'][] = 'يجب أن يحتوي الاسم على حروف عربية.';
        }
        if ($phone !== '' && !preg_match('/^\+?[0-9\s-]+$/', $phone)) {
            $errors['phone'][] = 'تنسيق رقم الهاتف غير صالح. يجب أن يحتوي على أرقام وعلامة (+) فقط.';
        }
        if ($whatsapp !== '' && !preg_match('/^\+?[0-9\s-]+$/', $whatsapp)) {
            $errors['whatsapp'][] = 'تنسيق رقم الواتساب غير صالح.';
        }

        // 5. Numeric validations
        if ($yearsExp !== '' && !preg_match('/^\d+$/', $yearsExp)) {
            $errors['years_experience'][] = 'يجب أن تكون سنوات الخبرة رقماً صحيحاً موجباً.';
        }
        if ($sortWeight !== '' && !preg_match('/^\d+$/', $sortWeight)) {
            $errors['sort_weight'][] = 'يجب أن يكون ترتيب الفرز رقماً صحيحاً موجباً.';
        }
        if ($startingPrice !== '' && !is_numeric($startingPrice)) {
            $errors['starting_price'][] = 'يجب أن يكون السعر المبدئي قيمة رقمية.';
        }

        // 6. Database Uniqueness Check
        if ($slug !== '' && empty($errors['slug'])) {
            if ($this->repo->existsBySlug($slug, $excludeId)) {
                $errors['slug'][] = 'الرابط اللطيف (Slug) مستخدم بالفعل.';
            }
        }
        if ($phone !== '' && empty($errors['phone'])) {
            if ($this->repo->existsByPhone($phone, $excludeId)) {
                $errors['phone'][] = 'رقم الهاتف مستخدم بالفعل لمزود آخر.';
            }
        }

        // 7. If clean, construct and return the DTO
        $dto = null;
        if (empty($errors)) {
            $dto = new ProviderData([
                'id' => $excludeId,
                'slug' => $slug,
                'display_name_ar' => $displayName,
                'business_type' => $businessType,
                'phone' => $phone,
                'whatsapp' => $whatsapp !== '' ? $whatsapp : null,
                'city_id' => $cityId,
                'primary_service_id' => $primaryServiceId,
                'short_description_ar' => $shortDesc !== '' ? $shortDesc : null,
                'description_ar' => $desc !== '' ? $desc : null,
                'years_experience' => (int)$yearsExp,
                'starting_price' => $startingPrice !== '' ? (float)$startingPrice : null,
                'price_unit' => $priceUnit,
                'verified' => $verified,
                'is_active' => $isActive,
                'sort_weight' => (int)$sortWeight,
                'status' => $status,
                'areas' => $areas,
                'services' => $services,
                'logo' => $logo,
                'work_photos' => $workPhotos,
                'meta_title_ar' => $metaTitle !== '' ? $metaTitle : null,
                'meta_description_ar' => $metaDesc !== '' ? $metaDesc : null,
            ]);
        }

        return [$errors, $dto];
    }
}
