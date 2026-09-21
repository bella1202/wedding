<?php
/** @var array $weddingData */
?>
<section class="scene scene--guestbook scene--flow" data-scene="guestbook" id="guestbook" data-couple-state="ending">
    <p class="sceneLabel">방명록</p>
    <p class="guestbook__lead">따뜻한 한마디를 남겨주세요</p>

    <button type="button" class="guestbookBox touchBtn" data-guestbook-more aria-label="메시지 박스 열기">
        <span class="guestbookBox__notes" aria-hidden="true">
            <span class="guestbookBox__note guestbookBox__note--a"><i>♥</i></span>
            <span class="guestbookBox__note guestbookBox__note--b"><i>♥</i></span>
            <span class="guestbookBox__note guestbookBox__note--c"><i>♥</i></span>
        </span>
        <span class="guestbookBox__unit">
            <span class="guestbookBox__lid"></span>
            <span class="guestbookBox__body">
                <span>MESSAGE BOX</span>
            </span>
        </span>
    </button>

    <button type="button" class="guestbookWrite touchBtn" data-guestbook-write>
        작성하기
    </button>
</section>

<div
    class="guestbookAll"
    data-guestbook-all
    hidden
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="guestbookAllTitle"
>
    <div class="guestbookAll__backdrop" data-guestbook-all-close></div>
    <div class="guestbookAll__panel">
        <button type="button" class="guestbookAll__close touchBtn" data-guestbook-all-close aria-label="닫기">&times;</button>
        <p class="guestbookAll__title" id="guestbookAllTitle">방명록</p>
        <div class="guestbookAll__list" data-guestbook-all-list>
            <p class="guestbookAll__empty" data-guestbook-all-empty hidden>
                아직 도착한 메시지가 없어요.<br>
                첫 번째 쪽지를 남겨주세요.
            </p>
        </div>
        <button type="button" class="guestbookAll__more touchBtn" data-guestbook-all-more hidden>더 불러오기</button>
    </div>
</div>

<div class="guestbookSheet" data-guestbook-sheet hidden aria-hidden="true">
    <div class="guestbookSheet__backdrop" data-guestbook-sheet-close></div>
    <div class="guestbookSheet__panel" role="dialog" aria-label="방명록 작성">
        <button type="button" class="guestbookSheet__close touchBtn" data-guestbook-sheet-close aria-label="닫기">&times;</button>
        <p class="guestbookSheet__title">방명록 작성</p>
        <p class="guestbookSheet__hint">작성 후 두 사람에게 배달돼요</p>

        <form class="guestbookForm" data-guestbook-form novalidate>
            <label class="guestbookForm__field">
                <span>이름</span>
                <input type="text" name="name" maxlength="20" autocomplete="name" required placeholder="홍길동">
            </label>
            <label class="guestbookForm__field">
                <span>축하 메시지</span>
                <textarea name="message" maxlength="300" rows="4" required placeholder="두 분 행복하세요!"></textarea>
            </label>
            <label class="guestbookForm__field">
                <span>비밀번호 (삭제용•4자 이상)</span>
                <input type="password" name="password" minlength="4" autocomplete="new-password" required placeholder="****">
            </label>
            <input type="text" name="website" class="guestbookForm__honey" tabindex="-1" autocomplete="off" aria-hidden="true">
            <p class="guestbookForm__error" data-guestbook-error hidden></p>
            <button type="submit" class="guestbookForm__submit touchBtn" data-guestbook-submit>배달하기</button>
        </form>

        <div class="guestbookSheet__legal">
            <button type="button" class="guestbookSheet__legalLink touchBtn" data-guestbook-legal-open="privacy">개인정보처리방침</button>
            <span class="guestbookSheet__legalSep" aria-hidden="true">·</span>
            <button type="button" class="guestbookSheet__legalLink touchBtn" data-guestbook-legal-open="terms">이용약관</button>
        </div>
    </div>
</div>

<?php require __DIR__ . '/guestbook-legal.php'; ?>

<div class="guestbookDeleteModal" data-guestbook-delete-modal hidden aria-hidden="true">
    <div class="guestbookDeleteModal__backdrop" data-guestbook-delete-close></div>
    <div class="guestbookDeleteModal__panel" role="dialog" aria-label="방명록 삭제">
        <p class="guestbookDeleteModal__title">메시지를 삭제할까요?</p>
        <label class="guestbookForm__field">
            <span>비밀번호</span>
            <input type="password" data-guestbook-delete-password minlength="4" placeholder="****">
        </label>
        <p class="guestbookForm__error" data-guestbook-delete-error hidden></p>
        <div class="guestbookDeleteModal__actions">
            <button type="button" class="touchBtn" data-guestbook-delete-close>취소</button>
            <button type="button" class="touchBtn guestbookDeleteModal__confirm" data-guestbook-delete-confirm>삭제</button>
        </div>
    </div>
</div>

<div class="guestbookToast" data-guestbook-toast hidden>마음이 도착했어요 ♥</div>
