<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function weddingBasePath(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    // Always resolve to the wedding app root (folder that contains includes/),
    // even when the active script lives in a subdirectory like /guest-snap/.
    $weddingFsRoot = realpath(dirname(__DIR__));
    $scriptFile = isset($_SERVER['SCRIPT_FILENAME'])
        ? realpath((string) $_SERVER['SCRIPT_FILENAME'])
        : false;
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    if ($weddingFsRoot && $scriptFile && str_starts_with($scriptFile, $weddingFsRoot)) {
        $rel = substr($scriptFile, strlen($weddingFsRoot));
        $rel = str_replace('\\', '/', $rel);
        if ($rel !== '' && str_ends_with($scriptName, $rel)) {
            $webRoot = substr($scriptName, 0, -strlen($rel));
            $base = rtrim($webRoot, '/');
            if ($base === '/' || $base === '\\') {
                $base = '';
            }
            return $base;
        }
    }

    $base = rtrim(dirname($scriptName), '/');
    if ($base === '/' || $base === '\\') {
        $base = '';
    }

    return $base;
}

function assetUrl(string $path): string
{
    $path = '/' . ltrim($path, '/');
    return weddingBasePath() . $path;
}

function parentLine(array $person): string
{
    $father = $person['father'] ?? '';
    $mother = $person['mother'] ?? '';
    $relation = $person['relation'] ?? '';
    $name = $person['name'] ?? '';

    return sprintf('%s · %s의 %s %s', $father, $mother, $relation, $name);
}

function parentsShort(array $person): string
{
    $father = $person['father'] ?? '';
    $mother = $person['mother'] ?? '';
    $relation = $person['relation'] ?? '';

    return sprintf('%s · %s의 %s', $father, $mother, $relation);
}

function weddingDateTime(array $wedding): DateTimeImmutable
{
    $date = $wedding['date'] ?? '2027-09-25';
    $time = $wedding['time'] ?? '11:00';

    return new DateTimeImmutable($date . ' ' . $time);
}

/**
 * Guest Snap upload unlock datetime (Asia/Seoul).
 * Prefer GUEST_SNAP_OPEN_AT in .env (Y-m-d H:i:s | Y-m-d\TH:i:s | Y-m-d).
 */
function guestSnapOpenAt(?array $wedding = null): DateTimeImmutable
{
    $tz = new DateTimeZone('Asia/Seoul');
    $raw = env('GUEST_SNAP_OPEN_AT');

    if (is_string($raw) && $raw !== '') {
        foreach (['Y-m-d H:i:s', 'Y-m-d\TH:i:s', 'Y-m-d H:i', 'Y-m-d'] as $format) {
            $dt = DateTimeImmutable::createFromFormat('!' . $format, $raw, $tz);
            if ($dt instanceof DateTimeImmutable) {
                return $dt;
            }
        }
    }

    if ($wedding !== null) {
        $date = $wedding['date'] ?? '2027-09-25';
        return new DateTimeImmutable($date . ' 00:00:00', $tz);
    }

    return new DateTimeImmutable('2027-09-25 00:00:00', $tz);
}

function guestSnapCanUpload(?array $wedding = null, ?DateTimeImmutable $now = null): bool
{
    $tz = new DateTimeZone('Asia/Seoul');
    $now = $now ?? new DateTimeImmutable('now', $tz);

    return $now >= guestSnapOpenAt($wedding);
}

function guestSnapOpenLabel(?array $wedding = null): string
{
    $dt = guestSnapOpenAt($wedding);
    $label = sprintf(
        '%d년 %d월 %d일',
        (int) $dt->format('Y'),
        (int) $dt->format('n'),
        (int) $dt->format('j')
    );

    if ($dt->format('H:i:s') !== '00:00:00') {
        $label .= ' ' . $dt->format('H:i');
    }

    return $label;
}

function buildIcsContent(array $weddingData): string
{
    $wedding = $weddingData['wedding'];
    $groom = $weddingData['groom']['name'];
    $bride = $weddingData['bride']['name'];
    $start = weddingDateTime($wedding);
    $end = $start->modify('+2 hours');

    $summary = $groom . ' ♥ ' . $bride . ' 결혼식';
    $location = ($wedding['venue'] ?? '') . ', ' . ($wedding['address'] ?? '');

    $format = static function (DateTimeImmutable $dt): string {
        return $dt->format('Ymd\THis');
    };

    $uid = md5($summary . $start->format('c')) . '@wedding.local';

    $lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Wedding Invitation//KO',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        'UID:' . $uid,
        'DTSTAMP:' . $format(new DateTimeImmutable('now', new DateTimeZone('UTC'))),
        'DTSTART:' . $format($start),
        'DTEND:' . $format($end),
        'SUMMARY:' . $summary,
        'LOCATION:' . str_replace(',', '\\,', $location),
        'DESCRIPTION:결혼식에 초대합니다.',
        'END:VEVENT',
        'END:VCALENDAR',
    ];

    return implode("\r\n", $lines) . "\r\n";
}

function handleIcsDownload(array $weddingData): void
{
    if (($_GET['action'] ?? '') !== 'calendar') {
        return;
    }

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="wedding.ics"');
    echo buildIcsContent($weddingData);
    exit;
}
