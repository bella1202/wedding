<?php
/** @var array $weddingData */
$g = $weddingData['groom'];
$b = $weddingData['bride'];
$shareTitle = $g['name'] . ' ♥ ' . $b['name'] . ' 결혼식';
?>
<section class="scene scene--share scene--flow" data-scene="share" data-couple-state="heart">
    <button type="button" class="shareFilm__btn touchBtn" data-rsvp-open>참석 여부 전달하기</button>
    <button type="button" class="shareFilm__btn touchBtn" data-web-share data-share-title="<?= e($shareTitle) ?>">공유하기</button>
    <a class="shareFilm__calendar touchBtn" href="?action=calendar">캘린더에 저장</a>
</section>
