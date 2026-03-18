<?php
/**
 * Visitfy Admin – Router + Login
 */
require_once __DIR__ . '/bootstrap.php';

$notice = '';
$error = '';

// Handle logout
if (isset($_GET['logout'])) {
    admin_logout();
    header('Location: index.php');
    exit;
}

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'login') {
    $password = (string)($_POST['password'] ?? '');
    if (admin_login($password)) {
        $redirect = preg_replace('/[^a-z_]/', '', (string)($_GET['p'] ?? 'dashboard'));
        header('Location: index.php?p=' . ($redirect ?: 'dashboard'));
        exit;
    }
    $error = 'Login fehlgeschlagen. Bitte Passwort prüfen.';
}

// Show login page if not authenticated
if (!admin_is_logged_in()) {
    $defaultPasswordWarning = admin_password() === 'visitfy-admin';
    ?><!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Visitfy Admin Login</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
      height: 100%;
      background: #080808;
      color: #ebebeb;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;
      -webkit-font-smoothing: antialiased;
    }
    body {
      display: grid;
      place-items: center;
      min-height: 100vh;
      background-image:
        radial-gradient(ellipse 80% 60% at 50% -10%, rgba(255,255,255,0.04) 0%, transparent 70%);
    }
    .login-wrap {
      width: min(420px, 92vw);
    }
    .login-logo {
      text-align: center;
      margin-bottom: 28px;
    }
    .login-logo img {
      height: 30px;
      opacity: 0.9;
    }
    .login-logo span {
      font-size: 18px;
      font-weight: 700;
      letter-spacing: 0.1em;
      color: #fff;
    }
    .login-card {
      background: #101010;
      border: 1px solid rgba(255,255,255,0.07);
      border-radius: 16px;
      padding: 32px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .login-title {
      font-size: 20px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 6px;
    }
    .login-sub {
      font-size: 13px;
      color: #888;
      margin-bottom: 24px;
      line-height: 1.5;
    }
    .field label {
      display: block;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: #888;
      margin-bottom: 6px;
    }
    input[type="password"] {
      width: 100%;
      background: #080808;
      border: 1px solid rgba(255,255,255,0.1);
      color: #ebebeb;
      border-radius: 8px;
      padding: 11px 14px;
      font-size: 14px;
      font-family: inherit;
      outline: none;
      transition: border-color 0.15s;
    }
    input[type="password"]:focus { border-color: rgba(255,255,255,0.3); }
    .login-btn {
      display: block;
      width: 100%;
      margin-top: 16px;
      padding: 12px;
      background: #fff;
      color: #000;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      transition: opacity 0.15s;
    }
    .login-btn:hover { opacity: 0.9; }
    .msg {
      font-size: 13px;
      padding: 10px 14px;
      border-radius: 8px;
      margin-bottom: 16px;
    }
    .msg-err {
      background: rgba(239,68,68,0.1);
      border: 1px solid rgba(239,68,68,0.2);
      color: #f87171;
    }
    .msg-warn {
      background: rgba(234,179,8,0.1);
      border: 1px solid rgba(234,179,8,0.2);
      color: #fbbf24;
    }
    .login-footer {
      text-align: center;
      margin-top: 20px;
      font-size: 12px;
      color: rgba(255,255,255,0.2);
    }
  </style>
</head>
<body>
  <div class="login-wrap">
    <div class="login-logo">
      <img src="../assets/img/logo-white.svg" alt="Visitfy" onerror="this.style.display='none';this.nextElementSibling.style.display='inline'">
      <span style="display:none">VISITFY</span>
    </div>
    <div class="login-card">
      <div class="login-title">Willkommen zurück</div>
      <div class="login-sub">Melde dich mit deinem Admin-Passwort an.</div>

      <?php if ($error): ?>
      <div class="msg msg-err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <?php if ($defaultPasswordWarning): ?>
      <div class="msg msg-warn">
        Standardpasswort aktiv. Bitte in den Einstellungen ändern.
      </div>
      <?php endif; ?>

      <form method="post" action="">
        <input type="hidden" name="action" value="login">
        <div class="field">
          <label for="pw">Passwort</label>
          <input type="password" id="pw" name="password" placeholder="••••••••" required autofocus>
        </div>
        <button type="submit" class="login-btn">Einloggen</button>
      </form>
    </div>
    <div class="login-footer">Visitfy Admin Panel</div>
  </div>
</body>
</html>
<?php
    exit;
}

// Route to page
$page = preg_replace('/[^a-z_]/', '', (string)($_GET['p'] ?? 'dashboard'));
$pageFile = __DIR__ . '/page/' . $page . '.php';
if (!is_file($pageFile)) {
    $pageFile = __DIR__ . '/page/dashboard.php';
    $page = 'dashboard';
}
require $pageFile;
