<?php
/** @var array $weddingData */
$inv = $weddingData['invitation'] ?? [];
$caption = $inv['letterCaption'] ?? '초대합니다';
$lines = $inv['lines'] ?? [];
$closing = $inv['closing'] ?? [];
?>
<section
    class="scene scene--letter scene--full"
    data-scene="letter"
    data-couple-state="heart"
    id="letter"
>
    <div class="letterScene">
        <p class="letterScene__caption"><?= e($caption) ?></p>

        <div class="letterScene__body" data-letter-lines>
            <?php foreach ($lines as $i => $line): ?>
                <p
                    class="letterScene__line"
                    data-letter-line
                    style="--letter-index: <?= (int) $i ?>"
                ><?= e($line) ?></p>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($closing)): ?>
            <div class="letterScene__closing" data-letter-closing>
                <?php foreach ($closing as $i => $line): ?>
                    <p
                        class="letterScene__closeLine"
                        data-letter-line
                        style="--letter-index: <?= (int) (count($lines) + $i) ?>"
                    ><?= e($line) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p class="letterScene__rule" aria-hidden="true"></p>
        <p class="letterScene__foot">2027.09.25</p>
    </div>
</section>
