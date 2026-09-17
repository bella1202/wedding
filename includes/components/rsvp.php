<?php
/** @var array $weddingData */
$g = $weddingData['groom'];
$b = $weddingData['bride'];
$w = $weddingData['wedding'];
$loc = $weddingData['location'] ?? [];

$venueLabel = $w['venueFull'] ?? ($loc['name'] ?? ($w['venue'] ?? ''));

$weddingDate = $w['date'] ?? '2027-09-25';
[$yy, $mm, $dd] = array_map('intval', explode('-', $weddingDate));
$dateLine = sprintf(
    '%d년 %d월 %d일 %s %s',
    $yy,
    $mm,
    $dd,
    $w['day'] ?? '',
    $w['displayTime'] ?? ''
);
?>
<div
    class="rsvpModal"
    data-rsvp-modal
    hidden
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="rsvpModalTitle"
>
    <div class="rsvpModal__backdrop" data-rsvp-close></div>
    <div class="rsvpModal__panel">
        <button type="button" class="rsvpModal__close touchBtn" data-rsvp-close aria-label="닫기">&times;</button>

        <div class="rsvpModal__view" data-rsvp-view="intro">
            <p class="rsvpModal__eyebrow">RSVP</p>
            <h2 class="rsvpModal__title" id="rsvpModalTitle">함께해 주실 수 있을까요?</h2>
            <p class="rsvpModal__lead">
                소중한 시간을 내어 참석해주시는<br>
                모든 분들께 감사드립니다.<br>
                귀하게 모실 수 있도록<br>
                참석 여부를 전달해주세요.
            </p>

            <div class="rsvpModal__info">
                <p class="rsvpModal__infoRow">
                    <span class="rsvpModal__infoIcon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 20s-7-4.4-7-9.2A3.8 3.8 0 0112 8.2a3.8 3.8 0 017 2.6C19 15.6 12 20 12 20z"/></svg>
                    </span>
                    <span>신랑 <?= e($g['name']) ?> &amp; 신부 <?= e($b['name']) ?></span>
                </p>
                <p class="rsvpModal__infoRow">
                    <span class="rsvpModal__infoIcon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                    </span>
                    <span><?= e($dateLine) ?></span>
                </p>
                <p class="rsvpModal__infoRow">
                    <span class="rsvpModal__infoIcon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-5.2 7-11a7 7 0 10-14 0c0 5.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.2"/></svg>
                    </span>
                    <span><?= e($venueLabel) ?></span>
                </p>
            </div>

            <button type="button" class="rsvpModal__cta touchBtn" data-rsvp-open-form>
                참석 의사 전달하기
            </button>

            <label class="rsvpModal__hideToday">
                <input type="checkbox" data-rsvp-hide-today>
                <span class="rsvpModal__check" aria-hidden="true"></span>
                <span class="rsvpModal__hideLabel">오늘 하루 보지 않기</span>
            </label>
        </div>

        <div class="rsvpModal__view" data-rsvp-view="form" hidden>
            <p class="rsvpModal__eyebrow">RSVP</p>
            <h2 class="rsvpModal__title">참석 여부를 알려주세요</h2>
            <p class="rsvpModal__lead rsvpModal__lead--compact">참석 여부를 알려주시면 정성껏 준비하는 데 큰 도움이 됩니다.</p>

            <form class="rsvpForm" data-rsvp-form novalidate>
                <input class="rsvpForm__honey" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div class="rsvpForm__field">
                    <div class="rsvpForm__tripleLabels">
                        <span>성함 <i class="rsvpForm__req" aria-hidden="true">*</i></span>
                        <span class="rsvpForm__tripleLabels--span2">구분 <i class="rsvpForm__req" aria-hidden="true">*</i></span>
                    </div>
                    <div class="rsvpForm__chips rsvpForm__chips--3" role="group" aria-label="성함 및 구분">
                        <label class="rsvpForm__nameCell">
                            <span class="visuallyHidden">성함</span>
                            <input type="text" name="name" maxlength="20" placeholder="홍길동" required autocomplete="name">
                        </label>
                        <label class="rsvpForm__chip">
                            <input type="radio" name="side" value="groom" required>
                            <span>신랑측</span>
                        </label>
                        <label class="rsvpForm__chip">
                            <input type="radio" name="side" value="bride">
                            <span>신부측</span>
                        </label>
                    </div>
                </div>

                <div class="rsvpForm__field" data-rsvp-attend-block>
                    <div class="rsvpForm__tripleLabels">
                        <span class="rsvpForm__tripleLabels--span2">참석 여부 <i class="rsvpForm__req" aria-hidden="true">*</i></span>
                        <span data-rsvp-guests-label hidden>참석 인원 <i class="rsvpForm__req" aria-hidden="true">*</i></span>
                    </div>
                    <div class="rsvpForm__chips rsvpForm__chips--2" data-rsvp-attend-grid role="radiogroup" aria-label="참석 여부">
                        <label class="rsvpForm__chip">
                            <input type="radio" name="attending" value="yes" required>
                            <span>참석</span>
                        </label>
                        <label class="rsvpForm__chip">
                            <input type="radio" name="attending" value="no">
                            <span>불참</span>
                        </label>
                        <label class="rsvpForm__guestCell" data-rsvp-guests-field hidden>
                            <span class="visuallyHidden">참석 인원 (본인 포함)</span>
                            <input
                                type="text"
                                name="guests"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="2"
                                value="1"
                                autocomplete="off"
                                data-rsvp-guests
                                required
                            >
                        </label>
                    </div>
                </div>

                <div class="rsvpForm__attendFields" data-rsvp-attend-fields hidden>
                    <fieldset class="rsvpForm__field">
                        <legend>식사 여부 <i class="rsvpForm__req" aria-hidden="true">*</i></legend>
                        <div class="rsvpForm__chips rsvpForm__chips--3" role="radiogroup" aria-label="식사 여부">
                            <label class="rsvpForm__chip">
                                <input type="radio" name="meal" value="yes">
                                <span>식사함</span>
                            </label>
                            <label class="rsvpForm__chip">
                                <input type="radio" name="meal" value="no">
                                <span>안함</span>
                            </label>
                            <label class="rsvpForm__chip">
                                <input type="radio" name="meal" value="maybe">
                                <span>미정</span>
                            </label>
                        </div>
                    </fieldset>

                    <fieldset class="rsvpForm__field">
                        <legend>전세버스 탑승 여부 <i class="rsvpForm__req" aria-hidden="true">*</i></legend>
                        <div class="rsvpForm__chips" role="radiogroup" aria-label="전세버스 탑승 여부">
                            <label class="rsvpForm__chip">
                                <input type="radio" name="bus" value="yes">
                                <span>탑승</span>
                            </label>
                            <label class="rsvpForm__chip">
                                <input type="radio" name="bus" value="no">
                                <span>미탑승</span>
                            </label>
                        </div>
                    </fieldset>
                </div>

                <label class="rsvpForm__field">
                    <span>전하고 싶은 말</span>
                    <textarea name="message" maxlength="300" rows="3" placeholder="축하 메시지를 남겨주세요"></textarea>
                </label>

                <label class="rsvpForm__consent">
                    <input type="checkbox" name="privacy" value="1" data-rsvp-privacy required>
                    <span class="rsvpModal__check" aria-hidden="true"></span>
                    <span class="rsvpForm__consentText">
                        개인정보 수집 및 활용 동의
                        <i class="rsvpForm__req" aria-hidden="true">*</i>
                        <button type="button" class="rsvpForm__consentLink" data-rsvp-privacy-open>[보기]</button>
                    </span>
                </label>

                <p class="rsvpForm__error" data-rsvp-error hidden></p>

                <button type="submit" class="rsvpForm__submit touchBtn" data-rsvp-submit>전달하기</button>
                <button type="button" class="rsvpForm__back touchBtn" data-rsvp-back>이전으로</button>
            </form>
        </div>

        <div class="rsvpModal__view" data-rsvp-view="done" hidden>
            <p class="rsvpModal__eyebrow">THANK YOU</p>
            <h2 class="rsvpModal__title">전달되었습니다</h2>
            <p class="rsvpModal__lead" data-rsvp-done-msg="yes">
                소중한 마음 감사드립니다.<br>
                예식에서 뵙기를 기대할게요.
            </p>
            <p class="rsvpModal__lead" data-rsvp-done-msg="no" hidden>
                소중한 마음 감사드립니다.<br>
                아쉽지만 축하의 마음만으로도 충분합니다.
            </p>
            <button type="button" class="rsvpModal__cta touchBtn" data-rsvp-close>
                청첩장 보기
            </button>
        </div>
    </div>
</div>

<div
    class="rsvpPrivacy"
    data-rsvp-privacy-modal
    hidden
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="rsvpPrivacyTitle"
>
    <div class="rsvpPrivacy__backdrop" data-rsvp-privacy-close></div>
    <div class="rsvpPrivacy__panel">
        <button type="button" class="rsvpPrivacy__close touchBtn" data-rsvp-privacy-close aria-label="닫기">&times;</button>
        <h3 class="rsvpPrivacy__title" id="rsvpPrivacyTitle">(필수) 개인정보 수집 및 활용 동의</h3>
        <div class="rsvpPrivacy__body">
            <p><strong>[수집 항목]</strong><br>이름, 전화번호등 RSVP에 입력 된 항목</p>
            <p><strong>[이용 목적]</strong><br>결혼식 참석 여부 확인 및 관련 서비스 제공</p>
            <p><strong>[보유 및 이용 기간]</strong><br>동의일로부터 청첩장 유효기간 동안</p>
            <p><strong>[동의 거부권 및 불이익 사항]</strong><br>동의 거부 시 서비스 이용 불가</p>
        </div>
        <button type="button" class="rsvpPrivacy__done touchBtn" data-rsvp-privacy-close>확인</button>
    </div>
</div>
