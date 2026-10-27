<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

class SnapDrive
{
    private ?string $token = null;

    public static function http(
        string $method,
        string $url,
        array $headers = [],
        ?string $body = null
    ): array {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        ]);

        if ($body !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException('Drive network failure');
        }

        return [$status, json_decode($response, true) ?? []];
    }

    private function refreshToken(): string
    {
        [$status, $data] = self::http(
            'POST',
            'https://oauth2.googleapis.com/token',
            ['Content-Type: application/x-www-form-urlencoded'],
            http_build_query([
                'client_id' => snapSecret('GUEST_SNAP_GOOGLE_CLIENT_ID'),
                'client_secret' => snapSecret('GUEST_SNAP_GOOGLE_CLIENT_SECRET'),
                'refresh_token' => snapSecret('GUEST_SNAP_GOOGLE_REFRESH_TOKEN'),
                'grant_type' => 'refresh_token',
            ])
        );

        if ($status !== 200 || empty($data['access_token'])) {
            throw new RuntimeException('Drive token refresh failed');
        }

        return $data['access_token'];
    }

    public function request(
        string $method,
        string $path,
        ?array $json = null,
        ?string $body = null,
        ?string $contentType = null
    ): array {
        if ($json !== null) {
            $body = json_encode($json, JSON_THROW_ON_ERROR);
            $contentType = 'application/json';
        }

        // Refresh once if Google rejects a cached access token.
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->token ??= $this->refreshToken();
            $headers = ['Authorization: Bearer ' . $this->token];

            if ($contentType) {
                $headers[] = 'Content-Type: ' . $contentType;
            }

            [$status, $data] = self::http($method, 'https://www.googleapis.com/' . $path, $headers, $body);

            if ($status !== 401) {
                return [$status, $data];
            }

            $this->token = null;
        }

        throw new RuntimeException('Drive authorization failed');
    }

    public function allocate(): string
    {
        [$status, $data] = $this->request('GET', 'drive/v3/files/generateIds?count=1&space=drive&type=files');

        if ($status !== 200 || empty($data['ids'][0])) {
            throw new RuntimeException('Drive ID allocation failed');
        }

        return $data['ids'][0];
    }

    public function checkFolder(): void
    {
        $folderId = rawurlencode(snapSecret('GUEST_SNAP_GOOGLE_FOLDER_ID'));
        [$status, $folder] = $this->request(
            'GET',
            "drive/v3/files/$folderId?fields=mimeType,trashed,capabilities(canAddChildren),permissions(type,role)"
        );

        if (
            $status !== 200
            || ($folder['mimeType'] ?? '') !== 'application/vnd.google-apps.folder'
            || !empty($folder['trashed'])
            || empty($folder['capabilities']['canAddChildren'])
        ) {
            throw new RuntimeException('Drive folder unavailable');
        }

        foreach ($folder['permissions'] ?? [] as $permission) {
            if (($permission['role'] ?? '') !== 'owner') {
                throw new RuntimeException('Drive folder must be private and unshared');
            }
        }

        if (empty($folder['permissions'])) {
            throw new RuntimeException('Cannot verify folder privacy');
        }
    }

    public function exists(array $record): bool
    {
        $fileId = rawurlencode($record['drive_id']);
        [$status, $file] = $this->request(
            'GET',
            "drive/v3/files/$fileId?fields=id,size,mimeType,trashed,appProperties,parents"
        );

        if ($status === 404) {
            return false;
        }

        if (
            $status !== 200
            || !empty($file['trashed'])
            || ($file['appProperties']['sha256'] ?? '') !== $record['sha256']
            || (int) ($file['size'] ?? 0) !== (int) $record['bytes']
            || ($file['mimeType'] ?? '') !== $record['mime']
            || !in_array($record['folder_id'], $file['parents'] ?? [], true)
        ) {
            throw new RuntimeException('Drive verification failed');
        }

        return true;
    }

    public function upload(array $record, string $filePath): void
    {
        if ($this->exists($record)) {
            return;
        }

        $boundary = 'snap_' . bin2hex(random_bytes(20));
        $metadata = [
            'id' => $record['drive_id'],
            'name' => $record['stored_name'],
            'parents' => [$record['folder_id']],
            'mimeType' => $record['mime'],
            'appProperties' => ['sha256' => $record['sha256']],
        ];
        $body = "--$boundary\r\nContent-Type: application/json; charset=UTF-8\r\n\r\n"
            . json_encode($metadata, JSON_THROW_ON_ERROR)
            . "\r\n--$boundary\r\nContent-Type: {$record['mime']}\r\n\r\n"
            . file_get_contents($filePath)
            . "\r\n--$boundary--\r\n";

        [$status] = $this->request(
            'POST',
            'upload/drive/v3/files?uploadType=multipart&fields=id',
            null,
            $body,
            'multipart/related; boundary=' . $boundary
        );

        if (!in_array($status, [200, 201, 409], true) || !$this->exists($record)) {
            throw new RuntimeException('Drive upload failed');
        }
    }
}
