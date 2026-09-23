<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $driver = Env::get('DB_DRIVER', 'mysql');
            $host = Env::get('DB_HOST', '127.0.0.1');
            $port = Env::get('DB_PORT', '3306');
            $name = Env::get('DB_DATABASE', 'cpi');
            $user = Env::get('DB_USERNAME', 'root');
            $pass = Env::get('DB_PASSWORD', '');
            $charset = Env::get('DB_CHARSET', 'utf8mb4');

            $dsn = "$driver:host=$host;port=$port;dbname=$name;charset=$charset";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                // Keep NOW()/CURRENT_TIMESTAMP in the app's timezone (APP_TIMEZONE), not the database server's.
                self::$instance->exec("SET time_zone = '" . date('P') . "'");
            } catch (PDOException $e) {
                if (Env::get('APP_DEBUG', false)) {
                    throw $e;
                }
                http_response_code(500);
                echo 'Service temporarily unavailable. Please try again shortly.';
                exit;
            }
        }

        return self::$instance;
    }
}
