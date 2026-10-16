/* ============================================================
   Visitfy3 – partner.js
   Nur auf pages/partner.php geladen.
   - Karte: Reveal beim Reinscrollen, Stadt-Liste ↔ Pin-Hover
   - Verdienst-Rechner (Range-Slider)
   - Dashboard-Tour (Tabs mit Autoplay + Toast)
   - Empfehlung: 35 %-Ring zählt von 1 hoch, Gutschrift-Stepper
   - Reveal-Trigger ([data-pw-reveal] → .is-live)
   ============================================================ */
(function () {
  'use strict';

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fmt = function (n) { return Math.round(n).toLocaleString('de-DE'); };
  var easeOut = function (t) { return 1 - Math.pow(1 - t, 3); };
  var easeInOut = function (t) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2; };

  /* Zahlen-Tween zwischen zwei Werten */
  function tween(el, from, to, dur) {
    if (!el) return;
    if (reduced || from === to) { el.textContent = fmt(to); return; }
    if (el._raf) cancelAnimationFrame(el._raf);
    var start = performance.now();
    (function step(now) {
      var t = Math.min((now - start) / dur, 1);
      el.textContent = fmt(from + (to - from) * easeOut(t));
      if (t < 1) el._raf = requestAnimationFrame(step);
    })(start);
  }

  /* Callback, sobald ein Element sichtbar wird (einmalig) */
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
    document.querySelectorAll('.pw-pins [data-pin-id]').forEach(function (pin) {
      var id = pin.getAttribute('data-pin-id');
      pin.addEventListener('mouseenter', function () { setHot(id, true); });
      pin.addEventListener('mouseleave', function () { setHot(id, false); });
    });

    /* „Region anfragen" → Fokus ins Stadt-Feld */
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
      var note = calc.querySelector('[data-out="badge"]');
      var lastMonth = Math.round(avg * perTour);

      var update = function () {
        var n = parseInt(range.value, 10);
        var month = Math.round(n * perTour);
        var pct = (n - range.min) / (range.max - range.min) * 100;
        range.style.setProperty('--fill', pct + '%');
        outTours.textContent = n;
        tween(outMonth, lastMonth, month, 450);
        tween(outYear, lastMonth * 12, month * 12, 450);
        lastMonth = month;
        if (note) {
          var diff = n - avg;
          note.textContent = diff === 0 ? 'Genau der Schnitt unserer Partner.'
            : diff > 0 ? (diff >= 8 ? 'Das schaffen unsere besten Partner.' : 'Über dem Schnitt unserer Partner.')
            : 'Ein guter Einstieg, auch nebenberuflich.';
        }
      };
      range.addEventListener('input', update);
      update();
    }

    /* ── Dashboard-Tour ──────────────────────────────── */
    var tabsWrap = document.querySelector('[data-pw-tabs]');
    var stage = document.querySelector('[data-pw-dash]');
    if (tabsWrap && stage) {
      var TAB_MS = 6000;
      var tabs = Array.prototype.slice.call(tabsWrap.querySelectorAll('.pw-tab'));
      var views = stage.querySelectorAll('.pw-view');
      var navItems = stage.querySelectorAll('[data-nav]');
      var panel = stage.querySelector('[role="tabpanel"]');
      var toast = stage.querySelector('[data-toast]');
      var toastTitle = stage.querySelector('[data-toast-out="title"]');
      var toastText = stage.querySelector('[data-toast-out="text"]');
      var current = 0;
      var timer = null;
      var manual = false;
      var live = false;
      var inView = false;

      tabsWrap.style.setProperty('--pw-tab-ms', TAB_MS + 'ms');

      var popToast = function (view) {
        if (!toast || !view) return;
        toastTitle.textContent = view.getAttribute('data-toast-title') || '';
        toastText.textContent = view.getAttribute('data-toast-text') || '';
        toast.classList.remove('is-pop');
        void toast.offsetWidth; /* Animation neu starten */
        toast.classList.add('is-pop');
      };

      var show = function (index, focus) {
        current = (index + tabs.length) % tabs.length;
        var key = tabs[current].getAttribute('data-view');
        var activeView = null;
        tabs.forEach(function (t, i) {
          var on = i === current;
          t.classList.toggle('is-active', on);
          t.setAttribute('aria-selected', on ? 'true' : 'false');
          t.tabIndex = on ? 0 : -1;
        });
        Array.prototype.forEach.call(views, function (v) {
          var on = v.getAttribute('data-view') === key;
          v.classList.toggle('is-active', on);
          v.setAttribute('aria-hidden', on ? 'false' : 'true');
          if (on) activeView = v;
        });
        Array.prototype.forEach.call(navItems, function (n) {
          n.classList.toggle('is-active', n.getAttribute('data-nav') === key);
        });
        if (panel) panel.setAttribute('aria-labelledby', tabs[current].id);
        if (focus) tabs[current].focus();
        if (live) setTimeout(function () { popToast(activeView); }, 650);
      };

      var schedule = function () {
        clearTimeout(timer);
        if (manual || reduced || !inView) return;
        timer = setTimeout(function () { show(current + 1); schedule(); }, TAB_MS);
      };
      var play = function () {
        if (manual || reduced) return;
        tabsWrap.classList.remove('is-playing');
        void tabsWrap.offsetWidth; /* Fortschrittsbalken neu starten */
        tabsWrap.classList.add('is-playing');
        schedule();
      };
      var pause = function () {
        clearTimeout(timer);
        tabsWrap.classList.remove('is-playing');
      };
      var takeOver = function () {
        manual = true;
        pause();
        tabsWrap.classList.add('is-manual');
      };

      tabs.forEach(function (tab, i) {
        tab.addEventListener('click', function () { takeOver(); show(i); });
        tab.addEventListener('keydown', function (e) {
          var k = e.key;
          if (k === 'ArrowDown' || k === 'ArrowRight') { e.preventDefault(); takeOver(); show(current + 1, true); }
          if (k === 'ArrowUp' || k === 'ArrowLeft') { e.preventDefault(); takeOver(); show(current - 1, true); }
          if (k === 'Home') { e.preventDefault(); takeOver(); show(0, true); }
          if (k === 'End') { e.preventDefault(); takeOver(); show(tabs.length - 1, true); }
        });
      });

      /* Erster Auftritt: Dashboard kippt rein, danach Toast + Autoplay */
      onVisible(stage, function () {
        live = true;
        stage.classList.add('is-live');
        setTimeout(function () { popToast(views[current]); }, reduced ? 0 : 2200);
      }, 0.3);

      /* Autoplay nur, solange die Tour sichtbar ist */
      if ('IntersectionObserver' in window && !reduced) {
        new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            inView = entry.isIntersecting;
            if (inView) play(); else pause();
          });
        }, { threshold: 0.45 }).observe(stage);
      }
    }

    /* ── Empfehlung: Ring + Prozentzahl 1 → 35 ───────── */
    var refSection = document.querySelector('[data-pw-ref-section]');
    var ring = document.querySelector('[data-ref-ring]');
    var pctEl = document.querySelector('[data-ref-pct]');
    if (refSection && ring && pctEl && !reduced) {
      var target = parseInt(pctEl.getAttribute('data-target'), 10) || 0;
      var setPct = function (v) {
        pctEl.textContent = Math.round(v);
        ring.style.strokeDashoffset = String(100 - v);
      };
      setPct(1);
      onVisible(refSection, function () {
        setTimeout(function () {
          var start = performance.now();
          var dur = 1900;
          (function step(now) {
            var t = Math.min((now - start) / dur, 1);
            setPct(1 + (target - 1) * easeInOut(t));
            if (t < 1) requestAnimationFrame(step);
          })(start);
        }, 350);
      }, 0.35);
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
      if (el.hasAttribute('data-pw-dash')) return; /* eigene Logik oben */
      onVisible(el, function () { el.classList.add('is-live'); }, 0.3);
    });
  });
})();
