<?php

declare(strict_types=1);

use App\Controllers\AccountController;
use App\Controllers\Admin\CategoryAdminController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\OrderAdminController;
use App\Controllers\Admin\ProductAdminController;
use App\Controllers\Admin\SettingsAdminController;
use App\Controllers\Admin\UserAdminController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\HomeController;
use App\Controllers\MediaController;
use App\Controllers\MetaController;
use App\Controllers\ProductController;
use App\Core\Router;

/**
 * كل روابط الموقع تُسجَّل هنا.
 * الميثود: GET للعرض، POST للإرسال/التعديل.
 * الحماية: ['auth'] = تسجيل دخول إجباري، ['role:admin'] = دور محدد.
 */
return function (Router $router): void {
    // ===== الرئيسية والمنتجات =====
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/products', [ProductController::class, 'index']);
    $router->get('/products/{slug}', [ProductController::class, 'show']);
    $router->get('/categories', [ProductController::class, 'categories']);
    $router->get('/category/{slug}', [ProductController::class, 'category']);
    $router->get('/search', [ProductController::class, 'index']);

    // الصور المرفوعة (تُقدَّم عبر مسار آمن)
    $router->get('/uploads/{file}', [MediaController::class, 'show']);

    // SEO والمراقبة
    $router->get('/sitemap.xml', [MetaController::class, 'sitemap']);
    $router->get('/robots.txt', [MetaController::class, 'robots']);
    $router->get('/healthz', [MetaController::class, 'healthz']);

    // ===== الحساب =====
    $router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
    $router->post('/login', [AuthController::class, 'login'], ['guest']);
    $router->get('/register', [AuthController::class, 'showRegister'], ['guest']);
    $router->post('/register', [AuthController::class, 'register'], ['guest']);
    $router->post('/logout', [AuthController::class, 'logout'], ['auth']);
    $router->get('/account', [AccountController::class, 'index'], ['auth']);

    // ===== السلة وإتمام الطلب =====
    $router->get('/cart', [CartController::class, 'index']);
    $router->post('/cart/add', [CartController::class, 'add']);
    $router->post('/cart/update', [CartController::class, 'update']);
    $router->post('/cart/remove', [CartController::class, 'remove']);

    $router->get('/checkout', [CheckoutController::class, 'show']);
    $router->post('/checkout', [CheckoutController::class, 'store']);
    $router->get('/order/{id}', [CheckoutController::class, 'order']);

    // ===== لوحة الإدارة: للمسؤولة والبائعة =====
    $router->get('/admin', [DashboardController::class, 'index'], ['auth', 'role:admin,seller']);

    // إدارة المنتجات
    $router->get('/admin/products', [ProductAdminController::class, 'index'], ['auth', 'role:admin,seller']);
    $router->get('/admin/products/create', [ProductAdminController::class, 'create'], ['auth', 'role:admin,seller']);
    $router->post('/admin/products/store', [ProductAdminController::class, 'store'], ['auth', 'role:admin,seller']);
    $router->get('/admin/products/{id}/edit', [ProductAdminController::class, 'edit'], ['auth', 'role:admin,seller']);
    $router->post('/admin/products/{id}/update', [ProductAdminController::class, 'update'], ['auth', 'role:admin,seller']);
    $router->post('/admin/products/{id}/delete', [ProductAdminController::class, 'delete'], ['auth', 'role:admin,seller']);

    // إدارة الطلبات
    $router->get('/admin/orders', [OrderAdminController::class, 'index'], ['auth', 'role:admin,seller']);
    $router->get('/admin/orders/{id}', [OrderAdminController::class, 'show'], ['auth', 'role:admin,seller']);
    $router->post('/admin/orders/{id}/status', [OrderAdminController::class, 'status'], ['auth', 'role:admin,seller']);

    // إدارة الأقسام (للمسؤولة فقط)
    $router->get('/admin/categories', [CategoryAdminController::class, 'index'], ['auth', 'role:admin']);
    $router->get('/admin/categories/create', [CategoryAdminController::class, 'create'], ['auth', 'role:admin']);
    $router->post('/admin/categories/store', [CategoryAdminController::class, 'store'], ['auth', 'role:admin']);
    $router->get('/admin/categories/{id}/edit', [CategoryAdminController::class, 'edit'], ['auth', 'role:admin']);
    $router->post('/admin/categories/{id}/update', [CategoryAdminController::class, 'update'], ['auth', 'role:admin']);
    $router->post('/admin/categories/{id}/delete', [CategoryAdminController::class, 'delete'], ['auth', 'role:admin']);

    // إعدادات المتجر: رسوم التوصيل والملاحظات (للمسؤولة فقط)
    $router->get('/admin/settings', [SettingsAdminController::class, 'index'], ['auth', 'role:admin']);
    $router->post('/admin/settings/update', [SettingsAdminController::class, 'update'], ['auth', 'role:admin']);

    // إدارة المستخدمين (للمسؤولة فقط)
    $router->get('/admin/users', [UserAdminController::class, 'index'], ['auth', 'role:admin']);
    $router->post('/admin/users/{id}/role', [UserAdminController::class, 'role'], ['auth', 'role:admin']);

    // تغيير كلمة المرور
    $router->get('/account/password', [AccountController::class, 'passwordForm'], ['auth']);
    $router->post('/account/password', [AccountController::class, 'passwordUpdate'], ['auth']);
};