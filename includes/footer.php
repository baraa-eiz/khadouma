<?php
/**
 * footer.php
 * Khadomeh Global Layout Footer
 */
if (!defined('IN_APP')) {
    exit;
}
?>
    </main>

    <!-- Global Footer -->
    <footer class="main-footer">
        <div class="container footer-container">
            <div class="footer-grid">
                <!-- Branding Info -->
                <div class="footer-col branding-col">
                    <h3 class="footer-title">منصة خدومة</h3>
                    <p class="footer-desc">دليل سوري محلي يربطك بأفضل الحرفيين ومزودي الخدمات المنزلية في منطقتك مباشرة دون وسيط أو عمولة.</p>
                </div>

                <!-- Navigation Links -->
                <div class="footer-col links-col">
                    <h4 class="footer-subtitle">روابط سريعة</h4>
                    <ul class="footer-links">
                        <li><a href="<?= base_url() ?>">الرئيسية</a></li>
                        <li><a href="<?= base_url('about-us') ?>">من نحن</a></li>
                        <li><a href="<?= base_url('terms') ?>">الشروط والأحكام</a></li>
                        <li><a href="<?= base_url('privacy') ?>">سياسة الخصوصية</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="footer-col contact-col">
                    <h4 class="footer-subtitle">تواصل معنا</h4>
                    <p class="contact-item">📧 contact@khadomeh.local</p>
                    <p class="contact-item">📞 +963 11 223 3445</p>
                    <p class="contact-item">💬 واتساب: +963 999 999 999</p>
                </div>
            </div>

            <!-- Copyright -->
            <div class="footer-bottom">
                <p class="copyright-text">جميع الحقوق محفوظة &copy; <?= date('Y') ?> منصة خدومة. صنع في سوريا 🇸🇾</p>
            </div>
        </div>
    </footer>

    <!-- Core Javascript -->
    <script src="<?= asset_url('js/main.js') ?>"></script>
</body>
</html>
