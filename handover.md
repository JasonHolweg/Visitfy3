# Handover – Visitfy3

Stand: 2026-10-17 · Branch: `seo/phase-0` – enthält den kompletten Live-Stand vom 17.10.2026 (Phasen 17–23 vom Server übernommen) plus Phasen 24–28

## Deploy

- **Stack**: PHP (server-rendered) · HTML/CSS/Vanilla JS · GSAP · Mailgun · Cloudflare Turnstile · JSON als Flat-File-CMS (keine DB).
- **Hosting (seit 2026-07-16)**: Eigener Server `178.104.95.146` (Ubuntu, Apache2, mod_php 8.3), DocumentRoot `/var/www/visitfy.de`, vHosts `visitfy.conf`/`visitfy-le-ssl.conf`, Let's-Encrypt-Cert (auto-renew). DNS via Cloudflare (proxied, SSL-Mode Full). Altes Shared Hosting (FTP `92.205.250.36`) ist tot. Hardening + Header über [.htaccess](.htaccess).
- **Deploy ab Phase 28**: über `ops/` – `bash ~/scratch/visitfy-sync.sh` (Live-Stand holen, nur lesend) → Paket mit `ops/build-package.py` gegen diesen Stand bauen → `bash ~/scratch/visitfy-deploy.sh` (Konfliktprüfung, Sicherung in `/root/visitfy-backups/`, Datenanpassung, Prüfung direkt am Server, automatisches Zurückrollen). SSH geht nur über die IP (Cloudflare davor), gemerkt in `~/scratch/.visitfy-ssh`.
- ⚠️ **`deploy.sh` (rsync) nur aus einem Stand, der `seo/phase-0` enthält** – ein älterer lokaler `main` überschreibt sonst Phase 24–28 auf dem Server.
- **Deploy (alt)**: `./deploy.sh` im Repo-Root (SSH/rsync als root, Config in `.deploy.env`, `DEPLOY_CHOWN` setzt danach `www-data`). Excludes schützen Runtime-Daten + Configs auf dem Server. Worktree-Branches müssen vorher nach `main` gemerged sein. Lokal: `php -S localhost:8000`.
- **Secrets auf dem Server** (`/var/www/visitfy.de`, chmod 600): `config.mail.php` (Mailgun via mg.jasonholweg.de, Anfragen → hallo@jasonholweg.de), `config.admin.php` (Admin-Login). `config.turnstile.php` fehlt bewusst → Turnstile deaktiviert (Keys wären domain-gebunden; bei Bedarf Widget für visitfy.de in Cloudflare anlegen).
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
- [x] **Phase 13 – Snap-Scroll** ⚠️ **2026-07-22 komplett entfernt** (Phase 19b) – nicht wieder einbauen
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

- [x] **Phase 17 – Server-Umzug auf eigenen Server** (2026-07-16)
  - [x] 17a `/var/www/visitfy.de` + Apache-vHosts (80/443) auf `178.104.95.146`, LE-Cert via webroot, HTTP→HTTPS-Redirect
  - [x] 17b Configs serverseitig neu erzeugt (Mailgun aus Portfolio-Config übernommen, Admin-Passwort neu — altes FTP-Hosting samt Secrets unerreichbar)
  - [x] 17c `.htaccess`: Direktzugriff auf `config.*.php` geblockt
  - [x] 17d `deploy.sh`: SSH-Modus mit `DEPLOY_CHOWN`, Excludes um `inquiries.json`, `config.*.php`, `*.bak` ergänzt; `.deploy.env` auf SSH umgestellt
  - [x] 17e Verifiziert: alle Seiten 200, Configs/inquiries 403, Sitemap/robots ok, Admin-Login ok

- [x] **Phase 18 – Hero-Tuning** (2026-07-16)
  - [x] 18a Hero-H1 verkleinert: `clamp(3.5rem, 11vw, 10rem)` → `clamp(3rem, 6vw, 6rem)` (war auf breiten Screens 160 px/Zeile)
  - [x] 18b Intro nur noch beim ersten Besuch je Browser-Session (`sessionStorage` `visitfyIntroSeen`; Inline-Guard in index.php setzt `html.intro-seen` vor dem Paint → kein Aufblitzen; CSS-Regeln analog Reduced-Motion)

- [x] **Phase 19 – Mobile-Scroll & Section-Symmetrie** (2026-07-22)
  - [x] 19a Empfänger der Formulare auf `info@visitfy.de` umgestellt (`config.mail.php` auf dem Server). Absender bleibt `noreply@jasonholweg.de` — Mailgun hat **nur `mg.jasonholweg.de`** als aktive Sendedomain, `@visitfy.de` als From würde ohne DKIM im Spam landen
  - [x] 19b **Scroll-Snap komplett entfernt** (`scroll-snap-type` auf `html` + `scroll-snap-align/-stop` auf Sections + Reduced-Motion-Ausnahme): rastete auf iOS/iPadOS gegen die GSAP-Pins und ruckelte. `scroll-padding-top: 56px` bleibt (nur für Anker-Sprünge unter der Sticky-Nav)
  - [x] 19c „Warum 360°"-Panels symmetrisch: `margin-top:auto` am Titel raus (drückte Titel+Text nach unten → wandernde Positionen je Textlänge); ab 769 px teilen sich die Panels per **Subgrid** die Zeilen-Tracks. ⚠️ Kein `gap:0` am Panel — es erbt den `row-gap` des Grids als echten Abstand; mit `gap:0` entstand die Lücke nur aus ungenutztem Platz und verschwand bei zweizeiliger Headline. Karte 03 auf „Atmosphäre erleben" gekürzt (einzeilig wie die anderen). Verifiziert: alle 4 Panels identisch (Titel 80 px, Text 134 px, Abstand 17 px, Höhe 270 px)

- [x] **Phase 20 – Über-uns überarbeitet** (2026-07-22)
  - [x] 20a Jason Holweg als „Team" entfernt: Karte in [pages/about.php](pages/about.php), `team.jason_*` in `content.json` **und** der `Person`-Eintrag mit `worksFor` im JSON-LD ([partials/cms.php](partials/cms.php)) — letzterer deklarierte ihn maschinenlesbar als Visitfy-Mitarbeiter. ⚠️ Der Footer-Credit „Webseite von Jason Holweg" ist bewusst **geblieben** (Dienstleister-Credit, steht auf allen Seiten)
  - [x] 20b Sektion heißt jetzt „Die Person hinter Visitfy" (Eyebrow) / „Kristian Meister" (Titel); einzelne Karte via `max-width: 560px` + `margin-inline: auto` mittig
  - [x] 20c Hero: `.about-hero-panel` bekam `margin-inline: auto` — `max-width` ohne auto-Margins ließ den Block trotz `text-align:center` links kleben
  - [x] 20d Hero-Text von 3 Absätzen auf **einen** Lead-Satz reduziert. Nutzt weiter `about.paragraph_1` (nicht ein neuer Key!), weil der Admin-Save das `about`-Array komplett ersetzt und einen unbekannten Key beim ersten Speichern gelöscht hätte. `paragraph_2/_3` + Jason-Felder aus [admin/page/content.php](admin/page/content.php) entfernt, damit dort nichts gepflegt wird, was nirgends erscheint

- [x] **Phase 21 – Kundenlogos / Marquee** (2026-07-22)
  - [x] 21a Server-Upload-Limit von 2M → 12M (`php_value upload_max_filesize/post_max_size` in [.htaccess](.htaccess), in `<IfModule php_module>` gekapselt). War die Ursache, warum große Logos im Admin-Media-Tool nicht hochluden. Via Apache verifiziert (CLI zeigt weiter 2M – irrelevant).
  - [x] 21b `.marquee-logo` bekommt **weißes Chip** (`background:#fff`, radius 14px): viele Kundenlogos sind dunkel-auf-weiß und wären auf dem `#0a0a0a`-Grund sonst unsichtbar. Img `max-height:64px/max-width:150px`, Chip `min-width:180px/height:96px`. `--empty`-Variante setzt Chip zurück.
  - [x] 21c 5 Kundenlogos optimiert & live (Little Jack's, dan·bo Møbler, Buddha Lounge, W&W/Wüstenrot, Flora): Originale waren 1536×1024-PNGs mit ~80 % leerer Transparenz (das „graue" im Viewer war nur die Alpha-Vorschau). Pipeline: auf Inhalt getrimmt (Alpha-Bbox), auf max 320 px skaliert, Farben quantisiert (FASTOCTREE, Alpha erhalten) → **je 5–17 KB, gesamt 51 KB** (vorher ~8,6 MB). **Flora** war weiß-auf-transparent (auf weißem Chip unsichtbar) → in warmes Braun `#7a5c3e` umgefärbt. Marquee liest den Ordner automatisch; Originale entfernt, damit nur die sauberen Logos globt werden.

- [x] **Phase 22 – Marquee: weiße Logos, kein Chip, langsamer** (2026-07-22)
  - [x] 22a Logos zu **weißen Silhouetten** konvertiert (Schwellwert-Key: Luminanz < 0.60 → deckendes Weiß, hellere Innen-Details → transparente Aussparung, Original-Alpha für AA). Nötig, weil simples „alles weiß" bei Logos mit Farb-Innendetail (Little Jack's weiße Schrift auf schwarzem Deckel, Buddha helle Schrift auf dunklem Balken, W&W weiße Lettern auf Orange) zum weißen Klumpen würde – der Schwellwert-Key erhält die Details als Aussparung.
  - [x] 22b `.marquee-logo`: weißes Chip/Border/Radius entfernt → transparent, `opacity: 0.68` (Hover → 1). `img max-height 58px`.
  - [x] 22c Animation `marquee-scroll` 30s → **48s** (langsamer).
  - [x] 22d ⚠️ **Cloudflare-Cache-Falle**: statische Assets werden mit `max-age 180d` gecacht (`cf-cache-status: HIT`) → neue Logos kamen trotz Deploy nicht durch. Fix: Logo-`<img>` in [index.php](index.php) bekommt `?v=<filemtime>` Cache-Buster (wie CSS/JS). Löst auch künftige Logo-Updates ohne CF-Purge.

- [x] **Phase 23 – Marquee-Loop nahtlos** (2026-07-22)
  - [x] 23a Flackern/„Abschneiden" beim Loop-Neustart behoben: Track = 2×25 Logos + `translateX(-50%)`, aber `gap: 3rem` (flex) verschob den Loop-Punkt um einen halben Gap → sichtbarer Sprung alle 48 s. Fix: `gap` raus, Abstand als `margin-right: 3rem` **pro Logo** (gilt auch fürs letzte Item jeder Kopie) → `-50%` trifft exakt die Kopie-Grenze. Verifiziert: 2. Kopie bei x=4292, halbe Track-Breite 4293 → Δ 0 px.

- [x] **Phase 24 – Partner-werden-Seite v2**
  - [x] 24a Hero mit Ø-Monatsumsatz (Count-up + Chrome-Shimmer), Stats-Leiste, Fußnote „Umsatz, nicht Gewinn“
  - [x] 24b Deutschlandkarte (Dot-Matrix, inline SVG, `partials/germany-map-paths.php`) mit Pins, Netzwerk-Bögen ab Flensburg, offenen Süd-Gebieten + Süden-CTA
  - [x] 24c Verdienst-Rechner (Range-Slider), Dashboard-Mockup, Empfehlungsprogramm (35 %-Ring + Stepper)
  - [x] 24d Formular: Pflichtfeld „Stadt / Region“ (Handler + Mail), `$extraScripts`-Hook im Footer, `assets/js/partner.js`
  - [x] 24e Zahlen & Standorte zentral in `partials/partner-network.php` (Hero-/Proof-Copy nicht mehr über Admin-Hero-Felder)
- [x] **Phase 25 – SEO / GEO**
  - [x] 25a `head.php`: Canonical pro Seite (vorher nie ausgegeben), `og:url`/`og:locale`/`og:image` absolut, `twitter:image`, `theme-color`, `$jsonLd`/`$pageRobots`/`$canonicalPath`-Hooks
  - [x] 25b Partner-Seite: neuer Title/Description, JSON-LD (Organization + WebPage/Breadcrumb + FAQPage), sichtbare FAQ (7 Fragen, identisch mit Schema), OG-Bild `assets/img/og-partner.png`
  - [x] 25c `robots.txt` (Admin + Handler gesperrt), `sitemap.xml`, `llms.txt` (GEO-Faktenblatt), Admin `noindex`
- [x] **Phase 26 – Partner-Seite Feinschliff**
  - [x] 26a KI-Optik raus: grüne Live-Pille, Aurora/Grid-Hintergrund, Scroll-Maus, Süd-Glow, Scan-Linie, Emoji, Icon-Kacheln, ✓-Trust-Zeile; Stats als Haarlinien-Spalten
  - [x] 26b Karte: freie Regionen bundesweit (`free` in `partner-network.php`, unbeschriftet) + Box „8 von 16 Bundesländern“ (automatisch aus `states` vs. aktiven Regionen)
  - [x] 26c Dashboard-Tour: echtes Logo, 3 Ansichten (Rechnungen/Firmen/Empfehlungen) als Tabs mit Autoplay, Fortschrittslinie und Toast
  - [x] 26d Empfehlung: 35 % zählt von 1 hoch (Ring synchron), Gutschrift-Tabelle statt 01/02/03-Karten, Stepper integriert
  - [x] 26e Leistungen als Zeilen statt Karten (Kicker entfallen), Fit-Panel ins Formular gezogen (zweispaltig); ungenutzte Admin-Felder: `card_*_kicker`, `fit_eyebrow`, `fit_title`, `form_sub`, `hero_*`
- [x] **Phase 27 – Partnerstimmen**
  - [x] 27a Sektion unter dem Empfehlungsprogramm: großes Foto, Zitat, Ø-Monatsumsatz, 5 Umschalter mit Autoplay (Pause bei Hover)
  - [x] 27b Inhalte in `partials/partner-testimonials.php`; live nur Einträge mit `freigegeben => true` + Name, Zitat, Foto (Datei muss existieren) und Umsatz; sonst ist die Sektion unsichtbar
  - [x] 27c Platzhalter nur lokal mit `VISITFY_PREVIEW=1 php -S localhost:8000`; Fotos nach `assets/img/partner/` (4:5, ≥ 800 × 1000 px)
  - [x] 27d `tabRotator()` in `partner.js` treibt Dashboard-Tour und Partnerstimmen
  - [x] 27e Live mit 4 echten Partnern (Daniel R., Sarah K., Lukas M., Ben S.; Fotos von JH als echt bestätigt), Alter in der Meta-Zeile, ohne Stadt Vorname im Umschalter, Bilder mit `?v=` (Cloudflare-Cache)
  - [x] 27f Neue Fotos für Daniel (Kundentermin im Autohaus) und Sarah (Friseursalon) am 21.10.2026; Gesichtsausschnitte `*-thumb.jpg` für die runden Umschalter, optionale Felder `thumb` und `foto_alt`

- [x] **Phase 28 – SEO/GEO Phase 0 (Fundament)**
  - [x] 28a Saubere URLs aus `partials/routes.php` (/partner-werden, /kontakt, /faq, /ueber-uns, /impressum, /datenschutz); `.htaccess` leitet /pages/*.php, ?page=…, /index.php, /de, www und http per 301 in einem Sprung um; Formulare (POST) bleiben unberührt
  - [x] 28b 404-Seite, interne Ordner (partials, tools, ops, assets/data) per 404 gesperrt, .md/.sh/.bak/.jsonl gesperrt, Komprimierung + Browser-Cache
  - [x] 28c `/sitemap.xml` dynamisch (`sitemap.php`), IndexNow-Schlüssel + `php tools/indexnow.php`
  - [x] 28d JSON-LD über `partials/schema.php` (Organization, WebSite, Service, WebPage/Breadcrumb, FAQPage, About/ContactPage); FAQ-Seite datengetrieben
  - [x] 28e Titel/Beschreibungen geschärft; Startseiten-Titel in content.json (seo.*) setzt das Deploy; KPI-Zahlen stehen im HTML
  - [x] 28f Intro nur beim Einstieg von außen (Inline-Script, kein Storage) und kürzer (900/600 ms); Partikel nach Bildschirmfläche skaliert, pausieren unsichtbar
  - [x] 28g Inter lokal (`assets/fonts/`), keine Google-Fonts-Verbindung mehr
  - [x] 28h Cookielose Statistik komplett serverseitig (kein JavaScript, kein Zugriff aufs Endgerät): `partials/stats.php`, Aufruf in `head.php` → `assets/data/stats/*.jsonl`, Admin › Statistik (Quellen inkl. KI-Suchen, Crawler, Anfragen, Bewerbungen, 404-Liste)
  - [x] 28i Deploy-Werkzeug `ops/`: `visitfy-sync.sh` (Live-Stand + Server-Infos nur lesend nach ~/scratch), `build-package.py` (Paket gegen Live-Stand), `visitfy-deploy.sh` (Mac) → `remote-apply.sh` (Server: Konfliktprüfung, Sicherung, Einspielen, `patch-data.php`, Live-Test, Rollback)
  - [x] 28j Rundgänge gesamt aus dem CRM: `partials/crm.php` + `config.crm.php` (nicht im Git, Vorlage `config.crm.php.example`, MySQL oder JSON-URL), 1 h Cache in `assets/data/cache/`, bei Ausfall letzter Wert bzw. `fallback`; Platzhalter `{rundgaenge}` / `{partner}` in content.json-Texten (`visitfy_get()` füllt sie), `patch-data.php` setzt sie, sobald das CRM antwortet
  - [x] 28k Lieferzeit einheitlich „max. 5 Tage“ (Code-Defaults, FAQ, llms.txt; content.json per `patch-data.php`); `llms.txt` jetzt dynamisch aus `llms.php`
  - [x] 28l Cloudflare berücksichtigt: visitfy.de läuft über Cloudflare → SSH nur direkt mit der Server-IP (`ops/visitfy-ssh.sh`, fragt einmal nach und merkt sie in `~/scratch/.visitfy-ssh`); Live-Prüfung im Deploy direkt am Server (an Cloudflare-Cache/Bot-Schutz vorbei), Blick von außen nur als Hinweis; Statistik nimmt die echte Besucher-IP (`CF-Connecting-IP`) für den Tages-Hash
  - [x] 28m Phase 0 live seit 17.10.2026; Datenschutzerklärung Abschnitt 3.3 „Reichweitenmessung ohne Cookies“ per `patch-data.php`

## SEO/GEO-Plan (Stand 17.10.2026)

- [ ] Phase 0 live: Sitemap in Google Search Console einreichen, Bing Webmaster Tools (Import aus GSC), Datenschutz-Absatz zur Statistik; in Cloudflare prüfen, dass KI-Crawler nicht blockiert werden (Sicherheit → Bots / KI-Crawler-Steuerung) – sonst sind robots.txt-Freigaben und llms.txt wirkungslos
- [ ] Phase 1: Stadtseiten für Partnerstandorte (mit echtem Partner, Referenzen, lokalen FAQ), Branchenseiten, Preisseite „Was kostet ein 360°-Rundgang?“, Referenzseite
- [ ] Phase 2: Ratgeber (2–4/Monat) für Kunden und Partner – Antwort im ersten Absatz, Tabellen, FAQ, Datum/Autor
- [ ] Phase 3: Google-Unternehmensprofile (Visitfy + Partner), Credit-Link „360°-Rundgang: Visitfy“ bei Kunden, einheitliche Verzeichniseinträge, Regionalpresse
- [x] Lieferzeit einheitlich „max. 5 Tage“
- [ ] Rundgänge gesamt: `config.crm.php` auf dem Server anlegen (Tabelle/Abfrage nach `visitfy-sync.sh` festlegen, DB-Benutzer nur mit Leserecht)
- [ ] Case-Prozente belegen, Flora als eigenes Café kennzeichnen
- [x] Partnerstimmen live (4 Partner); weitere nur mit schriftlicher Freigabe (Foto, Name, Zitat, Umsatz)
- [ ] Partnerstädte zu den Stimmen ergänzen (`stadt` in `partials/partner-testimonials.php`)
- [ ] Datenschutzerklärung: Cloudflare (vor der ganzen Seite) und Mailgun (Formular-Mails) fehlen noch

## Offen / Roadmap

- [ ] Visuelles QA des Apple-Refactors live (Hero ≥1024 px, Nav-Blur, Partikel-Dichte, Mobile)
- [ ] FPS-Throttling in Partikel-Render-Loop (falls < 50 fps auf schwachen Geräten)
- [ ] Critical CSS inline für above-the-fold (Hero+Nav)
- [ ] Interaktive 360°-Demo-Einbindung
- [ ] Echtes CI/CD-Deploy (GitHub Actions → SSH)
- [ ] Dynamische Content-Erweiterung im Admin

## Pflege dieser Datei

Bei jedem neuen Feature/Fix: kürzeste Zeile als neue Sub-Phase ergänzen und als `[x]` markieren. Erledigte Roadmap-Punkte nach oben in die Phasen verschieben.
