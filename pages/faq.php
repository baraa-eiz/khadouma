<?php
/**
 * faq.php
 * Khadomeh Platform General FAQ Page
 */

if (!defined('IN_APP')) {
    exit;
}

$db = \App\Core\Database::getInstance();
$faqEntries = $db->fetchAll("SELECT * FROM `faq_entries` WHERE `service_id` IS NULL AND `city_id` IS NULL AND `is_active` = 1 AND `deleted_at` IS NULL ORDER BY `sort_order` ASC");

$pageTitle = 'الأسئلة الشائعة - منصة خدومة';
$metaDesc = 'الأسئلة الأكثر شيوعاً حول كيفية استخدام منصة خدومة والتواصل مع الحرفيين ومزودي الخدمات في سوريا.';

$viewPath = __FILE__;
if (isset($isLayoutCalled) && $isLayoutCalled) {
    // Content
} else {
    $isLayoutCalled = true;
    require APP_DIR . '/includes/layout.php';
    exit;
}
?>

<div class="container" style="max-width: 800px; padding: 40px 20px;">
    <h1 style="font-family: var(--font-arabic); font-size: 2.2rem; font-weight: 800; margin-bottom: 25px; border-bottom: 2px solid var(--border-color); padding-bottom: 15px; color: var(--text-primary);">
        الأسئلة الشائعة
    </h1>
    
    <?php if (empty($faqEntries)): ?>
        <p class="text-center" style="color: var(--text-secondary); padding: 40px 0;">لا يوجد أسئلة شائعة حالياً.</p>
    <?php else: ?>
        <div class="faq-list" style="display: flex; flex-direction: column; gap: 15px;">
            <?php foreach ($faqEntries as $faq): ?>
                <details class="faq-item" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; cursor: pointer;">
                    <summary style="font-weight: 700; font-size: 1.05rem; color: var(--text-primary); list-style: none; display: flex; justify-content: space-between; align-items: center; outline: none;">
                        <span><?= e($faq['question_ar']) ?></span>
                        <span class="faq-icon" style="font-size: 0.8rem;">➕</span>
                    </summary>
                    <div class="faq-answer" style="margin-top: 10px; font-size: 0.95rem; line-height: 1.7; color: var(--text-secondary); border-top: 1px solid var(--border-color); padding-top: 10px;">
                        <?= nl2br(e($faq['answer_ar'])) ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.faq-item').forEach(item => {
                item.addEventListener('toggle', () => {
                    const icon = item.querySelector('.faq-icon');
                    if (item.open) {
                        icon.textContent = '➖';
                    } else {
                        icon.textContent = '➕';
                    }
                });
            });
        });
        </script>
    <?php endif; ?>
    
    <div style="margin-top: 40px; text-align: center;">
        <a href="<?= base_url() ?>" class="btn btn-secondary">العودة للرئيسية</a>
    </div>
</div>
