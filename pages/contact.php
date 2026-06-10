<?php
/**
 * contact.php
 * Khadomeh Platform Contact Us Page
 */

if (!defined('IN_APP')) {
    exit;
}

$pageTitle = 'اتصل بنا - منصة خدومة';
$metaDesc = 'تواصل مع إدارة منصة خدومة لأي استفسارات أو دعم فني. نحن هنا لمساعدتك في الوصول إلى أفضل مزودي الخدمات.';

$viewPath = __FILE__;
if (isset($isLayoutCalled) && $isLayoutCalled) {
    // Content
} else {
    $isLayoutCalled = true;
    require APP_DIR . '/includes/layout.php';
    exit;
}
?>

<div class="container" style="max-width: 600px; padding: 40px 20px;">
    <h1 style="font-family: var(--font-arabic); font-size: 2.2rem; font-weight: 800; margin-bottom: 25px; border-bottom: 2px solid var(--border-color); padding-bottom: 15px; color: var(--text-primary); text-align: center;">
        اتصل بنا
    </h1>
    
    <div class="card" style="padding: 30px; margin-top: 20px;">
        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-secondary); text-align: center; margin-bottom: 30px;">
            إذا كان لديك أي سؤال، استفسار، أو اقتراح لتطوير المنصة، يسعدنا تواصلك معنا مباشرة عبر إحدى القنوات التالية:
        </p>
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 15px; background: var(--bg-secondary); padding: 15px; border-radius: 8px;">
                <span style="font-size: 2rem;">💬</span>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">واتساب الدعم الفني</h3>
                    <a href="https://wa.me/963999999999" target="_blank" style="font-size: 1rem; color: var(--success-color); font-weight: 600; text-decoration: none; font-family: var(--font-latin);">+963 999 999 999</a>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 15px; background: var(--bg-secondary); padding: 15px; border-radius: 8px;">
                <span style="font-size: 2rem;">📞</span>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">رقم الهاتف</h3>
                    <a href="tel:+963112233445" style="font-size: 1rem; color: var(--accent-primary); font-weight: 600; text-decoration: none; font-family: var(--font-latin);">+963 11 223 3445</a>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 15px; background: var(--bg-secondary); padding: 15px; border-radius: 8px;">
                <span style="font-size: 2rem;">✉️</span>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">البريد الإلكتروني</h3>
                    <a href="mailto:contact@khadomeh.local" style="font-size: 1rem; color: var(--accent-primary); font-weight: 600; text-decoration: none; font-family: var(--font-latin);">contact@khadomeh.local</a>
                </div>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 30px; text-align: center;">
        <a href="<?= base_url() ?>" class="btn btn-secondary">العودة للرئيسية</a>
    </div>
</div>
