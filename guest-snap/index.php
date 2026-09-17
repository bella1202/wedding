<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/env.php';
require dirname(__DIR__) . '/includes/functions.php';
$weddingData = require dirname(__DIR__) . '/includes/data.php';

$g = $weddingData['groom'];
$b = $weddingData['bride'];
$w = $weddingData['wedding'];
$weddingDate = $w['date'] ?? '2027-09-25';
[$yy, $mm, $dd] = array_map('intval', explode('-', $weddingDate));
$openLabel = sprintf('%d년 %d월 %d일', $yy, $mm, $dd);

$today = new DateTimeImmutable('today', new DateTimeZone('Asia/Seoul'));
$openDate = new DateTimeImmutable($weddingDate, new DateTimeZone('Asia/Seoul'));
$canUpload = $today >= $openDate;

$pageTitle = '게스트 스냅 · ' . ($g['name'] ?? '') . ' ♥ ' . ($b['name'] ?? '');
$homeUrl = assetUrl('/');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f7f5f2">
    <meta name="robots" content="noindex,nofollow">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gowun+Dodum&family=Noto+Sans+KR:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(assetUrl('/assets/css/common.css')) ?>">
    <link rel="stylesheet" href="<?= e(assetUrl('/guest-snap/assets/css/guest-snap.css')) ?>">
</head>
<body class="guestSnapBody">
<div class="filmGrain" aria-hidden="true"></div>

<header class="guestSnapTop">
    <a class="guestSnapTop__back touchBtn" href="<?= e($homeUrl) ?>" aria-label="청첩장으로 돌아가기">←</a>
    <p class="guestSnapTop__title">GUEST SNAP</p>
</header>

<main
    class="guestSnap"
    data-guest-snap
    data-open-date="<?= e($weddingDate) ?>"
    data-can-upload="<?= $canUpload ? '1' : '0' ?>"
>
    <div class="guestSnapHero" aria-hidden="true">
        <span class="guestSnapSpark guestSnapSpark--a"></span>
        <span class="guestSnapSpark guestSnapSpark--b"></span>
        <span class="guestSnapSpark guestSnapSpark--c"></span>
        <span class="guestSnapSpark guestSnapSpark--d"></span>
        <span class="guestSnapSpark guestSnapSpark--e"></span>
        <span class="guestSnapSpark guestSnapSpark--f"></span>
        <span class="guestSnapSpark guestSnapSpark--g"></span>
        <img
            class="guestSnap__illust"
            src="<?= e(assetUrl('/assets/images/guest-snap/couple.png')) ?>"
            alt=""
            width="220"
            height="220"
            decoding="async"
        >
    </div>

    <h1 class="guestSnap__heading">게스트 스냅</h1>
    <p class="guestSnap__lead">신랑 신부의 행복한 순간을 담아주세요</p>

    <p class="guestSnap__openNote">
        <strong><?= e($openLabel) ?>부터</strong><br>
        사진 및 영상 업로드가 가능합니다.
    </p>

    <div class="guestSnapRole" aria-hidden="true">
        <span class="guestSnapRole__badge">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 8h3l1.5-2h7L17 8h3a1 1 0 011 1v9a1 1 0 01-1 1H4a1 1 0 01-1-1V9a1 1 0 011-1z"/>
                <circle cx="12" cy="13" r="3.2"/>
            </svg>
        </span>
        <p class="guestSnap__ctaCopy">저희의 스냅 작가가 되어주세요!</p>
    </div>

    <section class="guestSnapMission" aria-labelledby="guestSnapMissionTitle">
        <div class="guestSnapMission__head">
            <span class="guestSnapMission__stamp" aria-hidden="true">MISSION</span>
            <h2 class="guestSnapMission__title" id="guestSnapMissionTitle">이 순간들을 놓치지 마세요!</h2>
        </div>

        <div class="guestSnapMission__grid">
            <?php
            $missions = [
                [
                    'no' => '01',
                    'title' => '신부 대기실',
                    'desc' => '설렘 가득한 순간',
                    'icon' => '<path d="M12 3c2.8 2.4 4.5 5.2 4.5 8.1A4.5 4.5 0 0112 15.6a4.5 4.5 0 01-4.5-4.5C7.5 8.2 9.2 5.4 12 3z"/><path d="M8.2 15.8c.9 1.7 2.2 3 3.8 4.2 1.6-1.2 2.9-2.5 3.8-4.2"/>',
                ],
                [
                    'no' => '02',
                    'title' => '웃음 만개',
                    'desc' => '행복한 신랑신부',
                    'icon' => '<circle cx="12" cy="12" r="8"/><path d="M8.5 10.2h.01M15.5 10.2h.01"/><path d="M8.2 13.4c1.1 1.5 2.4 2.2 3.8 2.2s2.7-.7 3.8-2.2"/>',
                ],
                [
                    'no' => '03',
                    'title' => '눈빛 교환',
                    'desc' => '마주보는 신랑신부',
                    'icon' => '<path d="M2.8 12s3.2-5.5 9.2-5.5S21.2 12 21.2 12s-3.2 5.5-9.2 5.5S2.8 12 2.8 12z"/><circle cx="12" cy="12" r="2.4"/>',
                ],
                [
                    'no' => '04',
                    'title' => '행복한 피날레',
                    'desc' => '신랑신부 행진',
                    'icon' => '<path d="M8 21l2-7 2 2 2-5 2 3 2-4"/><circle cx="8" cy="7" r="1.6"/><circle cx="14" cy="6" r="1.6"/><path d="M5 21h14"/>',
                ],
                [
                    'no' => '05',
                    'title' => '여러분의 미소',
                    'desc' => '오늘 주인공은 여러분도!',
                    'icon' => '<circle cx="9" cy="10" r="2.4"/><circle cx="15.5" cy="10" r="2.4"/><path d="M4.8 17.2c1.2-1.8 2.9-2.8 4.7-2.8.8 0 1.5.2 2.2.5"/><path d="M12.5 15c.7-.4 1.5-.6 2.4-.6 1.9 0 3.7 1.1 5 3"/>',
                ],
                [
                    'no' => '06',
                    'title' => '감성 한 스푼',
                    'desc' => "예술이란 이런 것이다",
                    'icon' => '<circle cx="12" cy="12" r="8"/><circle cx="9.2" cy="10.2" r="1.3"/><circle cx="13.8" cy="9.2" r="1.3"/><circle cx="15" cy="13.2" r="1.3"/><circle cx="10.5" cy="14.5" r="1.3"/>',
                ],
            ];
            foreach ($missions as $mission):
            ?>
                <article class="guestSnapCard">
                    <span class="guestSnapCard__no"><?= e($mission['no']) ?></span>
                    <span class="guestSnapCard__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <?= $mission['icon'] ?>
                        </svg>
                    </span>
                    <p class="guestSnapCard__title"><?= e($mission['title']) ?></p>
                    <p class="guestSnapCard__desc"><?= e($mission['desc']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="guestSnapReward">
            <span class="guestSnapReward__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 11h16v2a6 6 0 01-6 6h-4a6 6 0 01-6-6v-2z"/>
                    <path d="M8 11V8.5A2.5 2.5 0 0110.5 6h3A2.5 2.5 0 0116 8.5V11"/>
                    <path d="M9 17.5c.8.6 1.8 1 3 1s2.2-.4 3-1"/>
                </svg>
            </span>
            <p class="guestSnapReward__text">
                가장 멋진 컷을 남겨주신 분께<br>
                <strong>맛있는 밥 한끼</strong>를 쏩니다!
            </p>
        </div>

        <p class="guestSnapMission__hint">
            당일, 아래에서 바로 올려주세요.<br>
            많은 참여 부탁드려요!
        </p>
    </section>

    <form class="guestSnapForm" data-guest-snap-form novalidate>
        <div class="guestSnapForm__card">
            <label class="guestSnapForm__field">
                <span>성함</span>
                <input
                    type="text"
                    name="name"
                    maxlength="20"
                    autocomplete="name"
                    placeholder="홍길동"
                    <?= $canUpload ? 'required' : 'disabled' ?>
                >
            </label>

            <div class="guestSnapForm__field">
                <span>사진 및 영상</span>
                <label
                    class="guestSnapDrop<?= $canUpload ? '' : ' isLocked' ?>"
                    data-guest-snap-drop
                >
                    <input
                        class="guestSnapDrop__input"
                        type="file"
                        name="files"
                        accept="image/*,video/*"
                        multiple
                        data-guest-snap-files
                        <?= $canUpload ? '' : 'disabled' ?>
                    >
                    <?php if (!$canUpload): ?>
                        <p class="guestSnapDrop__lock"><?= e($openLabel) ?>에 오픈됩니다!</p>
                    <?php endif; ?>
                    <span class="guestSnapDrop__plus" aria-hidden="true">+</span>
                    <span class="guestSnapDrop__label"><?= $canUpload ? '터치하여 추가' : '오픈 대기 중' ?></span>
                </label>
                <ul class="guestSnapDrop__preview" data-guest-snap-preview hidden></ul>
            </div>
        </div>

        <p class="guestSnapForm__error" data-guest-snap-error hidden></p>

        <button
            type="submit"
            class="guestSnapForm__submit touchBtn"
            data-guest-snap-submit
            <?= $canUpload ? '' : 'disabled' ?>
        >업로드</button>

        <p class="guestSnapForm__soon" data-guest-snap-soon<?= $canUpload ? ' hidden' : '' ?>>
            Google Drive 연동 업로드는 준비 중이며,<br>
            예식 당일부터 활성화됩니다.
        </p>
    </form>

    <section class="guestSnapNotice" aria-labelledby="guestSnapNoticeTitle">
        <h2 class="guestSnapNotice__title" id="guestSnapNoticeTitle">업로드 주의사항</h2>
        <ul class="guestSnapNotice__list">
            <li>한 번에 최대 100개까지 업로드하실 수 있어요.</li>
            <li>여러 번 나누어 업로드하실 수 있어요.</li>
            <li>영상은 파일당 200MB 이하만 첨부하실 수 있어요.</li>
            <li>업로드 중 화면이 꺼지면 중단될 수 있으니, 화면이 켜진 상태로 진행해주세요.</li>
        </ul>
    </section>
</main>

<script src="<?= e(assetUrl('/guest-snap/assets/js/guest-snap.js')) ?>"></script>
</body>
</html>
