<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * إدارة الأقسام (للمسؤولة فقط).
 */
final class CategoryRepository
{
    /** @return array<int,array<string,mixed>> */
    public static function all(): array
    {
        $rows = Database::all(
            'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS products_count
               FROM categories c ORDER BY c.position ASC, c.id ASC'
        );

        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'slug' => (string) $row['slug'],
            'emoji' => (string) $row['emoji'],
            'color' => (string) $row['color'],
            'description' => (string) $row['description'],
            'position' => (int) $row['position'],
            'products_count' => (int) $row['products_count'],
        ], $rows);
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        foreach (self::all() as $category) {
            if ($category['id'] === $id) {
                return $category;
            }
        }

        return null;
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $row = Database::first('SELECT id FROM categories WHERE slug = ? AND id != ?', [$slug, $exceptId ?? -1]);

        return $row !== null;
    }

    public static function countProducts(int $id): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM products WHERE category_id = ?', [$id]);
    }

    /** @param array<string,mixed> $data */
    public static function create(array $data): int
    {
        $position = (int) Database::scalar('SELECT COALESCE(MAX(position), 0) + 1 FROM categories');
        $id = Database::insert('categories', [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'emoji' => $data['emoji'],
            'color' => $data['color'],
            'description' => $data['description'],
            'position' => $position,
        ]);
        ProductRepository::reset();

        return $id;
    }

    /** @param array<string,mixed> $data */
    public static function update(int $id, array $data): int
    {
        $affected = Database::update('categories', $data, 'id = ?', [$id]);
        ProductRepository::reset();

        return $affected;
    }

    /** يرفض الحذف إذا كان القسم يحتوي منتجات (حماية من حذف متعمد) */
    public static function delete(int $id): bool
    {
        if (self::countProducts($id) > 0) {
            return false;
        }

        Database::delete('categories', 'id = ?', [$id]);
        ProductRepository::reset();

        return true;
    }
}