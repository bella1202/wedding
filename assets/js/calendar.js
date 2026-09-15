(function () {
  "use strict";

  var MONTHS_KO = [
    "1월", "2월", "3월", "4월", "5월", "6월",
    "7월", "8월", "9월", "10월", "11월", "12월",
  ];

  // 관공서 공휴일(해당 예식 연도 기준). 음력 명절·대체공휴일 포함.
  var HOLIDAYS = {
    "2027-01-01": "신정",
    "2027-02-06": "설 연휴",
    "2027-02-07": "설날",
    "2027-02-08": "설 연휴",
    "2027-02-09": "설 대체공휴일",
    "2027-03-01": "삼일절",
    "2027-05-01": "근로자의 날",
    "2027-05-03": "근로자의 날 대체공휴일",
    "2027-05-05": "어린이날",
    "2027-05-19": "부처님오신날",
    "2027-06-06": "현충일",
    "2027-07-17": "제헌절",
    "2027-07-19": "제헌절 대체공휴일",
    "2027-08-15": "광복절",
    "2027-08-16": "광복절 대체공휴일",
    "2027-09-14": "추석 연휴",
    "2027-09-15": "추석",
    "2027-09-16": "추석 연휴",
    "2027-10-03": "개천절",
    "2027-10-04": "개천절 대체공휴일",
    "2027-10-09": "한글날",
    "2027-10-11": "한글날 대체공휴일",
    "2027-12-25": "성탄절",
    "2027-12-27": "성탄절 대체공휴일",
  };

  function pad2(n) {
    return String(n).padStart(2, "0");
  }

  function holidayName(year, month, day) {
    return HOLIDAYS[year + "-" + pad2(month) + "-" + pad2(day)] || "";
  }

  function initWeddingCalendar() {
    var section = document.querySelector("[data-wedding-countdown]");
    var grid = document.querySelector("[data-calendar-grid]");
    var title = document.querySelector("[data-calendar-title]");
    if (!section || !grid) return;

    var year = parseInt(section.getAttribute("data-calendar-year"), 10);
    var month = parseInt(section.getAttribute("data-calendar-month"), 10);
    var weddingDay = parseInt(section.getAttribute("data-calendar-day"), 10);
    if (!year || !month || !weddingDay) return;

    if (title) {
      title.textContent = year + "년 " + MONTHS_KO[month - 1];
    }

    var first = new Date(year, month - 1, 1);
    var startWeekday = first.getDay(); // 0 Sun
    var daysInMonth = new Date(year, month, 0).getDate();
    var today = new Date();
    today.setHours(0, 0, 0, 0);

    grid.innerHTML = "";

    for (var i = 0; i < startWeekday; i++) {
      var empty = document.createElement("span");
      empty.className = "weddingCalendar__cell isEmpty";
      empty.setAttribute("aria-hidden", "true");
      grid.appendChild(empty);
    }

    for (var day = 1; day <= daysInMonth; day++) {
      var cell = document.createElement("div");
      cell.className = "weddingCalendar__cell";
      cell.textContent = String(day);

      var cellDate = new Date(year, month - 1, day);
      var weekday = cellDate.getDay();
      var holiday = holidayName(year, month, day);

      if (weekday === 0) {
        cell.classList.add("isSunday");
      }
      if (holiday) {
        cell.classList.add("isHoliday");
        cell.title = holiday;
        cell.setAttribute("aria-label", holiday + " " + year + "-" + month + "-" + day);
      }

      if (cellDate.getTime() === today.getTime()) {
        cell.classList.add("isToday");
      }

      if (day === weddingDay) {
        cell.classList.add("isWedding");
        cell.setAttribute(
          "aria-label",
          "결혼식 " + year + "-" + month + "-" + day
        );
        var mark = document.createElement("span");
        mark.className = "weddingCalendar__mark";
        mark.textContent = "♥";
        cell.appendChild(mark);
      }

      grid.appendChild(cell);
    }
  }

  document.addEventListener("DOMContentLoaded", initWeddingCalendar);
})();
