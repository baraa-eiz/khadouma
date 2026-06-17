<?php
/**
 * AreaChip.php
 * Reusable regional chip component for linking to area searches.
 */
$name = $data['area_name'] ?? '';
$citySlug = $data['city_slug'] ?? '';
$areaSlug = $data['area_slug'] ?? '';
$class = $data['class'] ?? '';
$style = $data['style'] ?? '';

if (empty($name)) {
    return;
}

$queryParams = [];
if (!empty($citySlug)) {
    $queryParams['city'] = $citySlug;
}
if (!empty($areaSlug)) {
    $queryParams['area'] = $areaSlug;
} else {
    $queryParams['q'] = $name;
}

$href = url('search') . '?' . http_build_query($queryParams);
$isSelf = is_self_link($href);
?>
<?php if ($isSelf): ?>
<span class="area-chip <?= e($class) ?>" style="display: inline-flex; align-items: center; background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; padding: 4px 10px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; cursor: default; <?= e($style) ?>">
    📍 <?= e($name) ?>
</span>
<?php else: ?>
<a href="<?= e($href) ?>" class="area-chip <?= e($class) ?>" style="display: inline-flex; align-items: center; background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; padding: 4px 10px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; <?= e($style) ?>" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='#f3f4f6'">
    📍 <?= e($name) ?>
</a>
<?php endif; ?>
