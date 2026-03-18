<?php
/**
 * Visitfy Admin – Shared Layout Shell
 * Variables expected: $pageTitle, $currentPage, $csrf, $pageContent
 * Optional: $notice, $error, $showActionBar, $actionBarFormId
 */
if (!defined('ADMIN_LAYOUT_GUARD')) define('ADMIN_LAYOUT_GUARD', true);
?><!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? 'Admin', ENT_QUOTES, 'UTF-8') ?> · Visitfy Admin</title>
  <style>
    :root {
      --bg: #080808;
      --surface: #101010;
      --surface-2: #161616;
      --surface-3: #1e1e1e;
      --border: rgba(255,255,255,0.07);
      --border-hover: rgba(255,255,255,0.14);
      --text: #ebebeb;
      --text-2: #888;
      --text-3: #444;
      --green: #22c55e;
      --red: #ef4444;
      --yellow: #eab308;
      --sidebar-w: 220px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      height: 100%;
      background: var(--bg);
      color: var(--text);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;
      font-size: 14px;
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
    }

    /* ── Layout Shell ─────────────────────────────────────── */
    .admin-shell {
      display: flex;
      min-height: 100vh;
    }

    /* ── Sidebar ──────────────────────────────────────────── */
    .sidebar {
      position: fixed;
      top: 0; left: 0; bottom: 0;
      width: var(--sidebar-w);
      background: var(--surface);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      z-index: 100;
      overflow-y: auto;
    }

    .sidebar-logo {
      padding: 20px 16px 16px;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }

    .sidebar-logo img {
      height: 26px;
      display: block;
    }

    .sidebar-nav {
      flex: 1;
      padding: 10px 8px;
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .nav-group {
      padding-bottom: 4px;
      margin-bottom: 4px;
    }

    .nav-label {
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--text-3);
      padding: 10px 8px 4px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 9px;
      padding: 8px 10px;
      border-radius: 8px;
      color: var(--text-2);
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      transition: background 0.15s, color 0.15s;
      cursor: pointer;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
    }

    .nav-item svg {
      flex-shrink: 0;
      opacity: 0.6;
    }

    .nav-item:hover {
      background: var(--surface-3);
      color: var(--text);
    }

    .nav-item:hover svg {
      opacity: 1;
    }

    .nav-item.active {
      background: rgba(255,255,255,0.08);
      color: #fff;
    }

    .nav-item.active svg {
      opacity: 1;
    }

    .sidebar-footer {
      padding: 8px 8px 16px;
      border-top: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      gap: 2px;
      flex-shrink: 0;
    }

    /* ── Main Content Area ────────────────────────────────── */
    .main {
      margin-left: var(--sidebar-w);
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* ── Top Bar ──────────────────────────────────────────── */
    .topbar {
      position: sticky;
      top: 0;
      z-index: 50;
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0 28px;
      height: 56px;
      background: rgba(8,8,8,0.9);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
    }

    .topbar-title {
      font-size: 15px;
      font-weight: 600;
      color: var(--text);
      flex: 1;
    }

    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* ── Page Body ────────────────────────────────────────── */
    .page-body {
      padding: 24px 28px;
      flex: 1;
    }

    .page-body.has-action-bar {
      padding-bottom: 80px;
    }

    /* ── Cards ────────────────────────────────────────────── */
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
    }

    .card + .card {
      margin-top: 16px;
    }

    .card-title {
      font-size: 14px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 4px;
    }

    .card-desc {
      font-size: 12px;
      color: var(--text-2);
      margin-bottom: 16px;
      line-height: 1.6;
    }

    .card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 16px;
      padding-bottom: 14px;
      border-bottom: 1px solid var(--border);
    }

    .card-header-left {}
    .card-header-right {}

    /* ── Form Layout ──────────────────────────────────────── */
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .form-row-3 {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 12px;
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .field label {
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: var(--text-2);
    }

    input[type="text"],
    input[type="email"],
    input[type="url"],
    input[type="number"],
    input[type="password"],
    input[type="color"],
    textarea,
    select {
      width: 100%;
      background: var(--bg);
      border: 1px solid rgba(255,255,255,0.1);
      color: var(--text);
      border-radius: 8px;
      padding: 9px 12px;
      font-size: 13px;
      font-family: inherit;
      line-height: 1.4;
      transition: border-color 0.15s;
      outline: none;
      appearance: none;
    }

    textarea {
      min-height: 88px;
      resize: vertical;
    }

    input:focus,
    textarea:focus,
    select:focus {
      border-color: rgba(255,255,255,0.3);
    }

    input[type="color"] {
      padding: 4px 6px;
      height: 36px;
      cursor: pointer;
    }

    input[type="checkbox"] {
      width: 16px;
      height: 16px;
      cursor: pointer;
      accent-color: #fff;
    }

    select option {
      background: var(--surface-2);
    }

    .checkbox-row {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 4px 0;
    }

    .checkbox-row label {
      font-size: 13px;
      color: var(--text);
      text-transform: none;
      letter-spacing: 0;
      font-weight: 400;
      cursor: pointer;
    }

    /* ── Buttons ──────────────────────────────────────────── */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 9px 16px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      text-decoration: none;
      transition: opacity 0.15s, background 0.15s;
      white-space: nowrap;
      line-height: 1;
    }

    .btn:hover { opacity: 0.85; }

    .btn-primary {
      background: #fff;
      color: #000;
    }

    .btn-secondary {
      background: transparent;
      border: 1px solid var(--border-hover);
      color: var(--text);
    }

    .btn-secondary:hover {
      background: var(--surface-3);
      opacity: 1;
    }

    .btn-danger {
      background: rgba(239,68,68,0.12);
      border: 1px solid rgba(239,68,68,0.25);
      color: #f87171;
    }

    .btn-danger:hover {
      background: rgba(239,68,68,0.2);
      opacity: 1;
    }

    .btn-sm {
      padding: 6px 12px;
      font-size: 12px;
    }

    .btn-xs {
      padding: 4px 10px;
      font-size: 11px;
    }

    /* ── Action Bar ───────────────────────────────────────── */
    .action-bar {
      position: fixed;
      bottom: 0;
      left: var(--sidebar-w);
      right: 0;
      z-index: 80;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 28px;
      background: rgba(8,8,8,0.95);
      backdrop-filter: blur(16px);
      border-top: 1px solid var(--border);
    }

    .action-bar-info {
      flex: 1;
      font-size: 12px;
      color: var(--text-2);
    }

    /* ── Repeater ─────────────────────────────────────────── */
    .repeater {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 10px;
    }

    .r-item {
      background: var(--surface-2);
      border: 1px solid var(--border);
      border-radius: 10px;
      overflow: hidden;
      transition: opacity 0.15s;
    }

    .r-item.is-ghost {
      opacity: 0.3;
    }

    .r-item.is-target {
      border-top: 2px solid rgba(255,255,255,0.5);
    }

    .r-item-head {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 12px;
      cursor: pointer;
      user-select: none;
      border-bottom: 1px solid var(--border);
    }

    .r-item-head:hover {
      background: rgba(255,255,255,0.02);
    }

    .r-handle {
      color: var(--text-3);
      cursor: grab;
      font-size: 16px;
      line-height: 1;
      padding: 0 2px;
    }

    .r-handle:active { cursor: grabbing; }

    .r-label {
      flex: 1;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-2);
    }

    .r-toggle {
      color: var(--text-3);
      font-size: 11px;
    }

    .r-remove {
      flex-shrink: 0;
    }

    .r-item-body {
      padding: 14px 12px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .r-item-body.collapsed {
      display: none;
    }

    .r-add {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px 12px;
      background: transparent;
      border: 1px dashed var(--border-hover);
      border-radius: 8px;
      color: var(--text-2);
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: border-color 0.15s, color 0.15s;
      width: 100%;
    }

    .r-add:hover {
      border-color: rgba(255,255,255,0.3);
      color: var(--text);
    }

    /* ── Badges ───────────────────────────────────────────── */
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 3px 8px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }

    .badge-green {
      background: rgba(34,197,94,0.12);
      border: 1px solid rgba(34,197,94,0.25);
      color: #4ade80;
    }

    .badge-red {
      background: rgba(239,68,68,0.12);
      border: 1px solid rgba(239,68,68,0.25);
      color: #f87171;
    }

    .badge-yellow {
      background: rgba(234,179,8,0.12);
      border: 1px solid rgba(234,179,8,0.25);
      color: #fbbf24;
    }

    .badge-gray {
      background: rgba(255,255,255,0.06);
      border: 1px solid var(--border);
      color: var(--text-2);
    }

    /* ── Messages ─────────────────────────────────────────── */
    .msg-ok {
      padding: 10px 14px;
      background: rgba(34,197,94,0.1);
      border: 1px solid rgba(34,197,94,0.2);
      color: #4ade80;
      border-radius: 8px;
      font-size: 13px;
    }

    .msg-err {
      padding: 10px 14px;
      background: rgba(239,68,68,0.1);
      border: 1px solid rgba(239,68,68,0.2);
      color: #f87171;
      border-radius: 8px;
      font-size: 13px;
    }

    /* ── Content Page Layout ──────────────────────────────── */
    .content-layout {
      display: grid;
      grid-template-columns: 200px 1fr;
      gap: 16px;
      align-items: start;
    }

    .subnav {
      position: sticky;
      top: 72px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 6px;
      overflow: hidden;
    }

    .subnav-group {
      margin-bottom: 4px;
    }

    .subnav-group-label {
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--text-3);
      padding: 8px 8px 4px;
    }

    .subnav-item {
      display: block;
      padding: 6px 8px;
      border-radius: 6px;
      color: var(--text-2);
      text-decoration: none;
      font-size: 12px;
      font-weight: 500;
      transition: background 0.12s, color 0.12s;
    }

    .subnav-item:hover {
      background: var(--surface-3);
      color: var(--text);
    }

    .subnav-item.active {
      background: rgba(255,255,255,0.08);
      color: #fff;
      font-weight: 600;
    }

    /* ── Dashboard Widgets ────────────────────────────────── */
    .widget-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 14px;
      margin-bottom: 16px;
    }

    .widget {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 18px 20px;
    }

    .widget-icon {
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--surface-3);
      border-radius: 8px;
      margin-bottom: 12px;
      color: var(--text-2);
    }

    .widget-value {
      font-size: 28px;
      font-weight: 700;
      color: var(--text);
      line-height: 1;
      margin-bottom: 4px;
    }

    .widget-label {
      font-size: 12px;
      color: var(--text-2);
    }

    /* ── Media Grid ───────────────────────────────────────── */
    .media-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 10px;
    }

    .media-item {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 10px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .media-item-thumb {
      aspect-ratio: 4/3;
      background: var(--surface-2);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .media-item-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .media-item-info {
      padding: 8px 10px;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .media-item-name {
      font-size: 11px;
      font-weight: 500;
      color: var(--text);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .media-item-folder {
      font-size: 10px;
      color: var(--text-2);
    }

    .media-item-actions {
      padding: 6px 10px 10px;
      display: flex;
      gap: 6px;
    }

    /* ── Upload Zone ──────────────────────────────────────── */
    .upload-zone {
      border: 2px dashed var(--border-hover);
      border-radius: 12px;
      padding: 40px 24px;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
      user-select: none;
    }

    .upload-zone:hover,
    .upload-zone.drag-over {
      border-color: rgba(255,255,255,0.3);
      background: rgba(255,255,255,0.02);
    }

    .upload-zone-icon {
      width: 48px;
      height: 48px;
      margin: 0 auto 12px;
      opacity: 0.3;
    }

    .upload-zone p {
      font-size: 13px;
      color: var(--text-2);
      margin-bottom: 4px;
    }

    .upload-zone strong {
      color: var(--text);
    }

    .upload-zone small {
      font-size: 11px;
      color: var(--text-3);
    }

    /* ── Upload Queue ─────────────────────────────────────── */
    .upload-queue {
      display: flex;
      flex-direction: column;
      gap: 5px;
      max-height: 280px;
      overflow-y: auto;
      margin-top: 12px;
    }

    .upload-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 12px;
      background: var(--surface-2);
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 12px;
    }

    .ui-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ui-size { color: var(--text-2); white-space: nowrap; font-size: 11px; }
    .ui-status { font-size: 11px; white-space: nowrap; font-weight: 600; }
    .ui-status.waiting { color: var(--text-3); }
    .ui-status.uploading { color: var(--yellow); }
    .ui-status.done { color: var(--green); }
    .ui-status.error { color: var(--red); }

    .progress-bar {
      height: 3px;
      background: var(--surface-3);
      border-radius: 2px;
      overflow: hidden;
      margin-top: 10px;
    }

    .progress-bar-fill {
      height: 100%;
      background: #fff;
      border-radius: 2px;
      transition: width 0.2s;
    }

    /* ── Dividers & Spacing ───────────────────────────────── */
    .divider {
      border: none;
      border-top: 1px solid var(--border);
      margin: 16px 0;
    }

    .mt-8 { margin-top: 8px; }
    .mt-12 { margin-top: 12px; }
    .mt-16 { margin-top: 16px; }
    .mt-20 { margin-top: 20px; }

    .flex { display: flex; }
    .flex-1 { flex: 1; }
    .items-center { align-items: center; }
    .gap-8 { gap: 8px; }
    .gap-12 { gap: 12px; }

    /* ── Status List ──────────────────────────────────────── */
    .status-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .status-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 8px 0;
      border-bottom: 1px solid var(--border);
      font-size: 13px;
    }

    .status-row:last-child {
      border-bottom: none;
    }

    .status-row-label {
      color: var(--text-2);
    }

    /* ── Quick Actions Grid ───────────────────────────────── */
    .quick-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
    }

    .quick-action {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 14px;
      background: var(--surface-2);
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--text);
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      transition: background 0.15s, border-color 0.15s;
    }

    .quick-action:hover {
      background: var(--surface-3);
      border-color: var(--border-hover);
    }

    /* ── Dashboard Grid ───────────────────────────────────── */
    .dash-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-top: 16px;
    }

    /* ── Checklist ────────────────────────────────────────── */
    .checklist {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .check-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 7px 0;
      border-bottom: 1px solid var(--border);
      font-size: 13px;
    }

    .check-item:last-child { border-bottom: none; }
    .check-item-label { color: var(--text-2); }
    .check-ok { color: var(--green); font-weight: 600; }
    .check-fail { color: var(--red); }

    /* ── Folder Filter Tabs ───────────────────────────────── */
    .filter-tabs {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .filter-tab {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 500;
      cursor: pointer;
      border: 1px solid var(--border);
      background: transparent;
      color: var(--text-2);
      transition: all 0.15s;
      text-decoration: none;
    }

    .filter-tab:hover, .filter-tab.active {
      background: var(--surface-3);
      border-color: var(--border-hover);
      color: var(--text);
    }

    /* ── Rename Form inline ───────────────────────────────── */
    .rename-form {
      display: flex;
      gap: 4px;
      align-items: center;
    }

    .rename-form input {
      font-size: 11px;
      padding: 5px 8px;
      flex: 1;
    }

    /* ── Toast Notifications ──────────────────────────────── */
    .toast {
      position: fixed;
      top: 16px;
      right: 16px;
      z-index: 999;
      padding: 12px 18px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      max-width: 360px;
      animation: slideIn 0.3s ease;
    }

    .toast-ok {
      background: rgba(34,197,94,0.15);
      border: 1px solid rgba(34,197,94,0.3);
      color: #4ade80;
    }

    .toast-err {
      background: rgba(239,68,68,0.15);
      border: 1px solid rgba(239,68,68,0.3);
      color: #f87171;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ── Scrollbar ────────────────────────────────────────── */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

    /* ── Responsive ───────────────────────────────────────── */
    @media (max-width: 900px) {
      .form-row, .form-row-3 { grid-template-columns: 1fr; }
      .content-layout { grid-template-columns: 1fr; }
      .dash-grid { grid-template-columns: 1fr; }
      .quick-actions { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
<div class="admin-shell">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <img src="../assets/img/logo-white.svg" alt="Visitfy" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
      <span style="display:none;font-size:15px;font-weight:700;letter-spacing:0.06em;color:#fff">VISITFY</span>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-group">
        <a href="?p=dashboard" class="nav-item <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="1" width="6" height="6" rx="1.5"/>
            <rect x="9" y="1" width="6" height="6" rx="1.5"/>
            <rect x="1" y="9" width="6" height="6" rx="1.5"/>
            <rect x="9" y="9" width="6" height="6" rx="1.5"/>
          </svg>
          Dashboard
        </a>
      </div>

      <div class="nav-group">
        <div class="nav-label">Anfragen</div>
        <a href="?p=anfragen" class="nav-item <?= ($currentPage ?? '') === 'anfragen' ? 'active' : '' ?>">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 3h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/>
            <path d="M1 4l7 5 7-5"/>
          </svg>
          Anfragen
          <?php
          $__iqPath = admin_root_path() . '/assets/data/inquiries.json';
          $__iqCount = count(admin_read_json($__iqPath, []));
          if ($__iqCount > 0): ?>
          <span style="margin-left:auto;background:rgba(255,255,255,0.1);border-radius:10px;padding:1px 7px;font-size:11px;font-weight:600"><?= $__iqCount ?></span>
          <?php endif; ?>
        </a>
      </div>

      <div class="nav-group">
        <div class="nav-label">Inhalt</div>
        <a href="?p=content" class="nav-item <?= ($currentPage ?? '') === 'content' ? 'active' : '' ?>">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 2h10a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z"/>
            <path d="M5 6h6M5 9h4"/>
          </svg>
          Inhalte
        </a>
        <a href="?p=tours" class="nav-item <?= ($currentPage ?? '') === 'tours' ? 'active' : '' ?>">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="8" cy="8" r="6"/>
            <path d="M6.5 5.5 11 8l-4.5 2.5V5.5z"/>
          </svg>
          Rundgänge
        </a>
      </div>

      <div class="nav-group">
        <div class="nav-label">Medien</div>
        <a href="?p=media" class="nav-item <?= ($currentPage ?? '') === 'media' ? 'active' : '' ?>">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="3" width="14" height="10" rx="1.5"/>
            <circle cx="5.5" cy="6.5" r="1.5"/>
            <path d="M1 10l3.5-3.5 3 3 2-2 5.5 5.5"/>
          </svg>
          Bilder
        </a>
      </div>

      <div class="nav-group">
        <div class="nav-label">System</div>
        <a href="?p=integrations" class="nav-item <?= ($currentPage ?? '') === 'integrations' ? 'active' : '' ?>">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 6 6 10"/>
            <path d="M7.5 3.5 12.5 8.5a2.12 2.12 0 0 1 0 3L11 13a2.12 2.12 0 0 1-3 0L3 8a2.12 2.12 0 0 1 0-3L4.5 3.5a2.12 2.12 0 0 1 3 0z"/>
            <path d="M5 11 2 14"/>
          </svg>
          Integrationen
        </a>
        <a href="?p=settings" class="nav-item <?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="8" cy="8" r="2"/>
            <path d="M8 1v2M8 13v2M1 8h2M13 8h2M3.05 3.05l1.41 1.41M11.54 11.54l1.41 1.41M3.05 12.95l1.41-1.41M11.54 4.46l1.41-1.41"/>
          </svg>
          Einstellungen
        </a>
      </div>
    </nav>

    <div class="sidebar-footer">
      <a href="../index.php" target="_blank" class="nav-item">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 3H3a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h9a1 1 0 0 0 1-1V9"/>
          <path d="M10 2h4v4"/>
          <path d="M14 2 8 8"/>
        </svg>
        Vorschau
      </a>
      <a href="logout.php" class="nav-item" style="color:rgba(255,255,255,0.35)">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 2H3a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/>
          <path d="M11 11l3-3-3-3"/>
          <path d="M14 8H6"/>
        </svg>
        Abmelden
      </a>
    </div>
  </aside>

  <!-- Main -->
  <div class="main">
    <?php if (!empty($notice)): ?>
    <div class="toast toast-ok" id="admin-toast">
      <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="margin-right:4px;vertical-align:-2px">
        <path d="M3 8l3.5 3.5L13 4"/>
      </svg>
      <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    <div class="toast toast-err" id="admin-toast">
      <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="margin-right:4px;vertical-align:-2px">
        <path d="M4 4l8 8M12 4l-8 8"/>
      </svg>
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <?= $pageContent ?? '' ?>

    <?php if (!empty($showActionBar)): ?>
    <div class="action-bar">
      <span class="action-bar-info">Nicht gespeicherte Änderungen werden verworfen.</span>
      <a href="?p=<?= htmlspecialchars($currentPage ?? 'dashboard', ENT_QUOTES) ?>" class="btn btn-secondary btn-sm">Verwerfen</a>
      <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">Vorschau ↗</a>
      <button type="submit" form="<?= htmlspecialchars($actionBarFormId ?? 'main-form', ENT_QUOTES) ?>" class="btn btn-primary btn-sm">Speichern</button>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Auto-dismiss toast notifications
document.querySelectorAll('.toast').forEach(function(el) {
  setTimeout(function() {
    el.style.transition = 'opacity 0.5s';
    el.style.opacity = '0';
    setTimeout(function() { el.remove(); }, 500);
  }, 4000);
});
</script>
</body>
</html>
