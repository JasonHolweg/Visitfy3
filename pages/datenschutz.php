<?php
/**
 * Visitfy3 – pages/datenschutz.php
 * Datenschutzerklärung – neutraler, rechtssicherer Aufbau.
 * Tracking standardmäßig deaktiviert. Keine Google Fonts (lokal / System-Stack).
 * TODO: Alle Platzhalter vor Veröffentlichung mit realen Angaben befüllen.
 */
$root = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) ? '' : '../';
$pageTitle = 'Datenschutzerklärung | Visitfy';
$pageDesc  = 'Datenschutzerklärung von Visitfy – Informationen zur Verarbeitung personenbezogener Daten gemäß DSGVO.';

require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/header.php';
?>

<main id="main-content">

  <section class="page-hero">
    <div class="container">
      <h1 class="fade-up">Datenschutzerklärung</h1>
      <p class="fade-up delay-1">Stand: <?= htmlspecialchars(date('d.m.Y'), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="legal-content fade-up">

        <h2>1. Verantwortlicher</h2>
        <p>
          <!-- TODO: Vollständige Angaben eintragen -->
          Visitfy<br>
          [Name des Verantwortlichen]<br>
          [Adresse]<br>
          E-Mail: <a href="mailto:info@visitfy.de">info@visitfy.de</a>
        </p>

        <h2>2. Allgemeines zur Datenverarbeitung</h2>
        <p>
          Wir nehmen den Schutz Ihrer personenbezogenen Daten sehr ernst. Personenbezogene Daten werden
          auf dieser Website nur im technisch notwendigen Umfang erhoben und verarbeitet.
          Es findet kein Tracking durch Drittanbieter statt, sofern Sie nicht ausdrücklich einwilligen.
        </p>
        <p>
          <strong>Hinweis zu Tracking-Diensten:</strong> Auf dieser Website sind standardmäßig keine
          Analyse-Dienste (z. B. Google Analytics), Werbenetzwerke oder Social-Media-Tracker eingebunden.
          Alle eingebundenen Drittsysteme sind im Folgenden aufgeführt.
        </p>

        <h2>3. Erhebung und Speicherung personenbezogener Daten sowie Art und Zweck ihrer Verwendung</h2>

        <h2>3.1 Beim Besuch der Website</h2>
        <p>
          Beim Aufrufen unserer Website werden durch den auf Ihrem Endgerät zum Einsatz kommenden
          Browser automatisch Informationen an den Server unserer Website gesendet. Diese Informationen
          werden temporär in einem sogenannten Logfile gespeichert. Folgende Informationen werden
          dabei ohne Ihr Zutun erfasst und bis zur automatisierten Löschung gespeichert:
        </p>
        <ul>
          <li>IP-Adresse des anfragenden Rechners</li>
          <li>Datum und Uhrzeit des Zugriffs</li>
          <li>Name und URL der abgerufenen Datei</li>
          <li>Website, von der aus der Zugriff erfolgt (Referrer-URL)</li>
          <li>Verwendeter Browser und ggf. das Betriebssystem</li>
        </ul>
        <p>
          Die genannten Daten werden verarbeitet zu dem Zweck der Gewährleistung eines reibungslosen
          Verbindungsaufbaus sowie zur Systemsicherheit.
          Rechtsgrundlage: Art. 6 Abs. 1 lit. f DSGVO (berechtigtes Interesse).
        </p>

        <h2>3.2 Kontaktformular</h2>
        <p>
          Wenn Sie uns über das Kontaktformular Anfragen zukommen lassen, werden Ihre Angaben aus dem
          Anfrageformular inklusive der von Ihnen angegebenen Kontaktdaten zwecks Bearbeitung der Anfrage
          und für den Fall von Anschlussfragen bei uns gespeichert. Rechtsgrundlage: Art. 6 Abs. 1 lit. b
          DSGVO (Vertragserfüllung / vorvertragliche Maßnahmen) bzw. Art. 6 Abs. 1 lit. a DSGVO (Einwilligung).
        </p>
        <p>
          Die Daten werden gelöscht, sobald sie für die Erreichung des Zweckes ihrer Erhebung nicht mehr
          erforderlich sind, spätestens nach Ablauf der gesetzlichen Aufbewahrungsfristen.
        </p>

        <h2>4. Eingebettete Inhalte Dritter</h2>
        <p>
          Diese Website bindet virtuelle 360°-Rundgänge über die Plattform Matterport
          (<a href="https://matterport.com" target="_blank" rel="noopener noreferrer">matterport.com</a>) ein.
          Beim Laden dieser Inhalte werden Daten (u. a. IP-Adresse) an Matterport Inc. übertragen.
          Bitte beachten Sie die Datenschutzerklärung von Matterport:
          <a href="https://matterport.com/privacy-policy" target="_blank" rel="noopener noreferrer">
            matterport.com/privacy-policy
          </a>.
        </p>
        <p>
          Die Rundgänge werden erst geladen, wenn Sie aktiv auf den Platzhalter klicken (Lazy Loading).
        </p>

        <h2>5. Schriftarten</h2>
        <p>
          Diese Website verwendet ausschließlich systemseitig verfügbare Schriftarten (System-Font-Stack).
          Es werden keine externen Schriftanbieter (z. B. Google Fonts) eingebunden. Es findet daher
          kein Datenaustausch mit Schriftdiensten statt.
        </p>

        <h2>6. Cookies</h2>
        <p>
          Diese Website setzt keine Marketing- oder Tracking-Cookies. Technisch notwendige Session-Cookies
          können im Rahmen der normalen PHP-Sitzung gesetzt werden.
        </p>

        <h2>7. Ihre Rechte als betroffene Person</h2>
        <p>Ihnen stehen folgende Rechte zu:</p>
        <ul>
          <li>Auskunft über Ihre bei uns gespeicherten Daten (Art. 15 DSGVO)</li>
          <li>Berichtigung unrichtiger Daten (Art. 16 DSGVO)</li>
          <li>Löschung Ihrer Daten (Art. 17 DSGVO)</li>
          <li>Einschränkung der Verarbeitung (Art. 18 DSGVO)</li>
          <li>Datenübertragbarkeit (Art. 20 DSGVO)</li>
          <li>Widerspruch gegen die Verarbeitung (Art. 21 DSGVO)</li>
          <li>Widerruf einer erteilten Einwilligung (Art. 7 Abs. 3 DSGVO)</li>
          <li>Beschwerderecht bei der zuständigen Aufsichtsbehörde (Art. 77 DSGVO)</li>
        </ul>
        <p>
          Zur Ausübung Ihrer Rechte genügt eine formlose E-Mail an
          <a href="mailto:info@visitfy.de">info@visitfy.de</a>.
        </p>

        <h2>8. Datensicherheit</h2>
        <p>
          Diese Website verwendet SSL- bzw. TLS-Verschlüsselung. Eine verschlüsselte Verbindung erkennen
          Sie daran, dass die Adresszeile des Browsers von „http://" auf „https://" wechselt und an dem
          Schloss-Symbol in Ihrer Browserzeile.
        </p>

        <h2>9. Aktualität dieser Datenschutzerklärung</h2>
        <p>
          Diese Datenschutzerklärung wird bei Bedarf aktualisiert. Der aktuelle Stand ist im Seitentitel
          angegeben. Wir empfehlen, diese Seite regelmäßig zu prüfen.
        </p>

      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
