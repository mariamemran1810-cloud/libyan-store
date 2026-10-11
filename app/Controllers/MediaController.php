<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Env;

/**
 * تقديم الصور المرفوعة عبر مسار آمن:
 * /uploads/{اسم-عشوائي.ext}
 * - لا مسارات مجلدات (basename فقط)
 * - لا ارتقاء في المجلدات (رفض أي / أو ..)
 * - امتداد ونوع ملف مسموحان فقط
 */
final class MediaController extends Controller
{
    public function show(string $file): void
    {
        $file = basename($file);

        if (!preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp|gif)$/', $file)) {
            http_response_code(404);
            exit('Not found');
        }

        $path = BASE_PATH . '/' . rtrim(Env::get('UPLOAD_DIR', 'storage/uploads'), '/') . '/' . $file;

        if (!is_file($path)) {
            http_response_code(404);
            exit('Not found');
        }

        $info = @getimagesize($path);
        $mime = is_array($info) ? (string) $info['mime'] : '';
        if (!in_array($mime, \App\Core\Upload::ALLOWED_MIME, true)) {
            http_response_code(404);
            exit('Not found');
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($path));
        header('Cache-Control: public, max-age=604800');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }
}