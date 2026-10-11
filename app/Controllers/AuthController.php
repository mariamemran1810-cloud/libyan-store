<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Validate;

/**
 * الدخول والتسجيل.
 */
final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login', [
            'page' => [
                'title' => 'تسجيل الدخول - ' . env('APP_NAME', 'المتجر الليبي'),
                'description' => 'سجّلي دخولك إلى حسابك في المتجر الليبي.',
                'canonical' => '/login',
                'noindex' => true,
            ],
        ]);
    }

    public function login(): void
    {
        Csrf::verify();

        $input = $_POST;
        $errors = Validate::check($input, [
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'max:100'],
        ], [
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
        ]);

        if ($errors !== []) {
            flash('errors', implode(' — ', $errors), 'error');
            redirect('/login');
        }

        if (Auth::isThrottled()) {
            flash('errors', Lang::get('auth.too_many_attempts'), 'error');
            redirect('/login');
        }

        if (!Auth::attempt((string) $input['email'], (string) $input['password'])) {
            flash('errors', Lang::get('auth.invalid_credentials'), 'error');
            redirect('/login');
        }

        flash('auth', Lang::get('auth.welcome_back', ['name' => Auth::user()['name'] ?? '']), 'success');
        redirect(Auth::roleIs(['admin', 'seller']) ? '/admin' : '/account');
    }

    public function showRegister(): void
    {
        $this->view('auth/register', [
            'page' => [
                'title' => 'إنشاء حساب - ' . env('APP_NAME', 'المتجر الليبي'),
                'description' => 'أنشئي حسابك في المتجر الليبي لتتتبّعي طلباتك.',
                'canonical' => '/register',
                'noindex' => true,
            ],
        ]);
    }

    public function register(): void
    {
        Csrf::verify();

        $input = $_POST;
        $errors = Validate::check($input, [
            'name' => ['required', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'max:30'],
            'password' => ['required', 'min:8', 'max:200', 'confirmed'],
        ], [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'password' => 'كلمة المرور',
        ]);

        if ($errors !== []) {
            flash('errors', implode(' — ', $errors), 'error');
            redirect('/register');
        }

        if (\App\Models\UserRepository::emailExists((string) $input['email'])) {
            flash('errors', Lang::get('auth.email_taken'), 'error');
            redirect('/register');
        }

        $userId = \App\Models\UserRepository::create([
            'name' => (string) $input['name'],
            'email' => (string) $input['email'],
            'phone' => (string) $input['phone'],
            'password' => (string) $input['password'],
            'role' => 'customer',
        ]);

        Auth::login($userId);
        flash('auth', Lang::get('auth.registered'), 'success');
        redirect('/account');
    }

    public function logout(): void
    {
        Csrf::verify();
        Auth::logout();
        flash('auth', Lang::get('auth.logged_out'), 'success');
        redirect('/');
    }
}