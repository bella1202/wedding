<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/database.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

const SNAP_MAX_FILES = 20;
const SNAP_MAX_BYTES = 15 * 1024 * 1024;
const SNAP_MAX_PIXELS = 24_000_000;
const SNAP_IMAGE_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

function snapSecret(string $key): string
{
    $value = env($key, '');

    if ($value === '') {
        throw new RuntimeException('Missing configuration: ' . $key);
    }

    return $value;
}

function snapSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Strict',
        'path' => '/',
    ]);
    session_start();

    $_SESSION['snap_csrf'] ??= bin2hex(random_bytes(32));
}

/** Validate the actual image bytes, independently of the browser's MIME type. */
function snapValidate(array $file): array
{
    $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if (is_array($uploadError)) {
        throw new InvalidArgumentException('잘못된 파일 요청입니다.');
    }

    if (in_array($uploadError, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        throw new InvalidArgumentException('사진은 장당 15MB 이하로 선택해주세요.');
    }

    if ($uploadError !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('파일 수신에 실패했습니다. 다시 선택해주세요.');
    }

    $filePath = $file['tmp_name'];
    $fileSize = filesize($filePath);

    if (!$fileSize || $fileSize > SNAP_MAX_BYTES) {
        throw new InvalidArgumentException('사진은 장당 15MB 이하로 선택해주세요.');
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($filePath);

    if (!isset(SNAP_IMAGE_TYPES[$mimeType])) {
        throw new InvalidArgumentException('JPEG, PNG, WebP 사진만 가능합니다. HEIC는 JPEG로 변환해주세요.');
    }

    $dimensions = @getimagesize($filePath);

    if (
        !$dimensions
        || $dimensions['mime'] !== $mimeType
        || $dimensions[0] * $dimensions[1] > SNAP_MAX_PIXELS
    ) {
        throw new InvalidArgumentException('유효한 2,400만 화소 이하 사진을 선택해주세요.');
    }

    $image = @imagecreatefromstring(file_get_contents($filePath));

    if ($image === false) {
        throw new InvalidArgumentException('손상된 사진입니다. 다른 사진을 선택해주세요.');
    }

    imagedestroy($image);

    $originalName = basename(str_replace('\\', '/', (string) $file['name']));
    $originalName = preg_replace('/[\x00-\x1f\x7f]/u', '', $originalName);

    if ($originalName === null || !mb_check_encoding($originalName, 'UTF-8')) {
        throw new InvalidArgumentException('파일명을 확인해주세요.');
    }

    return [
        'original_name' => mb_substr($originalName, 0, 255),
        'mime' => $mimeType,
        'bytes' => $fileSize,
        'sha256' => hash_file('sha256', $filePath),
        'extension' => SNAP_IMAGE_TYPES[$mimeType],
    ];
}

/** Normalize contact details before persisting them or constructing a filename. */
function snapContact(mixed $name, mixed $phone): array
{
    if (!is_string($name) || !mb_check_encoding($name, 'UTF-8')) {
        throw new InvalidArgumentException('성함을 1~20자로 입력해주세요.');
    }

    $name = trim($name);

    if (!preg_match("/^[\p{L}\p{M}\p{N} .’'_-]{1,20}$/uD", $name) || !preg_match('/[\p{L}\p{N}]/u', $name)) {
        throw new InvalidArgumentException('성함을 1~20자로 입력해주세요. 특수문자는 사용할 수 없습니다.');
    }

    if (!is_string($phone) || strlen($phone) > 30 || !preg_match('/^\+?[0-9() \-]+$/D', trim($phone))) {
        throw new InvalidArgumentException('올바른 전화번호를 입력해주세요.');
    }

    $phone = preg_replace('/[^0-9]/', '', $phone);

    if (!preg_match('/^[0-9]{8,15}$/D', $phone)) {
        throw new InvalidArgumentException('전화번호는 숫자 8~15자리로 입력해주세요.');
    }

    return ['name' => $name, 'phone' => $phone];
}

function snapStoredName(string $name, string $phone, string $extension): string
{
    $safeName = preg_replace('/[^\p{L}\p{M}\p{N}_-]/u', '_', $name);

    return $safeName . '_' . $phone . '_' . gmdate('Ymd_His')
        . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
}
