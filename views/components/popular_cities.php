<?php
/**
 * popular_cities.php
 * Reusable component showing active cities with badge-link styles.
 */
if (!defined('IN_APP')) {
    exit;
}

$citiesRepo = new \App\Modules\Locations\CitiesRepository();
$allCities = $citiesRepo->search(['is_active' => 1, 'is_deleted' => 0], 'sort_order', 'ASC', 100);
?>
<?php if (!empty($allCities)): ?>
<div class="content-block popular-cities-block" style="margin-top: 40px; margin-bottom: 40px;">
    <h3 class="block-title" style="font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; font-family: var(--font-arabic); color: var(--text-primary);">المدن الأكثر نشاطاً</h3>
    <div class="cities-link-grid" style="display: flex; flex-wrap: wrap; gap: 10px;">
        <?php foreach ($allCities as $ct): ?>
            <a href="<?= base_url('cities/' . $ct['slug']) ?>" class="badge-link" style="background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-primary); padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; text-decoration: none;">
                📍 <?= e($ct['display_name_ar']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
