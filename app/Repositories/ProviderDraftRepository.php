<?php

namespace App\Repositories;

use App\Core\Database;

class ProviderDraftRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Helper to decode JSON fields in retrieved draft arrays.
     */
    private function formatRow(?array $row): ?array
    {
        if (!$row) return null;

        $row['social_links'] = !empty($row['social_links']) ? json_decode($row['social_links'], true) : [];
        $row['work_photos_json'] = !empty($row['work_photos_json']) ? json_decode($row['work_photos_json'], true) : [];
        $row['secondary_services_json'] = !empty($row['secondary_services_json']) ? json_decode($row['secondary_services_json'], true) : [];
        $row['coverage_areas_json'] = !empty($row['coverage_areas_json']) ? json_decode($row['coverage_areas_json'], true) : [];
        
        return $row;
    }

    /**
     * Find a draft by ID.
     */
    public function find(int $id): ?array
    {
        $row = $this->db->fetch("SELECT * FROM `provider_drafts` WHERE `id` = :id LIMIT 1", ['id' => $id]);
        return $this->formatRow($row);
    }

    /**
     * Get the latest active draft for a specific account.
     */
    public function getLatestDraftForAccount(int $accountId): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM `provider_drafts` WHERE `provider_account_id` = :provider_account_id ORDER BY `id` DESC LIMIT 1",
            ['provider_account_id' => $accountId]
        );
        return $this->formatRow($row);
    }

    /**
     * Create a new draft.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO `provider_drafts` 
                (`provider_id`, `provider_account_id`, `display_name_ar`, `slug`, `business_type`, `phone`, `whatsapp`, `email`, `city_id`, `primary_service_id`, `short_description_ar`, `description_ar`, `years_experience`, `starting_price`, `price_unit`, `website`, `working_hours`, `social_links`, `logo_path`, `work_photos_json`, `secondary_services_json`, `coverage_areas_json`, `meta_title_ar`, `meta_description_ar`, `status`, `admin_notes`) 
                VALUES 
                (:provider_id, :provider_account_id, :display_name_ar, :slug, :business_type, :phone, :whatsapp, :email, :city_id, :primary_service_id, :short_description_ar, :description_ar, :years_experience, :starting_price, :price_unit, :website, :working_hours, :social_links, :logo_path, :work_photos_json, :secondary_services_json, :coverage_areas_json, :meta_title_ar, :meta_description_ar, :status, :admin_notes)";
        
        $this->db->execute($sql, [
            'provider_id' => $data['provider_id'] ?? null,
            'provider_account_id' => $data['provider_account_id'],
            'display_name_ar' => $data['display_name_ar'] ?? null,
            'slug' => $data['slug'] ?? null,
            'business_type' => $data['business_type'] ?? 'individual',
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'email' => $data['email'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'primary_service_id' => $data['primary_service_id'] ?? null,
            'short_description_ar' => $data['short_description_ar'] ?? null,
            'description_ar' => $data['description_ar'] ?? null,
            'years_experience' => $data['years_experience'] ?? 0,
            'starting_price' => $data['starting_price'] ?? null,
            'price_unit' => $data['price_unit'] ?? 'hour',
            'website' => $data['website'] ?? null,
            'working_hours' => $data['working_hours'] ?? null,
            'social_links' => isset($data['social_links']) ? (is_array($data['social_links']) ? json_encode($data['social_links'], JSON_UNESCAPED_UNICODE) : $data['social_links']) : null,
            'logo_path' => $data['logo_path'] ?? null,
            'work_photos_json' => isset($data['work_photos_json']) ? (is_array($data['work_photos_json']) ? json_encode($data['work_photos_json'], JSON_UNESCAPED_UNICODE) : $data['work_photos_json']) : null,
            'secondary_services_json' => isset($data['secondary_services_json']) ? (is_array($data['secondary_services_json']) ? json_encode($data['secondary_services_json'], JSON_UNESCAPED_UNICODE) : $data['secondary_services_json']) : null,
            'coverage_areas_json' => isset($data['coverage_areas_json']) ? (is_array($data['coverage_areas_json']) ? json_encode($data['coverage_areas_json'], JSON_UNESCAPED_UNICODE) : $data['coverage_areas_json']) : null,
            'meta_title_ar' => $data['meta_title_ar'] ?? null,
            'meta_description_ar' => $data['meta_description_ar'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'admin_notes' => $data['admin_notes'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update a draft.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE `provider_drafts` SET 
                `provider_id` = :provider_id,
                `display_name_ar` = :display_name_ar,
                `slug` = :slug,
                `business_type` = :business_type,
                `phone` = :phone,
                `whatsapp` = :whatsapp,
                `email` = :email,
                `city_id` = :city_id,
                `primary_service_id` = :primary_service_id,
                `short_description_ar` = :short_description_ar,
                `description_ar` = :description_ar,
                `years_experience` = :years_experience,
                `starting_price` = :starting_price,
                `price_unit` = :price_unit,
                `website` = :website,
                `working_hours` = :working_hours,
                `social_links` = :social_links,
                `logo_path` = :logo_path,
                `work_photos_json` = :work_photos_json,
                `secondary_services_json` = :secondary_services_json,
                `coverage_areas_json` = :coverage_areas_json,
                `meta_title_ar` = :meta_title_ar,
                `meta_description_ar` = :meta_description_ar,
                `status` = :status,
                `admin_notes` = :admin_notes
                WHERE `id` = :id";

        return $this->db->execute($sql, [
            'id' => $id,
            'provider_id' => $data['provider_id'] ?? null,
            'display_name_ar' => $data['display_name_ar'] ?? null,
            'slug' => $data['slug'] ?? null,
            'business_type' => $data['business_type'] ?? 'individual',
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'email' => $data['email'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'primary_service_id' => $data['primary_service_id'] ?? null,
            'short_description_ar' => $data['short_description_ar'] ?? null,
            'description_ar' => $data['description_ar'] ?? null,
            'years_experience' => $data['years_experience'] ?? 0,
            'starting_price' => $data['starting_price'] ?? null,
            'price_unit' => $data['price_unit'] ?? 'hour',
            'website' => $data['website'] ?? null,
            'working_hours' => $data['working_hours'] ?? null,
            'social_links' => isset($data['social_links']) ? (is_array($data['social_links']) ? json_encode($data['social_links'], JSON_UNESCAPED_UNICODE) : $data['social_links']) : null,
            'logo_path' => $data['logo_path'] ?? null,
            'work_photos_json' => isset($data['work_photos_json']) ? (is_array($data['work_photos_json']) ? json_encode($data['work_photos_json'], JSON_UNESCAPED_UNICODE) : $data['work_photos_json']) : null,
            'secondary_services_json' => isset($data['secondary_services_json']) ? (is_array($data['secondary_services_json']) ? json_encode($data['secondary_services_json'], JSON_UNESCAPED_UNICODE) : $data['secondary_services_json']) : null,
            'coverage_areas_json' => isset($data['coverage_areas_json']) ? (is_array($data['coverage_areas_json']) ? json_encode($data['coverage_areas_json'], JSON_UNESCAPED_UNICODE) : $data['coverage_areas_json']) : null,
            'meta_title_ar' => $data['meta_title_ar'] ?? null,
            'meta_description_ar' => $data['meta_description_ar'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'admin_notes' => $data['admin_notes'] ?? null
        ]);
    }

    /**
     * Delete a draft.
     */
    public function delete(int $id): bool
    {
        return $this->db->execute("DELETE FROM `provider_drafts` WHERE `id` = :id", ['id' => $id]);
    }

    /**
     * Get pending drafts for Admin dashboard.
     */
    public function getPendingDrafts(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT d.*, a.email as account_email, a.display_name as account_name 
             FROM `provider_drafts` d
             INNER JOIN `provider_accounts` a ON d.provider_account_id = a.id
             WHERE d.status = 'pending_review' 
             ORDER BY d.updated_at DESC"
        );
        
        return array_map([$this, 'formatRow'], $rows);
    }
}
