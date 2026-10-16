(function() {
  'use strict';

  function initCountdowns() {
    var elements = document.querySelectorAll('[data-countdown-target]');
    if (!elements.length) return;

    elements.forEach(function(el) {
      var targetStr = el.getAttribute('data-countdown-target');
      if (!targetStr) return;

      var targetDate = new Date(targetStr).getTime();
      if (isNaN(targetDate)) return;

      var daysEl = el.querySelector('.cd-days');
      var hoursEl = el.querySelector('.cd-hours');
      var minutesEl = el.querySelector('.cd-minutes');
      var secondsEl = el.querySelector('.cd-seconds');

      function update() {
        var now = new Date().getTime();
        var diff = targetDate - now;

        if (diff <= 0) {
          if (daysEl) daysEl.textContent = '00';
          if (hoursEl) hoursEl.textContent = '00';
          if (minutesEl) minutesEl.textContent = '00';
          if (secondsEl) secondsEl.textContent = '00';
          return;
        }

        var days = Math.floor(diff / (1000 * 60 * 60 * 24));
        var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((diff % (1000 * 60)) / 1000);

        if (daysEl) daysEl.textContent = (days < 10 ? '0' : '') + days;
        if (hoursEl) hoursEl.textContent = (hours < 10 ? '0' : '') + hours;
        if (minutesEl) minutesEl.textContent = (minutes < 10 ? '0' : '') + minutes;
        if (secondsEl) secondsEl.textContent = (seconds < 10 ? '0' : '') + seconds;
      }

      update();
      setInterval(update, 1000);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCountdowns);
  } else {
    initCountdowns();
  }
})();
