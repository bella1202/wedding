<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/database.php';

final class GuestbookRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? getPdo();
        ensureGuestbookSchema($this->pdo);
    }

    public function list(int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, message, created_at
             FROM guestbook
             ORDER BY id DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM guestbook')->fetchColumn();
    }

    public function create(string $name, string $message, string $passwordHash, ?string $ipHash = null): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO guestbook (name, message, password_hash, ip_hash)
             VALUES (:name, :message, :password_hash, :ip_hash)'
        );
        $stmt->execute([
            ':name' => $name,
            ':message' => $message,
            ':password_hash' => $passwordHash,
            ':ip_hash' => $ipHash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, message, password_hash, created_at
             FROM guestbook
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM guestbook WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
