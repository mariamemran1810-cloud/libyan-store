# ============================================================
#  المتجر الليبي — صورة نشر (Docker)
#  تُبنى على أي خادم يدعم Docker: Render، Railway، VPS...
#  جذر الويب = public/ (باقي الملفات خارج الويب)
# ============================================================
FROM php:8.3-apache

# وحدات Apache المطلوبة لملف .htaccess (توجيه الطلبات، الضغط، الرؤوس، الكاش)
# ملاحظة: AddOutputFilterByType في Apache 2.4 ينتمي إلى mod_filter
RUN a2enmod rewrite deflate filter headers expires

# جعل مجلد public/ هو جذر الويب
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf

# السماح لملف public/.htaccess بالعمل (الافتراضي None)
RUN printf '%s\n' \
        '<Directory /var/www/html/public>' \
        '    AllowOverride All' \
        '    Require all granted' \
        '</Directory>' \
        > /etc/apache2/conf-available/app.conf \
    && a2enconf app

# امتدادات PHP (pdo_mysql اختيارية لمن يفضّل MySQL بدل SQLite)
RUN docker-php-ext-install pdo_mysql >/dev/null || true

# نسخ المشروع وضبط الصلاحيات (قاعدة البيانات والصور قابلة للكتابة)
COPY . /var/www/html
RUN mkdir -p /var/www/html/database \
             /var/www/html/storage/logs \
             /var/www/html/storage/uploads \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/database \
    && chmod -R ug+rwX /var/www/html/storage /var/www/html/database

COPY docker/entrypoint.sh /usr/local/bin/app-entry
RUN chmod +x /usr/local/bin/app-entry

ENTRYPOINT ["app-entry"]
CMD ["apache2-foreground"]
