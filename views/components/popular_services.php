<?php
/**
 * popular_services.php
 * Reusable component showing popular/active services with badge-link styles.
 */
if (!defined('IN_APP')) {
    exit;
}

$serviceRepo = new \App\Repositories\ServiceRepository();
$allServices = $serviceRepo->getAllActive();
?>
<?php if (!empty($allServices)): ?>
<div class="content-block popular-services-block" style="margin-top: 40px; margin-bottom: 40px;">
    <h3 class="block-title" style="font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; font-family: var(--font-arabic); color: var(--text-primary);">الخدمات الشائعة</h3>
    <div class="services-link-grid" style="display: flex; flex-wrap: wrap; gap: 10px;">
        <?php foreach ($allServices as $srv): ?>
            <?php
            echo App\Core\View::render('components/ServiceChip', [
                'service_slug' => $srv['slug'],
                'service_name' => $srv['short_name_ar'],
                'style' => 'padding: 8px 16px; font-size: 0.9rem;'
            ]);
            ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
