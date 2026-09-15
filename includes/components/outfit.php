<?php
/** @var array $weddingData */
$outfit = $weddingData['outfit'] ?? [];
$stages = [
    'casual' => $outfit['states']['casual'] ?? '/assets/images/couple/01-casual.png',
    'date' => $outfit['states']['date'] ?? '/assets/images/couple/02-date.png',
    'wedding' => $outfit['states']['wedding'] ?? '/assets/images/couple/03-wedding.png',
    'heart' => $outfit['states']['heart'] ?? '/assets/images/couple/04-heart.png',
    'ending' => $outfit['states']['ending'] ?? '/assets/images/couple/05-ending.png',
];
?>
<aside class="floatingCouple isHidden" data-floating-couple aria-hidden="true">
    <div class="floatingCouple__frame" data-couple-pair>
        <?php foreach ($stages as $key => $src): ?>
            <div
                class="floatingCouple__layer<?= $key === 'casual' ? ' isActive' : '' ?>"
                data-outfit-layer="<?= e($key) ?>"
            >
                <img
                    src="<?= e(assetUrl($src)) ?>"
                    alt=""
                    width="256"
                    height="256"
                    decoding="async"
                    onerror="this.style.display='none'; this.parentElement.classList.add('isFallback');"
                >
                <span class="floatingCouple__fallbackSilhouette"></span>
            </div>
        <?php endforeach; ?>
    </div>
</aside>
