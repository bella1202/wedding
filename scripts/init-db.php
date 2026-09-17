<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/database.php';

try {
    $config = dbConfig();
    ensureWeddingDatabase();
    $pdo = getPdo();
    ensureGuestbookSchema($pdo);
    ensureRsvpSchema($pdo);

    echo 'OK: MySQL connected' . PHP_EOL;
    echo '  host: ' . $config['host'] . ':' . $config['port'] . PHP_EOL;
    echo '  database: ' . $config['database'] . PHP_EOL;
    echo '  tables: guestbook, rsvp' . PHP_EOL;
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
