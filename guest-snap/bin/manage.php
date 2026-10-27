<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/lib/Drive.php';
require_once dirname(__DIR__) . '/lib/Repository.php';

try {
    $repository = new SnapRepository(getPdo());
    $command = $argv[1] ?? '';

    if ($command === 'init') {
        $repository->schema();
        echo "Guest Snap tables ready.\n";
        exit;
    }

    if ($command !== 'reconcile') {
        throw new RuntimeException('Usage: php guest-snap/bin/manage.php init|reconcile');
    }

    $locked = (int) $repository->pdo->query("SELECT GET_LOCK('guest_snap_upload', 0)")->fetchColumn() === 1;

    if (!$locked) {
        throw new RuntimeException('Upload active; retry later');
    }

    try {
        $drive = new SnapDrive();
        $records = $repository->pdo->query(
            "SELECT * FROM guest_snap
             WHERE status = 'pending'
             ORDER BY COALESCE(checked_at, created_at), id
             LIMIT 100"
        )->fetchAll();
        $markChecked = $repository->pdo->prepare('UPDATE guest_snap SET checked_at = UTC_TIMESTAMP() WHERE id = ?');

        foreach ($records as $record) {
            $markChecked->execute([$record['id']]);

            if ($drive->exists($record)) {
                $repository->complete($record['upload_key']);
                echo "Recovered record {$record['id']}\n";
            } else {
                echo "Record {$record['id']}: waiting for guest retry (no Drive file).\n";
            }
        }

        $repository->pdo->exec('DELETE FROM guest_snap_rate WHERE expires_at < UNIX_TIMESTAMP()');
    } finally {
        $repository->pdo->query("SELECT RELEASE_LOCK('guest_snap_upload')");
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Guest Snap maintenance failed: ' . $exception->getMessage() . "\n");
    exit(1);
}
