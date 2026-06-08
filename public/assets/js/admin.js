/**
 * admin.js
 * Client-side behaviors for the Khadomeh Admin Panel.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Sidebar Toggler
    const sidebarToggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (sidebarToggleBtn && sidebar) {
        sidebarToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('open');
        });
        
        // Close sidebar if user clicks outside of it on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && e.target !== sidebarToggleBtn) {
                    sidebar.classList.remove('open');
                }
            }
        });
    }

    // 2. Flash Alert Dismissal
    document.querySelectorAll('.alert-close').forEach(btn => {
        btn.addEventListener('click', () => {
            const alert = btn.closest('.alert');
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.2s ease';
                setTimeout(() => alert.remove(), 200);
            }
        });
    });

    // 3. Lightweight Confirmation Dialog Component
    const confirmModal = document.getElementById('confirm-dialog');
    const confirmMessage = document.getElementById('confirm-dialog-message');
    const confirmCancelBtn = document.getElementById('confirm-dialog-cancel');
    const confirmOkBtn = document.getElementById('confirm-dialog-ok');
    let activeFormToSubmit = null;

    if (confirmModal && confirmMessage && confirmCancelBtn && confirmOkBtn) {
        // Intercept delete forms or buttons
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', (e) => {
                // If the element is a button/submit inside a form
                const form = element.closest('form');
                if (form) {
                    e.preventDefault();
                    activeFormToSubmit = form;
                    
                    const message = element.getAttribute('data-confirm') || 'هل أنت متأكد من تنفيذ هذا الإجراء؟';
                    confirmMessage.textContent = message;
                    
                    confirmModal.classList.add('open');
                }
            });
        });

        // Cancel click handler
        confirmCancelBtn.addEventListener('click', () => {
            confirmModal.classList.remove('open');
            activeFormToSubmit = null;
        });

        // OK click handler
        confirmOkBtn.addEventListener('click', () => {
            confirmModal.classList.remove('open');
            if (activeFormToSubmit) {
                activeFormToSubmit.submit();
            }
        });

        // Close on clicking outside modal card
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                confirmModal.classList.remove('open');
                activeFormToSubmit = null;
            }
        });
    }
});
