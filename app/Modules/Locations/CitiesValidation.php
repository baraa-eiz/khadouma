<?php
/**
 * CitiesValidation.php
 * Khadomeh Cities Form Validation and Normalization Pipeline
 */

namespace App\Modules\Locations;

class CitiesValidation
{
    private CitiesRepositoryInterface $repo;

    public function __construct(CitiesRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Normalize, trim, and validate input data.
     * Returns an array: [array $errors, ?CityData $dto]
     */
    public function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        // 1. Preprocessing, Trim and Arabic Normalization
        $key = isset($data['key']) ? trim(strtolower((string)$data['key'])) : '';
        $displayName = isset($data['display_name_ar']) ? normalize_arabic((string)$data['display_name_ar']) : '';
        $displayNameEn = isset($data['display_name_en']) ? trim((string)$data['display_name_en']) : '';
        
        // Auto-generate slug from key if empty
        $slug = isset($data['slug']) && trim($data['slug']) !== '' 
            ? slugify((string)$data['slug']) 
            : slugify($key);

        $sortOrder = isset($data['sort_order']) ? trim((string)$data['sort_order']) : '0';
        $metaTitle = isset($data['meta_title_ar']) ? normalize_arabic((string)$data['meta_title_ar']) : '';
        $metaDesc = isset($data['meta_description_ar']) ? normalize_arabic((string)$data['meta_description_ar']) : '';
        $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : true;

        // 2. Required Fields Validation
        if ($key === '') {
            $errors['key'][] = 'الرمز التعريفي (Key) مطلوب.';
        }
        if ($displayName === '') {
            $errors['display_name_ar'][] = 'اسم المدينة الكامل (بالعربية) مطلوب.';
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
            $errors['display_name_ar'][] = 'يجب ألا يتجاوز اسم المدينة الكامل 150 حرفاً.';
        }
        if ($displayNameEn !== '' && mb_strlen($displayNameEn) > 150) {
            $errors['display_name_en'][] = 'يجب ألا يتجاوز اسم المدينة بالإنجليزية 150 حرفاً.';
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
            $errors['display_name_ar'][] = 'يجب أن يحتوي اسم المدينة على حروف عربية.';
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
        if ($displayName !== '' && empty($errors['display_name_ar'])) {
            if ($this->repo->existsByName($displayName, $excludeId)) {
                $errors['display_name_ar'][] = 'اسم المدينة الكامل (بالعربية) مستخدم بالفعل.';
            }
        }

        // 7. Construct and return DTO
        $dto = null;
        if (empty($errors)) {
            $dto = new CityData([
                'public_id' => $data['public_id'] ?? null,
                'key' => $key,
                'slug' => $slug,
                'display_name_ar' => $displayName,
                'display_name_en' => $displayNameEn ?: null,
                'sort_order' => (int)$sortOrder,
                'meta_title_ar' => $metaTitle ?: null,
                'meta_description_ar' => $metaDesc ?: null,
                'is_active' => $isActive,
            ]);
        }

        return [$errors, $dto];
    }
}
