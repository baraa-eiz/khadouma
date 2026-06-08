<?php
/**
 * confirm_dialog.php
 * Reusable modal popup for validating high-risk actions.
 */
?>
<div id="confirm-dialog" class="modal-overlay" aria-hidden="true" role="dialog">
    <div class="modal-card">
        <div class="card-header" style="border-bottom: none; padding-bottom: 0;">
            <div class="card-title" style="display: flex; align-items: center; gap: 8px; color: var(--danger);">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>تأكيد الإجراء</span>
            </div>
        </div>
        <div class="card-body">
            <p id="confirm-dialog-message" style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                هل أنت متأكد من تنفيذ هذا الإجراء؟ قد لا تتمكن من التراجع عنه لاحقاً.
            </p>
        </div>
        <div class="card-footer" style="background-color: #fafafa; border-top: 1px solid var(--border-color);">
            <button type="button" id="confirm-dialog-cancel" class="btn btn-secondary">إلغاء</button>
            <button type="button" id="confirm-dialog-ok" class="btn btn-danger">تأكيد الإجراء</button>
        </div>
    </div>
</div>
