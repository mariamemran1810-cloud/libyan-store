<?php

declare(strict_types=1);

namespace App\Core;

/**
 * رمز الحماية CSRF: يمنع مواقع أخرى من إرسال نماذج باسمك.
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::KEY);
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set(self::KEY, $token);
        }

        return $token;
    }

    /** حقن حقل مخفي داخل النماذج: <?php echo Csrf::field(); ?> */
    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(self::token()) . '">' . "\n";
    }

    /** يتحقق من الرمز، وإذا لم يصح يوقف الطلب برسالة عربية. */
    public static function verify(): void
    {
        $sent = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!is_string($sent) || $sent === '' || !hash_equals(self::token(), $sent)) {
            http_response_code(419);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><html lang="ar" dir="rtl"><meta charset="utf-8">'
                . '<title>انتهت صلاحية الصفحة</title>'
                . '<body style="font-family:sans-serif;text-align:center;padding:60px">'
                . '<h1>انتهت صلاحية النموذج</h1>'
                . '<p>رجاءً عود/ي للخلفة ثم أعد/ي المحاولة.</p>'
                . '<a href="/">العودة للرئيسية</a></body></html>';
            exit;
        }
    }
}