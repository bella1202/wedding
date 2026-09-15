<?php
/** @var array $weddingData */
$images = $weddingData['gallery'] ?? [];
$messages = $weddingData['polaroidMessages'] ?? [];
$highlightA = $images[0] ?? '';
$highlightB = $images[5] ?? ($images[3] ?? $images[0] ?? '');
?>
<section
    class="scene scene--full galleryHighlight"
    data-scene="galleryHighlightA"
    data-couple-state="wedding"
>
    <button
        type="button"
        class="galleryHighlight__btn touchBtn"
        data-gallery-index="0"
        data-gallery-src="<?= e(assetUrl($highlightA)) ?>"
        aria-label="PHOTO 01 전체보기"
    >
        <img class="galleryHighlight__img" src="<?= e(assetUrl($highlightA)) ?>" alt="PHOTO 01" loading="lazy">
        <div class="galleryHighlight__veil"></div>
        <p class="galleryHighlight__caption">사진 01</p>
    </button>
</section>

<section class="scene scene--gallery scene--flow" data-scene="gallery" id="gallery" data-couple-state="heart">
    <p class="sceneLabel">갤러리</p>

    <div class="stillPair">
        <button type="button" class="stillFrame stillFrame--tall touchBtn" data-gallery-index="1" data-gallery-src="<?= e(assetUrl($images[1] ?? '')) ?>">
            <img src="<?= e(assetUrl($images[1] ?? '')) ?>" alt="PHOTO 02" loading="lazy">
            <span class="stillFrame__label">PHOTO 02</span>
        </button>
        <?php if (!empty($images[2])): ?>
            <button type="button" class="stillFrame stillFrame--tall stillFrame--polaroid touchBtn" data-polaroid-flip aria-label="PHOTO 03 뒤집기">
                <span class="polaroidCard__inner">
                    <span class="polaroidCard__face polaroidCard__face--front">
                        <img src="<?= e(assetUrl($images[2])) ?>" alt="PHOTO 03" loading="lazy">
                        <span class="stillFrame__label">PHOTO 03</span>
                    </span>
                    <span class="polaroidCard__face polaroidCard__face--back"><?= e($messages[0] ?? '우리의 한 장면') ?></span>
                </span>
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($images[3])): ?>
        <button type="button" class="stillFrame stillFrame--full touchBtn" data-gallery-index="3" data-gallery-src="<?= e(assetUrl($images[3])) ?>">
            <img src="<?= e(assetUrl($images[3])) ?>" alt="PHOTO 04" loading="lazy">
            <span class="stillFrame__label">PHOTO 04</span>
        </button>
    <?php endif; ?>

    <div class="filmStrip" data-film-strip>
        <p class="filmStrip__label">사진 · 옆으로 넘겨보세요</p>
        <div class="filmStrip__track" data-film-track>
            <?php for ($i = 0; $i < 4; $i++): ?>
                <?php $src = $images[$i] ?? $images[0]; ?>
                <figure class="filmStrip__frame">
                    <img src="<?= e(assetUrl($src)) ?>" alt="PHOTO 0<?= $i + 1 ?>">
                    <figcaption>PHOTO 0<?= $i + 1 ?></figcaption>
                </figure>
            <?php endfor; ?>
        </div>
    </div>

    <?php if (!empty($images[4])): ?>
        <button type="button" class="stillFrame stillFrame--polaroidWide touchBtn" data-polaroid-flip aria-label="PHOTO 05 뒤집기">
            <span class="polaroidCard__inner">
                <span class="polaroidCard__face polaroidCard__face--front">
                    <img src="<?= e(assetUrl($images[4])) ?>" alt="PHOTO 05" loading="lazy">
                    <span class="stillFrame__label">PHOTO 05</span>
                </span>
                <span class="polaroidCard__face polaroidCard__face--back"><?= e($messages[1] ?? '좋아하는 순간') ?></span>
            </span>
        </button>
    <?php endif; ?>
</section>

<section
    class="scene scene--full galleryHighlight"
    data-scene="galleryHighlightB"
    data-couple-state="heart"
>
    <button
        type="button"
        class="galleryHighlight__btn touchBtn"
        data-gallery-index="5"
        data-gallery-src="<?= e(assetUrl($highlightB)) ?>"
        aria-label="PHOTO highlight 전체보기"
    >
        <img class="galleryHighlight__img" src="<?= e(assetUrl($highlightB)) ?>" alt="PHOTO">
        <div class="galleryHighlight__veil"></div>
        <p class="galleryHighlight__caption">STILL</p>
    </button>
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
