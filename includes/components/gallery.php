<?php
/** @var array $weddingData */
$images = $weddingData['gallery'] ?? [];
$messages = $weddingData['polaroidMessages'] ?? [];
?>
<section class="scene scene--gallery scene--flow" data-scene="gallery" id="gallery" data-couple-state="heart">
    <p class="sceneLabel">갤러리</p>

    <div class="stillPair">
        <?php if (!empty($images[0])): ?>
            <button type="button" class="stillFrame stillFrame--tall touchBtn" data-gallery-index="0" data-gallery-src="<?= e(assetUrl($images[0])) ?>">
                <img src="<?= e(assetUrl($images[0])) ?>" alt="PHOTO 01" loading="lazy">
                <span class="stillFrame__label">PHOTO 01</span>
            </button>
        <?php endif; ?>
        <?php if (!empty($images[1])): ?>
            <button type="button" class="stillFrame stillFrame--tall stillFrame--polaroid touchBtn" data-polaroid-flip aria-label="PHOTO 02 뒤집기">
                <span class="polaroidCard__inner">
                    <span class="polaroidCard__face polaroidCard__face--front">
                        <img src="<?= e(assetUrl($images[1])) ?>" alt="PHOTO 02" loading="lazy">
                        <span class="stillFrame__label">PHOTO 02</span>
                    </span>
                    <span class="polaroidCard__face polaroidCard__face--back"><?= e($messages[0] ?? '우리의 한 장면') ?></span>
                </span>
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($images[2])): ?>
        <button type="button" class="stillFrame stillFrame--full touchBtn" data-gallery-index="1" data-gallery-src="<?= e(assetUrl($images[2])) ?>">
            <img src="<?= e(assetUrl($images[2])) ?>" alt="PHOTO 03" loading="lazy">
            <span class="stillFrame__label">PHOTO 03</span>
        </button>
    <?php endif; ?>

    <?php if (!empty($images)): ?>
        <div class="filmStrip" data-film-strip>
            <p class="filmStrip__label">사진 · 옆으로 넘겨보세요</p>
            <div class="filmStrip__track" data-film-track>
                <?php foreach ($images as $i => $src): ?>
                    <figure class="filmStrip__frame">
                        <img src="<?= e(assetUrl($src)) ?>" alt="PHOTO <?= sprintf('%02d', $i + 1) ?>">
                        <figcaption>PHOTO <?= sprintf('%02d', $i + 1) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<div class="galleryViewer" data-gallery-viewer hidden aria-hidden="true">
    <div class="galleryViewer__backdrop" data-gallery-close></div>
    <div class="galleryViewer__inner">
        <button type="button" class="galleryViewer__close touchBtn" data-gallery-close aria-label="닫기">&times;</button>
        <button type="button" class="galleryViewer__nav galleryViewer__nav--prev touchBtn" data-gallery-prev aria-label="이전">&lsaquo;</button>
        <img class="galleryViewer__image" data-gallery-viewer-image src="" alt="">
        <button type="button" class="galleryViewer__nav galleryViewer__nav--next touchBtn" data-gallery-next aria-label="다음">&rsaquo;</button>
        <p class="galleryViewer__caption" data-gallery-viewer-caption></p>
    </div>
</div>
