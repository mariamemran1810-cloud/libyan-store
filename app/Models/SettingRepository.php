<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * إعدادات المتجر (جدول settings بمفتاح/قيمة).
 * تُعدَّل من لوحة الإدارة: رسوم التوصيل، ملاحظة التوصيل، هاتف المتجر.
 */
final class SettingRepository
{
    public static function get(string $key, string $default = ''): string
    {
        $row = Database::first('SELECT value FROM settings WHERE key = ?', [$key]);

        return $row === null ? $default : (string) $row['value'];
    }

    public static function put(string $key, string $value): void
    {
        Database::run(
            "INSERT INTO settings (key, value, updated_at) VALUES (?, ?, datetime('now'))
             ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = datetime('now')",
            [$key, $value]
        );
    }

    /**
     * مدن التوصيل ورسومها من الإعداد:  "طرابلس:5, بنغازي:8, ..."
     *
     * @return array<int,array{name:string,fee:float}> مرتّبة كما كُتبت
     */
    public static function deliveryCities(): array
    {
        $cities = [];

        foreach (explode(',', self::get('delivery_cities')) as $entry) {
            $entry = trim($entry);
            if ($entry === '') {
                continue;
            }

            $parts = array_map('trim', explode(':', $entry, 2));
            $name = $parts[0];
            $fee = isset($parts[1]) && is_numeric($parts[1]) ? max(0.0, (float) $parts[1]) : 0.0;

            if ($name !== '') {
                $cities[] = ['name' => $name, 'fee' => $fee];
            }
        }

        return $cities;
    }

    /**
     * رسم التوصيل لمدينة معيّنة، أو null إذا كانت المدينة غير مدرجة.
     */
    public static function deliveryFeeFor(string $city): ?float
    {
        foreach (self::deliveryCities() as $entry) {
            if ($entry['name'] === $city) {
                return $entry['fee'];
            }
        }

        return null;
    }

    /** @param array<string,float> $cities [اسم المدينة => الرسم] */
    public static function setDeliveryCities(array $cities): void
    {
        $entries = [];
        foreach ($cities as $name => $fee) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $entries[] = $name . ':' . number_format(max(0.0, (float) $fee), 3, '.', '');
        }

        self::put('delivery_cities', implode(', ', $entries));
    }

    public static function deliveryNote(): string
    {
        return self::get('delivery_note');
    }

    public static function storePhone(): string
    {
        return self::get('store_phone');
    }
}
