<?php
/**
 * AreaData.php
 * Khadomeh Area Data Transfer Object (DTO)
 */

namespace App\Modules\Locations;

class AreaData
{
    public readonly string $public_id;
    public readonly int $city_id;
    public readonly string $key;
    public readonly string $slug;
    public readonly string $display_name_ar;
    public readonly ?string $display_name_en;
    public readonly int $sort_order;
    public readonly ?string $meta_title_ar;
    public readonly ?string $meta_description_ar;
    public readonly bool $is_active;

    public function __construct(array $data)
    {
        $this->public_id = $data['public_id'] ?? self::generateUuid4();
        $this->city_id = (int)($data['city_id'] ?? 0);
        $this->key = isset($data['key']) ? trim((string)$data['key']) : '';
        $this->slug = isset($data['slug']) ? trim((string)$data['slug']) : '';
        $this->display_name_ar = isset($data['display_name_ar']) ? trim((string)$data['display_name_ar']) : '';
        $this->display_name_en = isset($data['display_name_en']) && trim($data['display_name_en']) !== '' ? trim((string)$data['display_name_en']) : null;
        $this->sort_order = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;
        $this->meta_title_ar = isset($data['meta_title_ar']) && trim($data['meta_title_ar']) !== '' ? trim((string)$data['meta_title_ar']) : null;
        $this->meta_description_ar = isset($data['meta_description_ar']) && trim($data['meta_description_ar']) !== '' ? trim((string)$data['meta_description_ar']) : null;
        $this->is_active = isset($data['is_active']) ? (bool)$data['is_active'] : true;
    }

    /**
     * Factory method to construct the DTO from raw data.
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * Serialize the DTO to a plain array.
     */
    public function toArray(): array
    {
        return [
            'public_id' => $this->public_id,
            'city_id' => $this->city_id,
            'key' => $this->key,
            'slug' => $this->slug,
            'display_name_ar' => $this->display_name_ar,
            'display_name_en' => $this->display_name_en,
            'sort_order' => $this->sort_order,
            'meta_title_ar' => $this->meta_title_ar,
            'meta_description_ar' => $this->meta_description_ar,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Generate a cryptographically secure UUID version 4.
     */
    private static function generateUuid4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant RFC 4122
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
