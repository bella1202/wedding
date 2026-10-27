<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/lib/Drive.php';

// Credentials and tokens stay in the operator's terminal, never the wedding website.
try {
    $redirectUri = 'http://127.0.0.1:8765/callback';
    $server = stream_socket_server('tcp://127.0.0.1:8765', $errorNumber, $errorMessage);

    if (!$server) {
        throw new RuntimeException('Cannot open local OAuth callback');
    }

    $state = bin2hex(random_bytes(32));
    $verifier = bin2hex(random_bytes(32));
    $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    $parameters = [
        'client_id' => snapSecret('GUEST_SNAP_GOOGLE_CLIENT_ID'),
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'https://www.googleapis.com/auth/drive.file',
        'access_type' => 'offline',
        'prompt' => 'consent',
        'state' => $state,
        'code_challenge' => $challenge,
        'code_challenge_method' => 'S256',
    ];

    echo "Open on THIS computer within 5 minutes:\nhttps://accounts.google.com/o/oauth2/v2/auth?"
        . http_build_query($parameters) . "\n";

    $deadline = time() + 300;
    $code = null;

    while (time() < $deadline && $code === null) {
        $client = @stream_socket_accept($server, min(30, max(1, $deadline - time())));

        if (!$client) {
            continue;
        }

        stream_set_timeout($client, 5);
        $requestLine = fgets($client, 8192) ?: '';
        preg_match('~^GET (/callback\?\S+) HTTP/~', $requestLine, $matches);
        parse_str(parse_url($matches[1] ?? '', PHP_URL_QUERY) ?? '', $query);

        $valid = is_string($query['state'] ?? null)
            && hash_equals($state, $query['state'])
            && is_string($query['code'] ?? null);
        $message = $valid
            ? 'Account connected. Return to your terminal.'
            : 'Invalid callback. Return to your terminal.';
        $status = $valid ? '200 OK' : '400 Bad Request';

        fwrite(
            $client,
            "HTTP/1.1 $status\r\n"
                . "Content-Type: text/plain\r\nCache-Control: no-store\r\nConnection: close\r\n"
                . 'Content-Length: ' . strlen($message) . "\r\n\r\n$message"
        );
        fclose($client);

        if ($valid) {
            $code = $query['code'];
        }
    }

    fclose($server);

    if (!$code) {
        throw new RuntimeException('OAuth callback timed out');
    }

    [$status, $tokens] = SnapDrive::http(
        'POST',
        'https://oauth2.googleapis.com/token',
        ['Content-Type: application/x-www-form-urlencoded'],
        http_build_query([
            'client_id' => snapSecret('GUEST_SNAP_GOOGLE_CLIENT_ID'),
            'client_secret' => snapSecret('GUEST_SNAP_GOOGLE_CLIENT_SECRET'),
            'code' => $code,
            'code_verifier' => $verifier,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ])
    );

    if ($status !== 200 || empty($tokens['refresh_token'])) {
        throw new RuntimeException('No refresh token returned; reconnect with consent');
    }

    echo "Store privately in server environment (do not commit or share):\nGUEST_SNAP_GOOGLE_REFRESH_TOKEN="
        . $tokens['refresh_token'] . "\n";

    // drive.file grants access to app-created files. Create a dedicated private folder.
    [$status, $folder] = SnapDrive::http(
        'POST',
        'https://www.googleapis.com/drive/v3/files?fields=id',
        ['Authorization: Bearer ' . $tokens['access_token'], 'Content-Type: application/json'],
        json_encode(['name' => 'Wedding Guest Snap', 'mimeType' => 'application/vnd.google-apps.folder'])
    );

    if ($status !== 200 || empty($folder['id'])) {
        throw new RuntimeException('Folder creation failed; save refresh token and investigate before reconnecting');
    }

    echo 'GUEST_SNAP_GOOGLE_FOLDER_ID=' . $folder['id'] . "\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
