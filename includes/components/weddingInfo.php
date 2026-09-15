<?php
/** @var array $weddingData */
$w = $weddingData['wedding'];
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
                <p><?= e($w['day']) ?></p>
                <p><?= e($w['displayTime']) ?></p>
                <p><?= e($w['venue']) ?></p>
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

        <div class="flipClock" data-flip-clock data-date-stage="countdown">
            <p class="flipClock__eyebrow">예식까지</p>
            <div class="flipClock__row">
                <span class="flipClock__prefix">D</span>
                <span class="flipClock__dash">–</span>
                <div class="flipClock__digits" data-flip-digits aria-label="남은 일수">
                    <span class="flipDigit" data-flip-digit><span class="flipDigit__face">0</span></span>
                    <span class="flipDigit" data-flip-digit><span class="flipDigit__face">0</span></span>
                    <span class="flipDigit" data-flip-digit><span class="flipDigit__face">0</span></span>
                </div>
            </div>
            <p class="flipClock__status" data-flip-status hidden></p>
        </div>
    </div>
</section>
