<?php
/** Guestbook legal notice modals — 웨딩무드 */
?>
<div
    class="guestbookLegal"
    data-guestbook-legal="privacy"
    hidden
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="guestbookPrivacyTitle"
>
    <div class="guestbookLegal__backdrop" data-guestbook-legal-close></div>
    <div class="guestbookLegal__panel">
        <button type="button" class="guestbookLegal__close touchBtn" data-guestbook-legal-close aria-label="닫기">&times;</button>
        <h3 class="guestbookLegal__title" id="guestbookPrivacyTitle">개인정보 처리방침</h3>
        <div class="guestbookLegal__body">
            <?php require dirname(__DIR__) . '/legal/privacy.php'; ?>
        </div>
        <button type="button" class="guestbookLegal__done touchBtn" data-guestbook-legal-close>확인</button>
    </div>
</div>

<div
    class="guestbookLegal"
    data-guestbook-legal="terms"
    hidden
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="guestbookTermsTitle"
>
    <div class="guestbookLegal__backdrop" data-guestbook-legal-close></div>
    <div class="guestbookLegal__panel">
        <button type="button" class="guestbookLegal__close touchBtn" data-guestbook-legal-close aria-label="닫기">&times;</button>
        <h3 class="guestbookLegal__title" id="guestbookTermsTitle">이용약관</h3>
        <div class="guestbookLegal__body">
            <?php require dirname(__DIR__) . '/legal/terms.php'; ?>
        </div>
        <button type="button" class="guestbookLegal__done touchBtn" data-guestbook-legal-close>확인</button>
    </div>
</div>
