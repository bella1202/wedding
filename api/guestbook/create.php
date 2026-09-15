<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $body = readJsonBody();

    // honeypot
    if (!empty($body['website'])) {
        jsonResponse(['success' => false, 'error' => '요청이 거부되었습니다.'], 400);
    }

    $name = trim((string) ($body['name'] ?? ''));
    $message = trim((string) ($body['message'] ?? ''));
    $password = (string) ($body['password'] ?? '');

    if ($name === '' || mb_strlen($name) > 20) {
        jsonResponse(['success' => false, 'error' => '이름은 1~20자로 입력해주세요.'], 422);
    }

    if ($message === '' || mb_strlen($message) > 300) {
        jsonResponse(['success' => false, 'error' => '메시지는 1~300자로 입력해주세요.'], 422);
    }

    if (mb_strlen($password) < 4) {
        jsonResponse(['success' => false, 'error' => '비밀번호는 4자 이상 입력해주세요.'], 422);
    }

    $repo = new GuestbookRepository();
    $id = $repo->create(
        $name,
        $message,
        password_hash($password, PASSWORD_DEFAULT),
        clientIpHash()
    );

    $item = $repo->findById($id);
    unset($item['password_hash']);

    jsonResponse([
        'success' => true,
        'item' => $item,
    ], 201);
} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'error' => '방명록을 저장하지 못했습니다.',
        'detail' => $e->getMessage(),
    ], 500);
}
