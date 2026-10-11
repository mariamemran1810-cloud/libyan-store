<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Errors;
use App\Core\Validate;
use App\Models\UserRepository;

/**
 * إدارة المستخدمين — للمسؤولة فقط (role:admin في routes).
 */
final class UserAdminController extends Controller
{
    public function index(): void
    {
        $this->view('admin/users/index', [
            'users' => UserRepository::all(),
            'page' => [
                'title' => 'إدارة المستخدمين - ' . env('APP_NAME', 'المتجر الليبي'),
                'canonical' => '/admin/users',
                'noindex' => true,
            ],
        ]);
    }

    public function role(string $id): void
    {
        Csrf::verify();

        $user = UserRepository::find((int) $id);
        if ($user === null) {
            Errors::notFoundPage();
            return;
        }

        // منع تعديل حسابك الحالي لتجنيب قفل نفسك خارج اللوحة
        if (Auth::id() === (int) $user['id']) {
            flash('errors', 'لا يمكنكِ تعديل دورك أو حالة حسابك الحالي.', 'error');
            $this->back();
        }

        $errors = Validate::check($_POST, [
            'role' => ['required', 'in:admin,seller,customer'],
            'is_active' => ['required', 'boolean'],
        ], ['role' => 'الدور', 'is_active' => 'الحالة']);

        if ($errors !== []) {
            flash('errors', implode(' — ', $errors), 'error');
            $this->back();
        }

        UserRepository::updateRole(
            (int) $user['id'],
            (string) $_POST['role'],
            (string) $_POST['is_active'] === '1'
        );

        flash('admin', 'تم تحديث بيانات المستخدم: ' . $user['name'], 'success');
        $this->back();
    }
}