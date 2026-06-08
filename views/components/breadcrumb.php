<?php
/**
 * breadcrumb.php
 * Renders the breadcrumb trails.
 * Expects array $breadcrumbs of elements like ['label' => 'Title', 'url' => '/path']
 */
if (!empty($breadcrumbs)): ?>
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <?php if ($index > 0): ?>
                <span class="breadcrumb-separator" aria-hidden="true">/</span>
            <?php endif; ?>
            
            <?php if (isset($crumb['url']) && $index < count($breadcrumbs) - 1): ?>
                <a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
            <?php else: ?>
                <span aria-current="page"><?= e($crumb['label']) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
<?php endif; ?>
