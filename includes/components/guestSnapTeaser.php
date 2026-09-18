<?php
/** @var array $weddingData */
$w = $weddingData['wedding'];
$openLabel = guestSnapOpenLabel($w);
$guestSnapUrl = assetUrl('/guest-snap/');
?>
<section
    class="scene scene--guestSnap scene--flow"
    data-scene="guestSnap"
    data-couple-state="heart"
    id="guestSnap"
>
    <p class="sceneLabel">게스트 스냅</p>
    <img
        class="guestSnapTeaser__illust"
        src="<?= e(assetUrl('/assets/images/guest-snap-camera.svg')) ?>"
        alt=""
        width="35"
        height="35"
        decoding="async"
    >
    <p class="guestSnapTeaser__lead">
        저희의 특별한 하루를<br>
        함께 담아 주실 스냅 작가님을 찾습니다.
    </p>
    <p class="guestSnapTeaser__note"><?= e($openLabel) ?>부터 업로드 가능</p>
    <a class="guestSnapTeaser__btn touchBtn" href="<?= e($guestSnapUrl) ?>">자세히 보기</a>
</section>
