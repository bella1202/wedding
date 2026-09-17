<?php
/** @var array $weddingData */
$w = $weddingData['wedding'];
$g = $weddingData['groom'];
$b = $weddingData['bride'];
$weddingDate = $w['date'] ?? '2027-09-25';
[$weddingYear, $weddingMonth, $weddingDay] = array_map('intval', explode('-', $weddingDate));
?>
<section
    class="scene scene--date scene--full"
    data-scene="weddingDate"
    id="weddingDate"
    data-couple-state="date"
    data-wedding-countdown
    data-calendar-year="<?= (int) $weddingYear ?>"
    data-calendar-month="<?= (int) $weddingMonth ?>"
    data-calendar-day="<?= (int) $weddingDay ?>"
>
    <div class="dateScene">
        <p class="sceneLabel">예식 안내</p>

        <div class="dateHero" data-date-stage="hero">
            <p class="dateHero__ymd">
                <span data-date-part="y"><?= sprintf('%04d', $weddingYear) ?></span><span class="dateHero__dot">.</span><span data-date-part="m"><?= sprintf('%02d', $weddingMonth) ?></span><span class="dateHero__dot">.</span><span data-date-part="d"><?= sprintf('%02d', $weddingDay) ?></span>
            </p>
            <div class="dateMeta" data-date-meta>
                <p class="dateMeta__line"><?= e($w['day']) ?> <?= e($w['displayTime']) ?></p>
            </div>
        </div>

        <div class="weddingCalendar" data-wedding-calendar data-date-stage="calendar">
            <div class="weddingCalendar__head">
                <p class="weddingCalendar__title" data-calendar-title>2027년 9월</p>
                <p class="weddingCalendar__hint">♥ 예식일</p>
            </div>
            <div class="weddingCalendar__weekdays" aria-hidden="true">
                <span>일</span><span>월</span><span>화</span><span>수</span><span>목</span><span>금</span><span>토</span>
            </div>
            <div class="weddingCalendar__grid" data-calendar-grid></div>
        </div>

        <div class="dateCountdown" data-date-countdown data-date-stage="countdown">
            <div class="dateCountdown__units" data-countdown-units aria-live="polite">
                <div class="dateCountdown__unit">
                    <span class="dateCountdown__value" data-countdown="days">00</span>
                    <span class="dateCountdown__label">DAYS</span>
                </div>
                <span class="dateCountdown__sep" aria-hidden="true">:</span>
                <div class="dateCountdown__unit">
                    <span class="dateCountdown__value" data-countdown="hours">00</span>
                    <span class="dateCountdown__label">HOUR</span>
                </div>
                <span class="dateCountdown__sep" aria-hidden="true">:</span>
                <div class="dateCountdown__unit">
                    <span class="dateCountdown__value" data-countdown="mins">00</span>
                    <span class="dateCountdown__label">MIN</span>
                </div>
                <span class="dateCountdown__sep" aria-hidden="true">:</span>
                <div class="dateCountdown__unit">
                    <span class="dateCountdown__value" data-countdown="secs">00</span>
                    <span class="dateCountdown__label">SEC</span>
                </div>
            </div>
            <p class="dateCountdown__caption" data-countdown-caption>
                <?= e(mb_substr($g['name'], 1)) ?>
                <i class="dateCountdown__heart" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="14" height="14" focusable="false" aria-hidden="true">
                        <path fill="currentColor" d="M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3z"/>
                    </svg>
                </i>
                <?= e(mb_substr($b['name'], 1)) ?>이의 결혼식이<br>
                <span class="dateCountdown__remain"><span data-countdown-days-text>0</span>일</span> 남았습니다.
            </p>
            <p class="dateCountdown__status" data-countdown-status hidden></p>
        </div>
    </div>
</section>
