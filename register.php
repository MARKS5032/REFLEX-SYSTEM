<?php
require_once __DIR__ . '/auth/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . dashboardPathFor($_SESSION['role']));
    exit;
}

/**
 * Public self-registration is open to all three roles (retailer,
 * dispatcher, rider) so the full system can be demonstrated end-to-end
 * without needing to seed accounts manually via phpMyAdmin.
 *
 * NOTE for production use: in a real deployment you'd typically restrict
 * dispatcher/rider sign-up to internal staff (e.g. an invite code, or an
 * admin-only "add staff" screen) rather than open registration, since those
 * roles can assign deliveries and act as couriers. See docs/trade-off-log.md.
 */

$errors = [];
$old = ['name' => '', 'email' => '', 'phone' => '', 'role' => 'retailer'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $role     = trim($_POST['role'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        $old = ['name' => $name, 'email' => $email, 'phone' => $phone, 'role' => $role];

        if ($name === '' || $email === '' || $phone === '' || $password === '') {
            $errors[] = 'Please fill in all required fields.';
        }
        if ($name !== '' && strlen($name) < 2) {
            $errors[] = 'Please enter your full name.';
        }
        if ($email !== '' && !isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($phone !== '' && !isValidPhone($phone)) {
            $errors[] = 'Please enter a valid phone number (digits only, 7-15 characters).';
        }
        if (!in_array($role, VALID_ROLES, true)) {
            $errors[] = 'Please choose a valid account type.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (!$errors) {
            try {
                $pdo = getDbConnection();

                $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                $check->execute([$email]);
                if ($check->fetch()) {
                    // Edge case: duplicate email registration
                    $errors[] = 'An account with that email already exists. Try logging in instead.';
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $insert = $pdo->prepare(
                        'INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)'
                    );
                    $insert->execute([$name, $email, $hash, $role, $phone]);

                    flash('success', 'Account created as ' . ucfirst($role) . '! You can now log in.');
                    header('Location: /reflex/login.php');
                    exit;
                }
            } catch (Throwable $e) {
                error_log('Register error: ' . $e->getMessage());
                $errors[] = 'Something went wrong while creating your account. Please try again.';
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
<title>Create account · Reflex</title>
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
      <span class="eyebrow">Join Reflex</span>
      <h1>One account.<br>Every step of<br>the delivery.</h1>
      <p class="lead">Whether you send packages, dispatch riders, or deliver them &mdash; Reflex keeps everyone on the same page.</p>

      <div class="hero-stats">
        <div><div class="hero-stat-num">Retailer</div><div class="hero-stat-label">Create &amp; track</div></div>
        <div><div class="hero-stat-num">Dispatcher</div><div class="hero-stat-label">Assign riders</div></div>
        <div><div class="hero-stat-num">Rider</div><div class="hero-stat-label">Deliver &amp; update</div></div>
      </div>

      <svg class="route-illustration hero-illo" viewBox="0 0 420 360" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M20 300 C 90 300, 90 220, 160 220 S 230 140, 300 140 S 370 60, 400 60" stroke="url(#routeGrad2)" stroke-width="3" stroke-dasharray="2 14" stroke-linecap="round"/>
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
          <linearGradient id="routeGrad2" x1="20" y1="300" x2="400" y2="60" gradientUnits="userSpaceOnUse">
            <stop stop-color="#06B6D4"/>
            <stop offset="1" stop-color="#8257E5"/>
          </linearGradient>
        </defs>
      </svg>
    </div>

    <div class="auth-form-panel">
      <div class="auth-card" style="max-width:460px;">
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="brand-glyph"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12L11 4L21 4L13 12L21 20L11 20L3 12Z" fill="white" opacity="0.95"/></svg></span>
          <span class="brand-mark">Reflex</span>
        </div>
        <p class="auth-tagline">Create an account to start using Reflex.</p>

        <?php if ($errors): ?>
          <div class="alert alert-danger reflex-alert">
            <?php foreach ($errors as $err): ?><div><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($err) ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="/reflex/register.php" class="reflex-form" novalidate>
          <?= csrfField() ?>

          <div class="mb-3">
            <label for="role">Account type</label>
            <select class="form-select" id="role" name="role" required>
              <option value="retailer"   <?= $old['role'] === 'retailer'   ? 'selected' : '' ?>>Retailer</option>
              <option value="dispatcher" <?= $old['role'] === 'dispatcher' ? 'selected' : '' ?>>Dispatcher</option>
              <option value="rider"      <?= $old['role'] === 'rider'      ? 'selected' : '' ?>>Rider</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="name">Full name</label>
            <input type="text" class="form-control" id="name" name="name" required value="<?= e($old['name']) ?>" autofocus>
          </div>
          <div class="mb-3">
            <label for="email">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?= e($old['email']) ?>">
          </div>
          <div class="mb-3">
            <label for="phone">Phone number</label>
            <input type="tel" class="form-control" id="phone" name="phone" required value="<?= e($old['phone']) ?>" placeholder="07XXXXXXXX">
          </div>
          <div class="mb-3">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <div class="form-hint mt-1">At least 6 characters.</div>
          </div>
          <div class="mb-3">
            <label for="confirm_password">Confirm password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Create account</button>
        </form>

        <p class="text-center text-muted mt-3 mb-0" style="font-size:0.88rem;">
          Already have an account? <a href="/reflex/login.php">Log in</a>
        </p>
      </div>
    </div>
  </div>
</div>
</body>
</html>
