<?php
/** @var array $weddingData */
$images = array_values($weddingData['gallery'] ?? []);
$highlights = array_slice($images, 0, 3);
$strip = array_slice($images, 3);
?>
<section class="scene scene--gallery scene--flow" data-scene="gallery" id="gallery" data-couple-state="date">
    <p class="sceneLabel">갤러리</p>

    <div class="stillPair">
        <?php if (!empty($highlights[0])): ?>
            <button type="button" class="stillFrame stillFrame--tall touchBtn" data-gallery-index="0" data-gallery-src="<?= e(assetUrl($highlights[0])) ?>">
                <img src="<?= e(assetUrl($highlights[0])) ?>" alt="PHOTO 01" loading="lazy">
                <span class="stillFrame__label">PHOTO 01</span>
            </button>
        <?php endif; ?>
        <?php if (!empty($highlights[1])): ?>
            <button type="button" class="stillFrame stillFrame--tall touchBtn" data-gallery-index="1" data-gallery-src="<?= e(assetUrl($highlights[1])) ?>">
                <img src="<?= e(assetUrl($highlights[1])) ?>" alt="PHOTO 02" loading="lazy">
                <span class="stillFrame__label">PHOTO 02</span>
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($highlights[2])): ?>
        <button type="button" class="stillFrame stillFrame--full touchBtn" data-gallery-index="2" data-gallery-src="<?= e(assetUrl($highlights[2])) ?>">
            <img src="<?= e(assetUrl($highlights[2])) ?>" alt="PHOTO 03" loading="lazy">
            <span class="stillFrame__label">PHOTO 03</span>
        </button>
    <?php endif; ?>

    <?php if (!empty($strip)): ?>
        <div class="filmStrip" data-film-strip>
            <p class="filmStrip__label">사진 · 옆으로 넘겨보세요</p>
            <div class="filmStrip__track" data-film-track>
                <?php foreach ($strip as $i => $src): ?>
                    <?php
                    $galleryIndex = $i + 3;
                    $photoNo = sprintf('%02d', $galleryIndex + 1);
                    ?>
                    <button
                        type="button"
                        class="filmStrip__frame touchBtn"
                        data-gallery-index="<?= (int) $galleryIndex ?>"
                        data-gallery-src="<?= e(assetUrl($src)) ?>"
                        data-gallery-caption="PHOTO <?= e($photoNo) ?>"
                    >
                        <img src="<?= e(assetUrl($src)) ?>" alt="PHOTO <?= e($photoNo) ?>">
                        <span class="filmStrip__caption">PHOTO <?= e($photoNo) ?></span>
                    </button>
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
