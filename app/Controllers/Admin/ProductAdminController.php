<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Validate;
use App\Models\ProductRepository;

/**
 * إدارة المنتجات من لوحة الإدارة.
 */
final class ProductAdminController extends Controller
{
    public function index(): void
    {
        $this->view('admin/products/index', [
            'products' => ProductRepository::allForAdmin(),
            'page' => [
                'title' => 'إدارة المنتجات - ' . env('APP_NAME', 'المتجر الليبي'),
                'canonical' => '/admin/products',
                'noindex' => true,
            ],
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $product = ProductRepository::find((int) $id);
        if ($product === null) {
            \App\Core\Errors::notFoundPage();
            return;
        }

        $this->form($product);
    }

    private function form(?array $product): void
    {
        $this->view('admin/products/form', [
            'product' => $product,
            'categories' => ProductRepository::categories(),
            'page' => [
                'title' => ($product === null ? 'إضافة منتج' : 'تعديل منتج') . ' - ' . env('APP_NAME', 'المتجر الليبي'),
                'canonical' => $product === null ? '/admin/products/create' : '/admin/products/' . $product['id'] . '/edit',
                'noindex' => true,
            ],
        ]);
    }

    public function store(): void
    {
        Csrf::verify();
        $data = $this->validateInput();

        $upload = \App\Core\Upload::image($_FILES['image'] ?? []);
        if (!$upload['ok']) {
            \App\Core\Session::flash('old', $_POST);
            flash('errors', $upload['error'] ?? 'تعذّر رفع الصورة.', 'error');
            $this->back();
        }

        $data['image'] = $upload['name'] !== '' ? $upload['name'] : null;
        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        $data['featured'] = isset($_POST['featured']) ? 1 : 0;

        $id = ProductRepository::create($data);

        flash('admin', 'تمت إضافة المنتج بنجاح (#' . $id . ').', 'success');
        redirect('/admin/products');
    }

    public function update(string $id): void
    {
        Csrf::verify();

        $product = ProductRepository::find((int) $id);
        if ($product === null) {
            \App\Core\Errors::notFoundPage();
            return;
        }

        $data = $this->validateInput((int) $id);

        $upload = \App\Core\Upload::image($_FILES['image'] ?? []);
        if (!$upload['ok']) {
            \App\Core\Session::flash('old', $_POST);
            flash('errors', $upload['error'] ?? 'تعذّر رفع الصورة.', 'error');
            $this->back();
        }

        if ($upload['name'] !== '') {
            $data['image'] = $upload['name'];
        }

        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        $data['featured'] = isset($_POST['featured']) ? 1 : 0;

        ProductRepository::update((int) $id, $data);

        flash('admin', 'تم تحديث المنتج بنجاح.', 'success');
        redirect('/admin/products');
    }

    public function delete(string $id): void
    {
        Csrf::verify();

        $product = ProductRepository::find((int) $id);
        if ($product === null) {
            \App\Core\Errors::notFoundPage();
            return;
        }

        ProductRepository::delete((int) $id);

        flash('admin', 'تم حذف المنتج: ' . $product['name'], 'success');
        redirect('/admin/products');
    }

    /** التحقق من المدخلات + توليد معرّف فريد */
    private function validateInput(?int $currentId = null): array
    {
        // حقل اختياري: القيم الفارغة تُعامل كـ null (تتخطى قواعد التحقق)
        $_POST['prep_minutes'] = trim((string) ($_POST['prep_minutes'] ?? '')) === ''
            ? null
            : trim((string) $_POST['prep_minutes']);

        $errors = Validate::check($_POST, [
            'name' => ['required', 'min:2', 'max:150'],
            'slug' => ['max:150', 'slug'],
            'category_id' => ['required', 'integer', 'min_value:1'],
            'price' => ['required', 'numeric', 'min_value:0'],
            'old_price' => ['numeric', 'min_value:0'],
            'stock' => ['required', 'integer', 'min_value:0'],
            'prep_minutes' => ['integer', 'min_value:0', 'max_value:600'],
            'tagline' => ['required', 'max:190'],
            'description' => ['required', 'min:10', 'max:4000'],
        ], [
            'name' => 'اسم المنتج',
            'slug' => 'المعرّف (slug)',
            'category_id' => 'القسم',
            'price' => 'السعر',
            'old_price' => 'السعر قبل الخصم',
            'stock' => 'الكمية',
            'prep_minutes' => 'مدة التحضير',
            'tagline' => 'وصف مختصر',
            'description' => 'الوصف الكامل',
        ]);

        if ($errors !== []) {
            \App\Core\Session::flash('old', $_POST);
            flash('errors', implode(' — ', $errors), 'error');
            $this->back();
        }

        $slug = trim((string) ($_POST['slug'] ?? ''));
        if ($slug === '') {
            $slug = slugify((string) $_POST['name']);
        }
        if ($slug === '') {
            $slug = 'product-' . time();
        }

        // التأكد من فريدية المعرّف
        $existing = Database::first('SELECT id FROM products WHERE slug = ?', [$slug]);
        if ($existing !== null && (int) $existing['id'] !== (int) $currentId) {
            $slug .= '-' . random_int(100, 999);
        }

        $category = ProductRepository::categoryById((int) $_POST['category_id']);
        if ($category === null) {
            flash('errors', 'القسم المختار غير موجود.', 'error');
            $this->back();
        }

        $features = array_values(array_filter(array_map('trim', explode("\n", (string) ($_POST['features'] ?? '')))));

        return [
            'category_id' => (int) $category['id'],
            'name' => trim((string) $_POST['name']),
            'slug' => $slug,
            'price' => (float) $_POST['price'],
            'old_price' => ($_POST['old_price'] ?? '') === '' ? null : (float) $_POST['old_price'],
            'stock' => (int) $_POST['stock'],
            'prep_minutes' => $_POST['prep_minutes'] === null ? 0 : max(0, (int) $_POST['prep_minutes']),
            'emoji' => trim((string) ($_POST['emoji'] ?? '')) ?: '🛍️',
            'color' => preg_replace('/[^#a-zA-Z0-9]/', '', (string) ($_POST['color'] ?? '#e5e7eb')) ?: '#e5e7eb',
            'tagline' => trim((string) $_POST['tagline']),
            'description' => trim((string) $_POST['description']),
            'features' => $features,
        ];
    }
}