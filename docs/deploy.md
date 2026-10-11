# دليل النشر (المرحلة السابعة)

كل الخطوات هنا **بدون أي مكتبات خارجية** — فقط PHP 8.1+ وملف `.env` واحد.

---

## 1) قائمة تجهيز قبل النشر

| البند | كيف تتأكدين |
|-------|-------------|
| كل المراحل السابقة مختبرة | `php docs/selftest.php` ثم `php docs/httptest.php` (كلاهما أخضر) |
| `.env` جاهز للإنتاج | `APP_ENV=production` و `APP_DEBUG=false` و `APP_URL` بنطاقك الحقيقي |
| لا أسرار في الكود | الاختبار الذاتي يفحص ذلك تلقائيًا |
| نسخة احتياطية | `php scripts/backup.php` |
| بيانات الاختبار نظيفة | `php scripts/cleanup-tests.php` |

> ⚠️ **مهم**: `APP_DEBUG=true` يعرض تفاصيل الأخطاء للزوار. على الموقع الحقيقي **خليه false**.

---

## 2) على الاستضافة المشتركة (cPanel / DirectAdmin)

1. ارفعي **محتويات** مجلد `libyan-store` إلى `public_html` (أو إلى مجلد فرعي ثم اربطيه بـ `public`).
2. تأكدي أن الملفات الحساسة **خارج الويب**:
   - `.env` و `database/` و `storage/` و `app/` و `views/` و `lang/` و `scripts/` و `docs/` خارج `public_html`
   - داخل `public_html` فقط: `public/index.php` + `public/assets/` + `public/favicon.svg` + `public/.htaccess`
3. أنشئي قاعدة البيانات:
   ```powershell
   php scripts/migrate.php
   php scripts/migrate.php --admin="بريدك" --password="كلمة-مرور-قوية"
   ```
4. افتحي `https://نطاقك.com` — يجب أن تظهر الصفحة الرئيسية.
5. جرّبي `/healthz` — يجب أن يعرض `"status":"ok"`.

**إذا كانت الاستضافة لا تسمح بوضع `.env` خارج الويب**: ضعيه في `public/../.env` (أي بجانب `public`)،
وهو ما يفترضه المشروع افتراضيًا — تأكدي فقط أن `.htaccess` يمنع الوصول إليه (موجود بالفعل).

---

## 3) على خادم خاص (VPS) — Ubuntu/Debian مثالًا

```bash
# 1) المتطلبات
sudo apt update && sudo apt install -y php-fpm php-sqlite3 php-mbstring php-xml nginx

# 2) الموقع
sudo mkdir -p /var/www/libyan-store
sudo chown -R www-data:www-data /var/www/libyan-store

# 3) نقل الملفات ثم الإعداد
cd /var/www/libyan-store
cp -r /path/to/upload/. .
cp .env.example .env        # ثم عدّلي القيم
nano .env                    # APP_URL / APP_DEBUG=false / APP_ENV=production

# 4) قاعدة البيانات + حساب المسؤولة
php scripts/migrate.php
php scripts/migrate.php --admin="admin@store.ly" --password="كلمة-مرور-قوية-جدا"

# 5) صلاحيات (المجلدات القابلة للكتابة فقط)
chown -R www-data:www-data database storage
chmod -R u+rwX database storage

# 6) فحص
php docs/selftest.php
curl -I https://store.ly/healthz
```

ملف nginx مثال (في `sites-available`):

```nginx
server {
    listen 80;
    server_name store.ly;
    root /var/www/libyan-store/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    # ملفات ثابتة: تخزين مؤقت طويل
    location ~* \.(css|js|svg|png|jpg|jpeg|webp|ico)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # منع الوصول لملفات البداية
    location ~ /\. { deny all; }
}
```

ثم:
```bash
sudo ln -s /etc/nginx/sites-available/libyan-store /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

**HTTPS مجاني** بـ Certbot:
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d store.ly -d www.store.ly
```

---

## 4) التحديث لاحقًا

1. `php scripts/backup.php`
2. ارفعي الملفات الجديدة (لا تمسحي `.env` ولا `database/` ولا `storage/`)
3. إن تغيّر `database/schema.sql` شغّلي `php scripts/migrate.php` (آمنة ومتكررة)
4. `php docs/selftest.php` للتأكد

---

## 5) سرعة الموقع

- `asset()` يضيف `?v=<وقت التعديل>` للـ CSS/JS → لا كاش قديم.
- `.htaccess` يفعّل **الضغط** و**التخزين المؤقت** للملفات الثابتة.
- قاعدة البيانات SQLite مع **فهرس** على slug/category/status (سريعة لمتاجر صغيرة).
- استخدمي **SQLite** حتى ~50 ألف منتج وآلاف الطلبات، ثم انتقلي إلى MySQL عبر `.env` فقط
  (`DB_DRIVER=mysql` + بيانات الاتصال) دون تعديل الكود.

---

## 6) المراقبة والنسخ الاحتياطي

- **صحة الموقع**: `https://نطاقك.com/healthz` (يمكن إعداد تنبيه عند فشله).
- **نسخة احتياطية**: `php scripts/backup.php` (تنشئ مجلدًا في `backups/` يحتوي قاعدة البيانات + الصور).
  جدوليها مرة أسبوعيًا على الأقل، ونزلي المجلد إلى مكان آمن.
- **سجل الأخطاء**: `storage/logs/app.log`.

---

## 7) بعد النشر: تغييري بيانات الحسابات التجريبية

الحسابات الثلاثة (`admin@`, `seller@`, `customer@` بـ `example.com`)
هي بيانات تجريبية — **غيّري كلمات المرور أو احذفيها** من لوحة `/admin/users`.
