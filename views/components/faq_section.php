<?php
/**
 * faq_section.php
 * Reusable component showing FAQ details accordions.
 * Expects variables:
 *  - array $faqEntries
 */
if (!defined('IN_APP')) {
    exit;
}

if (!empty($faqEntries)):
?>
<div class="content-block faq-section" style="margin-top: 50px; border-top: 1px solid var(--border-color); padding-top: 40px;">
    <h3 class="block-title" style="font-size: 1.4rem; font-weight: 800; margin-bottom: 25px; font-family: var(--font-arabic); color: var(--text-primary);">الأسئلة الشائعة</h3>
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
