# Handover – Visitfy3

Stand: 2026-05-19 · Branch: `claude/elegant-sammet-caa710` (Auto-Deploy aktiv)

## Deploy

- **Stack**: PHP (server-rendered) · HTML/CSS/Vanilla JS · GSAP · Mailgun · Cloudflare Turnstile · JSON als Flat-File-CMS (keine DB).
- **Hosting**: Apache. Hardening + Header über [.htaccess](.htaccess).
- **Deploy**: `./deploy.sh` im Repo-Root (SSH/rsync, Config in `.deploy.env`). Worktree-Branches müssen vorher nach `main` gemerged sein. Lokal: `php -S localhost:8000`.
- **Geheimnisse** (alle `.gitignore`): `config.mail.php`, `config.admin.php`, `config.turnstile.php`, `.deploy.env`, `deploy.sh`.
- **Runtime-Daten** (nicht in Git, nur via Admin): `assets/data/content.json`, `assets/data/tours.json`, `inquiries.json`.
- **Rechtliche Seiten** (echte Kontaktdaten, nicht in Git): `pages/impressum.php`, `pages/datenschutz.php`.
- **Admin-Panel**: [admin/index.php](admin/index.php) – Login via `config.admin.php`. Bereiche: dashboard, content, tours, media, anfragen, integrations, settings.

## Phasen

- [x] **Phase 1 – Grundgerüst**
  - [x] 1a Initial Build (alle Seiten, CSS, JS, Partials)
  - [x] 1b Security-Basics (Email-Header-Guard, Matterport-URL-Validation, JS-Email-Check)
- [x] **Phase 2 – Struktur & CMS**
  - [x] 2a About-Page + Navigation
  - [x] 2b URL-/Basepath-Refactor über alle Seiten
  - [x] 2c JSON-CMS + Content-Config
- [x] **Phase 3 – Visuelles Polish**
  - [x] 3a Intro-Animation + Styles
  - [x] 3b Sections-Refactor (Mockups, Comparisons, Cases, Testimonials, Contact)
  - [x] 3c Cache-Busting via Filemtime-Query auf CSS/JS
  - [x] 3d Bento-Grid für Feature-Cards + Slide-In
- [x] **Phase 4 – Scroll-Animationen**
  - [x] 4a Scroll-Stack mit GSAP
  - [x] 4b Fly-In Mockups
  - [x] 4c Hero Parallax + Animationen
- [x] **Phase 5 – Content & Team**
  - [x] 5a Partner-Section
  - [x] 5b Team-Foto-Upload + About-Bilder
  - [x] 5c Initial `content.json` (SEO, Hero, KPIs, About, Team, CTA, Footer, Partner)
- [x] **Phase 6 – Security-Hardening**
  - [x] 6a CSRF-Schutz, Security-Headers, About-Icons-Fix, Scroll-Stack-Bugfix
  - [x] 6b Session-Cookie-Flags, CRLF-Stripping, Apache-2.4-Kompat in `.htaccess`
  - [x] 6c Cloudflare Turnstile Integration ([partials/turnstile.php](partials/turnstile.php))
- [x] **Phase 7 – Anfragen-System**
  - [x] 7a Inquiry-Management + Cookie-Consent
  - [x] 7b Admin-Interface für Anfragen ([admin/page/anfragen.php](admin/page/anfragen.php))
- [x] **Phase 8 – Repo-Hygiene**
  - [x] 8a README, LICENSE, Contact
  - [x] 8b Issue-Templates, CONTRIBUTING, [SECURITY.md](SECURITY.md), [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md)
- [x] **Phase 9 – UX-Features**
  - [x] 9a Tour-Fullscreen-Modal
  - [x] 9b Hero Particle Canvas mit Maus-Parallax ([hero-particle-canvas.md](hero-particle-canvas.md))
- [x] **Phase 10 – Apple-Style Refactor**
  - [x] 10a Tokens: `--bg: #0a0a0a`, `--font-display: Inter`, Text-Dim/Lines subtiler
  - [x] 10b Inter 400/500/600 via Google Fonts preconnect + non-blocking preload (`display=swap`)
  - [x] 10c Nav: Floating-Pill raus → Full-Width Sticky, Border-Bottom `1px rgba(255,255,255,0.06)`, `backdrop-filter: blur(24px) saturate(180%)`
  - [x] 10d Hero-Layout: Container/Glas-Panel raus, full-width Inner mit `.hero-text { max-width: min(62%, 880px) }` links angeschlagen
  - [x] 10e Hero-Glow weg: `::before`/`::after` Pseudo-Elemente, `.hero-overlay`-Gradient, CTA-Pulse-Animation entfernt (Keyframe-Defs bleiben tot stehen)
  - [x] 10f Typo: H1 `clamp(3.5rem, 11vw, 10rem)` / weight 600 / `letter-spacing: -0.03em` / `line-height: 0.95`; Eyebrow `12px / 0.2em / opacity 0.5`; Subline `rgba(255,255,255,0.62)`
  - [x] 10g Buttons global Apple-Spec: `padding: 14px 30px`, `border-radius: 980px`, weight 500, Hover nur `translateY(-1px) + opacity`
  - [x] 10h Trust-Pills monochrom (Stroke `rgba(255,255,255,0.4)`)
  - [x] 10i Partikel atmosphärischer: Count 500→850, MaxLineDist 90→70, kleiner/matter (`r ∈ [0.3, 1.1]`, `a ∈ [0.18, 0.48]`), Linien-Alpha 0.12→0.08, `willReadFrequently: false`
  - [x] 10j Scroll-Parallax-Layer: `.hero-canvas-wrap` mit `translateY(var(--hero-scroll-y))`, rAF-throttled passive scroll-Listener — kein Eingriff in Render-Loop
  - [x] 10k Bundle: −5.21 kB CSS, +0.51 kB JS, +0.77 kB Head (Fonts) → netto −1.97 kB
- [x] **Phase 11 – Auto-Deploy Workflow**
  - [x] 11a Memory-Regel "immer direkt deployn" (commit → merge `main` → `deploy.sh`)
  - [x] 11b `deploy.sh` `EXCLUDE_PATTERNS` um `.claude` / `.claude/**` ergänzt (Worktree-Leak verhindern)
- [x] **Phase 12 – Hero Tour-Teaser**
  - [x] 12a 3 floating Tour-Thumbnails rechts im Hero ab `min-width: 1024px`, geschichtet mit subtilen Rotationen
  - [x] 12b Matterport Thumb-Endpoint (`/api/v1/player/models/{id}/thumb?width=720&dis=1`) als Image-Quelle, Gradient-Card als Fallback (`onerror → opacity: 0`)
  - [x] 12c Klick führt zu `#tours`; Apple-feel: 18 px Border-Radius, `box-shadow`-Hover, `translateY(-3px)`
  - [x] 12d Card 3 nach unten (`bottom: 8%` → `2%`) um Überlappung mit H1 zu vermeiden
  - [x] 12e Sanftes Drift-Loop pro Card (eigene Keyframes, 11/13.5/16 s, negativer `animation-delay` für desynchronen Start). Nutzt CSS-Properties `translate` + `rotate` damit `transform` für Hover frei bleibt
- [x] **Phase 13 – Snap-Scroll**
  - [x] 13a `scroll-snap-type: y proximity` auf `html`, `scroll-padding-top: 56px` für Nav
  - [x] 13b Snap-Targets: `.hero` + `.mockup-section` (Hero → nächste Section); spätere Sections bleiben frei wegen GSAP-Pin in `.scroll-stack-section`
  - [x] 13c Reduced-Motion: Snap komplett deaktiviert
  - [x] 13d Auf alle Sections ausgeweitet (`mandatory`, früheres Einrasten); `.scroll-stack-section` per `snap-align: none` ausgenommen damit GSAP-Pin frei läuft
  - [x] 13e Zurück auf `proximity` + `.scroll-stack-section` bekommt `snap-align: start` (oben anrasten, dann GSAP-Pin übernimmt) — sonst skippte mandatory die Rundgänge-Section
- [x] **Phase 14 – Apple-Style Sweep Sections 2+**
  - [x] 14a Section-Typo: title weight `600` (war 800), eyebrow `12px/0.2em/opacity 0.5`, sub `rgba(255,255,255,0.62)`
  - [x] 14b Section padding-bug gefixt (`clamp(4rem,10vw,4rem)` → `clamp(5rem,9vw,9rem)`) → spürbar generöseres Spacing
  - [x] 14c `--glass-*` Tokens zentral abgeflacht: bg `0.025`, border `0.06/0.12`, blur 12 px, shadow `none`
  - [x] 14d Compare-Cards monochrom (kein grün-/rot-Border-Tint), Akzent-Grün nur noch auf Positive-Badge/Check als „Recommended"-Signal
  - [x] 14e Cards-Sweep (KPI/Feature/Case/Testimonial/Process/Stack): Hover-Lift `-4px → -1px`, kein Shadow-Pop, Headlines weight 600, Text `rgba(255,255,255,0.62)`, Tag-Pills auf 980 px-Pill mit weight 500
  - [x] 14f Marquee-Borders & FAQ-Items subtiler, FAQ-Question weight 500
  - [x] 14g Form-Inputs/Labels Apple-Style: 12 px Radius, Label weight 500 ohne uppercase, weichere Border/Focus
  - [x] 14h Mockup-Geräteborders `2px var(--line-strong)` → `1.5px rgba(255,255,255,0.12)`
  - [x] 14i Stack-Cards (Live-Demos): solider `#1a1a1a` BG statt 2 %-Transparenz — gestapelte Karten scheinten sonst durcheinander durch
- [x] **Phase 15 – „Warum 360°" als Apple-Sales-Section**
  - [x] 15a Bento-Grid + Emoji-Icons komplett raus (KI-Slop)
  - [x] 15b Neuer `.value-grid` (2×2) mit `.value-panel`: kleines `01`–`04` oben, große Claim-Headline (`clamp(1.6rem, 3vw, 2.5rem)`, weight 600) am Boden, kompakter Supporting-Text
  - [x] 15c Copy ergebnis-orientiert neu geschrieben („Mehr Anfragen. Sofort.", „Vertrauen, bevor der Anruf kommt.", …) statt Feature-Aufzählung
  - [x] 15d Slide-Left/Right Reveal-Animation bleibt erhalten (JS-Hook)
  - [x] 15e CMS-Hinweis: Falls live `content.json` noch alte `warum360.cards` mit Emojis hat, werden die Emojis stillschweigend verworfen; PHP-Defaults zeigen die neue Apple-Copy nur wenn der Key leer ist — ggf. im Admin aktualisieren
  - [x] 15f Sequentieller Reveal (Stagger 0/0.18/0.36/0.54 s) mit Headline-Pop (Scale `0.94 → 1`, cubic-bezier `0.34, 1.32, 0.64, 1` — leichter Overshoot)
  - [x] 15g Nummer (`01`–`04`) mit kontinuierlichem Chrome-Shimmer (220 %-Gradient-Sweep, 5 s linear, `background-clip: text`); Reduced-Motion: statisch
- [x] **Phase 16 – Mockup-Section („So sieht es aus") Refresh**
  - [x] 16a Bilder: `loading="lazy"` → `loading="eager" fetchpriority="high"` (Section direkt nach Hero, lazy war Quatsch)
  - [x] 16b Fake-Laptop-Boden (`::after` Strip) raus — Screenshots haben eigene Browser-/Device-Chrome
  - [x] 16c Frames vereinheitlicht: 14/22/26 px Radius, satter Drop-Shadow (`0 40px 80px -20px /0.7`), subtiler 0.06-Border
  - [x] 16d Tablet/Phone größer: Tablet `170-270 px` (war `120-200`), Phone `105-165 px` (war `70-120`) — substantieller Produkt-Showcase-Look

## Offen / Roadmap

- [ ] Visuelles QA des Apple-Refactors live (Hero ≥1024 px, Nav-Blur, Partikel-Dichte, Mobile)
- [ ] FPS-Throttling in Partikel-Render-Loop (falls < 50 fps auf schwachen Geräten)
- [ ] Critical CSS inline für above-the-fold (Hero+Nav)
- [ ] Interaktive 360°-Demo-Einbindung
- [ ] SEO-Optimierung
- [ ] Analytics / Conversion-Tracking
- [ ] Echtes CI/CD-Deploy (GitHub Actions → SSH)
- [ ] Dynamische Content-Erweiterung im Admin

## Pflege dieser Datei

Bei jedem neuen Feature/Fix: kürzeste Zeile als neue Sub-Phase ergänzen und als `[x]` markieren. Erledigte Roadmap-Punkte nach oben in die Phasen verschieben.
