<?php
$user = $user ?? currentUser();
$currentPath = $_SERVER['PHP_SELF'] ?? '';

function navActive(string $needle, string $path): string
{
    return str_contains($path, $needle) ? 'active' : '';
}

$initials = '';
if ($user) {
    $parts = preg_split('/\s+/', trim($user['name']));
    $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
}
?>
<?php if ($user): ?>
<!-- Mobile sticky topbar: hamburger + brand + logout are ALWAYS visible, never hidden.
     Deliberately rendered OUTSIDE .app-shell (a flex row) so it stacks full-width above
     the sidebar/content row instead of being squeezed as a flex sibling. -->
<div class="mobile-topbar">
  <button class="hamburger-btn" type="button" id="sidebarToggle" aria-label="Open menu" aria-expanded="false" aria-controls="reflexSidebar">
    <i class="bi bi-list"></i>
  </button>
  <a class="brand-row" href="<?= dashboardPathFor($user['role']) ?>">
    <span class="brand-glyph"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12L11 4L21 4L13 12L21 20L11 20L3 12Z" fill="white" opacity="0.95"/></svg></span>
    <span class="brand-mark">Reflex</span>
  </a>
  <a class="mobile-logout-btn" href="/reflex/logout.php" aria-label="Logout">
    <i class="bi bi-box-arrow-right"></i>
  </a>
</div>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<?php endif; ?>

<div class="app-shell">
<aside class="reflex-sidebar" id="reflexSidebar">
  <div class="sidebar-brand">
    <a class="brand-row d-flex align-items-center gap-2" href="<?= $user ? dashboardPathFor($user['role']) : '/reflex/index.php' ?>">
      <span class="brand-glyph"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12L11 4L21 4L13 12L21 20L11 20L3 12Z" fill="white" opacity="0.95"/></svg></span>
      <span class="brand-mark">Reflex</span>
    </a>
  </div>

  <?php if ($user): ?>
    <nav class="sidebar-nav">
      <div class="sidebar-section-label">Menu</div>
      <?php if ($user['role'] === 'retailer'): ?>
        <a class="sidebar-link <?= navActive('dashboard.php', $currentPath) ?>" href="/reflex/retailer/dashboard.php"><i class="bi bi-grid-1x2-fill"></i>Dashboard</a>
        <a class="sidebar-link <?= navActive('create_delivery.php', $currentPath) ?>" href="/reflex/retailer/create_delivery.php"><i class="bi bi-plus-circle-fill"></i>New Delivery</a>
        <a class="sidebar-link <?= navActive('deliveries.php', $currentPath) ?>" href="/reflex/retailer/deliveries.php"><i class="bi bi-box-seam-fill"></i>My Deliveries</a>
      <?php elseif ($user['role'] === 'dispatcher'): ?>
        <a class="sidebar-link <?= navActive('dashboard.php', $currentPath) ?>" href="/reflex/dispatcher/dashboard.php"><i class="bi bi-grid-1x2-fill"></i>Dashboard</a>
        <a class="sidebar-link <?= navActive('deliveries.php', $currentPath) ?>" href="/reflex/dispatcher/deliveries.php"><i class="bi bi-truck"></i>All Deliveries</a>
      <?php elseif ($user['role'] === 'rider'): ?>
        <a class="sidebar-link <?= navActive('dashboard.php', $currentPath) ?>" href="/reflex/rider/dashboard.php"><i class="bi bi-grid-1x2-fill"></i>Dashboard</a>
        <a class="sidebar-link <?= navActive('deliveries.php', $currentPath) ?>" href="/reflex/rider/deliveries.php"><i class="bi bi-bicycle"></i>My Jobs</a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <span class="sidebar-avatar"><?= e($initials) ?></span>
        <span>
          <span class="sidebar-user-name d-block"><?= e($user['name']) ?></span>
          <span class="sidebar-user-role"><?= e($user['role']) ?></span>
        </span>
      </div>
      <a class="sidebar-logout" href="/reflex/logout.php"><i class="bi bi-box-arrow-right"></i>Logout</a>
    </div>
  <?php else: ?>
    <nav class="sidebar-nav">
      <a class="sidebar-link" href="/reflex/login.php"><i class="bi bi-box-arrow-in-right"></i>Login</a>
    </nav>
  <?php endif; ?>
</aside>
