<?php
/**
 * empty_state.php
 * Renders placeholders for screens with empty lists or no records.
 * Expects variables:
 *  - string $empty_title (optional)
 *  - string $empty_desc (optional)
 *  - string $empty_action_url (optional)
 *  - string $empty_action_label (optional)
 */
?>
<div class="empty-state">
    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.008 1.24l.885 1.77a2.25 2.25 0 002.007 1.24h1.98a2.25 2.25 0 002.007-1.24l.885-1.77a2.25 2.25 0 012.007-1.24h3.86m-18 0a2.25 2.25 0 00-2.25 2.25v1.5a2.25 2.25 0 002.25 2.25h18a2.25 2.25 0 002.25-2.25v-1.5a2.25 2.25 0 00-2.25-2.25m-18 0A2.25 2.25 0 012.25 11.25V5.25A2.25 2.25 0 014.5 3h15a2.25 2.25 0 012.25 2.25v6.002a2.25 2.25 0 01-2.25 2.248M12 3v9M9 6l3 3 3-3" />
    </svg>
    <h3 class="empty-title"><?= e($empty_title ?? 'لا توجد بيانات لعرضها') ?></h3>
    <p class="empty-desc"><?= e($empty_desc ?? 'لم نجد أي سجلات هنا في الوقت الحالي. يمكنك البدء بإضافة سجل جديد.') ?></p>
    <?php if (!empty($empty_action_url) && !empty($empty_action_label)): ?>
        <a href="<?= e($empty_action_url) ?>" class="btn btn-primary" style="margin-top: 8px;">
            <?= e($empty_action_label) ?>
        </a>
    <?php endif; ?>
</div>
