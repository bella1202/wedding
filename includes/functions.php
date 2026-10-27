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

/**
 * Wedding schedule from WEDDING_DATE (Y-m-d) + WEDDING_TIME (H:i), Asia/Seoul.
 *
 * @return array{
 *   date: string,
 *   time: string,
 *   day: string,
 *   dayEn: string,
 *   displayTime: string,
 *   displayTimeEn: string,
 *   displayDateLine: string,
 *   displayDateDot: string,
 *   datetime: DateTimeImmutable
 * }
 */
function weddingSchedule(): array
{
    $tz = new DateTimeZone('Asia/Seoul');
    $date = env('WEDDING_DATE');
    $time = env('WEDDING_TIME');

    if (!is_string($date) || $date === '' || !is_string($time) || $time === '') {
        throw new RuntimeException('WEDDING_DATE and WEDDING_TIME must be set in .env');
    }

    $dt = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $date . ' ' . $time, $tz);
    $errors = DateTimeImmutable::getLastErrors();
    if (
        !$dt instanceof DateTimeImmutable
        || ($errors !== false && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0))
    ) {
        throw new RuntimeException('Invalid WEDDING_DATE or WEDDING_TIME (expected Y-m-d and H:i)');
    }

    $weekday = (int) $dt->format('w');
    $daysKo = ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'];
    $daysEn = ['SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];
    $daysEnShort = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];

    $hour24 = (int) $dt->format('G');
    $minute = (int) $dt->format('i');
    $ampmKo = $hour24 < 12 ? '오전' : '오후';
    $hour12 = $hour24 % 12;
    if ($hour12 === 0) {
        $hour12 = 12;
    }
    $displayTime = $minute === 0
        ? sprintf('%s %d시', $ampmKo, $hour12)
        : sprintf('%s %d시 %d분', $ampmKo, $hour12, $minute);

    return [
        'date' => $dt->format('Y-m-d'),
        'time' => $dt->format('H:i'),
        'day' => $daysKo[$weekday],
        'dayEn' => $daysEn[$weekday],
        'displayTime' => $displayTime,
        'displayTimeEn' => $dt->format('h:i A'),
        'displayDateLine' => sprintf(
            '%s. %s. %s. %s %s',
            $dt->format('Y'),
            $dt->format('m'),
            $dt->format('d'),
            $daysEnShort[$weekday],
            $dt->format('h:i A')
        ),
        'displayDateDot' => $dt->format('Y.m.d'),
        'datetime' => $dt,
    ];
}

function weddingDateTime(array $wedding): DateTimeImmutable
{
    if (isset($wedding['datetime']) && $wedding['datetime'] instanceof DateTimeImmutable) {
        return $wedding['datetime'];
    }

    $schedule = weddingSchedule();

    return $schedule['datetime'];
}

/**
 * Guest Snap upload unlock: WEDDING_DATE at 00:00:00 Asia/Seoul.
 */
function guestSnapOpenAt(?array $wedding = null): DateTimeImmutable
{
    $tz = new DateTimeZone('Asia/Seoul');
    $date = is_array($wedding) ? ($wedding['date'] ?? null) : null;

    if (!is_string($date) || $date === '') {
        $date = env('WEDDING_DATE');
    }

    if (!is_string($date) || $date === '') {
        throw new RuntimeException('WEDDING_DATE must be set in .env');
    }

    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $tz);
    $errors = DateTimeImmutable::getLastErrors();
    if (
        !$dt instanceof DateTimeImmutable
        || ($errors !== false && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0))
    ) {
        throw new RuntimeException('Invalid WEDDING_DATE (expected Y-m-d)');
    }

    return $dt;
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
