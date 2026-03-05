<?php
/**
 * Visitfy3 – pages/impressum.php
 * Rechtssicherer Impressumsaufbau (§ 5 TMG) ohne falsche Zusicherungen.
 * TODO: Reale Angaben eintragen vor Veröffentlichung.
 */
$root = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) ? '' : '../';
$pageTitle = 'Impressum | Visitfy';
$pageDesc  = 'Impressum von Visitfy – Angaben gemäß § 5 TMG.';

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
?>

<main id="main-content">

  <section class="page-hero">
    <div class="container">
      <h1 class="fade-up">Impressum</h1>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="legal-content fade-up">

        <h2>Angaben gemäß § 5 TMG</h2>
        <p>
          <!-- TODO: Vollständige Angaben vor Veröffentlichung eintragen -->
          Visitfy<br>
          [Vor- und Nachname des Inhabers / Firma]<br>
          [Straße und Hausnummer]<br>
          [PLZ] Flensburg<br>
          Deutschland
        </p>

        <h2>Kontakt</h2>
        <p>
          E-Mail: <a href="mailto:info@visitfy.de">info@visitfy.de</a><!-- TODO: verify --><br>
          <!-- TODO: Telefonnummer ergänzen falls vorhanden -->
        </p>

        <h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>
        <p>
          <!-- TODO: Name und Anschrift der verantwortlichen Person -->
          [Name und Anschrift]
        </p>

        <h2>Umsatzsteuer-Identifikationsnummer</h2>
        <p>
          <!-- TODO: USt-IdNr. eintragen falls vorhanden -->
          gemäß § 27a UStG: [USt-IdNr. oder „Nicht vorhanden"]
        </p>

        <h2>Streitschlichtung</h2>
        <p>
          Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:
          <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener noreferrer">
            https://ec.europa.eu/consumers/odr/
          </a>
        </p>
        <p>
          Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer
          Verbraucherschlichtungsstelle teilzunehmen.
        </p>

        <h2>Haftung für Inhalte</h2>
        <p>
          Als Diensteanbieter sind wir gemäß § 7 Abs. 1 TMG für eigene Inhalte auf diesen Seiten
          nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als
          Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde
          Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige
          Tätigkeit hinweisen.
        </p>

        <h2>Haftung für Links</h2>
        <p>
          Unser Angebot enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen
          Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen.
          Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der
          Seiten verantwortlich. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar.
        </p>

        <h2>Urheberrecht</h2>
        <p>
          Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem
          deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der
          Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung
          des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur für den
          privaten, nicht kommerziellen Gebrauch gestattet.
        </p>

      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
