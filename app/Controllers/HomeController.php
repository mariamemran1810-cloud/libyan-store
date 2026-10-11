<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ProductRepository;

/**
 * الصفحة الرئيسية.
 */
final class HomeController extends Controller
{
    public function index(): void
    {
        $categories = array_map(static function (array $category): array {
            $category['count'] = ProductRepository::countByCategory($category['slug']);

            return $category;
        }, ProductRepository::categories());

        $this->view('home', [
            'categories' => $categories,
            'featured' => ProductRepository::featured(8),
            'latest' => ProductRepository::latest(8),
            'page' => [
                'title' => 'المتجر الليبي - الرئيسية',
                'description' => 'متجر ليبي للتسوق الإلكتروني يعرض المنتجات بأسعار بالدينار الليبي مع توصيل سريع.',
                'canonical' => '/',
            ],
        ]);
    }
}