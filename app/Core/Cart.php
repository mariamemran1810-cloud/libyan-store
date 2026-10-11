<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\ProductRepository;

/**
 * سلة المشتريات المحفوظة في جلسة المستخدم.
 * لا تعتمد على ملفات تعريف ارتباط متعددة، وتُمنع من تجاوز المخزون.
 */
final class Cart
{
    private const KEY = '_cart';

    /** @return array<int,int> [معرّف المنتج => الكمية] */
    private static function raw(): array
    {
        $raw = Session::get(self::KEY, []);
        if (!is_array($raw)) {
            return [];
        }

        $items = [];
        foreach ($raw as $id => $qty) {
            $id = (int) $id;
            $qty = (int) $qty;
            if ($id > 0 && $qty > 0) {
                $items[$id] = $qty;
            }
        }

        return $items;
    }

    /**
     * محتويات السلة جاهزة للعرض (تُحذف تلقائيًا المنتجات المخفية أو المحذوفة).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function items(): array
    {
        $raw = self::raw();
        if ($raw === []) {
            return [];
        }

        $catalog = [];
        foreach (ProductRepository::products() as $product) {
            $catalog[(int) $product['id']] = $product;
        }

        $items = [];
        $changed = false;

        foreach ($raw as $id => $qty) {
            if (!isset($catalog[$id])) {
                $changed = true; // المنتج لم يعد موجودًا/منشورًا
                continue;
            }

            $product = $catalog[$id];
            $stock = (int) $product['stock'];
            $safeQty = min($qty, $stock);

            if ($safeQty !== $qty) {
                $changed = true;
            }
            if ($safeQty <= 0) {
                $changed = true;
                continue;
            }

            $items[$id] = [
                'id' => $id,
                'name' => $product['name'],
                'slug' => $product['slug'],
                'url' => $product['url'],
                'price' => (float) $product['price'],
                'emoji' => $product['emoji'],
                'color' => $product['color'],
                'stock' => $stock,
                'qty' => $safeQty,
                'line_total' => (float) $product['price'] * $safeQty,
            ];
        }

        if ($changed) {
            // إعادة حفظ السلة بعد تنظيفها
            $clean = [];
            foreach ($items as $item) {
                $clean[$item['id']] = $item['qty'];
            }
            Session::set(self::KEY, $clean);
        }

        return $items;
    }

    /** إضافة منتج. يعيد false إذا كان نفد المخزون. */
    public static function add(int $productId, int $qty = 1): bool
    {
        $qty = max(1, $qty);

        $product = ProductRepository::find($productId);
        if ($product === null || !$product['is_active'] || (int) $product['stock'] <= 0) {
            return false;
        }

        $items = self::raw();
        $current = $items[$productId] ?? 0;
        $newQty = min($current + $qty, (int) $product['stock']);

        if ($newQty === $current) {
            return false; // بلغت الحد الأقصى من المخزون
        }

        $items[$productId] = $newQty;
        Session::set(self::KEY, $items);

        return true;
    }

    public static function setQty(int $productId, int $qty): void
    {
        $items = self::raw();

        if (!isset($items[$productId])) {
            return;
        }

        $qty = max(0, $qty);
        $product = ProductRepository::find($productId);

        if ($qty === 0 || $product === null) {
            unset($items[$productId]);
        } else {
            $items[$productId] = min($qty, max(1, (int) $product['stock']));
        }

        Session::set(self::KEY, $items);
    }

    public static function remove(int $productId): void
    {
        $items = self::raw();
        unset($items[$productId]);
        Session::set(self::KEY, $items);
    }

    public static function clear(): void
    {
        Session::forget(self::KEY);
    }

    public static function isEmpty(): bool
    {
        return self::items() === [];
    }

    /** مجموع عدد القطع */
    public static function count(): int
    {
        return array_sum(array_column(self::items(), 'qty'));
    }

    /** الإجمالي الفرعي بالدينار الليبي */
    public static function subtotal(): float
    {
        return round(array_sum(array_column(self::items(), 'line_total')), 3);
    }
}