<?php
/** @var array $weddingData */
$g = $weddingData['groom'];
$b = $weddingData['bride'];
?>
<section class="scene scene--guestbook scene--flow" data-scene="guestbook" id="guestbook" data-couple-state="heart">
    <p class="sceneLabel">방명록</p>
    <p class="guestbook__for"><?= e($g['name']) ?> &amp; <?= e($b['name']) ?></p>
    <p class="guestbook__lead">따뜻한 한마디를 남겨주세요</p>

    <button type="button" class="guestbookWrite touchBtn" data-guestbook-write>
        <span class="guestbookWrite__icon" aria-hidden="true">✎</span>
        축하 메시지 남기기
    </button>

    <div class="guestbookBox" aria-hidden="true">
        <div class="guestbookBox__lid"></div>
        <div class="guestbookBox__body">
            <span>MESSAGE BOX</span>
        </div>
    </div>

    <div class="guestbookList" data-guestbook-list>
        <p class="guestbookList__empty" data-guestbook-empty>
            아직 도착한 메시지가 없어요.<br>
            첫 번째 쪽지를 남겨주세요.
        </p>
    </div>
    <button type="button" class="guestbookMore touchBtn" data-guestbook-more hidden>더 보기</button>
</section>

<div class="guestbookSheet" data-guestbook-sheet hidden aria-hidden="true">
    <div class="guestbookSheet__backdrop" data-guestbook-sheet-close></div>
    <div class="guestbookSheet__panel" role="dialog" aria-label="축하 메시지 작성">
        <button type="button" class="guestbookSheet__close touchBtn" data-guestbook-sheet-close aria-label="닫기">&times;</button>
        <p class="guestbookSheet__title">쪽지 남기기</p>
        <p class="guestbookSheet__hint">작성 후 두 사람에게 배달돼요</p>

        <form class="guestbookForm" data-guestbook-form novalidate>
            <label class="guestbookForm__field">
                <span>이름</span>
                <input type="text" name="name" maxlength="20" autocomplete="name" required placeholder="홍길동">
            </label>
            <label class="guestbookForm__field">
                <span>메시지</span>
                <textarea name="message" maxlength="300" rows="4" required placeholder="두 분 행복하세요!"></textarea>
            </label>
            <label class="guestbookForm__field">
                <span>비밀번호 (삭제용 · 4자 이상)</span>
                <input type="password" name="password" minlength="4" autocomplete="new-password" required placeholder="****">
            </label>
            <input type="text" name="website" class="guestbookForm__honey" tabindex="-1" autocomplete="off" aria-hidden="true">
            <p class="guestbookForm__error" data-guestbook-error hidden></p>
            <button type="submit" class="guestbookForm__submit touchBtn" data-guestbook-submit>배달하기</button>
        </form>
    </div>
</div>

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
