<?php
/** @var array $weddingData */
$g = $weddingData['groom'];
$b = $weddingData['bride'];
$w = $weddingData['wedding'];
?>
<footer class="scene scene--ending scene--full" data-scene="ending" data-ending-merge data-couple-state="ending">
    <p class="endingMerge__name endingMerge__name--g" data-merge-groom><?= e($g['name']) ?></p>
    <p class="endingMerge__symbol" data-merge-symbol>&amp;</p>
    <p class="endingMerge__name endingMerge__name--b" data-merge-bride><?= e($b['name']) ?></p>
    <p class="endingMerge__date" data-merge-date>2027.09.25</p>
    <p class="endingMerge__see">그날 뵙겠습니다</p>
    <p class="endingMerge__venue"><?= e($w['venueFull'] ?? $w['venue']) ?></p>
    <p class="endingMerge__thanks">감사합니다.</p>
</footer>
