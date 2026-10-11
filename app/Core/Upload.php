<?php

declare(strict_types=1);

namespace App\Core;

/**
 * رفع الملفات — بحماية صارمة:
 * 1) حجم أقصى من .env (UPLOAD_MAX_MB)
 * 2) صيغة مسموحة فقط (صور)
 * 3) فحص أن الملف صورة حقيقية فعليًا (ليس امتدادًا مزيفًا)
 * 4) اسم عشوائي جديد (لا يُستخدم اسم الملف الأصلي أبدًا)
 * 5) يُحفظ خارج مجلد العام (storage/uploads) ويُقدَّم عبر مسار آمن
 */
final class Upload
{
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    public const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /**
     * @param array<string,mixed> $file عنصر من $_FILES
     * @return array{ok:bool,name?:string,error?:string}
     */
    public static function image(array $file): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'name' => '']; // لم يتم اختيار صورة (اختياري)
        }

        if ($error !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => self::uploadErrorMessage($error)];
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);

        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['ok' => false, 'error' => 'ملف غير صالح.'];
        }

        $maxBytes = Env::int('UPLOAD_MAX_MB', 2) * 1024 * 1024;
        if ($size > $maxBytes) {
            return ['ok' => false, 'error' => 'حجم الصورة يتجاوز الحد المسموح (' . Env::get('UPLOAD_MAX_MB', '2') . ' ميجابايت).'];
        }

        $original = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return ['ok' => false, 'error' => 'صيغة غير مسموحة. المسموح: ' . implode(', ', self::ALLOWED_EXTENSIONS)];
        }

        // فحص حقيقي: هل الملف صورة فعلًا؟
        $info = @getimagesize($tmp);
        if ($info === false) {
            return ['ok' => false, 'error' => 'الملف ليس صورة صالحة.'];
        }
        $mime = (string) ($info['mime'] ?? '');
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return ['ok' => false, 'error' => 'نوع الصورة غير مدعوم.'];
        }

        $directory = self::directory();
        if (!is_dir($directory) && !@mkdir($directory, 0775, true)) {
            return ['ok' => false, 'error' => 'تعذّر إنشاء مجلد الرفع.'];
        }

        $name = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $directory . '/' . $name;

        if (!move_uploaded_file($tmp, $destination)) {
            return ['ok' => false, 'error' => 'تعذّر حفظ الصورة.'];
        }

        @chmod($destination, 0644);

        return ['ok' => true, 'name' => $name];
    }

    public static function directory(): string
    {
        return BASE_PATH . '/' . Env::get('UPLOAD_DIR', 'storage/uploads');
    }

    private static function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'حجم الملف يتجاوز الحد المسموح.',
            UPLOAD_ERR_PARTIAL => 'تم رفع الملف جزئيًا، حاولي مجددًا.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'خطأ داخلي أثناء الرفع.',
            default => 'فشل رفع الملف.',
        };
    }
}