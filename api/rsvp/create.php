<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $body = readJsonBody();

    if (!empty($body['website'])) {
        jsonResponse(['success' => false, 'error' => '요청이 거부되었습니다.'], 400);
    }

    $name = trim((string) ($body['name'] ?? ''));
    $side = trim((string) ($body['side'] ?? ''));
    $attending = trim((string) ($body['attending'] ?? ''));
    $guests = (int) preg_replace('/\D+/', '', (string) ($body['guests'] ?? '0'));
    $meal = trim((string) ($body['meal'] ?? ''));
    $bus = trim((string) ($body['bus'] ?? ''));
    $message = trim((string) ($body['message'] ?? ''));
    $privacy = trim((string) ($body['privacy'] ?? ''));

    if ($privacy !== '1') {
        jsonResponse(['success' => false, 'error' => '개인정보 수집 및 활용에 동의해주세요.'], 422);
    }

    if ($name === '' || mb_strlen($name) > 20) {
        jsonResponse(['success' => false, 'error' => '성함은 1~20자로 입력해주세요.'], 422);
    }

    if (!in_array($side, ['groom', 'bride'], true)) {
        jsonResponse(['success' => false, 'error' => '신랑측/신부측을 선택해주세요.'], 422);
    }

    if (!in_array($attending, ['yes', 'no'], true)) {
        jsonResponse(['success' => false, 'error' => '참석 여부를 선택해주세요.'], 422);
    }

    $isAttend = $attending === 'yes' ? 1 : 0;
    $isMeal = 0;
    $isBus = 0;

    if ($isAttend !== 1) {
        $guests = 0;
    } else {
        if ($guests < 1 || $guests > 20) {
            jsonResponse(['success' => false, 'error' => '참석 인원은 1~20명으로 입력해주세요.'], 422);
        }
        if ($meal === 'yes') {
            $isMeal = 1;
        } elseif ($meal === 'maybe') {
            $isMeal = 2;
        } elseif ($meal === 'no') {
            $isMeal = 0;
        } else {
            jsonResponse(['success' => false, 'error' => '식사 여부를 선택해주세요.'], 422);
        }
        if ($bus === 'yes') {
            $isBus = 1;
        } elseif ($bus === 'no') {
            $isBus = 0;
        } else {
            jsonResponse(['success' => false, 'error' => '전세버스 탑승 여부를 선택해주세요.'], 422);
        }
    }

    if (mb_strlen($message) > 300) {
        jsonResponse(['success' => false, 'error' => '메시지는 300자 이내로 입력해주세요.'], 422);
    }

    $repo = new RsvpRepository();
    $id = $repo->create(
        $name,
        $side,
        $isAttend,
        $guests,
        $isMeal,
        $isBus,
        $message !== '' ? $message : null,
        clientIpHash()
    );

    $item = $repo->findById($id);

    jsonResponse([
        'success' => true,
        'item' => $item,
    ], 201);
} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'error' => '참석 의사를 저장하지 못했습니다.',
        'detail' => $e->getMessage(),
    ], 500);
}
