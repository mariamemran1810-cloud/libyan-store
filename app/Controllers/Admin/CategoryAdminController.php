<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Csrf;
use App\Core\Errors;
use App\Core\Validate;
use App\Models\CategoryRepository;

/**
 * إدارة الأقسام — للمسؤولة فقط (role:admin في routes).
 */
final class CategoryAdminController extends Controller
{
    public function index(): void
    {
        $this->view('admin/categories/index', [
            'categories' => CategoryRepository::all(),
            'page' => [
                'title' => 'إدارة الأقسام - ' . env('APP_NAME', 'المتجر الليبي'),
                'canonical' => '/admin/categories',
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
        $category = CategoryRepository::find((int) $id);
        if ($category === null) {
            Errors::notFoundPage();
            return;
        }

        $this->form($category);
    }

    private function form(?array $category): void
    {
        $this->view('admin/categories/form', [
            'category' => $category,
            'page' => [
                'title' => ($category === null ? 'إضافة قسم' : 'تعديل قسم') . ' - ' . env('APP_NAME', 'المتجر الليبي'),
                'canonical' => '/admin/categories',
                'noindex' => true,
            ],
        ]);
    }

    public function store(): void
    {
        Csrf::verify();
        $data = $this->validateInput();

        CategoryRepository::create($data);

        flash('admin', 'تمت إضافة القسم: ' . $data['name'], 'success');
        redirect('/admin/categories');
    }

    public function update(string $id): void
    {
        Csrf::verify();

        $category = CategoryRepository::find((int) $id);
        if ($category === null) {
            Errors::notFoundPage();
            return;
        }

        $data = $this->validateInput((int) $id);
        CategoryRepository::update((int) $id, $data);

        flash('admin', 'تم تحديث القسم: ' . $data['name'], 'success');
        redirect('/admin/categories');
    }

    public function delete(string $id): void
    {
        Csrf::verify();

        $category = CategoryRepository::find((int) $id);
        if ($category === null) {
            Errors::notFoundPage();
            return;
        }

        if (!CategoryRepository::delete((int) $id)) {
            flash('errors', 'لا يمكن حذف القسم لوجود منتجات فيه (' . CategoryRepository::countProducts((int) $id) . '). انقلي المنتجات أولًا.', 'error');
            redirect('/admin/categories');
        }

        flash('admin', 'تم حذف القسم: ' . $category['name'], 'success');
        redirect('/admin/categories');
    }

    /** @return array<string,mixed> */
    private function validateInput(?int $currentId = null): array
    {
        $errors = Validate::check($_POST, [
            'name' => ['required', 'min:2', 'max:80'],
            'slug' => ['max:80', 'slug'],
            'description' => ['required', 'max:250'],
            'emoji' => ['max:8'],
        ], [
            'name' => 'اسم القسم',
            'slug' => 'المعرّف (slug)',
            'description' => 'الوصف',
            'emoji' => 'الأيقونة',
        ]);

        if ($errors !== []) {
            \App\Core\Session::flash('old', $_POST);
            flash('errors', implode(' — ', $errors), 'error');
            redirect('/admin/categories');
        }

        $slug = trim((string) ($_POST['slug'] ?? ''));
        if ($slug === '') {
            $slug = slugify((string) $_POST['name']);
        }
        if ($slug === '') {
            $slug = 'category-' . random_int(100, 999);
        }

        if (CategoryRepository::slugExists($slug, $currentId)) {
            $slug .= '-' . random_int(100, 999);
        }

        return [
            'name' => trim((string) $_POST['name']),
            'slug' => $slug,
            'emoji' => trim((string) ($_POST['emoji'] ?? '')) ?: '🛍️',
            'color' => preg_replace('/[^#a-zA-Z0-9]/', '', (string) ($_POST['color'] ?? '#e5e7eb')) ?: '#e5e7eb',
            'description' => trim((string) $_POST['description']),
        ];
    }
}