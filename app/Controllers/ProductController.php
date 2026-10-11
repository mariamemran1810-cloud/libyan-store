<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Errors;
use App\Models\ProductRepository;

/**
 * صفحات المنتجات والأقسام والبحث.
 */
final class ProductController extends Controller
{
    /** قائمة كل المنتجات مع البحث والفرز والصفحات: /products */
    public function index(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $category = preg_replace('/[^a-z0-9\-]/', '', (string) ($_GET['category'] ?? '')) ?? '';

        $heading = __('products.title');
        $description = 'تصفّح جميع المنتجات في المتجر الليبي بأسعار بالدينار الليبي.';
        $canonical = '/products';

        if ($query !== '') {
            $heading = __('products.search_results', ['q' => $query]);
            $description = 'نتائج البحث عن: ' . $query;
            $canonical = '/products?' . http_build_query(['q' => $query]);
        }

        $this->renderList($query, $category, $heading, $description, $canonical);
    }

    /** صفحة قسم واحد: /category/{slug} */
    public function category(string $slug): void
    {
        $category = ProductRepository::category($slug);
        if ($category === null) {
            Errors::notFoundPage();
            return;
        }

        $this->renderList(
            '',
            $slug,
            $category['name'],
            $category['description'] . ' منتجات قسم ' . $category['name'] . ' في المتجر الليبي.',
            '/category/' . $slug
        );
    }

    /** بناء قائمة المنتجات (مشتركة بين الرئيسية/القسم/البحث) */
    private function renderList(string $query, string $category, string $heading, string $description, string $canonical): void
    {
        $sort = (string) ($_GET['sort'] ?? 'newest');
        if (!in_array($sort, ['newest', 'price_asc', 'price_desc'], true)) {
            $sort = 'newest';
        }
        $currentPage = max(1, (int) ($_GET['page'] ?? 1));

        $result = ProductRepository::search($query, $category, $sort, $currentPage, 12);

        $this->view('products/index', [
            'result' => $result,
            'query' => $query,
            'currentCategory' => $category,
            'sort' => $sort,
            'categories' => ProductRepository::categories(),
            'heading' => $heading,
            'page' => [
                'title' => $heading . ' - ' . env('APP_NAME', 'المتجر الليبي'),
                'description' => $description,
                'canonical' => $canonical,
            ],
        ]);
    }

    /** صفحة المنتج: /products/{slug} */
    public function show(string $slug): void
    {
        $product = ProductRepository::bySlug($slug);
        if ($product === null) {
            Errors::notFoundPage();
            return;
        }

        $related = array_values(array_filter(
            ProductRepository::byCategory($product['category']),
            static fn (array $row): bool => $row['id'] !== $product['id']
        ));
        $related = array_slice($related, 0, 4);

        $this->view('products/show', [
            'product' => $product,
            'related' => $related,
            'page' => [
                'title' => $product['name'] . ' - ' . env('APP_NAME', 'المتجر الليبي'),
                'description' => $product['tagline'],
                'canonical' => '/products/' . $product['slug'],
            ],
        ]);
    }

    /** كل الأقسام: /categories */
    public function categories(): void
    {
        $categories = array_map(static function (array $category): array {
            $category['count'] = ProductRepository::countByCategory($category['slug']);

            return $category;
        }, ProductRepository::categories());

        $this->view('categories/index', [
            'categories' => $categories,
            'page' => [
                'title' => 'أقسام المتجر - ' . env('APP_NAME', 'المتجر الليبي'),
                'description' => 'جميع أقسام المتجر الليبي: ألبسة، إلكترونيات، مواد غذائية والمزيد.',
                'canonical' => '/categories',
            ],
        ]);
    }
}