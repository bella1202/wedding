<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/database.php';

final class RsvpRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? getPdo();
        ensureRsvpSchema($this->pdo);
    }

    public function create(
        string $name,
        string $side,
        int $isAttend,
        int $guests,
        int $isMeal,
        int $isBus,
        ?string $message,
        ?string $ipHash = null
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO rsvp (name, side, is_attend, guests, is_meal, is_bus, message, ip_hash)
             VALUES (:name, :side, :is_attend, :guests, :is_meal, :is_bus, :message, :ip_hash)'
        );
        $stmt->execute([
            ':name' => $name,
            ':side' => $side,
            ':is_attend' => $isAttend,
            ':guests' => $guests,
            ':is_meal' => $isMeal,
            ':is_bus' => $isBus,
            ':message' => $message,
            ':ip_hash' => $ipHash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, side, is_attend, guests, is_meal, is_bus, message, created_at
             FROM rsvp
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
