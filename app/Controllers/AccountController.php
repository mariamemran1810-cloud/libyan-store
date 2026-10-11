<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Validate;
use App\Models\OrderRepository;

/**
 * صفحة حساب العميلة + تغيير كلمة المرور.
 */
final class AccountController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        $orders = [];

        if ($user !== null) {
            $orders = Database::all(
                'SELECT id, status, total, created_at FROM orders WHERE user_id = ? ORDER BY id DESC',
                [$user['id']]
            );
        }

        $this->view('account/index', [
            'user' => $user,
            'orders' => $orders,
            'page' => [
                'title' => 'حسابي - ' . env('APP_NAME', 'المتجر الليبي'),
                'description' => 'بيانات حسابك وطلباتك في المتجر الليبي.',
                'canonical' => '/account',
                'noindex' => true,
            ],
        ]);
    }

    public function passwordForm(): void
    {
        $this->view('account/password', [
            'page' => [
                'title' => 'تغيير كلمة المرور - ' . env('APP_NAME', 'المتجر الليبي'),
                'canonical' => '/account/password',
                'noindex' => true,
            ],
        ]);
    }

    public function passwordUpdate(): void
    {
        Csrf::verify();

        $errors = Validate::check($_POST, [
            'current_password' => ['required', 'max:100'],
            'password' => ['required', 'min:8', 'max:200', 'confirmed'],
        ], [
            'current_password' => 'كلمة المرور الحالية',
            'password' => 'كلمة المرور الجديدة',
        ]);

        if ($errors !== []) {
            flash('errors', implode(' — ', $errors), 'error');
            redirect('/account/password');
        }

        $user = Auth::user();
        if ($user === null) {
            redirect('/login');
        }

        if (!password_verify((string) $_POST['current_password'], $user['password_hash'])) {
            flash('errors', 'كلمة المرور الحالية غير صحيحة.', 'error');
            redirect('/account/password');
        }

        if (password_verify((string) $_POST['password'], $user['password_hash'])) {
            flash('errors', 'كلمة المرور الجديدة مطابقة للحالية. اختاري كلمة مختلفة.', 'error');
            redirect('/account/password');
        }

        \App\Models\UserRepository::updatePassword((int) $user['id'], (string) $_POST['password']);
        Auth::logout();
        flash('auth', 'تم تغيير كلمة المرور. سجّلي دخولك بكلمة المرور الجديدة.', 'success');
        redirect('/login');
    }
}