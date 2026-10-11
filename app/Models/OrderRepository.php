<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * الطلبات وبنودها.
 */
final class OrderRepository
{
    public const STATUSES = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

    public const STATUS_LABELS = [
        'pending' => 'قيد المراجعة',
        'confirmed' => 'مؤكّد',
        'shipped' => 'قيد الشحن',
        'delivered' => 'تم التسليم',
        'cancelled' => 'ملغي',
    ];

    /**
     * إنشاء طلب جديد داخل معاملة واحدة (أو كل شيء يفشل):
     * - يتحقق من المخزون لكل صنف
     * - يخصم الكمية من المخزون
     * - يحفظ بنود الطلب
     *
     * @param array<string,mixed> $customer
     * @param array<int,array{product_id:int,name:string,price:float,qty:int}> $items
     * @param float $deliveryFee رسوم التوصيل (تُضاف إلى الإجمالي)
     * @throws \App\Exceptions\OutOfStockException
     */
    public static function create(array $customer, array $items, ?int $userId = null, float $deliveryFee = 0.0): int
    {
        return (int) Database::transaction(static function () use ($customer, $items, $userId, $deliveryFee): int {
            $total = 0.0;
            foreach ($items as $item) {
                $total += $item['price'] * $item['qty'];
            }
            $total = round($total + max(0.0, $deliveryFee), 3);

            // 1) فحص المخزون وخصمه (داخل المعاملة => إن فشل شيء رجعت القاعدة كما كانت)
            foreach ($items as $item) {
                $product = Database::first(
                    'SELECT id, name, stock FROM products WHERE id = ?',
                    [(int) $item['product_id']]
                );

                if ($product === null) {
                    throw new \App\Exceptions\OutOfStockException((string) $item['name'], 0);
                }

                $available = (int) $product['stock'];
                if ($available < (int) $item['qty']) {
                    throw new \App\Exceptions\OutOfStockException((string) $product['name'], $available);
                }

                Database::run(
                    'UPDATE products SET stock = stock - ?, updated_at = datetime(\'now\') WHERE id = ? AND stock >= ?',
                    [(int) $item['qty'], (int) $item['product_id'], (int) $item['qty']]
                );
            }

            $orderId = Database::insert('orders', [
                'user_id' => $userId,
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'customer_city' => $customer['city'],
                'customer_address' => $customer['address'],
                'notes' => $customer['notes'] ?? null,
                'status' => 'pending',
                'delivery_fee' => max(0.0, $deliveryFee),
                'total' => $total,
            ]);

            foreach ($items as $item) {
                Database::insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                ]);
            }

            return $orderId;
        });
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(): array
    {
        return Database::all(
            'SELECT o.*, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS items_count
               FROM orders o
              ORDER BY o.id DESC'
        );
    }

    /** @return array<int,array<string,mixed>> */
    public static function forUser(int $userId): array
    {
        return Database::all('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC', [$userId]);
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        return Database::first('SELECT * FROM orders WHERE id = ?', [$id]);
    }

    /** @return array<int,array<string,mixed>> */
    public static function items(int $orderId): array
    {
        return Database::all('SELECT * FROM order_items WHERE order_id = ?', [$orderId]);
    }

    public static function updateStatus(int $id, string $status): int
    {
        if (!in_array($status, self::STATUSES, true)) {
            return 0;
        }

        return Database::update('orders', ['status' => $status], 'id = ?', [$id]);
    }

    /** @return array<string,int> */
    public static function countByStatus(): array
    {
        $counts = array_fill_keys(self::STATUSES, 0);
        foreach (Database::all('SELECT status, COUNT(*) AS c FROM orders GROUP BY status') as $row) {
            $counts[$row['status']] = (int) $row['c'];
        }

        return $counts;
    }

    public static function countAll(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM orders');
    }
}