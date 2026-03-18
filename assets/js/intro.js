/**
 * Visitfy Web2.0 – Intro Animation
 * Animated logo assembly based on visitfy-logo.svg.
 * Adapted from Portfolio by Jason Holweg.
 */
(function () {
  'use strict';

  const scriptCfg = (window.VISITFY_SCRIPT_CONFIG && window.VISITFY_SCRIPT_CONFIG.intro) || {};

  /* ── Config ─────────────────────────────────────────────── */
  const LOGO_SRC          = 'assets/img/visitfy-logo.svg';
  const ASSEMBLE_DURATION = 2200;
  const SHINE_DURATION    = 800;
  const TEXT_DELAY        = Number(scriptCfg.text_delay ?? 200);
  const INTRO_HOLD        = Number(scriptCfg.intro_hold ?? 1500);
  const FADE_OUT_DURATION = Number(scriptCfg.fade_out_duration ?? 1100);
  const SHOW_TEXT_DELAY   = Number(scriptCfg.show_text_delay ?? 120);
  const SKIP_CLICK_DELAY  = Number(scriptCfg.skip_click_delay ?? 300);

  const FRAG_COLS = 22;
  const FRAG_ROWS = 18;

  /* ── State ──────────────────────────────────────────────── */
  let canvas, ctx, W, H, raf;
  let logoImg = null;
  let logoTint = null;
  let fragments = [];
  let logoReady = false;
  let phase  = 'idle';
  let phaseStart = 0;
  let skipCalled = false;

  /* ── Init ───────────────────────────────────────────────── */
  function init() {
    canvas = document.getElementById('intro-canvas');
    if (!canvas) return;

    ctx = canvas.getContext('2d');
    resize();
    window.addEventListener('resize', resize);

    // Skip long assembly animation and show the short logo/text intro only.
    setTimeout(showText, SHOW_TEXT_DELAY);

    // Skip button
    var skipBtn = document.getElementById('skip-btn');
    if (skipBtn) {
      skipBtn.addEventListener('click', function () { skip(); });
    }

    // Click anywhere to skip after short grace period
    setTimeout(function () {
      document.getElementById('intro').addEventListener('click', function (e) {
        if (e.target.id !== 'skip-btn') skip();
      }, { once: true });
    }, SKIP_CLICK_DELAY);
  }

  function resize() {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
    if (logoReady) buildFragments();
  }

  function loadLogo(onReady) {
    logoImg = new Image();
    logoImg.onload = function () {
      logoTint = buildTintedLogo('#eef3ff');
      logoReady = true;
      onReady();
    };
    logoImg.onerror = function () {
      showText();
    };
    logoImg.src = LOGO_SRC;
  }

  function buildTintedLogo(color) {
    var sourceW = logoImg.naturalWidth || 645;
    var sourceH = logoImg.naturalHeight || 563;
    var off = document.createElement('canvas');
    off.width = sourceW;
    off.height = sourceH;
    var offCtx = off.getContext('2d');

    offCtx.clearRect(0, 0, sourceW, sourceH);
    offCtx.drawImage(logoImg, 0, 0, sourceW, sourceH);
    offCtx.globalCompositeOperation = 'source-in';
    offCtx.fillStyle = color;
    offCtx.fillRect(0, 0, sourceW, sourceH);
    offCtx.globalCompositeOperation = 'source-over';

    return off;
  }

  function getLogoBox() {
    var sourceW = logoImg.naturalWidth || 645.35;
    var sourceH = logoImg.naturalHeight || 563.01;
    var ratio = sourceH / sourceW;
    var width = Math.min(W * 0.56, 460);
    var height = width * ratio;

    return {
      sourceW: sourceW,
      sourceH: sourceH,
      x: (W - width) / 2,
      y: (H - height) / 2 - Math.min(32, H * 0.04),
      width: width,
      height: height,
      centerX: W / 2,
      centerY: H / 2 - Math.min(28, H * 0.03)
    };
  }

  function buildFragments() {
    if (!logoReady) return;

    var box = getLogoBox();
    var fragW = box.sourceW / FRAG_COLS;
    var fragH = box.sourceH / FRAG_ROWS;
    fragments = [];

    for (var row = 0; row < FRAG_ROWS; row++) {
      for (var col = 0; col < FRAG_COLS; col++) {
        var srcX = col * fragW;
        var srcY = row * fragH;

        var tx = box.x + (srcX / box.sourceW) * box.width;
        var ty = box.y + (srcY / box.sourceH) * box.height;
        var dw = box.width / FRAG_COLS;
        var dh = box.height / FRAG_ROWS;

        var ang = Math.random() * Math.PI * 2;
        var radius = 90 + Math.random() * Math.max(W, H) * 0.38;
        var sx = box.centerX + Math.cos(ang) * radius;
        var sy = box.centerY + Math.sin(ang) * radius;

        fragments.push({
          srcX: srcX,
          srcY: srcY,
          srcW: fragW,
          srcH: fragH,
          sx: sx,
          sy: sy,
          tx: tx,
          ty: ty,
          dw: dw,
          dh: dh,
          rotStart: (Math.random() - 0.5) * 1.5,
          delay: Math.random() * 700,
          duration: 650 + Math.random() * 650,
        });
      }
    }
  }

  /* ── Main loop ──────────────────────────────────────────── */
  function loop(ts) {
    ctx.clearRect(0, 0, W, H);
    if (!logoReady) {
      raf = requestAnimationFrame(loop);
      return;
    }

    var elapsed = ts - phaseStart;
    var box = getLogoBox();

    if (phase === 'assemble') {
      drawAssembly(elapsed);
      if (elapsed >= ASSEMBLE_DURATION) {
        startConverge();
      }
    }

    if (phase === 'converge') {
      drawAssembledLogo(elapsed, box);
      if (elapsed >= SHINE_DURATION) {
        showText();
      }
    }

    raf = requestAnimationFrame(loop);
  }

  function drawAssembly(elapsed) {
    fragments.forEach(function (f) {
      var local = (elapsed - f.delay) / f.duration;
      if (local <= 0) return;

      var t = Math.min(local, 1);
      var e = easeOutCubic(t);
      var x = lerp(f.sx, f.tx, e);
      var y = lerp(f.sy, f.ty, e);
      var alpha = Math.min(1, t * 1.35);
      var rot = f.rotStart * (1 - e);

      ctx.save();
      ctx.translate(x + f.dw * 0.5, y + f.dh * 0.5);
      ctx.rotate(rot);
      ctx.globalAlpha = alpha;
      ctx.drawImage(
        logoTint || logoImg,
        f.srcX, f.srcY, f.srcW, f.srcH,
        -f.dw * 0.5, -f.dh * 0.5,
        f.dw, f.dh
      );
      ctx.restore();
    });
  }

  function drawAssembledLogo(elapsed, box) {
    var t = Math.min(elapsed / SHINE_DURATION, 1);
    var pulse = 1 + Math.sin(elapsed * 0.008) * 0.015;

    ctx.save();
    ctx.translate(box.centerX, box.centerY);
    ctx.scale(pulse, pulse);
    ctx.translate(-box.centerX, -box.centerY);

    ctx.globalAlpha = 1;
    ctx.shadowColor = 'rgba(171,196,236,0.2)';
    ctx.shadowBlur = 14;
    ctx.drawImage(logoTint || logoImg, box.x, box.y, box.width, box.height);

    var sweepX = box.x - box.width * 0.6 + (box.width * 1.95 * t);
    var shine = ctx.createLinearGradient(sweepX, box.y, sweepX + box.width * 0.3, box.y + box.height);
    shine.addColorStop(0, 'rgba(255,255,255,0)');
    shine.addColorStop(0.52, 'rgba(235,244,255,0.35)');
    shine.addColorStop(1, 'rgba(255,255,255,0)');

    ctx.globalCompositeOperation = 'screen';
    ctx.fillStyle = shine;
    ctx.fillRect(box.x, box.y, box.width, box.height);

    ctx.restore();
  }

  /* ── Phase transitions ──────────────────────────────────── */
  function startConverge() {
    if (phase === 'done') return;
    phase = 'converge';
    phaseStart = performance.now();
    setTimeout(showText, SHINE_DURATION + TEXT_DELAY);
  }

  function showText() {
    if (phase === 'done') return;
    phase = 'done';

    cancelAnimationFrame(raf);
    canvas.style.transition = 'opacity 0.5s';
    canvas.style.opacity = '0';

    var introText = document.getElementById('intro-text');
    if (introText) introText.classList.add('show');

    setTimeout(dismissIntro, INTRO_HOLD);
  }

  function dismissIntro() {
    var introEl = document.getElementById('intro');
    if (!introEl) return;
    introEl.classList.add('hide');
    setTimeout(function () {
      introEl.style.display = 'none';
      revealMain();
    }, FADE_OUT_DURATION);
  }

  function revealMain() {
    var main = document.getElementById('main-content');
    if (main) {
      main.style.transition = 'opacity 0.6s';
      main.style.opacity = '1';
    }
    triggerFadeUps();
    document.dispatchEvent(new CustomEvent('visitfy:intro-done'));
  }

  /* ── Skip ───────────────────────────────────────────────── */
  function skip() {
    if (skipCalled) return;
    skipCalled = true;
    cancelAnimationFrame(raf);
    var introEl = document.getElementById('intro');
    if (introEl) {
      introEl.classList.add('hide');
      setTimeout(function () {
        introEl.style.display = 'none';
        revealMain();
      }, FADE_OUT_DURATION);
    }
  }

  /* ── Helpers ────────────────────────────────────────────── */
  function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
  }

  function lerp(a, b, t) {
    return a + (b - a) * t;
  }

  /* ── Scroll-triggered fade-ups ──────────────────────────── */
  function triggerFadeUps() {
    var items = document.querySelectorAll('.fade-up');
    if (!items.length) return;

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });

    items.forEach(function (el) { io.observe(el); });
  }

  /* ── Bootstrap ──────────────────────────────────────────── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
