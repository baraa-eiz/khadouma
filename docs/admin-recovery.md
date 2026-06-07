# ملحق 2: دليل استعادة حساب الإشراف (Admin Access Recovery)

يوضح هذا المستند سكريبت الطوارئ وطرق استعادة كلمة مرور حساب المشرف الرئيسي في حال فقدانها أو الحاجة لإعادة التعيين السريع مباشرة من خلال سطر الأوامر (CLI) للـ VPS.

---

## 1. سكريبت استعادة وتعديل كلمة المرور

تم كتابة سكريبت PHP مستقل يمكن تشغيله مباشرة عبر سطر الأوامر. يقوم هذا السكريبت بطلب البريد الإلكتروني وكلمة المرور الجديدة، ويقوم بتشفير كلمة المرور وتحديثها في قاعدة البيانات بأمان.

أنشئ ملفاً باسم `reset_admin.php` في مسار جذر المشروع محلياً أو على الخادم:

```php
<?php
/**
 * reset_admin.php
 * سكريبت طوارئ لتحديث كلمة مرور المشرف عبر الطرفية (CLI)
 */

if (php_sapi_name() !== 'cli') {
    die("خطأ: هذا السكريبت يمكن تشغيله فقط عبر الطرفية (Command Line).\n");
}

require_once __DIR__ . '/includes/init.php';

use App\Core\Database;

$email = 'admin@khadomeh.local';
$newPassword = 'Admin@123456'; // كلمة المرور الجديدة الافتراضية

echo "بدء عملية إعادة تعيين حساب المشرف...\n";

// تشفير كلمة المرور الجديدة
$passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

try {
    $db = Database::getInstance();
    
    // التحقق من وجود الحساب أولاً
    $admin = $db->fetch("SELECT * FROM `admin_users` WHERE `email` = ? LIMIT 1", [$email]);
    
    if ($admin) {
        // تحديث الحساب الحالي
        $db->query(
            "UPDATE `admin_users` SET `password_hash` = ?, `is_active` = 1 WHERE `id` = ?",
            [$passwordHash, $admin['id']]
        );
        echo "نجاح: تم تحديث كلمة مرور المشرف بنجاح.\n";
    } else {
        // إنشاء حساب جديد في حال حذفه
        $db->query(
            "INSERT INTO `admin_users` (`name`, `email`, `password_hash`, `role`, `is_active`) VALUES (?, ?, ?, ?, ?)",
            ['مدير النظام', $email, $passwordHash, 'super_admin', 1]
        );
        echo "نجاح: لم يتم العثور على الحساب، تم إنشاء مستخدم مشرف جديد بالبيانات المطلوبة.\n";
    }
    
    echo "البريد الإلكتروني: $email\n";
    echo "كلمة المرور الجديدة: $newPassword\n";
    
} catch (\Exception $e) {
    echo "خطأ أثناء تحديث البيانات: " . $e->getMessage() . "\n";
}
```

---

## 2. طريقة التشغيل عبر الطرفية (VPS / Local)

1. اتصل بخادم الـ VPS عبر SSH أو افتح الطرفية المحلية (PowerShell/CMD).
2. اذهب إلى مجلد جذر المشروع:
   * **VPS**: `/home/cnc-jordan-service/htdocs/service.cnc-jordan.com`
   * **Local**: `C:\xampp\htdocs\khadomeh`
3. نفذ الأمر التالي لتشغيل السكريبت:
   ```bash
   php reset_admin.php
   ```
4. بعد تأكيد النجاح، قم بحذف ملف السكريبت فوراً من الخادم لحماية أمن الموقع:
   ```bash
   rm reset_admin.php
   ```
