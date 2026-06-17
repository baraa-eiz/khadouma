<?php
/**
 * UserData.php
 * Khadomeh User Account Data Transfer Object (DTO)
 */

namespace App\Modules\Users;

class UserData
{
    public readonly ?int $id;
    public readonly string $public_id;
    public readonly string $display_name;
    public readonly ?string $email;
    public readonly ?string $phone;
    public readonly ?string $avatar;
    public readonly ?int $city_id;
    public readonly ?int $area_id;
    public readonly ?string $default_address;
    public readonly string $preferred_contact_method;
    public readonly ?string $preferred_language;
    public readonly ?string $timezone;
    public readonly int $marketing_opt_in;
    public readonly ?string $notification_preferences;
    public readonly string $status;
    public readonly ?string $last_login_at;
    public readonly ?string $created_at;
    public readonly ?string $updated_at;
    public readonly ?string $deleted_at;
    public readonly int $completion_score;

    public function __construct(array $data)
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->public_id = $data['public_id'] ?? self::generateUuid4();
        
        // Normalize Display Name and Default Address via normalize_arabic helper
        $this->display_name = isset($data['display_name']) ? trim(normalize_arabic((string)$data['display_name'])) : '';
        $this->email = isset($data['email']) && trim($data['email']) !== '' ? strtolower(trim((string)$data['email'])) : null;
        
        // Normalize Phone via phone_format helper
        $this->phone = isset($data['phone']) && trim($data['phone']) !== '' ? phone_format((string)$data['phone']) : null;
        
        $this->avatar = isset($data['avatar']) && trim($data['avatar']) !== '' ? trim((string)$data['avatar']) : null;
        $this->city_id = isset($data['city_id']) && (int)$data['city_id'] > 0 ? (int)$data['city_id'] : null;
        $this->area_id = isset($data['area_id']) && (int)$data['area_id'] > 0 ? (int)$data['area_id'] : null;
        
        $this->default_address = isset($data['default_address']) && trim($data['default_address']) !== '' ? trim(normalize_arabic((string)$data['default_address'])) : null;
        $this->preferred_contact_method = $data['preferred_contact_method'] ?? 'phone';
        $this->preferred_language = $data['preferred_language'] ?? 'ar';
        $this->timezone = $data['timezone'] ?? 'Asia/Damascus';
        $this->marketing_opt_in = isset($data['marketing_opt_in']) ? (int)$data['marketing_opt_in'] : 0;
        $this->notification_preferences = isset($data['notification_preferences']) ? $data['notification_preferences'] : null;
        $this->status = $data['status'] ?? 'active';
        
        $this->last_login_at = $data['last_login_at'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->deleted_at = $data['deleted_at'] ?? null;

        // Calculate dynamic profile completeness score
        $fields = [$this->display_name, $this->email, $this->phone, $this->avatar, $this->city_id, $this->area_id, $this->default_address];
        $filled = 0;
        foreach ($fields as $field) {
            if ($field !== null && (is_string($field) ? trim($field) !== '' : true)) {
                $filled++;
            }
        }
        $this->completion_score = (int)round(($filled / count($fields)) * 100);
    }

    /**
     * Factory method to construct the DTO from raw data.
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * Serialize the DTO back to a plain array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'city_id' => $this->city_id,
            'area_id' => $this->area_id,
            'default_address' => $this->default_address,
            'preferred_contact_method' => $this->preferred_contact_method,
            'preferred_language' => $this->preferred_language,
            'timezone' => $this->timezone,
            'marketing_opt_in' => $this->marketing_opt_in,
            'notification_preferences' => $this->notification_preferences,
            'status' => $this->status,
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'completion_score' => $this->completion_score,
        ];
    }

    /**
     * Generate a cryptographically secure UUID version 4.
     */
    public static function generateUuid4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant RFC 4122
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
