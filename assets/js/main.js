/**
 * Visitfy3 – main.js
 * Includes:
 *  - Hero Particles canvas background
 *  - Sticky nav scroll effect + mobile nav toggle
 *  - Scroll-Reveal (IntersectionObserver)
 *  - KPI Count-Up animation [from Visitfy-Website]
 *  - Logo Marquee (infinite scroll) [from Visitfy-Website]
 *  - Scroll-Stack behavior + iFrame lazy-load
 *  - FAQ Accordion
 *  - prefers-reduced-motion handling throughout
 */
(function () {
  'use strict';

  /* ── Reduced-motion flag ────────────────────────────────── */
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Intro animation moved to dedicated assets/js/intro.js (Web2.0 original). */


  /* ═══════════════════════════════════════════════════════════
     HERO PARTICLES CANVAS
  ═══════════════════════════════════════════════════════════ */
  (function initParticles() {
    const heroCanvas = document.getElementById('hero-canvas');
    if (!heroCanvas) return;
    if (prefersReduced) return; /* skip animation on reduced-motion */

    const ctx = heroCanvas.getContext('2d');
    let W, H, dpr, particles, animFrame;

    /* Config */
    const PARTICLE_COUNT    = 500;
    const MAX_SPEED         = 0.45;
    const MAX_LINE_DIST     = 90;
    const MAX_LINE_DIST_SQ  = MAX_LINE_DIST * MAX_LINE_DIST;
    const MOUSE_RADIUS      = 120;
    const MOUSE_FORCE       = 0.012;

    let mouse = { x: -9999, y: -9999 };

    class Particle {
      constructor() { this.reset(true); }
      reset(randomY = false) {
        this.x    = Math.random() * W;
        this.y    = randomY ? Math.random() * H : -5;
        this.vx   = (Math.random() - 0.5) * MAX_SPEED;
        this.vy   = (Math.random() - 0.5) * MAX_SPEED;
        this.r    = Math.random() * 1.4 + 0.4;
        this.a    = Math.random() * 0.55 + 0.2;
      }
      update() {
        /* Mouse attraction (very subtle) */
        const dx = mouse.x - this.x;
        const dy = mouse.y - this.y;
        const distSq = dx * dx + dy * dy;
        if (distSq < MOUSE_RADIUS * MOUSE_RADIUS && distSq > 1) {
          const dist = Math.sqrt(distSq);
          this.vx += (dx / dist) * MOUSE_FORCE;
          this.vy += (dy / dist) * MOUSE_FORCE;
        }
        /* Speed clamp */
        const speed = Math.sqrt(this.vx * this.vx + this.vy * this.vy);
        if (speed > MAX_SPEED * 1.8) {
          this.vx = (this.vx / speed) * MAX_SPEED * 1.8;
          this.vy = (this.vy / speed) * MAX_SPEED * 1.8;
        }
        this.x += this.vx;
        this.y += this.vy;
        /* Wrap edges */
        if (this.x < -10) this.x = W + 10;
        if (this.x > W + 10) this.x = -10;
        if (this.y < -10) this.y = H + 10;
        if (this.y > H + 10) this.y = -10;
      }
      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(255,255,255,${this.a})`;
        ctx.fill();
      }
    }

    function resize() {
      dpr = window.devicePixelRatio || 1;
      W   = heroCanvas.offsetWidth  || window.innerWidth;
      H   = heroCanvas.offsetHeight || window.innerHeight;
      heroCanvas.width  = W * dpr;
      heroCanvas.height = H * dpr;
      heroCanvas.style.width  = W + 'px';
      heroCanvas.style.height = H + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function initParticlesList() {
      particles = Array.from({ length: PARTICLE_COUNT }, () => new Particle());
    }

    function drawLines() {
      /* Only connect pairs within MAX_LINE_DIST – iterate with early-exit */
      for (let i = 0; i < particles.length; i++) {
        const p = particles[i];
        for (let j = i + 1; j < particles.length; j++) {
          const q  = particles[j];
          const dx = p.x - q.x;
          const dy = p.y - q.y;
          const dSq = dx * dx + dy * dy;
          if (dSq > MAX_LINE_DIST_SQ) continue;
          const alpha = (1 - dSq / MAX_LINE_DIST_SQ) * 0.12;
          ctx.beginPath();
          ctx.moveTo(p.x, p.y);
          ctx.lineTo(q.x, q.y);
          ctx.strokeStyle = `rgba(255,255,255,${alpha})`;
          ctx.lineWidth   = 0.5;
          ctx.stroke();
        }
      }
    }

    function tick() {
      ctx.clearRect(0, 0, W, H);
      particles.forEach(p => { p.update(); p.draw(); });
      drawLines();
      animFrame = requestAnimationFrame(tick);
    }

    resize();
    initParticlesList();
    animFrame = requestAnimationFrame(tick);
    window.addEventListener('resize', () => { resize(); }, { passive: true });

    /* Mouse parallax (on hero only) */
    const heroSection = document.querySelector('.hero');
    if (heroSection) {
      heroSection.addEventListener('mousemove', e => {
        const rect = heroSection.getBoundingClientRect();
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
      }, { passive: true });
      heroSection.addEventListener('mouseleave', () => {
        mouse.x = -9999; mouse.y = -9999;
      }, { passive: true });
    }
  })();


  /* ═══════════════════════════════════════════════════════════
     MAIN DOM-READY INITIALIZATIONS
  ═══════════════════════════════════════════════════════════ */
  document.addEventListener('DOMContentLoaded', function () {

    /* ── Sticky Nav ────────────────────────────────────────── */
    const nav = document.querySelector('.site-nav');
    if (nav) {
      window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', window.scrollY > 80);
      }, { passive: true });
    }

    /* ── Mobile Nav Toggle ─────────────────────────────────── */
    const navToggle  = document.getElementById('nav-toggle');
    const navMobile  = document.getElementById('nav-mobile');
    if (navToggle && navMobile) {
      navToggle.addEventListener('click', () => {
        const open = navMobile.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        navToggle.querySelectorAll('span').forEach((s, i) => {
          if (open) {
            if (i === 0) s.style.transform = 'translateY(6.5px) rotate(45deg)';
            if (i === 1) s.style.opacity   = '0';
            if (i === 2) s.style.transform = 'translateY(-6.5px) rotate(-45deg)';
          } else {
            s.style.transform = '';
            s.style.opacity   = '';
          }
        });
      });
      document.addEventListener('click', e => {
        if (nav && !nav.contains(e.target) && !navMobile.contains(e.target)) {
          navMobile.classList.remove('open');
          navToggle.setAttribute('aria-expanded', 'false');
          navToggle.querySelectorAll('span').forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
        }
      });
    }

    /* ── Active nav link ───────────────────────────────────── */
    const path = window.location.pathname;
    document.querySelectorAll('.nav-links a, .nav-mobile a').forEach(a => {
      const href = (a.getAttribute('href') || '').replace(/^\.\.\//, '').replace(/^\.\//, '');
      if (href && path.endsWith(href)) a.classList.add('active');
    });

    /* ── Hero rotating text ────────────────────────────────── */
    initHeroRotatingText();

    /* ── Scroll Reveal (IntersectionObserver) ──────────────── */
    initFadeUps();

    /* ── KPI Count-Up ──────────────────────────────────────── */
    initCountUp();

    /* ── Logo Marquee ──────────────────────────────────────── */
    initMarquee();

    /* ── Scroll-Stack + iFrame lazy-load ───────────────────── */
    initScrollStack();

    /* ── FAQ Accordion ─────────────────────────────────────── */
    initAccordion();

    /* ── Contact forms ─────────────────────────────────────── */
    initForms();

  });


  /* ─────────────────────────────────────────────────────────
     Reveal main content (called after intro exits)
  ───────────────────────────────────────────────────────── */
  function revealMain() {
    const main = document.getElementById('main-content');
    if (main) {
      main.style.transition = 'opacity 0.6s ease';
      main.style.opacity    = '1';
    }
    initFadeUps();
  }
  window._visitfyRevealMain = revealMain;


  /* ═══════════════════════════════════════════════════════════
     SCROLL-REVEAL
  ═══════════════════════════════════════════════════════════ */
  function initFadeUps() {
    if (prefersReduced) {
      document.querySelectorAll('.fade-up').forEach(el => el.classList.add('visible'));
      return;
    }
    const items = document.querySelectorAll('.fade-up:not(.visible)');
    if (!items.length) return;
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    items.forEach(el => io.observe(el));
  }


  /* ═══════════════════════════════════════════════════════════
     HERO ROTATING TEXT
  ═══════════════════════════════════════════════════════════ */
  function initHeroRotatingText() {
    const wordEl = document.querySelector('[data-hero-rotate-word]');
    if (!wordEl) return;

    const words = ['SICHTBARKEIT.', 'VERTRAUEN.', 'ANFRAGEN.'];
    let index = 0;
    const fadeDuration = 420;
    const holdDuration = 1900;

    if (prefersReduced) {
      wordEl.textContent = words[0];
      return;
    }

    window.setInterval(() => {
      wordEl.classList.add('is-exiting');

      window.setTimeout(() => {
        index = (index + 1) % words.length;
        wordEl.textContent = words[index];
        wordEl.classList.remove('is-exiting');
        wordEl.classList.add('is-entering');

        requestAnimationFrame(() => {
          wordEl.classList.remove('is-entering');
        });
      }, fadeDuration);
    }, holdDuration + fadeDuration);
  }


  /* ═══════════════════════════════════════════════════════════
     KPI COUNT-UP  [from Visitfy-Website]
     Uses requestAnimationFrame for smooth number tween.
  ═══════════════════════════════════════════════════════════ */
  function initCountUp() {
    const counters = document.querySelectorAll('[data-countup]');
    if (!counters.length) return;

    if (prefersReduced) {
      counters.forEach(el => { el.textContent = el.getAttribute('data-target') || el.textContent; });
      return;
    }

    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        io.unobserve(entry.target);
        animateCount(entry.target);
      });
    }, { threshold: 0.3 });

    counters.forEach(el => io.observe(el));
  }

  function animateCount(el) {
    const raw    = el.getAttribute('data-target') || '0';
    const suffix = el.getAttribute('data-suffix') || '';
    const prefix = el.getAttribute('data-prefix') || '';
    /* Extract numeric part */
    const num  = parseFloat(raw.replace(/[^0-9.]/g, ''));
    const dur  = 1800; /* ms */
    const start = performance.now();

    function tick(now) {
      const elapsed = now - start;
      const prog    = Math.min(elapsed / dur, 1);
      const ease    = easeOutExpo(prog);
      const current = Math.round(ease * num);
      el.textContent = prefix + current.toLocaleString('de-DE') + suffix;
      if (prog < 1) requestAnimationFrame(tick);
      else el.textContent = prefix + num.toLocaleString('de-DE') + suffix;
    }
    requestAnimationFrame(tick);
  }

  function easeOutExpo(t) {
    return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
  }


  /* ═══════════════════════════════════════════════════════════
     LOGO MARQUEE  [from Visitfy-Website]
     Duplicates the track for seamless infinite scroll.
  ═══════════════════════════════════════════════════════════ */
  function initMarquee() {
    const tracks = document.querySelectorAll('.marquee-track');
    if (prefersReduced) return;
    tracks.forEach(track => {
      /* Clone content for seamless loop */
      const clone = track.cloneNode(true);
      track.parentNode.appendChild(clone);
    });
  }


  /* ═══════════════════════════════════════════════════════════
     SCROLL-STACK + IFRAME LAZY-LOAD
     Each .stack-item is CSS sticky; JS adds subtle rotation
     transform as items accumulate at top.
     iFrames use IntersectionObserver for lazy loading.
  ═══════════════════════════════════════════════════════════ */
  function initScrollStack() {
    const items = document.querySelectorAll('.stack-item');
    if (!items.length) return;

    /* Config (matching the CSS vars) */
    const ROTATION_AMOUNT = 0.5; /* deg */
    const ITEM_STACK_DIST = 15;  /* px, vertical offset per stacked card */

    if (!prefersReduced) {
      /* Apply subtle rotation + offset as cards stack */
      window.addEventListener('scroll', () => {
        items.forEach((item, idx) => {
          const rect = item.getBoundingClientRect();
          const card = item.querySelector('.stack-card');
          if (!card) return;
          /* How many cards are above this one and stuck? */
          const stickyTop = parseFloat(getComputedStyle(item).top) || 0;
          const isStuck   = rect.top <= stickyTop + 2;
          if (isStuck) {
            /* Count how many items are currently stuck above */
            let stackCount = 0;
            items.forEach((other, j) => {
              if (j >= idx) return;
              const otherRect = other.getBoundingClientRect();
              const otherTop  = parseFloat(getComputedStyle(other).top) || 0;
              if (otherRect.top <= otherTop + 2) stackCount++;
            });
            const rot    = (idx % 2 === 0 ? 1 : -1) * ROTATION_AMOUNT * Math.min(stackCount, 3) * 0.5;
            const offset = stackCount * ITEM_STACK_DIST;
            card.style.transform = `translateY(${offset}px) rotate(${rot}deg)`;
          } else {
            card.style.transform = '';
          }
        });
      }, { passive: true });
    }

    /* iFrame lazy-load via IntersectionObserver */
    const lazyIO = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const placeholder = entry.target;
        const src         = placeholder.getAttribute('data-src');
        if (!src) return;
        lazyIO.unobserve(placeholder);

        /* Replace placeholder with iframe */
        const wrap    = placeholder.parentNode;
        const iframe  = document.createElement('iframe');
        iframe.src    = src;
        iframe.title  = placeholder.getAttribute('data-title') || '360° Rundgang';
        iframe.allow  = 'fullscreen; xr-spatial-tracking;';
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
        iframe.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox allow-presentation');
        wrap.replaceChild(iframe, placeholder);
      });
    }, { rootMargin: '200px' });

    document.querySelectorAll('.iframe-placeholder[data-src]').forEach(el => lazyIO.observe(el));
  }


  /* ═══════════════════════════════════════════════════════════
     FAQ ACCORDION
  ═══════════════════════════════════════════════════════════ */
  function initAccordion() {
    document.querySelectorAll('.faq-question').forEach(btn => {
      btn.addEventListener('click', () => {
        const item   = btn.closest('.faq-item');
        const isOpen = item.classList.contains('open');
        /* Close all */
        document.querySelectorAll('.faq-item.open').forEach(i => {
          i.classList.remove('open');
          i.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
        });
        /* Toggle current */
        if (!isOpen) {
          item.classList.add('open');
          btn.setAttribute('aria-expanded', 'true');
        }
      });
    });
  }


  /* ═══════════════════════════════════════════════════════════
     CONTACT / PARTNER FORMS
  ═══════════════════════════════════════════════════════════ */
  function initForms() {
    document.querySelectorAll('form[data-ajax]').forEach(form => {
      form.addEventListener('submit', e => {
        e.preventDefault();
        handleFormSubmit(form);
      });
    });
  }

  function handleFormSubmit(form) {
    const statusEl  = form.querySelector('.form-status');
    const submitBtn = form.querySelector('button[type="submit"]');

    /* Basic client-side validation */
    let valid = true;
    form.querySelectorAll('[required]').forEach(field => {
      if (!field.value.trim()) {
        field.style.borderColor = 'rgba(255,100,100,0.5)';
        valid = false;
      } else {
        field.style.borderColor = '';
      }
    });

    const emailEl = form.querySelector('input[type="email"]');
    if (emailEl && !emailEl.validity.valid) {
      emailEl.style.borderColor = 'rgba(255,100,100,0.5)';
      valid = false;
    }

    if (!valid) {
      showFormStatus(statusEl, 'error', 'Bitte fülle alle Pflichtfelder korrekt aus.');
      return;
    }

    const orig = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Wird gesendet…'; }

    fetch(form.action || 'kontakt-handler.php', {
      method: 'POST',
      body: new FormData(form),
    })
      .then(r => r.json())
      .then(json => {
        if (json.ok) {
          showFormStatus(statusEl, 'success', '✓ Nachricht gesendet. Wir melden uns bald!');
          form.reset();
        } else {
          throw new Error(json.error || 'Fehler');
        }
      })
      .catch(() => {
        showFormStatus(statusEl, 'error', 'Fehler beim Senden. Bitte schreibe direkt an info@visitfy.de.');
      })
      .finally(() => {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = orig; }
      });
  }

  function showFormStatus(el, type, msg) {
    if (!el) return;
    el.className = 'form-status ' + type;
    el.textContent = msg;
    setTimeout(() => { el.className = 'form-status'; }, 7000);
  }


  /* ═══════════════════════════════════════════════════════════
     EASING UTILITIES
  ═══════════════════════════════════════════════════════════ */
  function easeInOutQuart(t) {
    return t < 0.5 ? 8 * t * t * t * t : 1 - Math.pow(-2 * t + 2, 4) / 2;
  }

})();
