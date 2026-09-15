<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

try {
    $limit = max(1, min(20, (int) ($_GET['limit'] ?? 10)));
    $offset = max(0, (int) ($_GET['offset'] ?? 0));

    $repo = new GuestbookRepository();
    $items = $repo->list($limit, $offset);
    $total = $repo->count();

    jsonResponse([
        'success' => true,
        'items' => $items,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset,
        'hasMore' => ($offset + count($items)) < $total,
    ]);
} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'error' => '방명록을 불러오지 못했습니다.',
        'detail' => $e->getMessage(),
    ], 500);
}
