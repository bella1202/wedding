<?php
/** @var array $weddingData */
$inv = $weddingData['invitation'] ?? [];
$title = $inv['title'] ?? '소중한 분들을 초대합니다';
$quoteEn = $inv['quoteEn'] ?? [];
$quoteKo = $inv['quoteKo'] ?? [];
$quoteCredit = $inv['quoteCredit'] ?? '';
$lines = $inv['lines'] ?? [];
$closing = $inv['closing'] ?? [];

$groom = $weddingData['groom'] ?? [];
$bride = $weddingData['bride'] ?? [];
$phones = $weddingData['contact'] ?? [];

$groomParents = trim(($groom['father'] ?? '') . ' · ' . ($groom['mother'] ?? ''), ' ·');
$brideParents = trim(($bride['father'] ?? '') . ' · ' . ($bride['mother'] ?? ''), ' ·');

$contactGroups = [
    'groom' => [
        'label' => '신랑측',
        'people' => [
            ['role' => '아버지', 'name' => $groom['father'] ?? '', 'phone' => $phones['groom']['father'] ?? ''],
            ['role' => '어머니', 'name' => $groom['mother'] ?? '', 'phone' => $phones['groom']['mother'] ?? ''],
            ['role' => '신랑', 'name' => $groom['name'] ?? '', 'phone' => $phones['groom']['self'] ?? ''],
        ],
    ],
    'bride' => [
        'label' => '신부측',
        'people' => [
            ['role' => '아버지', 'name' => $bride['father'] ?? '', 'phone' => $phones['bride']['father'] ?? ''],
            ['role' => '어머니', 'name' => $bride['mother'] ?? '', 'phone' => $phones['bride']['mother'] ?? ''],
            ['role' => '신부', 'name' => $bride['name'] ?? '', 'phone' => $phones['bride']['self'] ?? ''],
        ],
    ],
];

$letterIndex = 0;

$phoneDigits = static function (string $phone): string {
    return preg_replace('/\D+/', '', $phone) ?? '';
};
?>
<section
    class="scene scene--invitation scene--full"
    data-scene="invitation"
    data-couple-state="casual"
    id="invitation"
>
    <div class="inviteScene">
        <?php if (!empty($quoteEn) || !empty($quoteKo) || $quoteCredit !== ''): ?>
            <blockquote
                class="inviteScene__quote"
                data-letter-line
                style="--letter-index: <?= (int) $letterIndex++ ?>"
            >
                <?php if (!empty($quoteEn)): ?>
                    <div class="inviteScene__quoteEn">
                        <?php foreach ($quoteEn as $line): ?>
                            <p><?= e($line) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($quoteKo)): ?>
                    <div class="inviteScene__quoteKo">
                        <?php foreach ($quoteKo as $line): ?>
                            <p><?= e($line) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($quoteCredit !== ''): ?>
                    <footer class="inviteScene__quoteCredit">
                        &lt;Zootopia&gt;, Judy Hopps
                    </footer>
                <?php endif; ?>
            </blockquote>
        <?php endif; ?>

        <img
            class="inviteScene__flower"
            src="<?= e(assetUrl('/assets/images/invite-flower.png')) ?>"
            alt=""
            width="58"
            height="58"
            decoding="async"
            data-letter-line
            style="--letter-index: <?= (int) $letterIndex++ ?>"
        >

        <h2
            class="inviteScene__title"
            data-letter-line
            style="--letter-index: <?= (int) $letterIndex++ ?>"
        ><?= e($title) ?></h2>

        <div class="inviteScene__body">
            <?php foreach ($lines as $line): ?>
                <p
                    class="inviteScene__line"
                    data-letter-line
                    style="--letter-index: <?= (int) $letterIndex++ ?>"
                ><?= e($line) ?></p>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($closing)): ?>
            <div class="inviteScene__closing">
                <?php foreach ($closing as $line): ?>
                    <p
                        class="inviteScene__closeLine"
                        data-letter-line
                        style="--letter-index: <?= (int) $letterIndex++ ?>"
                    ><?= e($line) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div
            class="inviteScene__parents"
            data-letter-line
            style="--letter-index: <?= (int) $letterIndex++ ?>"
        >
            <p class="inviteScene__parentLine">
                <?= e($groomParents) ?> <span class="inviteScene__of">의</span> <?= e($groom['relation'] ?? '장남') ?>
                <strong><?= e($groom['name'] ?? '') ?></strong>
            </p>
            <p class="inviteScene__parentLine">
                <?= e($brideParents) ?> <span class="inviteScene__of">의</span> <?= e($bride['relation'] ?? '차녀') ?>
                <strong><?= e($bride['name'] ?? '') ?></strong>
            </p>
        </div>

    </div>

    <button
        type="button"
        class="inviteScene__contactBtn touchBtn"
        data-contact-open
        data-letter-line
        style="--letter-index: <?= (int) $letterIndex++ ?>"
    >연락하기</button>

    <div class="contactSheet" data-contact-sheet hidden aria-hidden="true">
        <div class="contactSheet__backdrop" data-contact-close></div>
        <div class="contactSheet__panel" role="dialog" aria-modal="true" aria-labelledby="contactSheetTitle">
            <button type="button" class="contactSheet__close touchBtn" data-contact-close aria-label="닫기">&times;</button>
            <p class="contactSheet__title" id="contactSheetTitle">연락하기</p>
            <div class="contactSheet__tabs" role="tablist">
                <button type="button" class="contactSheet__tab isActive touchBtn" data-contact-tab="groom" role="tab" aria-selected="true">신랑측</button>
                <button type="button" class="contactSheet__tab touchBtn" data-contact-tab="bride" role="tab" aria-selected="false">신부측</button>
            </div>
            <?php foreach ($contactGroups as $key => $group): ?>
                <div
                    class="contactSheet__panelBody"
                    data-contact-panel="<?= e($key) ?>"
                    <?= $key === 'groom' ? '' : 'hidden' ?>
                >
                    <ul class="contactSheet__list">
                        <?php foreach ($group['people'] as $person): ?>
                            <?php
                            $digits = $phoneDigits((string) ($person['phone'] ?? ''));
                            $hasPhone = $digits !== '';
                            ?>
                            <li class="contactSheet__row">
                                <div class="contactSheet__who">
                                    <span class="contactSheet__role"><?= e($person['role']) ?></span>
                                    <strong class="contactSheet__name"><?= e($person['name']) ?></strong>
                                </div>
                                <div class="contactSheet__actions">
                                    <?php if ($hasPhone): ?>
                                        <a
                                            class="contactSheet__action touchBtn"
                                            href="tel:<?= e($digits) ?>"
                                            aria-label="<?= e($person['name']) ?>에게 전화"
                                        >
                                            <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="currentColor">
                                                <path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 011 1V20a1 1 0 01-1 1C10.4 21 3 13.6 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.46.57 3.58a1 1 0 01-.25 1.02l-2.2 2.19z"/>
                                            </svg>
                                        </a>
                                        <a
                                            class="contactSheet__action touchBtn"
                                            href="sms:<?= e($digits) ?>"
                                            aria-label="<?= e($person['name']) ?>에게 메시지"
                                        >
                                            <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 6.5A2.5 2.5 0 016.5 4h11A2.5 2.5 0 0120 6.5v7A2.5 2.5 0 0117.5 16H10l-4 3.2V16H6.5A2.5 2.5 0 014 13.5v-7z"/>
                                            </svg>
                                        </a>
                                    <?php else: ?>
                                        <span class="contactSheet__action isDisabled" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                                <path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 011 1V20a1 1 0 01-1 1C10.4 21 3 13.6 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.46.57 3.58a1 1 0 01-.25 1.02l-2.2 2.19z"/>
                                            </svg>
                                        </span>
                                        <span class="contactSheet__action isDisabled" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 6.5A2.5 2.5 0 016.5 4h11A2.5 2.5 0 0120 6.5v7A2.5 2.5 0 0117.5 16H10l-4 3.2V16H6.5A2.5 2.5 0 014 13.5v-7z"/>
                                            </svg>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
