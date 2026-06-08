<?php
/**
 * pagination.php
 * Renders pagination bars for list displays.
 * Expects variables:
 *  - int $current_page
 *  - int $total_pages
 *  - int $total_records
 *  - int $per_page
 *  - string $base_url
 */
if (!isset($total_pages) || $total_pages <= 1) {
    return;
}

$currentPage = $current_page ?? 1;
$baseUrl = $base_url ?? '';
$separator = (strpos($baseUrl, '?') === false) ? '?' : '&';

$startRecord = (($currentPage - 1) * ($per_page ?? 15)) + 1;
$endRecord = min($currentPage * ($per_page ?? 15), $total_records ?? 0);
?>
<div class="pagination-container">
    <div class="pagination-info">
        عرض السجلات <strong><?= $startRecord ?></strong> - <strong><?= $endRecord ?></strong> من أصل <strong><?= $total_records ?? 0 ?></strong> سجل
    </div>
    
    <div class="pagination-buttons">
        <?php if ($currentPage > 1): ?>
            <a href="<?= $baseUrl . $separator ?>page=<?= $currentPage - 1 ?>" class="btn btn-secondary" style="padding: 0.35rem 0.75rem;">&larr; السابق</a>
        <?php else: ?>
            <button class="btn btn-secondary" style="padding: 0.35rem 0.75rem;" disabled>&larr; السابق</button>
        <?php endif; ?>

        <?php
        $startRange = max(1, $currentPage - 2);
        $endRange = min($total_pages, $currentPage + 2);
        
        for ($i = $startRange; $i <= $endRange; $i++):
            $isActive = ($i === $currentPage);
        ?>
            <a href="<?= $baseUrl . $separator ?>page=<?= $i ?>" class="btn <?= $isActive ? 'btn-primary' : 'btn-secondary' ?>" style="padding: 0.35rem 0.75rem; min-width: 38px;">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($currentPage < $total_pages): ?>
            <a href="<?= $baseUrl . $separator ?>page=<?= $currentPage + 1 ?>" class="btn btn-secondary" style="padding: 0.35rem 0.75rem;">التالي &rarr;</a>
        <?php else: ?>
            <button class="btn btn-secondary" style="padding: 0.35rem 0.75rem;" disabled>التالي &rarr;</button>
        <?php endif; ?>
    </div>
</div>
