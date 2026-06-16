<?php
/**
 * nearby_areas.php
 * Reusable component showing area coverage links for a selected city.
 * Expects variables:
 *  - array $currentCity
 *  - array $currentService (optional)
 */
if (!defined('IN_APP')) {
    exit;
}

if (isset($currentCity)):
    $areasRepo = new \App\Modules\Locations\AreasRepository();
    $cityAreas = $areasRepo->search([
        'city_id' => $currentCity['id'],
        'is_active' => 1,
        'is_deleted' => 0
    ], 'sort_order', 'ASC', 100);
    if (!empty($cityAreas)):
?>
<div class="content-block nearby-areas-block" style="margin-top: 40px; margin-bottom: 40px;">
    <h3 class="block-title" style="font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; font-family: var(--font-arabic); color: var(--text-primary);">المناطق والتغطية في <?= e($currentCity['display_name_ar']) ?></h3>
    <div class="areas-link-grid" style="display: flex; flex-wrap: wrap; gap: 10px;">
        <?php foreach ($cityAreas as $ar): ?>
            <?php
            $viewData = [
                'area_name' => $ar['display_name_ar'],
                'city_slug' => $currentCity['slug'],
                'area_slug' => $ar['slug'],
                'style' => 'padding: 8px 16px; font-size: 0.9rem;'
            ];
            echo App\Core\View::render('components/AreaChip', $viewData);
            ?>
        <?php endforeach; ?>
    </div>
</div>
<?php 
    endif;
endif; 
?>
