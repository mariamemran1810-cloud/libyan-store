<?php

/** متغيرات متاحة: $messageText (الرسالة) و $showDetails (عرض التفاصيل) و $error (الاستثناء) */
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>حدث خطأ غير متوقع</title>
<style>
    body{font-family:system-ui,"Segoe UI",Tahoma,sans-serif;background:#f7f7fb;color:#1f2430;margin:0;padding:40px 16px;text-align:center}
    .card{max-width:640px;margin:0 auto;background:#fff;border-radius:14px;padding:32px;box-shadow:0 8px 30px rgba(0,0,0,.07)}
    h1{margin-top:0;color:#b42318}
    code,pre{text-align:left;direction:ltr;background:#f2f2f7;border-radius:8px;padding:10px;overflow:auto;font-size:13px}
    a{color:#2563eb}
</style>
</head>
<body>
<div class="card">
    <h1><?= e(__('errors.500_title')) ?></h1>
    <p><?= e($messageText ?? __('errors.500_message')) ?></p>
    <p><a href="/">العودة إلى الرئيسية</a></p>
    <?php if (!empty($showDetails) && isset($error)): ?>
        <details open>
            <summary>تفاصيل تقنية (وضع التطوير فقط)</summary>
            <p><strong>الرسالة:</strong></p>
            <pre><?= e($error->getMessage()) ?></pre>
            <p><strong>الملف:</strong> <?= e($error->getFile()) ?> : <?= (int) $error->getLine() ?></p>
        </details>
    <?php endif; ?>
</div>
</body>
</html>