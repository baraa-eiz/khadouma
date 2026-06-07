<?php
/**
 * SettingsRepository.php
 * Khadomeh Settings Data Repository
 */

namespace App\Repositories;

use App\Core\Database;

class SettingsRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get setting value by key. Returns default if not found.
     */
    public function get($key, $default = null) {
        $row = $this->db->fetch(
            "SELECT `setting_value` FROM `settings` WHERE `setting_key` = ? LIMIT 1",
            [$key]
        );
        return $row ? $row['setting_value'] : $default;
    }

    /**
     * Update setting value by key.
     */
    public function set($key, $value) {
        return $this->db->query(
            "UPDATE `settings` SET `setting_value` = ? WHERE `setting_key` = ?",
            [$value, $key]
        );
    }

    /**
     * Get all public settings.
     */
    public function getPublicSettings() {
        return $this->db->fetchAll(
            "SELECT `setting_key`, `setting_value` FROM `settings` WHERE `is_public` = 1"
        );
    }
}
