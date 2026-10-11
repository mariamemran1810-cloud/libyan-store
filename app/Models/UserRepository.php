<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * جدول المستخدمين (مسؤولة / بائعة / عميلة).
 */
final class UserRepository
{
    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        $row = Database::first('SELECT * FROM users WHERE id = ?', [$id]);

        return $row === null ? null : self::map($row);
    }

    /** @return array<string,mixed>|null */
    public static function findByEmail(string $email): ?array
    {
        $row = Database::first('SELECT * FROM users WHERE email = ?', [mb_strtolower(trim($email))]);

        return $row === null ? null : self::map($row);
    }

    /** @param array<string,mixed> $data */
    public static function create(array $data): int
    {
        return Database::insert('users', [
            'name' => trim((string) $data['name']),
            'email' => mb_strtolower(trim((string) $data['email'])),
            'phone' => $data['phone'] ?? null,
            'password_hash' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'customer',
        ]);
    }

    public static function emailExists(string $email): bool
    {
        return Database::first('SELECT id FROM users WHERE email = ?', [mb_strtolower(trim($email))]) !== null;
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(): array
    {
        $rows = Database::all('SELECT * FROM users ORDER BY id DESC');

        return array_map([self::class, 'map'], $rows);
    }

    public static function countAll(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM users');
    }

    public static function updateRole(int $id, string $role, bool $active): int
    {
        return Database::update('users', ['role' => $role, 'is_active' => $active ? 1 : 0], 'id = ?', [$id]);
    }

    public static function updatePassword(int $id, string $password): int
    {
        return Database::update('users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], 'id = ?', [$id]);
    }

    /** @param array<string,mixed> $row @return array<string,mixed> */
    private static function map(array $row): array
    {
        $roles = ['admin', 'seller', 'customer'];

        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'email' => (string) $row['email'],
            'phone' => $row['phone'] ?? '',
            'password_hash' => (string) $row['password_hash'],
            'role' => in_array($row['role'], $roles, true) ? (string) $row['role'] : 'customer',
            'is_active' => (int) $row['is_active'] === 1,
            'created_at' => substr((string) $row['created_at'], 0, 16),
        ];
    }
}