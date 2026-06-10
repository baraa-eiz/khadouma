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
            $link = $citySlug ? base_url($citySlug . '/' . $srv['slug']) : base_url('services/' . $srv['slug']);
        ?>
            <a href="<?= $link ?>" class="badge-link" style="background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-primary); padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; text-decoration: none;">
                <?= e($srv['short_name_ar']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
