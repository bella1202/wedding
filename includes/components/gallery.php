<?php
/** @var array $weddingData */
$images = array_values($weddingData['gallery'] ?? []);
$orientations = [
    0 => 'portrait',
    1 => 'landscape',
    2 => 'portrait',
    3 => 'portrait',
    4 => 'portrait',
    5 => 'portrait',
];

$photos = [];
foreach ($images as $index => $src) {
    $no = sprintf('%02d', $index + 1);
    $photos[] = [
        'id' => $index,
        'src' => assetUrl($src),
        'caption' => 'PHOTO ' . $no,
        'orient' => $orientations[$index] ?? 'portrait',
    ];
}

$feature = $photos[0] ?? null;
$strip = array_slice($photos, 1);
$galleryJson = htmlspecialchars(
    json_encode($photos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ENT_QUOTES,
    'UTF-8'
);
?>
<section
    class="scene scene--gallery scene--flow"
    data-scene="gallery"
    id="gallery"
    data-couple-state="date"
    data-gallery
    data-gallery-items="<?= $galleryJson ?>"
>
    <p class="sceneLabel">갤러리</p>

    <?php if ($feature): ?>
        <button
            type="button"
            class="stillFrame stillFrame--feature touchBtn"
            data-gallery-feature
            data-gallery-id="<?= (int) $feature['id'] ?>"
            aria-label="<?= e($feature['caption']) ?> 크게 보기"
        >
            <img
                data-gallery-feature-image
                src="<?= e($feature['src']) ?>"
                alt=""
                loading="eager"
            >
            <span class="stillFrame__expand" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 15 6 6"/>
                    <path d="m15 9 6-6"/>
                    <path d="M21 16.2V21h-4.8"/>
                    <path d="M21 7.8V3h-4.8"/>
                    <path d="M3 16.2V21h4.8"/>
                    <path d="m3 21 6-6"/>
                    <path d="M3 7.8V3h4.8"/>
                    <path d="M9 9 3 3"/>
                </svg>
            </span>
        </button>
    <?php endif; ?>

    <?php if (!empty($strip)): ?>
        <div class="filmStrip" data-film-strip>
            <div class="filmStrip__track" data-film-track data-gallery-strip>
                <?php foreach ($strip as $item): ?>
                    <button
                        type="button"
                        class="filmStrip__frame touchBtn"
                        data-gallery-strip-item
                        data-gallery-id="<?= (int) $item['id'] ?>"
                        aria-label="<?= e($item['caption']) ?> 선택"
                    >
                        <img src="<?= e($item['src']) ?>" alt="" loading="lazy">
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
