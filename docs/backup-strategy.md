# ملحق 2: إستراتيجية وبرمجة النسخ الاحتياطي التلقائي (Database Backup Plan)

يوضح هذا المستند كيفية حماية وصيانة قواعد بيانات منصة **خدومة** على خادم الـ VPS لمنع فقدان البيانات وضمان استمرارية التشغيل.

---

## 1. إعداد سكريبت النسخ الاحتياطي التلقائي

تم إعداد سكريبت Bash مخصص لتصدير نسخة مضغوطة من جداول قاعدة البيانات وحفظها في مجلد `database/backups` بشكل منظم وتسميتها بالتاريخ والوقت.

يمكن إنشاء ملف السكريبت باسم `backup_db.sh` في مسار المشروع على الخادم وتضمين الكود التالي:

```bash
#!/bin/bash

# 1. إعداد المتغيرات الأساسية
DB_USER="khadomeh"
DB_PASS="kH3d0M3h_db_p@ss_2026!"
DB_NAME="khadomeh"
BACKUP_DIR="/home/cnc-jordan-service/htdocs/service.cnc-jordan.com/database/backups"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
FILE_NAME="khadomeh_backup_$DATE.sql.gz"

# 2. إنشاء مجلد الحفظ إذا لم يكن موجوداً
mkdir -p "$BACKUP_DIR"

# 3. تشغيل أمر التصدير والضغط
mysqldump --single-transaction --quick -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/$FILE_NAME"

# 4. حذف النسخ القديمة التي تجاوز عمرها 30 يوماً لتوفير المساحة
find "$BACKUP_DIR" -type f -name "khadomeh_backup_*.sql.gz" -mtime +30 -exec rm {} \;

echo "تمت عملية النسخ الاحتياطي بنجاح وحفظ الملف باسم: $FILE_NAME"
```

---

## 2. إعداد مجدول المهام (Cron Job)

لتشغيل السكريبت تلقائياً كل يوم في تمام الساعة 2 بعد منتصف الليل، يجب إضافة مهمة جديدة لمجدول النظام (Cron):

1. افتح محرر مهام كرون عبر الطرفية:
   ```bash
   crontab -e
   ```
2. أضف السطر التالي في نهاية الملف:
   ```text
   0 2 * * * /bin/bash /home/cnc-jordan-service/htdocs/service.cnc-jordan.com/database/backup_db.sh >/dev/null 2>&1
   ```
3. احفظ الملف واغلق المحرر. سيقوم النظام الآن بجدولة النسخ تلقائياً بشكل يومي.
