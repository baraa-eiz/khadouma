# منصة خدومة (Khadomeh Platform) - التأسيس (Stage 1)

منصة **خدومة** هي دليل ووسيط محلي سوري يربط أصحاب المنازل والشركات بـ **مزودي الخدمات المنزلية والصيانة** (عمال التنظيف، السباكين، الكهربائيين، الدهانين، وناقلي الأثاث) في دمشق وسوريا مباشرة وبدون عمولات.

تم بناء التأسيس (المرحلة الأولى) باستخدام لغة **PHP النظيفة (Native PHP)** وقاعدة بيانات **MySQL/MariaDB** بدون استخدام أي أطر عمل (Frameworks) لضمان الخفة والسرعة العالية وتوافقها مع شبكات الإنترنت الضعيفة.

---

## 🛠️ البنية البرمجية والتقنيات (Tech Stack)

* **اللغة**: Native PHP (متوافق مع PHP 8.1+)
* **قاعدة البيانات**: MySQL / MariaDB (مع استخدام PDO ومحرك InnoDB ومجموعة المحارف `utf8mb4_unicode_ci` للتوافق التام مع اللغة العربية)
* **التصميم والواجهات**: Vanilla CSS / JS مخصص بالكامل ومبني بلغة تصميم **Light Warm Trust** (ألوان دافئة، واجهات سريعة التجاوب، خط القاهرة العربي).
* **إدارة الجلسات**: جلسات PHP محمية ومؤمنة تماماً ضد هجمات CSRF و XSS.

---

## 📂 الهيكل التنظيمي للمشروع (Directory Structure)

```text
/khadomeh
├── admin/                     # لوحة التحكم الإدارية
│   ├── dashboard.php          # لوحة متابعة الإحصائيات العامة
│   ├── login.php              # صفحة تسجيل دخول المشرف
│   └── logout.php             # تسجيل الخروج وتصفية الجلسة
├── app/                       # النواة البرمجية للمشروع (App Core)
│   ├── Core/                  # فئات النواة الأساسية (قاعدة البيانات، الموجه، المدقق)
│   ├── Helpers/               # الدوال المساعدة (الأمان، الروابط، النصوص، الصور)
│   └── Repositories/          # مستودعات البيانات لعزل أوامر SQL عن الواجهات
├── config/                    # ملفات الإعدادات والاتصال
├── database/                  # جداول قاعدة البيانات والبيانات التجريبية
│   ├── migrations/            # سجل تتبع التعديلات والهيكلية
│   └── schema.sql             # الهيكل الأساسي للجداول الـ 19
├── docs/                      # التوثيقات الفنية التفصيلية للمشروع
├── includes/                  # الأجزاء المشتركة للواجهات والتهيئات (Header, Footer, Layout)
├── pages/                     # الواجهات العامة (الرئيسية، 404، الصفحات الثابتة)
├── public/                    # الملفات العامة المتاحة للزوار (CSS, JS, Images)
└── index.php                  # نقطة الدخول والتحكم الرئيسية (Front Controller)
```

---

## 📖 التوثيقات الفنية التفصيلية (Documentation Index)

للاطلاع على التوثيقات الكاملة للمشروع، يرجى تصفح المجلد [docs/](file:///c:/Users/pc/Desktop/service/docs):

1. **الهيكل الفني**: [docs/architecture.md](file:///c:/Users/pc/Desktop/service/docs/architecture.md)
2. **قاموس قاعدة البيانات**: [docs/database-schema.md](file:///c:/Users/pc/Desktop/service/docs/database-schema.md)
3. **خارطة الأكواد**: [docs/code-map.md](file:///c:/Users/pc/Desktop/service/docs/code-map.md)
4. **دليل الإعداد المحلي**: [docs/setup-xampp.md](file:///c:/Users/pc/Desktop/service/docs/setup-xampp.md)
5. **سياسة معالجة الصور**: [docs/image-policy.md](file:///c:/Users/pc/Desktop/service/docs/image-policy.md)
6. **خطة عمل لوحة التحكم**: [docs/admin-control-plan.md](file:///c:/Users/pc/Desktop/service/docs/admin-control-plan.md)
7. **خارطة الطريق المستقبلية**: [docs/future-stages.md](file:///c:/Users/pc/Desktop/service/docs/future-stages.md)

---

## 🚀 التشغيل والإعداد السريع

للبدء بتشغيل المشروع محلياً أو رفعه على الخادم:
1. قم بتهيئة قاعدة البيانات واستيراد هيكل الجداول `database/schema.sql` والبيانات التجريبية `database/seed.sql`.
2. قم بتعديل ملف الإعدادات `config/config.php` لإضافة بيانات قاعدة البيانات الخاصة بك (الملف يدعم استكشاف البيئة المحلية والتلقائية على الخادم).
3. بيانات الدخول الافتراضية للمشرف:
   * **البريد الإلكتروني**: `admin@khadomeh.local`
   * **كلمة المرور**: `Admin@123456`
