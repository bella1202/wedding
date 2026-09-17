<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

function dbConfig(): array
{
    return [
        'driver' => env('DB_CONNECTION', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'wedding'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'socket' => env('DB_SOCKET', ''),
    ];
}

function resolveMysqlSocket(string $configured): string
{
    if ($configured !== '' && file_exists($configured)) {
        return $configured;
    }

    $candidates = [
        '/tmp/mysql.sock',
        '/opt/homebrew/var/mysql/mysql.sock',
        '/var/run/mysqld/mysqld.sock',
        '/Applications/MAMP/tmp/mysql/mysql.sock',
    ];

    foreach ($candidates as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }

    return '';
}

function getPdo(?string $database = null): PDO
{
    static $pool = [];

    $config = dbConfig();
    $driver = strtolower((string) $config['driver']);

    if ($driver !== 'mysql') {
        throw new RuntimeException('Only mysql is supported. Set DB_CONNECTION=mysql in .env');
    }

    if (!extension_loaded('pdo_mysql')) {
        throw new RuntimeException(
            'PDO MySQL extension is required. Enable pdo_mysql in PHP. (php -m | grep pdo_mysql)'
        );
    }

    $dbName = $database ?? (string) $config['database'];
    $socket = resolveMysqlSocket((string) ($config['socket'] ?? ''));
    $key = $config['host'] . ':' . $config['port'] . '/' . $dbName . '/' . $socket;

    if (isset($pool[$key]) && $pool[$key] instanceof PDO) {
        return $pool[$key];
    }

    $host = (string) $config['host'];

    if ($socket !== '' && ($host === 'localhost' || $host === '127.0.0.1')) {
        $dsn = sprintf('mysql:unix_socket=%s;charset=%s', $socket, $config['charset']);
    } else {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;charset=%s',
            $host,
            $config['port'],
            $config['charset']
        );
    }

    if ($dbName !== '') {
        $dsn .= ';dbname=' . $dbName;
    }

    $pdo = new PDO($dsn, (string) $config['username'], (string) $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $pool[$key] = $pdo;

    return $pdo;
}

function ensureWeddingDatabase(): void
{
    $config = dbConfig();
    $database = (string) $config['database'];

    if ($database === '') {
        throw new RuntimeException('DB_DATABASE is empty');
    }

    $pdo = getPdo('');
    $safeName = str_replace('`', '``', $database);
    $pdo->exec(
        "CREATE DATABASE IF NOT EXISTS `{$safeName}`
         DEFAULT CHARACTER SET utf8mb4
         COLLATE utf8mb4_unicode_ci"
    );
}

function ensureGuestbookSchema(?PDO $pdo = null): void
{
    $pdo ??= getPdo();

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS guestbook (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(50) NOT NULL,
            message VARCHAR(500) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ip_hash VARCHAR(64) NULL,
            PRIMARY KEY (id),
            KEY idx_guestbook_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function ensureRsvpSchema(?PDO $pdo = null): void
{
    $pdo ??= getPdo();

    $needsRebuild = false;
    try {
        $cols = $pdo->query('SHOW COLUMNS FROM rsvp')->fetchAll(PDO::FETCH_ASSOC);
        $fields = [];
        foreach ($cols as $col) {
            $fields[$col['Field'] ?? ''] = true;
        }
        if (isset($fields['attending']) || isset($fields['meal']) || isset($fields['bus'])) {
            $needsRebuild = true;
        }
        if (!isset($fields['is_attend']) || !isset($fields['is_meal']) || !isset($fields['is_bus'])) {
            $needsRebuild = true;
        }
    } catch (Throwable $e) {
        $needsRebuild = false;
    }

    if ($needsRebuild) {
        $pdo->exec('DROP TABLE IF EXISTS rsvp');
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS rsvp (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(50) NOT NULL,
            side ENUM('groom','bride') NOT NULL,
            is_attend TINYINT(1) NOT NULL DEFAULT 0,
            guests TINYINT UNSIGNED NOT NULL DEFAULT 0,
            is_meal TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=no, 1=yes, 2=maybe',
            is_bus TINYINT(1) NOT NULL DEFAULT 0,
            message VARCHAR(500) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ip_hash VARCHAR(64) NULL,
            PRIMARY KEY (id),
            KEY idx_rsvp_created_at (created_at),
            KEY idx_rsvp_is_attend (is_attend)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}
