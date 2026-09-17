<?php
/** @var array $weddingData */
$accounts = $weddingData['accounts'];
$hasGroom = !empty($accounts['groom']);
$hasBride = !empty($accounts['bride']);
?>
<section class="scene scene--account scene--flow" data-scene="account" data-couple-state="heart" id="account">
    <div class="accountIntro">
        <button type="button" class="accountSheet__open touchBtn" data-account-open>마음 전하실 곳</button>
        <img
            class="accountIntro__flower"
            src="<?= e(assetUrl('/assets/images/account-flower.png')) ?>"
            alt=""
            width="35"
            height="35"
            decoding="async"
        >
        <p class="accountSheet__note">참석이 어려우신 분들을 위해<br>마음을 전하실 수 있는 공간을 마련했습니다.<br><br>보내주시는 소중한 축하에<br>진심으로 감사드립니다.</p>
    </div>
    <div class="accountSheet" data-account-sheet hidden aria-hidden="true">
        <div class="accountSheet__backdrop" data-account-close></div>
        <div class="accountSheet__panel">
            <button type="button" class="accountSheet__close touchBtn" data-account-close aria-label="닫기">&times;</button>
            <p class="accountSheet__title">마음 전하실 곳</p>
            <div class="accountSheet__tabs" role="tablist">
                <button type="button" class="accountSheet__tab isActive touchBtn" data-account-tab="groom" role="tab" aria-selected="true">신랑측</button>
                <button type="button" class="accountSheet__tab touchBtn" data-account-tab="bride" role="tab" aria-selected="false">신부측</button>
            </div>
            <div class="accountSheet__content" data-account-panel="groom">
                <?php if (!$hasGroom): ?>
                    <p class="accountSheet__empty">계좌 정보 입력 예정</p>
                <?php else: ?>
                    <?php foreach ($accounts['groom'] as $acc): ?>
                        <button type="button" class="accountRow touchBtn" data-copy-target="<?= e($acc['number'] ?? '') ?>">
                            <strong><?= e($acc['holder'] ?? '') ?></strong>
                            <span><?= e(($acc['bank'] ?? '') . ' ' . ($acc['number'] ?? '')) ?></span>
                            <span class="accountRow__hint" data-copy-label>터치하여 복사</span>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="accountSheet__content" data-account-panel="bride" hidden>
                <?php if (!$hasBride): ?>
                    <p class="accountSheet__empty">계좌 정보 입력 예정</p>
                <?php else: ?>
                    <?php foreach ($accounts['bride'] as $acc): ?>
                        <button type="button" class="accountRow touchBtn" data-copy-target="<?= e($acc['number'] ?? '') ?>">
                            <strong><?= e($acc['holder'] ?? '') ?></strong>
                            <span><?= e(($acc['bank'] ?? '') . ' ' . ($acc['number'] ?? '')) ?></span>
                            <span class="accountRow__hint" data-copy-label>터치하여 복사</span>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
