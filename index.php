<?php
require_once __DIR__ . '/auth/auth.php';

if (isLoggedIn()) {
    header('Location: ' . dashboardPathFor($_SESSION['role']));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reflex · Simple delivery management for small retailers</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="/reflex/assets/css/style.css">
</head>
<body>

<div class="auth-topbar" style="background:var(--ink-950);">
  <a class="brand-row" href="/reflex/index.php">
    <span class="brand-glyph"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12L11 4L21 4L13 12L21 20L11 20L3 12Z" fill="white" opacity="0.95"/></svg></span>
    <span class="brand-mark" style="color:#fff;">Reflex</span>
  </a>
  <div class="d-flex gap-2">
    <a href="/reflex/login.php" class="btn btn-outline-light btn-sm">Log in</a>
    <a href="/reflex/register.php" class="btn btn-primary btn-sm">Sign up</a>
  </div>
</div>

<section class="auth-hero" style="min-height: auto;">
  <div class="auth-hero-panel" style="padding: 76px 56px;">
    <span class="eyebrow">Built for Kenyan retailers</span>
    <h1>Manage deliveries.<br>Track orders.<br>Deliver with confidence.</h1>
    <p class="lead mt-2">Create deliveries, assign riders, and track every parcel from pickup to doorstep &mdash; without spreadsheets, WhatsApp chaos, or guesswork.</p>
    <div class="mt-4 d-flex gap-2 flex-wrap">
      <a href="/reflex/register.php" class="btn btn-primary btn-lg">Create an account</a>
      <a href="/reflex/login.php" class="btn btn-lg btn-outline-light">I already have an account</a>
    </div>

    <svg class="route-illustration hero-illo" viewBox="0 0 420 360" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <path d="M20 300 C 90 300, 90 220, 160 220 S 230 140, 300 140 S 370 60, 400 60" stroke="url(#routeGrad3)" stroke-width="3" stroke-dasharray="2 14" stroke-linecap="round"/>
      <circle cx="20" cy="300" r="7" fill="#06B6D4"/>
      <circle cx="160" cy="220" r="5" fill="#8257E5" opacity="0.8"/>
      <circle cx="300" cy="140" r="5" fill="#8257E5" opacity="0.6"/>
      <g transform="translate(384,42)">
        <path d="M16 0C7.163 0 0 7.163 0 16c0 12 16 26 16 26s16-14 16-26c0-8.837-7.163-16-16-16z" fill="#06B6D4"/>
        <circle cx="16" cy="16" r="6.5" fill="#0A0E27"/>
      </g>
      <g transform="translate(4,282)" opacity="0.9">
        <rect x="0" y="6" width="34" height="24" rx="4" fill="#4C63EA"/>
        <rect x="0" y="6" width="34" height="8" rx="4" fill="#2F49E0"/>
        <rect x="12" y="0" width="10" height="10" rx="2" fill="#8257E5"/>
      </g>
      <defs>
        <linearGradient id="routeGrad3" x1="20" y1="300" x2="400" y2="60" gradientUnits="userSpaceOnUse">
          <stop stop-color="#06B6D4"/>
          <stop offset="1" stop-color="#8257E5"/>
        </linearGradient>
      </defs>
    </svg>
  </div>

  <div class="auth-form-panel" style="background:var(--surface);">
    <div style="max-width:380px;">
      <span class="eyebrow">How it works</span>
      <h2 class="mb-3" style="font-size:1.5rem;">One flow, three roles</h2>
      <p class="text-muted" style="font-size:0.95rem;">Retailer creates a delivery &rarr; dispatcher assigns a rider &rarr; rider updates status &rarr; retailer sees it land. Every step is logged.</p>
    </div>
  </div>
</section>

<section class="landing-features">
  <div class="app-container">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="feature-card">
          <div class="feature-icon" style="background:var(--royal-50); color:var(--royal-600);"><i class="bi bi-shop"></i></div>
          <h5>Retailers</h5>
          <p class="text-muted mb-0">Create a delivery in seconds and track it in real time until it reaches your customer.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card">
          <div class="feature-icon" style="background:var(--amber-100); color:#A6660F;"><i class="bi bi-diagram-3"></i></div>
          <h5>Dispatchers</h5>
          <p class="text-muted mb-0">See every incoming request, assign the right rider, and monitor progress across your fleet.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card">
          <div class="feature-icon" style="background:var(--mint-100); color:var(--mint-600);"><i class="bi bi-bicycle"></i></div>
          <h5>Riders</h5>
          <p class="text-muted mb-0">Get assigned jobs on one screen and update status as you pick up, move, and deliver.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="app-footer">
  <div class="container-fluid app-container">
    <span>Reflex &middot; Simple delivery management for small retailers.</span>
    <span class="text-muted">Academic demo build &middot; <?= date('Y') ?></span>
  </div>
</footer>
</body>
</html>
