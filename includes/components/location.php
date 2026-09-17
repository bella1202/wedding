<?php
/** @var array $weddingData */
/** @var string|null $kakaoJavaScriptKey */

$loc = $weddingData['location'] ?? [];
$w = $weddingData['wedding'];
$t = $weddingData['transportation'] ?? [];

$venueName = $loc['name'] ?? $w['venue'];
$address = $loc['address'] ?? $w['address'];
$phone = $loc['phone'] ?? '';
$lat = $loc['latitude'] ?? null;
$lng = $loc['longitude'] ?? null;
$kakaoMapUrl = $loc['kakaoMapUrl'] ?? '';
$naverMapUrl = $loc['naverMapUrl'] ?? '';
$tmapUrl = $loc['tmapUrl'] ?? '';

$subway = $t['subway'] ?? '';
$busLines = $t['bus'] ?? [];
if (is_string($busLines) && $busLines !== '') {
    $busLines = [$busLines];
}
if (!is_array($busLines)) {
    $busLines = [];
}
$parking = $t['parking'] ?? '주차';
$parkingNote = $t['parkingNote'] ?? '';

$hasCoords = $lat !== null && $lng !== null && $lat !== '' && $lng !== '';
$hasKakaoKey = !empty($kakaoJavaScriptKey);
$canRenderMap = $hasKakaoKey && $hasCoords;
$phoneTel = $phone !== '' ? preg_replace('/\D+/', '', $phone) : '';

if ($kakaoMapUrl === '') {
    if ($hasCoords) {
        $kakaoMapUrl = 'https://map.kakao.com/link/map/' . rawurlencode($venueName)
            . ',' . rawurlencode((string) $lat)
            . ',' . rawurlencode((string) $lng);
    } else {
        $kakaoMapUrl = 'https://map.kakao.com/?q=' . rawurlencode($venueName . ' ' . $address);
    }
}
if ($naverMapUrl === '') {
    $naverMapUrl = 'https://map.naver.com/v5/search/' . rawurlencode($venueName . ' ' . $address);
}
if ($tmapUrl === '') {
    if ($hasCoords) {
        $tmapUrl = 'tmap://route?goalname=' . rawurlencode($venueName)
            . '&goalx=' . rawurlencode((string) $lng)
            . '&goaly=' . rawurlencode((string) $lat);
    } else {
        $tmapUrl = 'tmap://search?name=' . rawurlencode($venueName . ' ' . $address);
    }
}
?>
<section class="scene scene--location scene--full" data-scene="location" data-couple-state="heart" id="location">
    <div class="locationInner">
    <p class="sceneLabel">오시는 길</p>

    <div class="location__head">
        <h2 class="location__venue"><?= e($venueName) ?></h2>

        <div class="location__addressRow">
            <p class="location__address"><?= e($address) ?></p>
            <button
                type="button"
                class="location__copyBadge touchBtn"
                data-copy-target="<?= e($address) ?>"
                data-copy-default="복사"
                aria-label="주소 복사"
            >
                <span data-copy-label>복사</span>
            </button>
            <?php if ($phone !== ''): ?>
                <a
                    class="location__callBadge touchBtn"
                    href="tel:<?= e($phoneTel) ?>"
                    aria-label="<?= e($venueName) ?>에 전화하기 <?= e($phone) ?>"
                    title="<?= e($phone) ?>"
                >
                    <svg viewBox="0 0 24 24" width="11" height="11" aria-hidden="true" fill="currentColor">
                        <path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 011 1V20a1 1 0 01-1 1C10.4 21 3 13.6 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.46.57 3.58a1 1 0 01-.25 1.02l-2.2 2.19z"/>
                    </svg>
                    <span>전화</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div
        class="kakaoMap"
        data-kakao-map
        data-lat="<?= $hasCoords ? e((string) $lat) : '' ?>"
        data-lng="<?= $hasCoords ? e((string) $lng) : '' ?>"
        data-name="<?= e($venueName) ?>"
        data-address="<?= e($address) ?>"
        data-has-key="<?= $hasKakaoKey ? '1' : '0' ?>"
    >
        <div class="kakaoMap__canvas" data-kakao-map-canvas<?= $canRenderMap ? '' : ' hidden' ?>></div>
        <div class="kakaoMap__fallback" data-kakao-map-fallback<?= $canRenderMap ? ' hidden' : '' ?>>
            <p class="kakaoMap__fallbackTitle"><?= e($venueName) ?></p>
            <p class="kakaoMap__fallbackAddress"><?= e($address) ?></p>
            <p class="kakaoMap__fallbackHint" data-kakao-map-hint>지도를 불러오지 못했습니다.</p>
        </div>

        <div class="mapNav" role="group" aria-label="지도 앱으로 길찾기">
            <a
                class="mapNav__item touchBtn"
                href="<?= e($naverMapUrl) ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <img
                    class="mapNav__icon"
                    src="<?= e(assetUrl('/assets/images/map/navermap.png')) ?>"
                    alt=""
                    width="16"
                    height="16"
                    decoding="async"
                >
                <span class="mapNav__label">네이버 지도</span>
            </a>
            <a
                class="mapNav__item touchBtn"
                href="<?= e($kakaoMapUrl) ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <img
                    class="mapNav__icon"
                    src="<?= e(assetUrl('/assets/images/map/kakaomap.png')) ?>"
                    alt=""
                    width="16"
                    height="16"
                    decoding="async"
                >
                <span class="mapNav__label">카카오맵</span>
            </a>
            <a
                class="mapNav__item touchBtn"
                href="<?= e($tmapUrl) ?>"
            >
                <img
                    class="mapNav__icon"
                    src="<?= e(assetUrl('/assets/images/map/tmap.png')) ?>"
                    alt=""
                    width="16"
                    height="16"
                    decoding="async"
                >
                <span class="mapNav__label">티맵</span>
            </a>
        </div>
    </div>

    <div class="locationGuide">
        <?php if ($subway !== ''): ?>
            <article class="locationGuide__item">
                <h3 class="locationGuide__label">
                    <span class="locationGuide__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M4 9h16M9 21V9M15 21V9"/><circle cx="9" cy="6" r="0.8" fill="currentColor" stroke="none"/><circle cx="15" cy="6" r="0.8" fill="currentColor" stroke="none"/></svg>
                    </span>
                    지하철
                </h3>
                <p class="locationGuide__text"><?= e($subway) ?></p>
            </article>
        <?php endif; ?>

        <?php if (!empty($busLines)): ?>
            <article class="locationGuide__item">
                <h3 class="locationGuide__label">
                    <span class="locationGuide__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="12" rx="2"/><path d="M8 16v2M16 16v2M4 12h16M7 8h.01M17 8h.01"/><path d="M6 16h12"/></svg>
                    </span>
                    버스
                </h3>
                <ul class="locationGuide__list">
                    <?php foreach ($busLines as $line): ?>
                        <li><?= e($line) ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endif; ?>

        <?php if ($parkingNote !== '' || $parking !== ''): ?>
            <article class="locationGuide__item">
                <h3 class="locationGuide__label">
                    <span class="locationGuide__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 17V7h4.2a3 3 0 010 6H9"/></svg>
                    </span>
                    <?= e($parking !== '' ? $parking : '주차') ?>
                </h3>
                <?php if ($parkingNote !== ''): ?>
                    <p class="locationGuide__text"><?= e($parkingNote) ?></p>
                <?php endif; ?>
            </article>
        <?php endif; ?>
    </div>
    </div>
</section>
