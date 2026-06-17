<?php
/**
 * ServiceChip.php
 * Reusable SEO chip component for linking to Service or City-Service landing pages.
 */
$slug = $data['service_slug'] ?? '';
$name = $data['service_name'] ?? '';
$citySlug = $data['city_slug'] ?? '';
$class = $data['class'] ?? '';
$style = $data['style'] ?? '';

if (empty($slug)) {
    return;
}

$href = !empty($citySlug) ? url($citySlug . '/' . $slug) : url('services/' . $slug);
$isSelf = is_self_link($href);
?>
<?php if ($isSelf): ?>
<span class="service-chip <?= e($class) ?>" style="display: inline-flex; align-items: center; background-color: #eff6ff; color: #1e40af; border: 1px solid #dbeafe; padding: 4px 12px; border-radius: 9999px; font-size: 0.85rem; font-weight: 700; cursor: default; <?= e($style) ?>">
    🛠️ <?= e($name) ?>
</span>
<?php else: ?>
<a href="<?= e($href) ?>" class="service-chip <?= e($class) ?>" style="display: inline-flex; align-items: center; background-color: #eff6ff; color: #1e40af; border: 1px solid #dbeafe; padding: 4px 12px; border-radius: 9999px; font-size: 0.85rem; font-weight: 700; text-decoration: none; transition: all 0.2s ease; <?= e($style) ?>" onmouseover="this.style.backgroundColor='#dbeafe'" onmouseout="this.style.backgroundColor='#eff6ff'">
    🛠️ <?= e($name) ?>
</a>
<?php endif; ?>
