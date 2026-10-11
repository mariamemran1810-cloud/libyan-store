<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * الاتصال بقاعدة البيانات واستعلامات جاهزة.
 * - SQLite افتراضيًا (لا يحتاج تثبيت أي برنامج).
 * - يمكن التبديل إلى MySQL عبر .env فقط.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $driver = Env::get('DB_DRIVER', 'sqlite');

        if ($driver === 'mysql') {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                Env::get('DB_HOST', '127.0.0.1'),
                Env::get('DB_PORT', '3306'),
                Env::get('DB_NAME', 'libyan_store')
            );
            self::$pdo = new PDO($dsn, Env::get('DB_USER', 'root'), Env::get('DB_PASSWORD', ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } else {
            $path = BASE_PATH . '/' . Env::get('DB_DATABASE', 'database/database.sqlite');
            $dir = dirname($path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            self::$pdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::$pdo->exec('PRAGMA foreign_keys = ON');
            self::$pdo->exec('PRAGMA journal_mode = WAL');
        }

        return self::$pdo;
    }

    /** تنفيذ استعلام استعدادي (مع الحماية من حقن SQL) */
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /** @return array<string,mixed>|null */
    public static function first(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function scalar(string $sql, array $params = []): mixed
    {
        return self::run($sql, $params)->fetchColumn();
    }

    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', array_fill(0, count($columns), '?'))
        );

        self::run($sql, array_values($data));

        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = implode(', ', array_map(static fn (string $c): string => $c . ' = ?', array_keys($data)));
        $stmt = self::run(
            sprintf('UPDATE %s SET %s WHERE %s', $table, $sets, $where),
            array_merge(array_values($data), $whereParams)
        );

        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        return self::run(sprintf('DELETE FROM %s WHERE %s', $table, $where), $params)->rowCount();
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}