<?php
/** @var array $weddingData */
$title = $weddingData['invitation']['title'] ?? '';
?>
<section class="scene scene--invitation scene--flow" data-scene="invitation" data-couple-state="casual">
    <p class="sceneLabel">인사말</p>
    <?php if ($title !== ''): ?>
        <h2 class="invitation__title"><?= e($title) ?></h2>
    <?php endif; ?>
</section>
