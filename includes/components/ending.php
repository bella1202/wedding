<?php
/** @var array $weddingData */
$w = $weddingData['wedding'];
$footDate = $w['displayDateDot'] ?? str_replace('-', '.', $w['date']);
?>
<footer class="scene scene--ending scene--full" data-scene="ending" data-couple-state="ending">
    <p class="endingMerge__date"><?= e($footDate) ?></p>
    <p class="endingMerge__see">그날 뵙겠습니다</p>
    <p class="endingMerge__venue"></p>
    <p class="endingMerge__thanks">감사합니다.</p>
    <p class="endingMerge__copy">COPYRIGHT SR. All rights reserved.</p>
</footer>
