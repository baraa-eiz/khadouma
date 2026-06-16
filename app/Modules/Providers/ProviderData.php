<?php
/**
 * ProviderData.php
 * Khadomeh Provider Data Transfer Object (DTO)
 */

namespace App\Modules\Providers;

class ProviderData
{
    public readonly ?int $id;
    public readonly string $slug;
    public readonly string $display_name_ar;
    public readonly string $business_type;
    public readonly string $phone;
    public readonly ?string $whatsapp;
    public readonly int $city_id;
    public readonly int $primary_service_id;
    public readonly ?string $short_description_ar;
    public readonly ?string $description_ar;
    public readonly int $years_experience;
    public readonly ?float $starting_price;
    public readonly string $price_unit;
    public readonly bool $verified;
    public readonly bool $is_active;
    public readonly int $sort_weight;
    public readonly string $status;
    
    // Mappings and collections
    public readonly array $areas;            // Array of area IDs
    public readonly array $services;         // Array of secondary service IDs
    public readonly ?string $logo;           // Profile image path
    public readonly array $work_photos;      // Array of gallery image paths
    
    // SEO fields
    public readonly ?string $meta_title_ar;
    public readonly ?string $meta_description_ar;

    // Additional fields
    public readonly ?string $website;
    public readonly ?string $working_hours;
    public readonly array $social_links;

    public function __construct(array $data)
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->slug = isset($data['slug']) ? trim((string)$data['slug']) : '';
        $this->display_name_ar = isset($data['display_name_ar']) ? trim((string)$data['display_name_ar']) : '';
        $this->business_type = isset($data['business_type']) ? trim((string)$data['business_type']) : 'individual';
        $this->phone = isset($data['phone']) ? trim((string)$data['phone']) : '';
        $this->whatsapp = isset($data['whatsapp']) && trim($data['whatsapp']) !== '' ? trim((string)$data['whatsapp']) : null;
        $this->city_id = isset($data['city_id']) ? (int)$data['city_id'] : 0;
        $this->primary_service_id = isset($data['primary_service_id']) ? (int)$data['primary_service_id'] : 0;
        $this->short_description_ar = isset($data['short_description_ar']) && trim($data['short_description_ar']) !== '' ? trim((string)$data['short_description_ar']) : null;
        $this->description_ar = isset($data['description_ar']) && trim($data['description_ar']) !== '' ? trim((string)$data['description_ar']) : null;
        $this->years_experience = isset($data['years_experience']) ? (int)$data['years_experience'] : 0;
        $this->starting_price = isset($data['starting_price']) && $data['starting_price'] !== '' ? (float)$data['starting_price'] : null;
        $this->price_unit = isset($data['price_unit']) && trim($data['price_unit']) !== '' ? trim((string)$data['price_unit']) : 'hour';
        $this->verified = isset($data['verified']) ? (bool)$data['verified'] : false;
        $this->is_active = isset($data['is_active']) ? (bool)$data['is_active'] : true;
        $this->sort_weight = isset($data['sort_weight']) ? (int)$data['sort_weight'] : 0;
        $this->status = isset($data['status']) ? trim((string)$data['status']) : 'approved';
        
        $this->areas = isset($data['areas']) && is_array($data['areas']) ? array_map('intval', $data['areas']) : [];
        $this->services = isset($data['services']) && is_array($data['services']) ? array_map('intval', $data['services']) : [];
        $this->logo = isset($data['logo']) && trim($data['logo']) !== '' ? trim((string)$data['logo']) : null;
        $this->work_photos = isset($data['work_photos']) && is_array($data['work_photos']) ? array_map('strval', $data['work_photos']) : [];
        
        $this->meta_title_ar = isset($data['meta_title_ar']) && trim($data['meta_title_ar']) !== '' ? trim((string)$data['meta_title_ar']) : null;
        $this->meta_description_ar = isset($data['meta_description_ar']) && trim($data['meta_description_ar']) !== '' ? trim((string)$data['meta_description_ar']) : null;

        $this->website = isset($data['website']) && trim($data['website']) !== '' ? trim((string)$data['website']) : null;
        $this->working_hours = isset($data['working_hours']) && trim($data['working_hours']) !== '' ? trim((string)$data['working_hours']) : null;
        
        if (isset($data['social_links'])) {
            if (is_array($data['social_links'])) {
                $this->social_links = $data['social_links'];
            } elseif (is_string($data['social_links'])) {
                $decoded = json_decode($data['social_links'], true);
                $this->social_links = is_array($decoded) ? $decoded : [];
            } else {
                $this->social_links = [];
            }
        } else {
            $this->social_links = [];
        }
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'display_name_ar' => $this->display_name_ar,
            'business_type' => $this->business_type,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'city_id' => $this->city_id,
            'primary_service_id' => $this->primary_service_id,
            'short_description_ar' => $this->short_description_ar,
            'description_ar' => $this->description_ar,
            'years_experience' => $this->years_experience,
            'starting_price' => $this->starting_price,
            'price_unit' => $this->price_unit,
            'verified' => $this->verified ? 1 : 0,
            'is_active' => $this->is_active ? 1 : 0,
            'sort_weight' => $this->sort_weight,
            'status' => $this->status,
            'areas' => $this->areas,
            'services' => $this->services,
            'logo' => $this->logo,
            'work_photos' => $this->work_photos,
            'meta_title_ar' => $this->meta_title_ar,
            'meta_description_ar' => $this->meta_description_ar,
            'website' => $this->website,
            'working_hours' => $this->working_hours,
            'social_links' => $this->social_links,
        ];
    }
}
