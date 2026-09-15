<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST' && $method !== 'DELETE') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $body = readJsonBody();

    if (!empty($body['website'])) {
        jsonResponse(['success' => false, 'error' => '요청이 거부되었습니다.'], 400);
    }

    $id = (int) ($body['id'] ?? 0);
    $password = (string) ($body['password'] ?? '');

    if ($id <= 0) {
        jsonResponse(['success' => false, 'error' => '잘못된 요청입니다.'], 422);
    }

    if (mb_strlen($password) < 4) {
        jsonResponse(['success' => false, 'error' => '비밀번호는 4자 이상 입력해주세요.'], 422);
    }

    $repo = new GuestbookRepository();
    $row = $repo->findById($id);

    if ($row === null) {
        jsonResponse(['success' => false, 'error' => '메시지를 찾을 수 없습니다.'], 404);
    }

    if (!password_verify($password, $row['password_hash'])) {
        jsonResponse(['success' => false, 'error' => '비밀번호가 일치하지 않습니다.'], 403);
    }

    $repo->delete($id);

    jsonResponse(['success' => true]);
} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'error' => '삭제에 실패했습니다.',
        'detail' => $e->getMessage(),
    ], 500);
}
