<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

class SnapRepository
{
    public function __construct(public PDO $pdo)
    {
    }

    public function schema(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS guest_snap (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            upload_key CHAR(64) NOT NULL UNIQUE,
            batch_key CHAR(64) NOT NULL,
            drive_id VARCHAR(128) NOT NULL UNIQUE,
            folder_id VARCHAR(128) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            stored_name VARCHAR(100) NOT NULL,
            mime VARCHAR(50) NOT NULL,
            bytes BIGINT UNSIGNED NOT NULL,
            sha256 CHAR(64) NOT NULL,
            guest_name VARCHAR(20) NOT NULL DEFAULT '',
            guest_phone VARCHAR(15) NOT NULL DEFAULT '',
            status VARCHAR(16) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            uploaded_at DATETIME NULL,
            checked_at DATETIME NULL,
            KEY (batch_key), KEY (status, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (!$this->pdo->query("SHOW COLUMNS FROM guest_snap LIKE 'guest_phone'")->fetch()) {
            $this->pdo->exec("ALTER TABLE guest_snap ADD guest_phone VARCHAR(15) NOT NULL DEFAULT '' AFTER guest_name");
        }
        if (!$this->pdo->query("SHOW COLUMNS FROM guest_snap LIKE 'checked_at'")->fetch()) {
            $this->pdo->exec('ALTER TABLE guest_snap ADD checked_at DATETIME NULL');
        }
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS guest_snap_rate (bucket CHAR(64) PRIMARY KEY, hits INT UNSIGNED NOT NULL, expires_at BIGINT NOT NULL) ENGINE=InnoDB");
    }

    public function rate(string $bucket, int $limit): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO guest_snap_rate (bucket, hits, expires_at)
             VALUES (?, 1, ?)
             ON DUPLICATE KEY UPDATE hits = hits + 1'
        );
        $statement->execute([$bucket, time() + 172800]);

        $statement = $this->pdo->prepare('SELECT hits FROM guest_snap_rate WHERE bucket = ?');
        $statement->execute([$bucket]);

        if ((int) $statement->fetchColumn() > $limit) {
            throw new OverflowException('요청이 많습니다. 잠시 후 다시 시도해주세요.');
        }
    }

    public function find(string $uploadKey): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM guest_snap WHERE upload_key = ?');
        $statement->execute([$uploadKey]);

        return $statement->fetch() ?: null;
    }

    public function reserve(array $record): array
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM guest_snap WHERE batch_key = ?');
        $statement->execute([$record['batch_key']]);

        if ((int) $statement->fetchColumn() >= SNAP_MAX_FILES) {
            throw new InvalidArgumentException('한 번에 최대 ' . SNAP_MAX_FILES . '장까지 가능합니다.');
        }

        $columns = implode(', ', array_keys($record));
        $placeholders = implode(', ', array_fill(0, count($record), '?'));
        $statement = $this->pdo->prepare("INSERT INTO guest_snap ($columns) VALUES ($placeholders)");
        $statement->execute(array_values($record));

        return $this->find($record['upload_key']);
    }

    public function complete(string $uploadKey): void
    {
        $statement = $this->pdo->prepare(
            "UPDATE guest_snap
             SET status = 'complete', uploaded_at = COALESCE(uploaded_at, UTC_TIMESTAMP())
             WHERE upload_key = ?"
        );
        $statement->execute([$uploadKey]);
    }
}
