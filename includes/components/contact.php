<?php
/** @var array $weddingData */
$c = $weddingData['contact'] ?? [];
?>
<section class="scene scene--contact scene--flow" data-scene="contact" data-couple-state="heart">
    <p class="sceneLabel">연락처</p>
    <p class="contact__placeholder">연락처 입력 예정</p>
    <?php if (!empty($c['groomPhone']) || !empty($c['bridePhone'])): ?>
        <ul class="contact__list">
            <?php if (!empty($c['groomPhone'])): ?>
                <li><a class="touchBtn" href="tel:<?= e(preg_replace('/\D/', '', $c['groomPhone'])) ?>">신랑 <?= e($c['groomPhone']) ?></a></li>
            <?php endif; ?>
            <?php if (!empty($c['bridePhone'])): ?>
                <li><a class="touchBtn" href="tel:<?= e(preg_replace('/\D/', '', $c['bridePhone'])) ?>">신부 <?= e($c['bridePhone']) ?></a></li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>
</section>
