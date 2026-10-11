#!/bin/sh
# ============================================================
#  تجهيز الحاوية عند كل تشغيل:
#  1) إنشاء .env إن لم يوجد
#  2) ربط إعدادات الاستضافة (APP_URL...) بملف .env
#  3) تهيئة قاعدة البيانات والبيانات التجريبية
# ============================================================
set -e
cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
    echo "تم إنشاء .env من .env.example"
fi

# نسخ القيم القادمة من بيئة الاستضافة إلى .env
for key in APP_ENV APP_DEBUG APP_URL APP_KEY SESSION_NAME DB_DRIVER DB_DATABASE; do
    val=$(printenv "$key" 2>/dev/null || true)
    if [ -n "$val" ]; then
        if grep -q "^${key}=" .env; then
            sed -i "s|^${key}=.*|${key}=${val}|" .env
        else
            echo "${key}=${val}" >> .env
        fi
        echo " · ${key} مأخوذة من بيئة التشغيل"
    fi
done

echo "تهيئة قاعدة البيانات..."
php scripts/migrate.php || echo "تنبيه: تعذّرت تهيئة القاعدة الآن (يمكن إعادة المحاولة)"

# تملّك الملفات لمستخدم ويب Apache (وإلا تتعذّر كتابة قاعدة البيانات والصور)
chown -R www-data:www-data /var/www/html/database /var/www/html/storage

exec "$@"
