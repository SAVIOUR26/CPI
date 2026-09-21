<?php

namespace App\Core;

use PDO;

/**
 * Lightweight active-record-ish base. Every query is a prepared statement —
 * no raw string interpolation of caller-supplied values anywhere.
 */
abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public static function find(int|string $id): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM ' . static::table() . ' WHERE ' . static::$primaryKey . ' = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findOrFail(int|string $id): array
    {
        $row = static::find($id);
        if (!$row) {
            throw new \RuntimeException('Record not found in ' . static::table());
        }
        return $row;
    }

    public static function all(string $orderBy = ''): array
    {
        $sql = 'SELECT * FROM ' . static::table();
        if ($orderBy) {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        return static::db()->query($sql)->fetchAll();
    }

    public static function where(string $column, mixed $operatorOrValue, mixed $value = null): array
    {
        [$operator, $value] = func_num_args() === 2 ? ['=', $operatorOrValue] : [$operatorOrValue, $value];
        $stmt = static::db()->prepare('SELECT * FROM ' . static::table() . " WHERE $column $operator ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    public static function first(string $column, mixed $operatorOrValue, mixed $value = null): ?array
    {
        $rows = func_num_args() === 2 ? static::where($column, $operatorOrValue) : static::where($column, $operatorOrValue, $value);
        return $rows[0] ?? null;
    }

    public static function query(string $sql, array $params = []): array
    {
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function statement(string $sql, array $params = []): \PDOStatement
    {
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $sql = 'INSERT INTO ' . static::table() . ' (' . implode(',', $columns) . ') VALUES (' . $placeholders . ')';
        $stmt = static::db()->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) static::db()->lastInsertId();
    }

    public static function update(int|string $id, array $data): bool
    {
        $set = implode(',', array_map(fn($c) => "$c = ?", array_keys($data)));
        $sql = 'UPDATE ' . static::table() . " SET $set WHERE " . static::$primaryKey . ' = ?';
        $stmt = static::db()->prepare($sql);
        return $stmt->execute([...array_values($data), $id]);
    }

    public static function delete(int|string $id): bool
    {
        $stmt = static::db()->prepare('DELETE FROM ' . static::table() . ' WHERE ' . static::$primaryKey . ' = ?');
        return $stmt->execute([$id]);
    }

    public static function count(string $where = '1', array $params = []): int
    {
        $stmt = static::db()->prepare('SELECT COUNT(*) c FROM ' . static::table() . " WHERE $where");
        $stmt->execute($params);
        return (int) $stmt->fetch()['c'];
    }

    protected static function table(): string
    {
        if (static::$table === '') {
            throw new \RuntimeException('Model table not set for ' . static::class);
        }
        return static::$table;
    }
}
