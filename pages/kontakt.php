<?php
/**
 * Visitfy3 – pages/kontakt.php
 * Contact page with form, server-side validation, honeypot, CSRF, DSGVO checkbox.
 */
require __DIR__ . '/../partials/cms.php';

/* ── Session for CSRF token ────────────────────────────── */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}

$root = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) ? visitfy_base_path() : '../';
$pageTitle = 'Kontakt | Visitfy – 360° Rundgänge anfragen';
$pageDesc  = 'Kontaktieren Sie Visitfy für ein unverbindliches Angebot für Ihren 360° Rundgang. Nutzen Sie unser Kontaktformular oder schreiben Sie direkt.';

/* ── Server-side form handling ─────────────────────────── */
$formSent  = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* CSRF check */
    $csrfToken = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), $csrfToken)) {
        $formError = 'Ungültige Anfrage. Bitte laden Sie die Seite neu.';
    /* Honeypot check */
    } elseif (!empty($_POST['hp_website'])) {
        /* Silent reject */
        $formSent = true;
    } else {
        /* Sanitize & validate */
        $name     = trim(strip_tags($_POST['name']     ?? ''));
        $firma    = trim(strip_tags($_POST['firma']    ?? ''));
        $email    = trim(strip_tags($_POST['email']    ?? ''));
        $telefon  = trim(strip_tags($_POST['telefon']  ?? ''));
        $branche  = trim(strip_tags($_POST['branche']  ?? ''));
        $nachricht= trim(strip_tags($_POST['nachricht']?? ''));
        $dsgvo    = isset($_POST['dsgvo']) ? (bool)$_POST['dsgvo'] : false;

        $allowedBranchen = ['gastronomie','hotel','immobilien','einzelhandel','praxis','fitness','sonstiges',''];

        if (empty($name) || strlen($name) > 120) {
            $formError = 'Bitte geben Sie einen gültigen Namen an.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 200) {
            $formError = 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
        } elseif (strlen($telefon) > 40) {
            $formError = 'Die Telefonnummer ist zu lang.';
        } elseif (empty($nachricht) || strlen($nachricht) > 3000) {
            $formError = 'Bitte geben Sie eine Nachricht ein (max. 3000 Zeichen).';
        } elseif (!$dsgvo) {
            $formError = 'Bitte akzeptieren Sie die Datenschutzerklärung.';
        } elseif (!in_array($branche, $allowedBranchen, true)) {
            $formError = 'Ungültige Branchenauswahl.';
        } else {
            /* TODO: replace with SMTP mailer (e.g. PHPMailer + config) */
            $to = 'info@visitfy.de'; /* TODO: verify recipient */
            /* Strip CRLF from subject fields to prevent email header injection */
            $safeSubjectName = str_replace(["\r", "\n"], '', $name);
            $subject = 'Neue Anfrage von ' . $safeSubjectName;
            $body    = "Name: $name\nFirma: $firma\nE-Mail: $email\nTelefon: $telefon\nBranche: $branche\n\nNachricht:\n$nachricht";
            /* Sanitize email to prevent header injection: reject newlines */
            if (preg_match('/[\r\n]/', $email)) {
                $formError = 'Ungültige E-Mail-Adresse.';
            } else {
                $headers = 'From: noreply@visitfy.de' . "\r\n" . 'Reply-To: ' . $email;
                /* @mail() – server must be configured for mail delivery */
                /* $sent = mail($to, $subject, $body, $headers); */
                /* For now, always treat as sent (TODO: enable mail() on server) */
                $formSent = true;
                /* Regenerate CSRF token after successful submission */
                $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
            }
        }
    }
}

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
?>

<main id="main-content">

  <section class="page-hero">
    <div class="container">
      <p class="section-eyebrow fade-up">Kontakt</p>
      <h1 class="fade-up delay-1">Angebot anfragen</h1>
      <p class="fade-up delay-2">
        Unverbindlich, persönlich und kostenlos. Wir melden uns innerhalb von 24 Stunden.
      </p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="contact-grid">

        <!-- Contact info -->
        <div class="contact-info fade-up">
          <h3>So erreichen Sie uns</h3>
          <p>
            Nutzen Sie das Formular für eine schnelle Anfrage – oder schreiben Sie uns
            direkt per E-Mail.
          </p>
          <p style="margin-top:1.5rem">
            <a href="mailto:info@visitfy.de">info@visitfy.de</a><!-- TODO: verify -->
          </p>
          <div style="margin-top:2.5rem">
            <p style="font-size:0.78rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted);margin-bottom:0.75rem">Antwortzeit</p>
            <p>In der Regel innerhalb von 24 Stunden an Werktagen.</p>
          </div>
          <div style="margin-top:2rem">
            <p style="font-size:0.78rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-muted);margin-bottom:0.75rem">Standort</p>
            <p>Flensburg, Deutschland<!-- TODO: Adresse ergänzen --></p>
          </div>
        </div>

        <!-- Form -->
        <div class="contact-form-box fade-up delay-2">
<?php if ($formSent): ?>
          <div class="form-status success" role="alert" style="display:block">
            ✓ Vielen Dank! Ihre Anfrage ist eingegangen. Wir melden uns bald.
          </div>
<?php elseif ($formError): ?>
          <div class="form-status error" role="alert" style="display:block">
            <?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?>
          </div>
<?php endif; ?>

          <form method="post" action="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/kontakt.php" novalidate>
            <!-- CSRF token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <!-- Honeypot -->
            <div class="form-honeypot" aria-hidden="true">
              <label for="hp_website">Website</label>
              <input type="text" id="hp_website" name="hp_website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="k_name">Name *</label>
                <input type="text" id="k_name" name="name" required autocomplete="name"
                       value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Max Mustermann">
              </div>
              <div class="form-group">
                <label for="k_firma">Firma</label>
                <input type="text" id="k_firma" name="firma" autocomplete="organization"
                       value="<?= htmlspecialchars($_POST['firma'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Muster GmbH">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="k_email">E-Mail *</label>
                <input type="email" id="k_email" name="email" required autocomplete="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="name@firma.de">
              </div>
              <div class="form-group">
                <label for="k_telefon">Telefon (optional)</label>
                <input type="tel" id="k_telefon" name="telefon" autocomplete="tel"
                       value="<?= htmlspecialchars($_POST['telefon'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="+49 …">
              </div>
            </div>

            <div class="form-group">
              <label for="k_branche">Branche</label>
              <select id="k_branche" name="branche">
                <option value="">Bitte wählen…</option>
                <?php
                $branchen = [
                    'gastronomie' => 'Gastronomie (Restaurant, Café, Bar)',
                    'hotel'       => 'Hotel &amp; Wellness',
                    'immobilien'  => 'Immobilien',
                    'einzelhandel'=> 'Einzelhandel &amp; Showroom',
                    'praxis'      => 'Praxis &amp; Medizin',
                    'fitness'     => 'Fitness &amp; Sport',
                    'sonstiges'   => 'Sonstiges',
                ];
                $selBranche = $_POST['branche'] ?? '';
                foreach ($branchen as $val => $label):
                    $sel = ($selBranche === $val) ? ' selected' : '';
                ?>
                <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"<?= $sel ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="k_nachricht">Nachricht *</label>
              <textarea id="k_nachricht" name="nachricht" required rows="5"
                        placeholder="Beschreiben Sie kurz Ihre Location und was Sie sich vorstellen…"><?= htmlspecialchars($_POST['nachricht'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-check">
              <input type="checkbox" id="k_dsgvo" name="dsgvo" required<?= !empty($_POST['dsgvo']) ? ' checked' : '' ?>>
              <label for="k_dsgvo">
                Ich habe die <a href="<?= htmlspecialchars($root, ENT_QUOTES, 'UTF-8') ?>pages/datenschutz.php">Datenschutzerklärung</a> gelesen und bin mit der
                Verarbeitung meiner Daten zur Bearbeitung meiner Anfrage einverstanden. *
              </label>
            </div>

            <button type="submit" class="btn btn-primary js-btnfx-kontakt" style="width:100%">Anfrage absenden</button>
          </form>
        </div>

      </div><!-- /.contact-grid -->
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
