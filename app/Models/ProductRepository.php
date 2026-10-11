<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * مصدر بيانات المنتجات — يقرأ ويكتب في قاعدة البيانات.
 * (كان بيانات تجريبية في المرحلة الثانية، والآن أصبح قاعدة بيانات حقيقية).
 */
final class ProductRepository
{
    /** @var array<int,array<string,mixed>>|null */
    private static ?array $categories = null;

    /** @var array<int,array<string,mixed>>|null */
    private static ?array $catalog = null;

    // ================= الأقسام =================

    /** @return array<int,array<string,mixed>> */
    public static function categories(): array
    {
        if (self::$categories === null) {
            $rows = Database::all('SELECT * FROM categories ORDER BY position ASC, id ASC');
            self::$categories = array_map([self::class, 'mapCategory'], $rows);
        }

        return self::$categories;
    }

    /** @return array<string,mixed>|null */
    public static function category(string $slug): ?array
    {
        foreach (self::categories() as $category) {
            if ($category['slug'] === $slug) {
                return $category;
            }
        }

        return null;
    }

    /** @return array<string,mixed>|null */
    public static function categoryById(int $id): ?array
    {
        $row = Database::first('SELECT * FROM categories WHERE id = ?', [$id]);

        return $row === null ? null : self::mapCategory($row);
    }

    // ================= المنتجات =================

    /** كل المنتجات المنشورة (مخدومة للزوار) */
    /** @return array<int,array<string,mixed>> */
    public static function products(): array
    {
        if (self::$catalog === null) {
            $rows = Database::all(
                'SELECT p.*, c.name AS category_name, c.slug AS category_slug
                   FROM products p
                   JOIN categories c ON c.id = p.category_id
                  WHERE p.is_active = 1
                  ORDER BY p.created_at DESC, p.id DESC'
            );
            self::$catalog = array_map([self::class, 'mapProduct'], $rows);
        }

        return self::$catalog;
    }

    /** @return array<string,mixed>|null */
    public static function bySlug(string $slug): ?array
    {
        foreach (self::products() as $product) {
            if ($product['slug'] === $slug) {
                return $product;
            }
        }

        return null;
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        $row = Database::first(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
               FROM products p JOIN categories c ON c.id = p.category_id
              WHERE p.id = ?',
            [$id]
        );

        return $row === null ? null : self::mapProduct($row);
    }

    /** @return array<int,array<string,mixed>> */
    public static function featured(int $limit = 8): array
    {
        $items = array_values(array_filter(self::products(), static fn (array $p): bool => $p['featured'] === true));

        return array_slice($items, 0, $limit);
    }

    /** @return array<int,array<string,mixed>> */
    public static function latest(int $limit = 8): array
    {
        return array_slice(self::products(), 0, $limit);
    }

    /** @return array<int,array<string,mixed>> */
    public static function byCategory(string $slug): array
    {
        return array_values(array_filter(self::products(), static fn (array $p): bool => $p['category'] === $slug));
    }

    public static function countByCategory(string $slug): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE c.slug = ? AND p.is_active = 1',
            [$slug]
        );
    }

    /**
     * البحث والفرز والتقسيم لصفحات — تُنفَّذ في قاعدة البيانات (سريعة ومحايدة للحجم).
     *
     * @return array{items:array<int,array<string,mixed>>,total:int,page:int,pages:int}
     */
    public static function search(
        string $query = '',
        string $category = '',
        string $sort = 'newest',
        int $page = 1,
        int $perPage = 12
    ): array {
        $where = ['p.is_active = 1'];
        $params = [];

        if ($category !== '') {
            $where[] = 'c.slug = ?';
            $params[] = $category;
        }

        if ($query !== '') {
            $where[] = '(p.name LIKE ? OR p.tagline LIKE ? OR c.name LIKE ?)';
            $like = '%' . $query . '%';
            array_push($params, $like, $like, $like);
        }

        $whereSql = implode(' AND ', $where);
        $orderSql = match ($sort) {
            'price_asc' => 'p.price ASC, p.id ASC',
            'price_desc' => 'p.price DESC, p.id ASC',
            default => 'p.created_at DESC, p.id DESC',
        };

        $total = (int) Database::scalar(
            'SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE ' . $whereSql,
            $params
        );

        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);

        $rows = Database::all(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
               FROM products p JOIN categories c ON c.id = p.category_id
              WHERE ' . $whereSql . '
              ORDER BY ' . $orderSql . '
              LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage),
            $params
        );

        return [
            'items' => array_map([self::class, 'mapProduct'], $rows),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    // ================= إدارة المنتجات (للمسؤولة/البائعة) =================

    /** @return array<int,array<string,mixed>> كل المنتجات بما فيها غير المنشورة */
    public static function allForAdmin(): array
    {
        $rows = Database::all(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
               FROM products p JOIN categories c ON c.id = p.category_id
              ORDER BY p.id DESC'
        );

        return array_map([self::class, 'mapProduct'], $rows);
    }

    /** @param array<string,mixed> $data */
    public static function create(array $data): int
    {
        $id = Database::insert('products', self::toColumns($data));
        self::reset();

        return $id;
    }

    /** @param array<string,mixed> $data */
    public static function update(int $id, array $data): int
    {
        $affected = Database::update('products', self::toColumns($data), 'id = ?', [$id]);
        self::reset();

        return $affected;
    }

    public static function delete(int $id): int
    {
        $affected = Database::delete('products', 'id = ?', [$id]);
        self::reset();

        return $affected;
    }

    public static function adjustStock(int $id, int $delta): void
    {
        Database::run('UPDATE products SET stock = MAX(0, stock + ?), updated_at = datetime(\'now\') WHERE id = ?', [$delta, $id]);
        self::reset();
    }

    /** مسح الكاش الداخلي بعد أي تعديل */
    public static function reset(): void
    {
        self::$catalog = null;
        self::$categories = null;
    }

    // ================= تحويل الصفوف =================

    /** @param array<string,mixed> $row */
    private static function mapCategory(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'slug' => (string) $row['slug'],
            'emoji' => (string) $row['emoji'],
            'color' => (string) $row['color'],
            'description' => (string) $row['description'],
            'position' => (int) $row['position'],
        ];
    }

    /** @param array<string,mixed> $row */
    private static function mapProduct(array $row): array
    {
        $features = json_decode((string) ($row['features'] ?? '[]'), true);
        if (!is_array($features)) {
            $features = [];
        }

        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'slug' => (string) $row['slug'],
            'category_id' => (int) $row['category_id'],
            'category' => (string) ($row['category_slug'] ?? ''),
            'category_name' => (string) ($row['category_name'] ?? ''),
            'price' => (float) $row['price'],
            'old_price' => $row['old_price'] === null ? null : (float) $row['old_price'],
            'stock' => (int) $row['stock'],
            'prep_minutes' => (int) ($row['prep_minutes'] ?? 0),
            'emoji' => (string) $row['emoji'],
            'color' => (string) $row['color'],
            'tagline' => (string) $row['tagline'],
            'description' => (string) $row['description'],
            'features' => $features,
            'image' => $row['image'] ?? null,
            'featured' => (int) $row['featured'] === 1,
            'is_active' => (int) $row['is_active'] === 1,
            'created_at' => substr((string) $row['created_at'], 0, 10),
            'url' => '/products/' . $row['slug'],
        ];
    }

    /** @param array<string,mixed> $data @return array<string,mixed> */
    private static function toColumns(array $data): array
    {
        $allowed = [
            'category_id', 'name', 'slug', 'price', 'old_price', 'stock',
            'emoji', 'color', 'tagline', 'description', 'features',
            'image', 'featured', 'is_active',
        ];

        $columns = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $columns[$key] = $data[$key];
            }
        }

        if (isset($columns['features']) && is_array($columns['features'])) {
            $columns['features'] = json_encode($columns['features'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($columns['featured'])) {
            $columns['featured'] = $columns['featured'] ? 1 : 0;
        }
        if (isset($columns['is_active'])) {
            $columns['is_active'] = $columns['is_active'] ? 1 : 0;
        }

        $columns['updated_at'] = date('Y-m-d H:i:s');

        return $columns;
    }
}