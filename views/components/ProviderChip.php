<?php
/**
 * ProviderChip.php
 * Reusable provider chip component for linking to provider public profiles.
 */
$slug = $data['provider_slug'] ?? '';
$name = $data['provider_name'] ?? '';
$rating = $data['provider_rating'] ?? null;
$verified = $data['verified'] ?? false;
$class = $data['class'] ?? '';
$style = $data['style'] ?? '';

if (empty($slug)) {
    return;
}

$href = url('provider/' . $slug);
?>
<a href="<?= e($href) ?>" class="provider-chip <?= e($class) ?>" style="display: inline-flex; align-items: center; gap: 6px; background-color: #ffffff; color: var(--text-primary); border: 1px solid var(--border-color); padding: 5px 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: all 0.2s ease; <?= e($style) ?>" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
    👤 <?= e($name) ?>
    <?php if ($verified): ?>
        <span style="color: #3b82f6; font-size: 0.85rem;" title="حساب موثق">🛡️</span>
    <?php endif; ?>
    <?php if ($rating !== null): ?>
        <span style="color: #fbbf24; font-size: 0.75rem; font-weight: bold; margin-right: 2px;">★ <?= number_format((float)$rating, 1) ?></span>
    <?php endif; ?>
</a>
