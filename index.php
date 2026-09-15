<?php
declare(strict_types=1);

require __DIR__ . '/includes/env.php';
require __DIR__ . '/includes/functions.php';
$weddingData = require __DIR__ . '/includes/data.php';
handleIcsDownload($weddingData);

$g = $weddingData['groom'];
$b = $weddingData['bride'];
$w = $weddingData['wedding'];
$weddingAt = weddingDateTime($w)->format('c');
$pageTitle = $g['name'] . ' ♥ ' . $b['name'] . ' · Wedding Film';

$kakaoJavaScriptKey = env('KAKAO_JAVASCRIPT_KEY');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f7f5f2">
    <meta name="description" content="김병진 &amp; 최숙란 결혼식에 초대합니다. 2027.09.25 더컨벤션 신사">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gowun+Dodum&family=Great+Vibes&family=IBM+Plex+Mono:wght@400;500&family=Noto+Sans+KR:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(assetUrl('/assets/css/common.css')) ?>">
    <link rel="stylesheet" href="<?= e(assetUrl('/assets/css/cinematic.css')) ?>">
    <link rel="stylesheet" href="<?= e(assetUrl('/assets/css/animation.css')) ?>">
</head>
<body class="filmBody">
<div class="filmGrain" aria-hidden="true"></div>
<nav class="siteMenu isHidden" data-quick-nav aria-label="메뉴">
    <button
        type="button"
        class="siteMenu__toggle touchBtn"
        data-menu-toggle
        aria-expanded="false"
        aria-controls="siteMenuPanel"
        aria-label="메뉴 열기"
    >
        <span class="siteMenu__burger" aria-hidden="true">
            <span></span><span></span><span></span>
        </span>
    </button>
    <div class="siteMenu__backdrop" data-menu-close></div>
    <div class="siteMenu__panel" id="siteMenuPanel" data-menu-panel aria-hidden="true">
        <p class="siteMenu__title">메뉴</p>
        <div class="siteMenu__list">
            <button type="button" class="siteMenu__item touchBtn" data-nav-target="weddingDate">
                <span class="siteMenu__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                </span>
                <span class="siteMenu__label">예식 안내</span>
            </button>
            <button type="button" class="siteMenu__item touchBtn" data-nav-target="gallery">
                <span class="siteMenu__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 16l-5.5-5.5L7 19"/></svg>
                </span>
                <span class="siteMenu__label">사진</span>
            </button>
            <button type="button" class="siteMenu__item touchBtn" data-nav-target="location">
                <span class="siteMenu__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s7-5.2 7-11a7 7 0 10-14 0c0 5.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.2"/></svg>
                </span>
                <span class="siteMenu__label">오시는 길</span>
            </button>
            <button type="button" class="siteMenu__item touchBtn" data-nav-target="guestbook">
                <span class="siteMenu__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 4h11a2 2 0 012 2v14l-3-2-3 2-3-2-3 2V6a2 2 0 012-2z"/><path d="M9 9h6M9 13h4"/></svg>
                </span>
                <span class="siteMenu__label">방명록</span>
            </button>
            <button type="button" class="siteMenu__item touchBtn" data-nav-target="account">
                <span class="siteMenu__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 20s-7-4.4-7-9.2A3.8 3.8 0 0112 8.2a3.8 3.8 0 017 2.6C19 15.6 12 20 12 20z"/></svg>
                </span>
                <span class="siteMenu__label">마음 전하기</span>
            </button>
        </div>
    </div>
</nav>
<div class="filmApp" id="filmApp" data-wedding-at="<?= e($weddingAt) ?>">
    <?php
    require __DIR__ . '/includes/components/hero.php';
    require __DIR__ . '/includes/components/invitation.php';
    require __DIR__ . '/includes/components/couple.php';
    require __DIR__ . '/includes/components/weddingInfo.php';
    require __DIR__ . '/includes/components/gallery.php';
    require __DIR__ . '/includes/components/letter.php';
    require __DIR__ . '/includes/components/location.php';
    require __DIR__ . '/includes/components/guestbook.php';
    require __DIR__ . '/includes/components/account.php';
    require __DIR__ . '/includes/components/contact.php';
    require __DIR__ . '/includes/components/share.php';
    require __DIR__ . '/includes/components/ending.php';
    ?>
</div>
<?php require __DIR__ . '/includes/components/outfit.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<?php if (!empty($kakaoJavaScriptKey)): ?>
<script>
window.__weddingKakaoReady = new Promise(function (resolve) {
  window.__resolveWeddingKakao = resolve;
});
</script>
<script
    src="https://dapi.kakao.com/v2/maps/sdk.js?appkey=<?= e($kakaoJavaScriptKey) ?>&autoload=false"
    onload="window.__resolveWeddingKakao && window.__resolveWeddingKakao()"
    onerror="window.__resolveWeddingKakao && window.__resolveWeddingKakao(new Error('sdk'))"
></script>
<?php endif; ?>
<script src="<?= e(assetUrl('/assets/js/common.js')) ?>"></script>
<script src="<?= e(assetUrl('/assets/js/gallery.js')) ?>"></script>
<script src="<?= e(assetUrl('/assets/js/share.js')) ?>"></script>
<script src="<?= e(assetUrl('/assets/js/cinematic.js')) ?>"></script>
<script src="<?= e(assetUrl('/assets/js/calendar.js')) ?>"></script>
<script src="<?= e(assetUrl('/assets/js/outfit.js')) ?>"></script>
<script src="<?= e(assetUrl('/assets/js/scenes.js')) ?>"></script>
<script src="<?= e(assetUrl('/assets/js/playful.js')) ?>"></script>
<script src="<?= e(assetUrl('/assets/js/guestbook.js')) ?>"></script>
<script src="<?= e(assetUrl('/assets/js/map.js')) ?>"></script>
</body>
</html>
