<?php
/** @var array $weddingData */
/** @var string|null $kakaoJavaScriptKey */

$loc = $weddingData['location'] ?? [];
$w = $weddingData['wedding'];
$t = $weddingData['transportation'];

$venueName = $loc['name'] ?? $w['venue'];
$address = $loc['address'] ?? $w['address'];
$lat = $loc['latitude'] ?? null;
$lng = $loc['longitude'] ?? null;
$kakaoMapUrl = $loc['kakaoMapUrl'] ?? '';
$naverMapUrl = $loc['naverMapUrl'] ?? '';

$hasCoords = $lat !== null && $lng !== null && $lat !== '' && $lng !== '';
$hasKakaoKey = !empty($kakaoJavaScriptKey);
$canRenderMap = $hasKakaoKey && $hasCoords;

if ($kakaoMapUrl === '') {
    $kakaoMapUrl = 'https://map.kakao.com/?q=' . rawurlencode($venueName . ' ' . $address);
}
if ($naverMapUrl === '') {
    $naverMapUrl = 'https://map.naver.com/v5/search/' . rawurlencode($venueName . ' ' . $address);
}
?>
<section class="scene scene--location scene--flow" data-scene="location" data-couple-state="heart" id="location">
    <p class="sceneLabel">오시는 길</p>
    <h2 class="location__venueEn"><?= e($venueName) ?></h2>
    <p class="location__venue"><?= e($w['venueEn'] ?? '') ?></p>
    <p class="location__address"><?= e($address) ?></p>

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
    </div>

    <p class="location__meta">
        <span><?= e($w['day']) ?></span>
        <span><?= e($w['displayTime']) ?></span>
    </p>

    <div class="location__actions">
        <a class="location__action touchBtn" href="<?= e($kakaoMapUrl) ?>" target="_blank" rel="noopener noreferrer">카카오맵에서 보기</a>
        <a class="location__action touchBtn" href="<?= e($naverMapUrl) ?>" target="_blank" rel="noopener noreferrer">네이버지도에서 보기</a>
        <button type="button" class="location__action touchBtn" data-copy-target="<?= e($address) ?>">
            <span data-copy-label>주소 복사</span>
        </button>
    </div>

    <ul class="location__transport">
        <li><span>주차</span><?= e($t['parking']) ?></li>
        <li><span>지하철</span><?= e($t['subway']) ?></li>
        <li><span>버스</span><?= e($t['bus']) ?></li>
    </ul>
</section>
