# 03. Request Lifecycle (دورة حياة الطلب)

يوثق هذا المستند رحلة الطلب البرمجي عبر منصة خدومة منذ لحظة نقر المستخدم على الرابط وحتى استلام استجابة الـ HTML أو البيانات.

---

## 1. دورة حياة طلبات القراءة (GET Request Flow)

تسير طلبات عرض الصفحات وفق التدفق الخطي التالي لضمان أمان البيانات وجودة الأداء:

```mermaid
sequenceDiagram
    autonumber
    actor User as متصفح المستخدم
    participant Index as public/index.php
    participant Router as App\Core\Router
    participant Middleware as App\Middleware
    participant Controller as App\Controllers
    participant Service as App\Services
    participant Repo as App\Repositories
    participant DB as Database (MySQL)
    participant View as View Renderer

    User->>Index: طلب الصفحة (e.g., GET /damascus/plumbing)
    Index->>Router: تمرير الطلب والـ URI
    Router->>Middleware: تشغيل مصافي الطلب (أمان، كاش، جلسات)
    Middleware->>Controller: توجيه الطلب إلى الأكشن المناسب
    Controller->>Service: طلب منطق العمل والبيانات المصادق عليها
    Service->>Repo: استدعاء البيانات المطلوبة
    Repo->>DB: استعلام SQL آمن (Prepared Statement)
    DB-->>Repo: نتيجة الاستعلام
    Repo-->>Service: تحويل النتيجة لكائنات/مصفوفات
    Service-->>Controller: تسليم البيانات المعالجة والمصفاة
    Controller->>View: تمرير البيانات وتحديد قالب العرض
    View-->>User: إرجاع كود HTML نقي ورسائل العرض للزائر
```

---

## 2. دورة حياة طلبات الإرسال والكتابة (POST/Form Submission Flow)

عند محاولة الحرفي أو الزائر إرسال بيانات (مثل التسجيل، إضافة مراجعة، أو الدخول للوحة التحكم)، يتبع النظام آلية صارمة تضمن الأمان وتلافي تكرار الإرسال:

```mermaid
flowchart TD
    A[إرسال النموذج POST] --> B{التحقق من رمز CSRF}
    B -- فشل الرمز --> C[إرجاع خطأ 403 / استجابة غير مصرح بها]
    B -- نجاح الرمز --> D[إدخال البيانات في مدقق الحقول Validator]
    D -- وجود أخطاء في المدخلات --> E[حفظ الأخطاء بالجلسة Flash Messages + إعادة التوجيه Redirect للخلف]
    D -- المدخلات صالحة بنجاح --> F[تمرير البيانات المعقمة إلى Service Layer]
    F --> G{عمليات ممتدة / متعددة الجداول؟}
    G -- نعم --> H[بدء معاملة قاعدة البيانات Transaction]
    G -- لا --> I[تنفيذ الاستعلام عبر المستودع Repository]
    H --> J[تنفيذ الاستعلامات المتعددة]
    J -- نجاح الكل --> K[تثبيت المعاملة Commit]
    J -- فشل أحدها --> L[التراجع الكامل Rollback وإرجاع خطأ للمستخدم]
    K --> M[حفظ رسالة النجاح في الجلسة Flash Session]
    I --> M
    M --> N[إعادة توجيه المتصفح Redirect URL لمنع إعادة الإرسال F5]
    N --> O[المستقبل يستقبل استجابة GET نظيفة ويعرض رسالة النجاح للمستخدم]
```

---

## 3. تفاصيل حماية إعادة الإرسال (POST-Redirect-GET Pattern)

لمنع إرسال البيانات المكررة إلى قاعدة البيانات عند تحديث المستخدم للمتصفح (F5)، يمنع النظام تماماً إرجاع واجهة HTML مباشرة من طلبات POST. يجب على أي متحكم يستقبل طلب POST أن ينتهي بـ:
1. معالجة البيانات بنجاح أو فشل.
2. تخزين الرسائل القصيرة (Success/Error Messages) في الجلسة `$_SESSION['flash']`.
3. إرسال رأس إعادة التوجيه `Location: /target-page` وإنهاء السكربت فوراً عبر `exit()`.
4. يستقبل المتصفح طلب GET ويعرض الرسالة ثم يحذفها من الجلسة تلقائياً.
