<?php
/**
 * Visitfy Admin – Statistik (cookielos, Daten aus assets/data/stats/*.jsonl, siehe partials/stats.php)
 */
require_once dirname(__DIR__) . '/bootstrap.php';
require_once admin_root_path() . '/partials/stats.php';
admin_require_login();

date_default_timezone_set('Europe/Berlin');

$days = (int)($_GET['d'] ?? 30);
if (!in_array($days, [7, 30, 90], true)) {
    $days = 30;
}

/* Tage in deutscher Zeit (Einträge sind in UTC gespeichert) */
$todayTs = strtotime('today');
$fromTs = strtotime('-' . ($days - 1) . ' days', $todayTs);
$fromDay = date('Y-m-d', $fromTs);

$daily = [];
for ($i = 0; $i < $days; $i++) {
    $daily[date('Y-m-d', strtotime('+' . $i . ' days', $fromTs))] = ['visitors' => [], 'pv' => 0, 'leads' => 0];
}

$pageViews = 0;
$pages = [];          // path => [pv, visitors-set]
$sources = [];        // source => visits (Einstiege)
$devices = [];        // m/t/d => visitor-day-set
$events = [];         // name => count
$bots = [];           // name => [count, last, paths]
$notFound = [];       // path => count (Aufrufe nicht vorhandener Seiten)
$aiSources = visitfy_stats_ai_sources();

/* Monatsdateien im Zeitraum lesen */
$months = [];
for ($ts = $fromTs - 86400; $ts <= time() + 86400; $ts += 86400) {
    $months[gmdate('Y-m', $ts)] = true;
}
$dir = visitfy_stats_dir();
foreach (array_keys($months) as $month) {
    $file = $dir . '/' . $month . '.jsonl';
    if (!is_file($file)) {
        continue;
    }
    $fh = fopen($file, 'rb');
    if (!$fh) {
        continue;
    }
    while (($line = fgets($fh)) !== false) {
        $row = json_decode($line, true);
        if (!is_array($row) || empty($row['t'])) {
            continue;
        }
        $rowTs = strtotime((string)$row['t']);
        $day = $rowTs ? date('Y-m-d', $rowTs) : '';
        if ($day === '' || !isset($daily[$day])) {
            continue;
        }
        $kind = (string)($row['k'] ?? '');
        $path = (string)($row['p'] ?? '/');
        if (strpos($path, '/__') === 0) {
            continue; /* technische Testaufrufe (Deploy-Prüfung) */
        }
        if ($kind === 'pv') {
            $v = (string)($row['v'] ?? '');
            $pageViews++;
            $daily[$day]['pv']++;
            $daily[$day]['visitors'][$v] = true;
            if ((int)($row['c'] ?? 200) === 404) {
                $notFound[$path] = ($notFound[$path] ?? 0) + 1;
            } else {
                $pages[$path]['pv'] = ($pages[$path]['pv'] ?? 0) + 1;
                $pages[$path]['v'][$day . $v] = true;
            }
            $src = (string)($row['s'] ?? 'Direkt');
            if ($src !== 'Intern') {
                $sources[$src] = ($sources[$src] ?? 0) + 1;
            }
            $devices[(string)($row['d'] ?? '?')][$day . $v] = true;
        } elseif ($kind === 'ev') {
            $name = (string)($row['e'] ?? '');
            $events[$name] = ($events[$name] ?? 0) + 1;
            if (strpos($name, 'lead_') === 0) {
                $daily[$day]['leads']++;
            }
        } elseif ($kind === 'bot') {
            $b = (string)($row['b'] ?? '?');
            $bots[$b]['n'] = ($bots[$b]['n'] ?? 0) + 1;
            $bots[$b]['last'] = max((string)($bots[$b]['last'] ?? ''), (string)$row['t']);
            $bots[$b]['paths'][$path] = ($bots[$b]['paths'][$path] ?? 0) + 1;
        }
    }
    fclose($fh);
}

$visitors = 0;
$maxVisitors = 1;
foreach ($daily as $d) {
    $visitors += count($d['visitors']);
    $maxVisitors = max($maxVisitors, count($d['visitors']));
}
$aiVisits = 0;
foreach ($sources as $src => $n) {
    if (in_array($src, $aiSources, true)) {
        $aiVisits += $n;
    }
}
$searchVisits = ($sources['Google'] ?? 0) + ($sources['Bing'] ?? 0) + ($sources['DuckDuckGo'] ?? 0) + ($sources['Ecosia'] ?? 0) + ($sources['Yahoo'] ?? 0) + ($sources['Startpage'] ?? 0) + ($sources['Qwant'] ?? 0);
$leadsKontakt = $events['lead_kontakt'] ?? 0;
$leadsPartner = $events['lead_partner'] ?? 0;

uasort($pages, static fn($a, $b) => $b['pv'] <=> $a['pv']);
arsort($sources);
arsort($notFound);
uasort($bots, static fn($a, $b) => $b['n'] <=> $a['n']);
$entries = array_sum($sources);
$deviceNames = ['m' => 'Smartphone', 't' => 'Tablet', 'd' => 'Desktop', '?' => 'Unbekannt'];
$deviceTotal = 0;
foreach ($devices as $set) {
    $deviceTotal += count($set);
}

$e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$n = static fn($v): string => number_format((float)$v, 0, ',', '.');
$fmtTime = static function (string $iso): string {
    $ts = strtotime($iso);
    return $ts ? date('d.m.Y H:i', $ts) : '–';
};

ob_start();
?>
<div class="topbar">
  <div class="topbar-title">Statistik</div>
  <div class="topbar-actions">
    <?php foreach ([7, 30, 90] as $opt): ?>
      <a href="?p=statistik&amp;d=<?= $opt ?>" class="btn btn-sm <?= $opt === $days ? 'btn-primary' : 'btn-secondary' ?>"><?= $opt ?> Tage</a>
    <?php endforeach; ?>
  </div>
</div>

<div class="page-body">

  <div class="widget-grid">
    <div class="widget">
      <div class="widget-value"><?= $n($visitors) ?></div>
      <div class="widget-label">Besucher <span style="color:var(--text-3)">(pro Tag gezählt)</span></div>
    </div>
    <div class="widget">
      <div class="widget-value"><?= $n($pageViews) ?></div>
      <div class="widget-label">Seitenaufrufe</div>
    </div>
    <div class="widget">
      <div class="widget-value"><?= $n($searchVisits) ?></div>
      <div class="widget-label">über Suchmaschinen</div>
    </div>
    <div class="widget">
      <div class="widget-value"><?= $n($aiVisits) ?></div>
      <div class="widget-label">über KI-Suchen</div>
    </div>
    <div class="widget">
      <div class="widget-value"><?= $n($leadsKontakt) ?></div>
      <div class="widget-label">Kundenanfragen</div>
    </div>
    <div class="widget">
      <div class="widget-value"><?= $n($leadsPartner) ?></div>
      <div class="widget-label">Partner-Bewerbungen</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-header-left">
        <div class="card-title">Besucher pro Tag</div>
        <div class="card-desc" style="margin-bottom:0">Letzte <?= $days ?> Tage · Punkte = Anfragen oder Bewerbungen</div>
      </div>
      <span class="badge badge-gray">max. <?= $n($maxVisitors) ?></span>
    </div>
    <?php
      $count = count($daily);
      $bw = 100 / max(1, $count);
    ?>
    <svg viewBox="0 0 100 40" preserveAspectRatio="none" style="width:100%;height:180px;display:block" role="img" aria-label="Besucher pro Tag">
      <line x1="0" y1="39.8" x2="100" y2="39.8" stroke="rgba(255,255,255,0.12)" stroke-width="0.2" vector-effect="non-scaling-stroke"/>
      <?php $i = 0; foreach ($daily as $day => $d): $v = count($d['visitors']); $h = $v / $maxVisitors * 36; ?>
        <rect x="<?= round($i * $bw + $bw * 0.15, 3) ?>" y="<?= round(40 - $h, 3) ?>" width="<?= round($bw * 0.7, 3) ?>" height="<?= round($h, 3) ?>" fill="rgba(255,255,255,<?= $day === date('Y-m-d') ? '0.45' : '0.75' ?>)"><title><?= $e(date('d.m.', strtotime($day))) ?>: <?= $v ?> Besucher, <?= $d['pv'] ?> Aufrufe<?= $d['leads'] ? ', ' . $d['leads'] . ' Anfragen' : '' ?></title></rect>
        <?php if ($d['leads'] > 0): ?>
          <circle cx="<?= round($i * $bw + $bw / 2, 3) ?>" cy="2" r="0.9" fill="#22c55e"/>
        <?php endif; ?>
      <?php $i++; endforeach; ?>
    </svg>
    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-2);margin-top:6px">
      <span><?= $e(date('d.m.', $fromTs)) ?></span><span>heute</span>
    </div>
  </div>

  <div class="dash-grid">
    <div class="card" style="margin-top:0">
      <div class="card-header"><div class="card-header-left"><div class="card-title">Woher die Besucher kommen</div><div class="card-desc" style="margin-bottom:0">Einstiege von außen</div></div></div>
      <?php if (!$sources): ?>
        <p style="color:var(--text-2);font-size:13px">Noch keine Daten.</p>
      <?php else: ?>
        <div class="status-list">
          <?php foreach (array_slice($sources, 0, 15, true) as $src => $cnt): ?>
            <div class="status-row">
              <span class="status-row-label"><?= $e($src) ?><?php if (in_array($src, $aiSources, true)): ?> <span class="badge badge-green">KI</span><?php endif; ?></span>
              <span><?= $n($cnt) ?> <span style="color:var(--text-2)">· <?= $entries ? round($cnt / $entries * 100) : 0 ?> %</span></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="card" style="margin-top:0">
      <div class="card-header"><div class="card-header-left"><div class="card-title">Meistbesuchte Seiten</div></div></div>
      <?php if (!$pages): ?>
        <p style="color:var(--text-2);font-size:13px">Noch keine Daten.</p>
      <?php else: ?>
        <div class="status-list">
          <?php foreach (array_slice($pages, 0, 15, true) as $path => $pd): ?>
            <div class="status-row">
              <span class="status-row-label" style="max-width:65%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= $e($path) ?></span>
              <span><?= $n($pd['pv']) ?> <span style="color:var(--text-2)">· <?= $n(count($pd['v'])) ?> Besucher</span></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="card" style="margin-top:0">
      <div class="card-header"><div class="card-header-left"><div class="card-title">Crawler</div><div class="card-desc" style="margin-bottom:0">Suchmaschinen und KI-Dienste, die Seiten abrufen</div></div></div>
      <?php if (!$bots): ?>
        <p style="color:var(--text-2);font-size:13px">Noch keine Crawler-Besuche erfasst.</p>
      <?php else: ?>
        <div class="status-list">
          <?php foreach ($bots as $name => $bd): arsort($bd['paths']); $topPath = (string)array_key_first($bd['paths']); ?>
            <div class="status-row">
              <span class="status-row-label"><?= $e($name) ?> <span style="color:var(--text-3)"><?= $e($topPath) ?></span></span>
              <span><?= $n($bd['n']) ?> <span style="color:var(--text-2)">· zuletzt <?= $e($fmtTime($bd['last'])) ?></span></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="card" style="margin-top:0">
      <div class="card-header"><div class="card-header-left"><div class="card-title">Geräte</div></div></div>
      <?php if (!$deviceTotal): ?>
        <p style="color:var(--text-2);font-size:13px">Noch keine Daten.</p>
      <?php else: ?>
        <div class="status-list">
          <?php foreach ($devices as $key => $set): ?>
            <div class="status-row">
              <span class="status-row-label"><?= $e($deviceNames[$key] ?? $key) ?></span>
              <span><?= round(count($set) / $deviceTotal * 100) ?> %</span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <p style="color:var(--text-2);font-size:12px;margin-top:14px;line-height:1.5">Ohne Cookies, ohne JavaScript, ohne gespeicherte IP-Adressen. Besucher werden pro Tag gezählt; wer an zwei Tagen kommt, zählt zweimal.</p>
    </div>

    <?php if ($notFound): ?>
    <div class="card" style="margin-top:0">
      <div class="card-header"><div class="card-header-left"><div class="card-title">Nicht gefundene Adressen</div><div class="card-desc" style="margin-bottom:0">Links, die ins Leere führen – lohnt eine Weiterleitung</div></div></div>
      <div class="status-list">
        <?php foreach (array_slice($notFound, 0, 15, true) as $path => $cnt): ?>
          <div class="status-row">
            <span class="status-row-label" style="max-width:75%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= $e($path) ?></span>
            <span><?= $n($cnt) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

</div>
<?php
$pageContent = ob_get_clean();
$pageTitle = 'Statistik';
$currentPage = 'statistik';
include dirname(__DIR__) . '/partial/layout.php';
