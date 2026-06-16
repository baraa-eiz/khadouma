<?php
/**
 * related_services.php
 * Reusable component showing services related to the current context.
 * Expects variables:
 *  - array $currentService (optional)
 *  - array $currentCity (optional)
 */
if (!defined('IN_APP')) {
    exit;
}

$serviceRepo = new \App\Repositories\ServiceRepository();
$services = $serviceRepo->getAllActive();
$citySlug = isset($currentCity) ? $currentCity['slug'] : null;
?>
<?php if (!empty($services)): ?>
<div class="content-block related-services-block" style="margin-top: 40px; margin-bottom: 40px;">
    <h3 class="block-title" style="font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; font-family: var(--font-arabic); color: var(--text-primary);">خدمات أخرى قد تهمك</h3>
    <div class="services-link-grid" style="display: flex; flex-wrap: wrap; gap: 10px;">
        <?php foreach ($services as $srv): 
            if (isset($currentService) && $srv['slug'] === $currentService['slug']) continue;
            echo App\Core\View::render('components/ServiceChip', [
                'service_slug' => $srv['slug'],
                'service_name' => $srv['short_name_ar'],
                'city_slug' => $citySlug,
                'style' => 'padding: 8px 16px; font-size: 0.9rem;'
            ]);
        endforeach; ?>
    </div>
</div>
<?php endif; ?>
