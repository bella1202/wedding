<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/Upload.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$pdo = null;
$locked = false;
$errorCode = 'invalid_request';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $errorCode = 'method_not_allowed';
        http_response_code(405);
        header('Allow: POST');
        throw new InvalidArgumentException('POST 요청만 가능합니다.');
    }

    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > SNAP_MAX_BYTES + 1024 * 1024) {
        $errorCode = 'file_too_large';
        http_response_code(413);
        throw new InvalidArgumentException('사진은 장당 15MB 이하로 선택해주세요.');
    }

    snapSession();

    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['snap_csrf'], $_POST['csrf'])) {
        $errorCode = 'session_expired';
        http_response_code(403);
        throw new InvalidArgumentException('페이지를 새로고침한 뒤 다시 시도해주세요.');
    }

    $sessionId = session_id();
    session_write_close();

    $weddingData = require dirname(__DIR__, 2) . '/includes/data.php';

    if (env('GUEST_SNAP_ENABLED', '0') !== '1' || !guestSnapCanUpload($weddingData['wedding'])) {
        $errorCode = 'upload_closed';
        http_response_code(403);
        throw new InvalidArgumentException('현재 사진 업로드가 열려 있지 않습니다.');
    }

    foreach (['batch', 'item'] as $field) {
        if (!is_string($_POST[$field] ?? null) || !preg_match('/^[a-f0-9]{32}$/D', $_POST[$field])) {
            throw new InvalidArgumentException('잘못된 업로드 요청입니다.');
        }
    }

    $contact = snapContact($_POST['name'] ?? null, $_POST['phone'] ?? null);

    $photo = $_FILES['photo'] ?? null;

    if (count($_FILES) !== 1 || !is_array($photo) || !is_string($photo['tmp_name'] ?? null) || !is_string($photo['name'] ?? null)) {
        throw new InvalidArgumentException('사진 한 장씩 전송해주세요.');
    }

    if (in_array($photo['error'] ?? null, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        $errorCode = 'file_too_large';
        http_response_code(413);
        throw new InvalidArgumentException('사진은 장당 15MB 이하로 선택해주세요.');
    }

    if (($photo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        snapValidate($photo);
    }

    if (!is_uploaded_file($photo['tmp_name'])) {
        throw new InvalidArgumentException('사진 수신을 확인할 수 없습니다.');
    }

    foreach ([
        'APP_KEY',
        'GUEST_SNAP_GOOGLE_CLIENT_ID',
        'GUEST_SNAP_GOOGLE_CLIENT_SECRET',
        'GUEST_SNAP_GOOGLE_REFRESH_TOKEN',
        'GUEST_SNAP_GOOGLE_FOLDER_ID',
    ] as $requiredSetting) {
        snapSecret($requiredSetting);
    }

    $pdo = getPdo();
    $repository = new SnapRepository($pdo);

    // Serialize workers to bound memory use and protect the Drive quota.
    $locked = (int) $pdo->query("SELECT GET_LOCK('guest_snap_upload', 0)")->fetchColumn() === 1;

    if (!$locked) {
        throw new OverflowException('다른 사진을 처리하고 있습니다. 잠시 후 다시 시도해주세요.');
    }

    $secret = snapSecret('APP_KEY');
    $ipHash = hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? '', $secret);
    $repository->rate(hash('sha256', 'ip:' . $ipHash . ':' . gmdate('YmdH')), 120);
    $repository->rate(hash('sha256', 'global:' . gmdate('Ymd')), 2000);

    $metadata = snapValidate($photo);
    $batchIdentity = $sessionId . ':' . $_POST['batch'];
    $uploadKey = hash_hmac('sha256', $batchIdentity . ':' . $_POST['item'], $secret);
    $batchKey = hash_hmac('sha256', $batchIdentity, $secret);

    snapStore(
        $repository,
        new SnapDrive(),
        $metadata,
        $uploadKey,
        $batchKey,
        $contact['name'],
        $contact['phone'],
        $photo['tmp_name']
    );

    echo json_encode([
        'ok' => true,
        'code' => 'uploaded',
        'message' => '사진이 비공개로 저장되었습니다.',
    ], JSON_UNESCAPED_UNICODE);
} catch (InvalidArgumentException $exception) {
    if (http_response_code() < 400) {
        http_response_code(422);
    }

    echo json_encode([
        'ok' => false,
        'code' => $errorCode,
        'message' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
} catch (OverflowException $exception) {
    http_response_code(429);
    header('Retry-After: 30');

    echo json_encode([
        'ok' => false,
        'code' => 'rate_limited',
        'retryAfter' => 30,
        'message' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log('Guest Snap failure: ' . get_class($exception));
    http_response_code(503);

    echo json_encode([
        'ok' => false,
        'code' => 'storage_unavailable',
        'message' => '저장을 확인하지 못했습니다. 같은 화면에서 다시 시도해주세요.',
    ], JSON_UNESCAPED_UNICODE);
} finally {
    if ($locked && $pdo) {
        try {
            $pdo->query("SELECT RELEASE_LOCK('guest_snap_upload')");
        } catch (Throwable $ignored) {
            // A lost database connection releases its advisory lock automatically.
        }
    }
}
