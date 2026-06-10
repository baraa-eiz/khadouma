<?php
/**
 * related_providers.php
 * Reusable component showing alternative providers in the same service and city.
 * Expects variables:
 *  - array $provider
 */
if (!defined('IN_APP')) {
    exit;
}

$providerRepo = new \App\Repositories\ProviderRepository();
$relatedFilters = [
    'service' => $provider['service_slug'],
    'city' => $provider['city_slug'],
    'is_active' => 1,
    'status' => 'approved'
];
$allRelated = $providerRepo->search($relatedFilters, 'sort_weight', 'DESC', 4);
$related = [];
foreach ($allRelated as $rp) {
    if ((int)$rp['id'] === (int)$provider['id']) continue;
    $rp['profile_image'] = $providerRepo->getProviderProfileImage($rp['id']);
    $related[] = $rp;
    if (count($related) >= 3) break;
}
if (!empty($related)):
?>
<div class="content-block related-providers-block" style="margin-top: 50px; border-top: 1px solid var(--border-color); padding-top: 40px;">
    <h3 class="block-title" style="font-size: 1.4rem; font-weight: 800; margin-bottom: 25px; font-family: var(--font-arabic); color: var(--text-primary);">مزودو خدمة آخرون في <?= e($provider['city_name']) ?></h3>
    <div class="results-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php foreach ($related as $rp): ?>
            <article class="card provider-card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <div>
                    <div class="provider-header" style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div class="provider-img-wrapper" style="width: 50px; height: 50px; border-radius: 50%; overflow: hidden; border: 2px solid var(--border-color); flex-shrink: 0;">
                            <img src="<?= get_provider_image($rp['profile_image'] ?? null, 50, 50, '👨‍🔧') ?>" alt="صورة <?= e($rp['display_name_ar']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="provider-info-header" style="display: flex; flex-direction: column; justify-content: center;">
                            <h4 class="provider-name" style="font-size: 1rem; font-weight: 700; margin: 0;">
                                <a href="<?= base_url('provider/' . $rp['slug']) ?>" style="color: var(--text-primary); text-decoration: none;"><?= e($rp['display_name_ar']) ?></a>
                            </h4>
                            <span class="provider-service-tag" style="font-size: 0.75rem; font-weight: 700; color: var(--accent-primary); background-color: #fdf2ee; padding: 1px 6px; border-radius: 4px; align-self: flex-start; margin-top: 4px;">
                                <?= e($rp['service_name']) ?>
                            </span>
                        </div>
                    </div>
                    <p class="provider-desc-text" style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; height: 2.6rem;">
                        <?= e($rp['short_description_ar']) ?>
                    </p>
                </div>
                <div>
                    <div class="provider-meta" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 10px; margin-bottom: 15px; font-size: 0.8rem; color: var(--text-secondary);">
                        <span class="provider-location">📍 <?= e($rp['city_name']) ?></span>
                    </div>
                    <div class="provider-actions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <button class="btn btn-primary contact-btn" data-provider-id="<?= (int)$rp['id'] ?>" data-method="phone_call" style="font-size: 0.8rem; padding: 8px 10px;">
                            📞 اتصل الآن
                        </button>
                        <button class="btn btn-whatsapp-outline contact-btn" data-provider-id="<?= (int)$rp['id'] ?>" data-method="whatsapp_message" style="font-size: 0.8rem; padding: 8px 10px;">
                            💬 واتساب
                        </button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
