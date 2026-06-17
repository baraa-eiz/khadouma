<?php
/**
 * UsersValidation.php
 * Khadomeh Users Validation and Normalization Pipeline
 */

namespace App\Modules\Users;

use App\Core\Database;

class UsersValidation
{
    private UsersRepositoryInterface $repo;

    public function __construct(UsersRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Validate user form input.
     * Returns array: [array $errors, ?UserData $dto]
     */
    public function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        $displayName = isset($data['display_name']) ? trim(normalize_arabic((string)$data['display_name'])) : '';
        $email = isset($data['email']) && trim($data['email']) !== '' ? strtolower(trim((string)$data['email'])) : '';
        $phone = isset($data['phone']) && trim($data['phone']) !== '' ? phone_format((string)$data['phone']) : '';
        $cityId = isset($data['city_id']) && (int)$data['city_id'] > 0 ? (int)$data['city_id'] : null;
        $areaId = isset($data['area_id']) && (int)$data['area_id'] > 0 ? (int)$data['area_id'] : null;
        $defaultAddress = isset($data['default_address']) ? trim(normalize_arabic((string)$data['default_address'])) : '';
        
        $preferredContactMethod = $data['preferred_contact_method'] ?? 'phone';
        $preferredLanguage = $data['preferred_language'] ?? 'ar';
        $timezone = $data['timezone'] ?? 'Asia/Damascus';
        $marketingOptIn = isset($data['marketing_opt_in']) ? (int)$data['marketing_opt_in'] : 0;
        $status = $data['status'] ?? 'active';

        // 1. Validate Display Name
        if ($displayName === '') {
            $errors['display_name'][] = 'اسم المستخدم مطلوب.';
        } elseif (mb_strlen($displayName) < 3) {
            $errors['display_name'][] = 'يجب أن يكون اسم المستخدم 3 أحرف على الأقل.';
        } elseif (mb_strlen($displayName) > 150) {
            $errors['display_name'][] = 'يجب ألا يتجاوز اسم المستخدم 150 حرفاً.';
        }

        // 2. Validate Email
        if ($email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'][] = 'البريد الإلكتروني المدخل غير صالح.';
            } else {
                // Check uniqueness
                $existing = $this->repo->findByEmail($email);
                if ($existing && $existing->id !== $excludeId) {
                    $errors['email'][] = 'البريد الإلكتروني مستخدم بالفعل.';
                }
            }
        }

        // 3. Validate Phone
        if ($phone !== '') {
            // Check length and format
            if (!preg_match('/^09\d{8}$/', $phone)) {
                $errors['phone'][] = 'رقم الهاتف السوري غير صالح. يجب أن يبدأ بـ 09 ويتكون من 10 أرقام.';
            } else {
                // Check uniqueness
                $existing = $this->repo->findByPhone($phone);
                if ($existing && $existing->id !== $excludeId) {
                    $errors['phone'][] = 'رقم الهاتف مستخدم بالفعل.';
                }
            }
        }

        // 4. Require at least one contact channel (email or phone)
        if ($email === '' && $phone === '') {
            $errors['email'][] = 'يجب إدخال البريد الإلكتروني أو رقم الهاتف على الأقل.';
            $errors['phone'][] = 'يجب إدخال البريد الإلكتروني أو رقم الهاتف على الأقل.';
        }

        // 5. Validate city/area if provided
        $db = Database::getInstance();
        if ($cityId !== null) {
            $cityExists = $db->fetchColumn("SELECT COUNT(*) FROM `cities` WHERE `id` = :id AND `is_deleted` = 0", ['id' => $cityId]);
            if (!$cityExists) {
                $errors['city_id'][] = 'المدينة المحددة غير موجودة.';
            }
        }
        if ($areaId !== null) {
            $areaExists = $db->fetchColumn("SELECT COUNT(*) FROM `areas` WHERE `id` = :id AND `is_deleted` = 0", ['id' => $areaId]);
            if (!$areaExists) {
                $errors['area_id'][] = 'المنطقة المحددة غير موجودة.';
            }
        }

        $dto = null;
        if (empty($errors)) {
            $dto = new UserData([
                'id' => $excludeId,
                'public_id' => $data['public_id'] ?? null,
                'display_name' => $displayName,
                'email' => $email ?: null,
                'phone' => $phone ?: null,
                'avatar' => $data['avatar'] ?? null,
                'city_id' => $cityId,
                'area_id' => $areaId,
                'default_address' => $defaultAddress ?: null,
                'preferred_contact_method' => $preferredContactMethod,
                'preferred_language' => $preferredLanguage,
                'timezone' => $timezone,
                'marketing_opt_in' => $marketingOptIn,
                'notification_preferences' => $data['notification_preferences'] ?? null,
                'status' => $status,
                'last_login_at' => $data['last_login_at'] ?? null,
            ]);
        }

        return [$errors, $dto];
    }
}
