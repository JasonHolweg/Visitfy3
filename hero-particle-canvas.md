# Hero Particle Canvas

Interaktiver Partikel-Canvas-Hintergrund mit Maus-Parallax-Effekt. Partikel bewegen sich, verbinden sich mit Linien und reagieren subtil auf die Mausposition.

---

## HTML

```html
<section class="hero">
  <canvas id="hero-canvas" aria-hidden="true"></canvas>
  <div class="hero-overlay" aria-hidden="true"></div>

  <!-- Dein Hero-Content hier -->
  <div class="hero-content">
    <h1>Deine Headline</h1>
  </div>
</section>
```

---

## CSS

```css
/* Custom Property (global definieren) */
:root {
  --ease-out: cubic-bezier(0.2, 0.8, 0.2, 1);
}

/* Hero Section */
.hero {
  position: relative;
  min-height: 100svh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background: #0a0a0a; /* Dunkler Hintergrund fuer die Partikel */

  --hero-parallax-x: 0px;
  --hero-parallax-y: 0px;
  --hero-glow-opacity: 0.2;
}

/* Glow-Effekt (Pseudo-Element hinter den Partikeln) */
.hero::before,
.hero::after {
  content: '';
  position: absolute;
  inset: auto;
  pointer-events: none;
  z-index: 1;
}

.hero::before {
  width: min(72vw, 860px);
  height: min(72vw, 860px);
  left: 50%;
  top: 50%;
  transform: translate(
    calc(-50% + var(--hero-parallax-x) * 0.22),
    calc(-50% + var(--hero-parallax-y) * 0.18)
  );
  background: radial-gradient(
    circle,
    rgba(255, 255, 255, 0.16) 0%,
    rgba(255, 255, 255, 0.08) 22%,
    rgba(255, 255, 255, 0.025) 48%,
    rgba(255, 255, 255, 0) 74%
  );
  opacity: var(--hero-glow-opacity);
  filter: blur(18px);
  transition: transform 0.7s var(--ease-out), opacity 0.45s ease;
}

.hero::after {
  inset: 12% -12% auto;
  height: 48%;
  background:
    radial-gradient(circle at 50% 0%, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0) 58%),
    linear-gradient(110deg, rgba(255, 255, 255, 0) 25%, rgba(255, 255, 255, 0.05) 49%, rgba(255, 255, 255, 0) 72%);
  opacity: 0.4;
  transform: translate3d(
    calc(var(--hero-parallax-x) * -0.08),
    calc(var(--hero-parallax-y) * -0.08),
    0
  );
  mix-blend-mode: screen;
  animation: hero-sheen 13s linear infinite;
  transition: transform 0.7s var(--ease-out);
}

/* Canvas */
#hero-canvas {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  transform: translate3d(
    calc(var(--hero-parallax-x) * -0.12),
    calc(var(--hero-parallax-y) * -0.12),
    0
  ) scale(1.03);
  transition: transform 0.7s var(--ease-out);
}

/* Overlay (optionaler Gradient ueber dem Canvas) */
.hero-overlay {
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: none;
  background: linear-gradient(to bottom, transparent 60%, #0a0a0a);
}

/* Content ueber dem Canvas */
.hero-content {
  position: relative;
  z-index: 2;
}

/* Sheen Animation */
@keyframes hero-sheen {
  0%   { transform: translate3d(-2%, 0, 0); opacity: 0.18; }
  50%  { opacity: 0.36; }
  100% { transform: translate3d(2%, 0, 0); opacity: 0.18; }
}

/* Reduced Motion: Parallax & Canvas deaktivieren */
@media (prefers-reduced-motion: reduce) {
  .hero::before,
  .hero::after,
  #hero-canvas {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
```

---

## JavaScript

Kann als eigenstaendiges `<script>` oder Modul eingebunden werden. Keine Abhaengigkeiten.

```js
(function () {
  'use strict';

  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const heroCanvas = document.getElementById('hero-canvas');
  if (!heroCanvas) return;
  const heroSection = document.querySelector('.hero');

  if (prefersReduced) {
    if (heroSection) {
      heroSection.style.setProperty('--hero-parallax-x', '0px');
      heroSection.style.setProperty('--hero-parallax-y', '0px');
    }
    return;
  }

  const ctx = heroCanvas.getContext('2d');
  let W, H, dpr, particles, animFrame;

  /* ── Konfiguration (anpassen nach Bedarf) ────────────── */
  const PARTICLE_COUNT   = 500;   // Anzahl Partikel
  const MAX_SPEED        = 0.45;  // Max Geschwindigkeit
  const MAX_LINE_DIST    = 90;    // Max Distanz fuer Verbindungslinien (px)
  const MAX_LINE_DIST_SQ = MAX_LINE_DIST * MAX_LINE_DIST;
  const MOUSE_RADIUS     = 120;   // Maus-Einflussradius (px)
  const MOUSE_FORCE      = 0.012; // Staerke der Maus-Anziehung

  let mouse = { x: -9999, y: -9999 };

  class Particle {
    constructor() { this.reset(true); }

    reset(randomY = false) {
      this.x  = Math.random() * W;
      this.y  = randomY ? Math.random() * H : -5;
      this.vx = (Math.random() - 0.5) * MAX_SPEED;
      this.vy = (Math.random() - 0.5) * MAX_SPEED;
      this.r  = Math.random() * 1.4 + 0.4;   // Radius
      this.a  = Math.random() * 0.55 + 0.2;   // Alpha
    }

    update() {
      // Maus-Anziehung (subtil)
      const dx = mouse.x - this.x;
      const dy = mouse.y - this.y;
      const distSq = dx * dx + dy * dy;
      if (distSq < MOUSE_RADIUS * MOUSE_RADIUS && distSq > 1) {
        const dist = Math.sqrt(distSq);
        this.vx += (dx / dist) * MOUSE_FORCE;
        this.vy += (dy / dist) * MOUSE_FORCE;
      }
      // Speed clamp
      const speed = Math.sqrt(this.vx * this.vx + this.vy * this.vy);
      if (speed > MAX_SPEED * 1.8) {
        this.vx = (this.vx / speed) * MAX_SPEED * 1.8;
        this.vy = (this.vy / speed) * MAX_SPEED * 1.8;
      }
      this.x += this.vx;
      this.y += this.vy;
      // Wrap edges
      if (this.x < -10)    this.x = W + 10;
      if (this.x > W + 10) this.x = -10;
      if (this.y < -10)    this.y = H + 10;
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

  function initParticles() {
    particles = Array.from({ length: PARTICLE_COUNT }, () => new Particle());
  }

  function drawLines() {
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

  // Init
  resize();
  initParticles();
  animFrame = requestAnimationFrame(tick);
  window.addEventListener('resize', resize, { passive: true });

  // Maus-Parallax (auf Hero-Section)
  if (heroSection) {
    heroSection.addEventListener('mousemove', e => {
      const rect = heroSection.getBoundingClientRect();
      mouse.x = e.clientX - rect.left;
      mouse.y = e.clientY - rect.top;
      const offsetX = ((mouse.x / rect.width) - 0.5) * 20;
      const offsetY = ((mouse.y / rect.height) - 0.5) * 20;
      heroSection.style.setProperty('--hero-parallax-x', `${offsetX}px`);
      heroSection.style.setProperty('--hero-parallax-y', `${offsetY}px`);
      heroSection.style.setProperty('--hero-glow-opacity', '0.28');
    }, { passive: true });

    heroSection.addEventListener('mouseleave', () => {
      mouse.x = -9999;
      mouse.y = -9999;
      heroSection.style.setProperty('--hero-parallax-x', '0px');
      heroSection.style.setProperty('--hero-parallax-y', '0px');
      heroSection.style.setProperty('--hero-glow-opacity', '0.2');
    }, { passive: true });
  }
})();
```

---

## Konfigurierbare Parameter

| Parameter | Default | Beschreibung |
|---|---|---|
| `PARTICLE_COUNT` | `500` | Anzahl der Partikel |
| `MAX_SPEED` | `0.45` | Maximale Geschwindigkeit pro Frame |
| `MAX_LINE_DIST` | `90` | Max Distanz (px) fuer Verbindungslinien zwischen Partikeln |
| `MOUSE_RADIUS` | `120` | Radius (px) in dem die Maus Partikel anzieht |
| `MOUSE_FORCE` | `0.012` | Staerke der Maus-Anziehungskraft |

---

## Features

- **Partikel-Netzwerk**: Partikel verbinden sich automatisch mit Linien wenn sie nah genug sind
- **Maus-Interaktion**: Partikel werden subtil zur Mausposition angezogen
- **Parallax-Effekt**: Canvas, Glow und Content bewegen sich mit unterschiedlicher Intensitaet bei Mausbewegung
- **HiDPI-Support**: Automatische Skalierung fuer Retina/HiDPI Displays via `devicePixelRatio`
- **Edge Wrapping**: Partikel wrappen an den Kanten (kein Verschwinden)
- **Reduced Motion**: Komplett deaktiviert bei `prefers-reduced-motion: reduce`
- **Keine Abhaengigkeiten**: Reines Vanilla JS + Canvas API

---

## Anpassung fuer andere Projekte

1. **Partikelfarbe aendern**: `rgba(255,255,255,...)` in `draw()` und `drawLines()` ersetzen
2. **Hintergrundfarbe**: `.hero` `background` anpassen
3. **Partikelanzahl reduzieren**: `PARTICLE_COUNT` senken fuer bessere Performance auf schwachen Geraeten
4. **Glow-Effekt entfernen**: `.hero::before` und `.hero::after` entfernen falls nicht gewuenscht
5. **Container anpassen**: `.hero` Klasse und `#hero-canvas` ID an dein Layout anpassen
