<?php
require_once __DIR__ . '/auth.php';
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Access Denied - Reflex</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="/reflex/assets/css/style.css">
</head>
<body style="background:var(--canvas); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;">
  <div class="auth-card text-center">
    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
      <span class="brand-glyph"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12L11 4L21 4L13 12L21 20L11 20L3 12Z" fill="white" opacity="0.95"/></svg></span>
      <span class="brand-mark">Reflex</span>
    </div>
    <div class="empty-icon mx-auto" style="background:var(--coral-100); color:var(--coral-600);"><i class="bi bi-shield-lock-fill"></i></div>
    <h2 class="mb-2 mt-3">Access denied</h2>
    <p class="text-muted mb-4">You don't have permission to view that page. Your account role is
      <strong><?= htmlspecialchars($user['role'] ?? 'unknown') ?></strong>.</p>
    <?php if ($user): ?>
      <a class="btn btn-primary w-100" href="<?= dashboardPathFor($user['role']) ?>">Go to my dashboard</a>
      <a class="btn btn-link w-100 mt-2" href="/reflex/logout.php">Log out</a>
    <?php else: ?>
      <a class="btn btn-primary w-100" href="/reflex/login.php">Go to login</a>
    <?php endif; ?>
  </div>
</body>
</html>
