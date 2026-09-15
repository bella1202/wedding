<?php
/** @var array $weddingData */
$g = $weddingData['groom'];
$b = $weddingData['bride'];
?>
<section class="scene scene--guestbook scene--flow" data-scene="guestbook" id="guestbook" data-couple-state="heart">
    <p class="sceneLabel">방명록</p>
    <p class="guestbook__for">
        <?= e($g['name']) ?> &amp; <?= e($b['name']) ?>
    </p>
    <p class="guestbook__lead">축하의 마음을 남겨주세요</p>

    <form class="guestbookForm" data-guestbook-form novalidate>
        <label class="guestbookForm__field">
            <span>이름</span>
            <input type="text" name="name" maxlength="20" autocomplete="name" required placeholder="홍길동">
        </label>
        <label class="guestbookForm__field">
            <span>마음을 남겨주세요</span>
            <textarea name="message" maxlength="300" rows="4" required placeholder="두 분 행복하세요!"></textarea>
        </label>
        <label class="guestbookForm__field">
            <span>비밀번호 (삭제용)</span>
            <input type="password" name="password" minlength="4" autocomplete="new-password" required placeholder="****">
        </label>
        <input type="text" name="website" class="guestbookForm__honey" tabindex="-1" autocomplete="off" aria-hidden="true">
        <p class="guestbookForm__error" data-guestbook-error hidden></p>
        <button type="submit" class="guestbookForm__submit touchBtn" data-guestbook-submit>마음 남기기</button>
    </form>

    <div class="guestbookList" data-guestbook-list>
        <p class="guestbookList__empty" data-guestbook-empty>첫 번째 마음을 남겨주세요.</p>
    </div>
    <button type="button" class="guestbookMore touchBtn" data-guestbook-more hidden>더 보기</button>
</section>

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
