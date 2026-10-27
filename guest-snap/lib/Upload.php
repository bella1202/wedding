<?php

declare(strict_types=1);

require_once __DIR__ . '/Drive.php';
require_once __DIR__ . '/Repository.php';

/** Caller must hold the database advisory lock until completion. */
function snapStore(
    SnapRepository $repository,
    SnapDrive $drive,
    array $metadata,
    string $uploadKey,
    string $batchKey,
    string $guestName,
    string $guestPhone,
    string $filePath
): void {
    $record = $repository->find($uploadKey);

    if ($record && $record['sha256'] !== $metadata['sha256']) {
        throw new InvalidArgumentException('재시도 사진이 변경되었습니다. 다시 선택해주세요.');
    }

    if ($record && ($record['guest_name'] !== $guestName || $record['guest_phone'] !== $guestPhone)) {
        throw new InvalidArgumentException('재시도할 때는 처음 입력한 성함과 전화번호를 유지해주세요.');
    }

    if (!$record) {
        $drive->checkFolder();

        // Persist the Drive ID before uploading so interrupted requests can recover.
        $record = $repository->reserve([
            'upload_key' => $uploadKey,
            'batch_key' => $batchKey,
            'drive_id' => $drive->allocate(),
            'folder_id' => snapSecret('GUEST_SNAP_GOOGLE_FOLDER_ID'),
            'original_name' => $metadata['original_name'],
            'stored_name' => snapStoredName($guestName, $guestPhone, $metadata['extension']),
            'mime' => $metadata['mime'],
            'bytes' => $metadata['bytes'],
            'sha256' => $metadata['sha256'],
            'guest_name' => $guestName,
            'guest_phone' => $guestPhone,
        ]);
    }

    if ($record['status'] === 'complete') {
        return;
    }

    $drive->checkFolder();

    if ($record['folder_id'] !== snapSecret('GUEST_SNAP_GOOGLE_FOLDER_ID')) {
        throw new RuntimeException('Folder configuration changed');
    }

    $drive->upload($record, $filePath);
    $repository->complete($uploadKey);
}
