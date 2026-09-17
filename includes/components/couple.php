<?php
/** @var array $weddingData */
$g = $weddingData['groom'];
$b = $weddingData['bride'];
?>
<section class="scene scene--couple scene--flow" data-scene="couple" data-couple-state="casual">
    <!-- <p class="sceneLabel">소개</p> -->
    <article class="couplePanel couplePanel--groom" data-couple-panel>
        <p class="couplePanel__role">신랑</p>
        <p class="couplePanel__name"><?= e($g['name']) ?></p>
        <p class="couplePanel__nameEn"><?= e($g['familyEn']) ?> <?= e($g['nameEn']) ?></p>
        <p class="couplePanel__parents">
            <?= e($g['father'] ?? '') ?> · <?= e($g['mother'] ?? '') ?> <span class="couplePanel__of">의</span> <?= e($g['relation'] ?? '') ?>
        </p>
        <figure class="couplePanel__photo">
            <img src="<?= e(assetUrl('/assets/images/placeholder/02.svg')) ?>" alt="">
        </figure>
    </article>
    <article class="couplePanel couplePanel--bride" data-couple-panel>
        <p class="couplePanel__role">신부</p>
        <p class="couplePanel__name"><?= e($b['name']) ?></p>
        <p class="couplePanel__nameEn"><?= e($b['familyEn']) ?> <?= e($b['nameEn']) ?></p>
        <p class="couplePanel__parents">
            <?= e($b['father'] ?? '') ?> · <?= e($b['mother'] ?? '') ?> <span class="couplePanel__of">의</span> <?= e($b['relation'] ?? '') ?>
        </p>
        <figure class="couplePanel__photo">
            <img src="<?= e(assetUrl('/assets/images/placeholder/03.svg')) ?>" alt="">
        </figure>
    </article>
</section>
