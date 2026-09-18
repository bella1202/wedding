<?php
/** @var array $weddingData */
$g = $weddingData['groom'];
$b = $weddingData['bride'];
$w = $weddingData['wedding'];
$dateParts = explode('-', $w['date'] ?? '2027-09-25');
$month = $dateParts[1] ?? '09';
$day = $dateParts[2] ?? '25';
$dateLine = $w['displayDateLine'] ?? '2027. 09. 25. SAT 11:00 AM';
$venueLine = $w['venueFull'] ?? ($w['venue'] ?? '');
?>
<div class="cinematicIntro" data-cinematic-intro role="dialog" aria-label="Opening">
    <div class="cinematicIntro__slides">
        <p class="cinematicIntro__line isActive" data-intro-line>
            <span><?= e($g['name']) ?></span>
            <span class="cinematicIntro__amp">&amp;</span>
            <span><?= e($b['name']) ?></span>
        </p>
        <p class="cinematicIntro__line cinematicIntro__line--date" data-intro-line>2027.09.25</p>
    </div>
    <button type="button" class="cinematicIntro__skip touchBtn" data-intro-skip>건너뛰기</button>
</div>

<section class="scene scene--hero scene--full" data-scene="hero">
    <div class="heroCard" data-hero-still>
        <div class="heroCard__photo">
            <img
                class="heroStill__img"
                src="<?= e(assetUrl('/assets/images/placeholder/01.svg')) ?>"
                alt="<?= e($g['name'] . ' & ' . $b['name']) ?>"
            >
            <div class="heroCard__handwrite" data-hero-handwrite aria-label="Happy Wedding Day">
                <p class="heroCard__handwriteLine">
                    <span data-handwrite-text="Happy"></span><span class="heroCard__caret" data-handwrite-caret hidden></span>
                </p>
                <p class="heroCard__handwriteLine">
                    <span data-handwrite-text="Wedding Day"></span><span class="heroCard__caret" data-handwrite-caret hidden></span>
                </p>
            </div>
        </div>
        <div class="heroCard__info">
            <div class="heroCard__names">
                <p class="heroCard__name"><?= e($g['name']) ?></p>
                <div class="heroCard__md" aria-hidden="true">
                    <span><?= e($month) ?></span>
                    <span class="heroCard__mdRule"></span>
                    <span><?= e($day) ?></span>
                </div>
                <p class="heroCard__name"><?= e($b['name']) ?></p>
            </div>
            <p class="heroCard__datetime"><?= e($dateLine) ?></p>
            <p class="heroCard__venue"><?= e($venueLine) ?></p>
        </div>
    </div>
</section>
