/* ============================================================
   Visitfy3 – partner.js
   Nur auf pages/partner.php geladen.
   - Karte: Reveal beim Reinscrollen, Stadt-Liste ↔ Pin-Hover
   - Verdienst-Rechner (Range-Slider)
   - Empfehlungs-Stepper
   - Reveal-Trigger für Dashboard + Prämien-Ring
   ============================================================ */
(function () {
  'use strict';

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fmt = function (n) { return Math.round(n).toLocaleString('de-DE'); };

  /* Sanfter Zahlen-Tween zwischen zwei Werten */
  function tween(el, from, to, dur) {
    if (!el) return;
    if (reduced || from === to) { el.textContent = fmt(to); return; }
    if (el._raf) cancelAnimationFrame(el._raf);
    var start = performance.now();
    (function step(now) {
      var t = Math.min((now - start) / dur, 1);
      var e = 1 - Math.pow(1 - t, 3);
      el.textContent = fmt(from + (to - from) * e);
      if (t < 1) el._raf = requestAnimationFrame(step);
    })(start);
  }

  /* Klasse .is-live setzen, sobald Element sichtbar ist */
  function onVisible(el, cb, threshold) {
    if (!el) return;
    if (reduced || !('IntersectionObserver' in window)) { cb(); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        io.disconnect();
        cb();
      });
    }, { threshold: threshold || 0.3 });
    io.observe(el);
  }

  document.addEventListener('DOMContentLoaded', function () {

    /* ── Karte ───────────────────────────────────────── */
    var map = document.querySelector('.pw-map');
    onVisible(map, function () { map.classList.add('is-live'); }, 0.25);

    function setHot(id, on) {
      document.querySelectorAll('[data-pin-id="' + id + '"], [data-label-id="' + id + '"], [data-pin="' + id + '"]')
        .forEach(function (el) { el.classList.toggle('is-hot', on); });
    }
    document.querySelectorAll('.pw-city').forEach(function (btn) {
      var id = btn.getAttribute('data-pin');
      ['mouseenter', 'focus'].forEach(function (ev) { btn.addEventListener(ev, function () { setHot(id, true); }); });
      ['mouseleave', 'blur'].forEach(function (ev) { btn.addEventListener(ev, function () { setHot(id, false); }); });
    });
    /* Pins selbst hoverbar (Desktop) */
    document.querySelectorAll('.pw-pins [data-pin-id]').forEach(function (pin) {
      var id = pin.getAttribute('data-pin-id');
      pin.style.cursor = 'pointer';
      pin.addEventListener('mouseenter', function () { setHot(id, true); });
      pin.addEventListener('mouseleave', function () { setHot(id, false); });
    });

    /* „Region sichern" → Fokus ins Stadt-Feld */
    document.querySelectorAll('[data-prefill-city]').forEach(function (a) {
      a.addEventListener('click', function () {
        var input = document.getElementById('p_stadt');
        if (input) setTimeout(function () { input.focus({ preventScroll: true }); }, 700);
      });
    });

    /* ── Rechner ─────────────────────────────────────── */
    var calc = document.querySelector('[data-pw-calc]');
    if (calc) {
      var perTour = parseFloat(calc.getAttribute('data-per-tour')) || 0;
      var avg = parseInt(calc.getAttribute('data-avg'), 10) || 0;
      var range = calc.querySelector('input[type="range"]');
      var outTours = calc.querySelector('[data-out="tours"]');
      var outMonth = calc.querySelector('[data-out="month"]');
      var outYear = calc.querySelector('[data-out="year"]');
      var badge = calc.querySelector('[data-out="badge"]');
      var lastMonth = Math.round(avg * perTour);

      function update() {
        var n = parseInt(range.value, 10);
        var month = Math.round(n * perTour);
        var pct = (n - range.min) / (range.max - range.min) * 100;
        range.style.setProperty('--fill', pct + '%');
        outTours.textContent = n;
        tween(outMonth, lastMonth, month, 450);
        tween(outYear, lastMonth * 12, month * 12, 450);
        lastMonth = month;
        if (badge) {
          var diff = n - avg;
          badge.textContent = diff === 0 ? 'Genau der Schnitt unserer Partner.'
            : diff > 0 ? (diff >= 8 ? 'Top-Partner-Liga.' : 'Über dem Partner-Schnitt.')
            : 'Guter Einstieg – auch nebenberuflich machbar.';
        }
      }
      range.addEventListener('input', update);
      update();
    }

    /* ── Empfehlungs-Stepper ─────────────────────────── */
    var ref = document.querySelector('[data-pw-ref]');
    if (ref) {
      var bonus = parseFloat(ref.getAttribute('data-bonus')) || 0;
      var outCount = ref.querySelector('[data-out="count"]');
      var outSum = ref.querySelector('[data-out="sum"]');
      var count = parseInt(outCount.textContent, 10) || 3;
      var lastSum = Math.round(bonus) * count;
      ref.querySelectorAll('[data-step]').forEach(function (b) {
        b.addEventListener('click', function () {
          count = Math.max(1, Math.min(50, count + parseInt(b.getAttribute('data-step'), 10)));
          outCount.textContent = count;
          var sum = Math.round(bonus) * count;
          tween(outSum, lastSum, sum, 400);
          lastSum = sum;
        });
      });
    }

    /* ── Reveals ─────────────────────────────────────── */
    document.querySelectorAll('[data-pw-reveal]').forEach(function (el) {
      onVisible(el, function () { el.classList.add('is-live'); }, 0.3);
    });
  });
})();
