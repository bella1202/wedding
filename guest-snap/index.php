<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/env.php';
require dirname(__DIR__) . '/includes/functions.php';
$weddingData = require dirname(__DIR__) . '/includes/data.php';

$g = $weddingData['groom'];
$b = $weddingData['bride'];
$w = $weddingData['wedding'];
$openAt = guestSnapOpenAt($w);
$openLabel = guestSnapOpenLabel($w);
$canUpload = guestSnapCanUpload($w);

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
    data-open-at="<?= e($openAt->format('c')) ?>"
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
    <p class="guestSnap__lead">
        신랑 신부의 행복한 순간을<br>
        사진 한 장 한 장에 예쁘게 담아주세요.<br><br>
        저희의 특별한 하루를 함께해 주실<br>
        스냅 작가님을 찾습니다.<br><br>
        많은 관심과 참여 부탁드려요<i class="guestSnap__heart" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="12" height="12" focusable="false">
                <path fill="currentColor" d="M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3z"/>
            </svg>
        </i>
    </p>

    <p class="guestSnap__openPill">
        <span class="guestSnap__openPillIcon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 8h3l1.5-2h7L17 8h3a1 1 0 011 1v9a1 1 0 01-1 1H4a1 1 0 01-1-1V9a1 1 0 011-1z"/>
                <circle cx="12" cy="13" r="3"/>
            </svg>
        </span>
        <span><?= e($openLabel) ?>부터 업로드 가능</span>
    </p>

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
                    // vanity mirror
                    'icon' => '<circle cx="12" cy="11" r="5.5"/><path d="M7.5 16.5h9"/><path d="M9 19h6"/><path d="M12 5.5V4"/><path d="M8.2 6.2l-1-1"/><path d="M15.8 6.2l1-1"/>',
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
                    // party popper
                    'icon' => '<path d="M11 13c1.8 1.8 2.6 3.9 1.9 4.6-.7.7-2.8-.1-4.6-1.9-1.8-1.8-2.6-3.9-1.9-4.6.7-.7 2.8.1 4.6 1.9z"/><path d="M5.5 11.5 2.2 21.2l9.7-3.3"/><path d="M14 3.2c.5 1.8 1.6 2.9 3.4 3.5"/><path d="M16.8 2.5c.3 1.3 1 2.1 2.3 2.6"/><path d="M12.2 5.8c.6 1.4 1.7 2.3 3.2 2.9"/><path d="M18.5 9.2c.2.9.8 1.5 1.7 1.8"/><circle cx="20.2" cy="4.8" r=".7" fill="currentColor" stroke="none"/><circle cx="15.2" cy="2.8" r=".55" fill="currentColor" stroke="none"/><circle cx="21.2" cy="8.5" r=".55" fill="currentColor" stroke="none"/><circle cx="13.2" cy="4.2" r=".5" fill="currentColor" stroke="none"/>',
                ],
                [
                    'no' => '05',
                    'title' => '여러분의 미소',
                    'desc' => '오늘은 여러분도 주인공!',
                    // guests with smiles
                    'icon' => '<circle cx="8.5" cy="9" r="2.5"/><circle cx="15.5" cy="9" r="2.5"/><path d="M4.2 17.5c.9-2 2.4-3.1 4.3-3.1 1 0 1.9.3 2.6.9"/><path d="M12.9 15.3c.7-.5 1.6-.8 2.6-.8 1.9 0 3.4 1.1 4.3 3"/><path d="M7.2 9.8c.4.5.9.8 1.3.8s.9-.3 1.3-.8"/><path d="M14.2 9.8c.4.5.9.8 1.3.8s.9-.3 1.3-.8"/>',
                ],
                [
                    'no' => '06',
                    'title' => '감성 한 스푼',
                    'desc' => "예술이란 이런 것이다",
                    // picture frame + spark
                    'icon' => '<rect x="4" y="5" width="16" height="14" rx="1.5"/><path d="M7 15l3.2-3.8 2.4 2.6L16 10l3 5"/><path d="M17.5 6.2l.5 1.2 1.2.5-1.2.5-.5 1.2-.5-1.2-1.2-.5 1.2-.5z"/>',
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
                <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true">
                    <!-- spoon: pink handle first, gray head/neck on top -->
                    <rect x="5.9" y="11.6" width="3.2" height="9" rx="1.6" fill="currentColor"/>
                    <circle cx="7.5" cy="6.2" r="3.6" fill="#9a9a9a"/>
                    <rect x="5.9" y="9.2" width="3.2" height="4.5" fill="#9a9a9a"/>
                    <!-- fork: pink handle first, gray tines/neck on top -->
                    <rect x="15.7" y="11.6" width="3.2" height="9" rx="1.6" fill="currentColor"/>
                    <rect x="14.2" y="2.6" width="1.55" height="6.2" rx=".75" fill="#9a9a9a"/>
                    <rect x="16.55" y="2.6" width="1.55" height="6.2" rx=".75" fill="#9a9a9a"/>
                    <rect x="18.9" y="2.6" width="1.55" height="6.2" rx=".75" fill="#9a9a9a"/>
                    <rect x="14.2" y="7.8" width="6.25" height="2.2" rx="1.1" fill="#9a9a9a"/>
                    <rect x="15.7" y="9.4" width="3.2" height="4.5" fill="#9a9a9a"/>
                </svg>
            </span>
            <p class="guestSnapReward__text">
                가장 멋진 순간을 담아주신 분께<br>
                <strong>맛있는 식사 한 끼</strong>를 대접하겠습니다!
            </p>
        </div>
    </section>

    <div class="guestSnapFormWrap<?= $canUpload ? '' : ' isLocked' ?>">
        <?php if (!$canUpload): ?>
            <div class="guestSnapFormWrap__overlay" aria-hidden="true">
                <p><?= e($openLabel) ?>에 오픈됩니다!</p>
            </div>
        <?php endif; ?>

        <form class="guestSnapForm" data-guest-snap-form novalidate>
            <div class="guestSnapForm__card">
                <label class="guestSnapForm__field">
                    <input
                        type="text"
                        name="name"
                        maxlength="20"
                        autocomplete="name"
                        placeholder="성함을 입력해주세요."
                        aria-label="성함"
                        <?= $canUpload ? 'required' : 'disabled' ?>
                    >
                </label>

                <div class="guestSnapForm__field">
                    <label
                        class="guestSnapDrop"
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
                        <span class="guestSnapDrop__plus" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="34" height="34" fill="currentColor" aria-hidden="true">
                                <path d="M4 5.75A1.75 1.75 0 015.75 4h12.5A1.75 1.75 0 0120 5.75v12.5A1.75 1.75 0 0118.25 20H5.75A1.75 1.75 0 014 18.25V5.75zm2.2 11.5h11.6l-3.55-4.15a.9.9 0 00-1.38-.04l-2.12 2.3-1.28-1.4a.9.9 0 00-1.36.02L6.2 17.25zM9 10.1a1.85 1.85 0 100-3.7 1.85 1.85 0 000 3.7z"/>
                            </svg>
                        </span>
                        <span class="guestSnapDrop__label">클릭하여<br>사진이나 영상을 업로드해주세요.</span>
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
        </form>
    </div>

    <section class="guestSnapNotice" aria-labelledby="guestSnapNoticeTitle">
        <h2 class="guestSnapNotice__title" id="guestSnapNoticeTitle">업로드 주의사항</h2>
        <ul class="guestSnapNotice__list">
            <li>한 번에 최대 100개까지 업로드하실 수 있어요.</li>
            <li>여러 번 나누어 업로드하실 수 있어요.</li>
            <li>영상은 파일당 200MB 이하만 첨부하실 수 있어요.</li>
            <li>업로드 중 화면이 꺼지면 중단될 수 있으니, 화면이 켜진 상태로 진행해주세요.</li>
        </ul>
    </section>

    <p class="guestSnapCopy">COPYRIGHT SR. All rights reserved.</p>
</main>

<script src="<?= e(assetUrl('/guest-snap/assets/js/guest-snap.js')) ?>"></script>
</body>
</html>
