<?php
/**
 * ServicesValidation.php
 * Khadomeh Services Form Validation and Normalization Pipeline
 */

namespace App\Modules\Services;

class ServicesValidation
{
    private ServicesRepositoryInterface $repo;

    public function __construct(ServicesRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Normalize, trim, and validate input data.
     * Returns an array: [array $errors, ?ServiceData $dto]
     */
    public function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        // 1. Preprocessing, Trim and Arabic Normalization
        $key = isset($data['key']) ? trim(strtolower((string)$data['key'])) : '';
        $shortName = isset($data['short_name_ar']) ? normalize_arabic((string)$data['short_name_ar']) : '';
        $displayName = isset($data['display_name_ar']) ? normalize_arabic((string)$data['display_name_ar']) : '';
        
        // Auto-generate slug from key if empty
        $slug = isset($data['slug']) && trim($data['slug']) !== '' 
            ? slugify((string)$data['slug']) 
            : slugify($key);

        $description = isset($data['description_ar']) ? normalize_arabic((string)$data['description_ar']) : '';
        $icon = isset($data['icon']) ? trim((string)$data['icon']) : '';
        $sortOrder = isset($data['sort_order']) ? trim((string)$data['sort_order']) : '0';
        $metaTitle = isset($data['meta_title_ar']) ? normalize_arabic((string)$data['meta_title_ar']) : '';
        $metaDesc = isset($data['meta_description_ar']) ? normalize_arabic((string)$data['meta_description_ar']) : '';
        $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : true;

        // 2. Required Fields Validation
        if ($key === '') {
            $errors['key'][] = 'الرمز التعريفي (Key) مطلوب.';
        }
        if ($displayName === '') {
            $errors['display_name_ar'][] = 'اسم الخدمة الكامل (بالعربية) مطلوب.';
        }
        if ($shortName === '') {
            $errors['short_name_ar'][] = 'الاسم المختصر (بالعربية) مطلوب.';
        }
        if ($slug === '' || $slug === 'n-a') {
            $errors['slug'][] = 'الرابط اللطيف (Slug) مطلوب.';
        }

        // 3. Length Validations
        if ($key !== '' && mb_strlen($key) > 100) {
            $errors['key'][] = 'يجب ألا يتجاوز الرمز التعريفي 100 حرف.';
        }
        if ($slug !== '' && mb_strlen($slug) > 100) {
            $errors['slug'][] = 'يجب ألا يتجاوز الرابط اللطيف 100 حرف.';
        }
        if ($displayName !== '' && mb_strlen($displayName) > 150) {
            $errors['display_name_ar'][] = 'يجب ألا يتجاوز اسم الخدمة الكامل 150 حرفاً.';
        }
        if ($shortName !== '' && mb_strlen($shortName) > 50) {
            $errors['short_name_ar'][] = 'يجب ألا يتجاوز الاسم المختصر 50 حرفاً.';
        }
        if ($icon !== '' && mb_strlen($icon) > 100) {
            $errors['icon'][] = 'يجب ألا يتجاوز اسم الأيقونة 100 حرف.';
        }
        if ($metaTitle !== '' && mb_strlen($metaTitle) > 255) {
            $errors['meta_title_ar'][] = 'يجب ألا يتجاوز عنوان الميتا 255 حرفاً.';
        }

        // 4. Format / Character Sets Validation
        if ($key !== '' && !preg_match('/^[a-z0-9_-]+$/', $key)) {
            $errors['key'][] = 'يجب أن يحتوي الرمز التعريفي على أحرف إنجليزية صغيرة، أرقام، شرطة (-) أو شرطة سفلية (_) فقط.';
        }
        if ($slug !== '' && !preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors['slug'][] = 'يجب أن يحتوي الرابط اللطيف على أحرف إنجليزية صغيرة، أرقام، وشرطات (-) فقط.';
        }
        // Ensure Arabic input contains Arabic letters
        if ($displayName !== '' && !preg_match('/[\x{0600}-\x{06FF}]/u', $displayName)) {
            $errors['display_name_ar'][] = 'يجب أن يحتوي اسم الخدمة على حروف عربية.';
        }
        if ($shortName !== '' && !preg_match('/[\x{0600}-\x{06FF}]/u', $shortName)) {
            $errors['short_name_ar'][] = 'يجب أن يحتوي الاسم المختصر على حروف عربية.';
        }

        // 5. Sort Order Integer Validation
        if (!preg_match('/^\d+$/', $sortOrder)) {
            $errors['sort_order'][] = 'يجب أن يكون ترتيب الفرز رقماً صحيحاً موجباً.';
        }

        // 6. Database Uniqueness Check
        if ($key !== '' && empty($errors['key'])) {
            if ($this->repo->existsByKey($key, $excludeId)) {
                $errors['key'][] = 'الرمز التعريفي (Key) مستخدم بالفعل.';
            }
        }
        if ($slug !== '' && empty($errors['slug'])) {
            if ($this->repo->existsBySlug($slug, $excludeId)) {
                $errors['slug'][] = 'الرابط اللطيف (Slug) مستخدم بالفعل.';
            }
        }

        // 7. If clean, construct and return the DTO
        $dto = null;
        if (empty($errors)) {
            $dto = new ServiceData([
                'public_id' => $data['public_id'] ?? null,
                'key' => $key,
                'slug' => $slug,
                'display_name_ar' => $displayName,
                'short_name_ar' => $shortName,
                'description_ar' => $description,
                'icon' => $icon,
                'sort_order' => (int)$sortOrder,
                'meta_title_ar' => $metaTitle,
                'meta_description_ar' => $metaDesc,
                'is_active' => $isActive,
            ]);
        }

        return [$errors, $dto];
    }
}
