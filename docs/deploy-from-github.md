# نشر المتجر من GitHub إلى الإنترنت

دليل خطوة بخطوة بالعربية، وخياران:

| الخيار | الأنسب لـ | المدة |
|--------|-----------|-------|
| **أ) Render.com** (بالـ Docker الجاهز) | تجربة سريعة والرابط يعمل خلال دقائق | مجاني (محدوديات مذكورة أدناه) |
| **ب) استضافة cPanel** | بيانات حقيقية تدوم ودومين خاص | حسب الباقة |

> قبل أي شيء: المستودع جاهز على
> `https://github.com/mariamemran1810-cloud/libyan-store`
> وكل `push` يشغّل الاختبارات تلقائيًا (89 فحصًا ذاتيًا + 104 سيناريو HTTP + بناء صورة النشر).

---

## أ) النشر على Render.com (الأسرع)

**زر النشر بضغطة واحدة** (يفتح Render مباشرة مع `render.yaml` جاهزًا):

[![Deploy to Render](https://render.com/images/render-deploy-button.svg)](https://render.com/deploy?repo=https://github.com/mariamemran1810-cloud/libyan-store)

إن لم يعمل الزر، التدوين اليدوي خطوة بخطوة:

ملف `render.yaml` في جذر المستودع يحمل الإعدادات كاملة، فيكفي ربط الحساب:

1. أنشئي حسابًا مجانيًا على **render.com** (بالبريد أو بحساب GitHub).
2. اضغطي **New + → Web Service**.
3. افعلي **Connect GitHub** وحدّدي المستودع `libyan-store`.
   (إن لم يظهر، سلّمي GitHub أذونات read للتطبيق عبر OAuth).
4. اسحبي/اختاري المستودع واضغطي **Connect**.
   سيراها Render يقرأ `render.yaml` تلقائيًا (اسم الخدمة، Docker، رابط فحص الصحة).
5. اضغطي **Create Web Service** وانتظري بناء الصورة (دقيقة أو دقيقتين).
6. جرّبي الرابط: `https://libyan-store.onrender.com/`
   ثم `https://libyan-store.onrender.com/healthz` ويجب أن يعرض:
   `{"status":"ok","database":"ok",...}`

**ماذا يحدث تلقائيًا عند كل تعديل؟**

- `push` إلى فرع `main` → تشغيل الاختبارات على GitHub (يجب أن تبقى خضراء ✅)
- بعدها → إعادة بناء ونشر الصورة على Render (لو كان موصولًا بالمستودع)

**ملاحظات مهمة قبل الاستخدام الفعلي:**

1. الخادم المجاني **ينام** بعد فترة عدم استخدام؛ أول زيارة بعدها تنتظر ~30 ثانية.
2. قاعدة البيانات SQLite في الباقة المجانية **تُعاد تهيئتها عند إعادة النشر** — مناسبة
   للتجربة، وليست لبيانات عملاء حقيقيين.
   - للبيانات الدائمة: فعّلي **Disk** في باقة Render المدفوعة، أو استخدمي الخيار (ب) cPanel.
3. غيّري كلمات مرور الحسابات التجريبية فور أول دخول.

### حسابات التجربة (بعد النشر)

| الدور | البريد | كلمة المرور |
|-------|--------|-------------|
| مسؤولة | admin@example.com | ChangeMe123! |
| بائعة | seller@example.com | ChangeMe123! |
| عميلة | customer@example.com | ChangeMe123! |

---

## ب) النشر على استضافة cPanel (لبيانات تدوم)

1. من جذر المستودع فارغي نسخة ZIP للملفات (أو نزّليها من GitHub: Code → Download ZIP).
2. ارفعيها عبر **File Manager** إلى مجلد فرعي مثل `store/`
   (وليس `public_html` مباشرة، لأن باقي الملفات يجب أن تبقى **خارج** الويب).
3. اجعل **جذر الويب** يشير إلى `store/public`
   (في cPanel: `Domains → إضافة دومين فرعي → Document Root = store/public`
   أو `MultiPHP Manager` حسب الاستضافة).
4. افتحي **Terminal** في cPanel وشغّلي من داخل `store/`:
   ```bash
   cp .env.example .env
   nano .env          # عدّلي APP_URL لرابطك واحفظي (Ctrl+O ثم Enter ثم Ctrl+X)
   php scripts/migrate.php
   ```
5. أنشئي حسابك الخاص (وليس الحسابات التجريبية):
   ```bash
   php scripts/migrate.php --admin="بريدك" --password="كلمة-سر-قوية"
   ```
6. فعّلي **SSL مجاني (Let's Encrypt)** من لوحة cPanel، ثم فعّلي سطر إعادة
   التوجيه إلى HTTPS في `public/.htaccess` (السطر معلّق جاهز).
7. اختبري: `https://دومينك/healthz` → `"status":"ok"`.

التفاصيل الأعمق (nginx + VPS + Certbot + نسخ احتياطي): **[deploy.md](deploy.md)**

---

## ماذا يفعل ملف النشر؟؟ (Dockerfile)

- يبني صورة فيها **PHP 8.3 + Apache** ويجعل `public/` جذر الويب فقط.
- `docker/entrypoint.sh` عند كل تشغيل: ينشئ `.env` إن لم يوجد، ينسخ إعدادات
  الاستضافة (APP_URL و APP_KEY...)، يهيّئ القاعدة وبيانات التجربة،
  ويضبط الصلاحيات لمستخدم Apache.
- **مُختبر فعليًا على GitHub Actions**: يُبنى ويُشغَّل ويُطلب `/healthz`
  والصفحة الرئيسية وصفحة منتج وسلة في كل دفعة رفع.

```bash
# للتجربة على أي جهاز فيه Docker:
docker build -t libyan-store .
docker run -d -p 8080:80 -e APP_URL=http://localhost:8080 libyan-store
# ثم افتح http://localhost:8080
```

---

## أوامر مفيدة بعد النشر

```bash
git add -A
git commit -m "وصف التغيير"
git push origin main        # الاختبارات ثم النشر التلقائي
```

رابط صفحة العرض الثابتة (GitHub Pages):
`https://mariamemran1810-cloud.github.io/libyan-store/`
