/**
 * main.js
 * Khadomeh Global Javascript
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('خدومة: تم تحميل مكتبة الواجهة بنجاح.');

    // Mobile Hamburger Menu Toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navWrapper = document.querySelector('.nav-wrapper');

    if (menuToggle && navWrapper) {
        menuToggle.addEventListener('click', function() {
            const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', !isExpanded);
            navWrapper.classList.toggle('active');
        });

        // Close menu when clicking outside header area
        document.addEventListener('click', function(e) {
            if (!menuToggle.contains(e.target) && !navWrapper.contains(e.target)) {
                menuToggle.setAttribute('aria-expanded', 'false');
                navWrapper.classList.remove('active');
            }
        });
    }
});
