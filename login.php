<?php
require_once __DIR__ . '/auth/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . dashboardPathFor($_SESSION['role']));
    exit;
}

$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $oldEmail = $email;

        if ($email === '' || $password === '') {
            $errors[] = 'Please enter both email and password.';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            try {
                $pdo = getDbConnection();
                $stmt = $pdo->prepare('SELECT id, name, email, password, role, is_active FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                // Edge case: invalid login -> one generic friendly error
                // (never reveal whether the email or the password was wrong).
                if (!$user || !password_verify($password, $user['password'])) {
                    $errors[] = 'Incorrect email or password. Please try again.';
                } elseif (!$user['is_active']) {
                    $errors[] = 'This account has been deactivated. Contact your dispatcher.';
                } else {
                    loginUser($user);
                    header('Location: ' . dashboardPathFor($user['role']));
                    exit;
                }
            } catch (Throwable $e) {
                error_log('Login error: ' . $e->getMessage());
                $errors[] = 'Something went wrong. Please try again shortly.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log in · Reflex</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="/reflex/assets/css/style.css">
</head>
<body>
<div class="auth-shell">
  <div class="auth-hero">
    <div class="auth-hero-panel">
      <span class="eyebrow">Delivery management</span>
      <h1>Manage deliveries.<br>Track orders.<br>Deliver with confidence.</h1>
      <p class="lead">Log in to see your live dashboard &mdash; every delivery, every rider, every status update, in one place.</p>

      <div class="hero-stats">
        <div><div class="hero-stat-num">3</div><div class="hero-stat-label">Roles, one system</div></div>
        <div><div class="hero-stat-num">5</div><div class="hero-stat-label">Tracked stages</div></div>
        <div><div class="hero-stat-num">100%</div><div class="hero-stat-label">Audit trail</div></div>
      </div>

      <svg class="route-illustration hero-illo" viewBox="0 0 420 360" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M20 300 C 90 300, 90 220, 160 220 S 230 140, 300 140 S 370 60, 400 60" stroke="url(#routeGrad)" stroke-width="3" stroke-dasharray="2 14" stroke-linecap="round"/>
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
          <linearGradient id="routeGrad" x1="20" y1="300" x2="400" y2="60" gradientUnits="userSpaceOnUse">
            <stop stop-color="#06B6D4"/>
            <stop offset="1" stop-color="#8257E5"/>
          </linearGradient>
        </defs>
      </svg>
    </div>

    <div class="auth-form-panel">
      <div class="auth-card">
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="brand-glyph"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12L11 4L21 4L13 12L21 20L11 20L3 12Z" fill="white" opacity="0.95"/></svg></span>
          <span class="brand-mark">Reflex</span>
        </div>
        <p class="auth-tagline">Simple delivery management for small retailers.</p>

        <?php if ($errors): ?>
          <div class="alert alert-danger reflex-alert">
            <?php foreach ($errors as $err): ?><div><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($err) ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="/reflex/login.php" class="reflex-form" novalidate>
          <?= csrfField() ?>
          <div class="mb-3">
            <label for="email">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?= e($oldEmail) ?>" autofocus>
          </div>
          <div class="mb-3">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Log in</button>
        </form>

        <p class="text-center text-muted mt-3 mb-0" style="font-size:0.88rem;">
          New here? <a href="/reflex/register.php">Create an account</a>
        </p>

        <div class="demo-creds">
          <strong>Demo accounts</strong> (password for all: <code>Reflex@123</code>)<br>
          Retailer &mdash; <code>retailer@reflex.test</code><br>
          Dispatcher &mdash; <code>dispatcher@reflex.test</code><br>
          Rider &mdash; <code>rider1@reflex.test</code>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
