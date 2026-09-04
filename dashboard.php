<?php
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('dispatcher');

$user = currentUser();
$pdo = getDbConnection();

$statsStmt = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'PENDING') AS pending,
        SUM(status = 'ASSIGNED') AS assigned,
        SUM(status IN ('PICKED_UP','IN_TRANSIT')) AS in_transit,
        SUM(status = 'DELIVERED') AS delivered
    FROM deliveries
");
$stats = $statsStmt->fetch();
$stats = array_map(fn($v) => (int) $v, $stats);

// Deliveries needing dispatcher attention: unassigned (PENDING).
$needsAttentionStmt = $pdo->query("
    SELECT d.*, ret.name AS retailer_name
    FROM deliveries d
    JOIN users ret ON ret.id = d.retailer_id
    WHERE d.status = 'PENDING'
    ORDER BY d.created_at ASC
    LIMIT 8
");
$needsAttention = $needsAttentionStmt->fetchAll();

// Available riders (active riders, with their current active job count).
$ridersStmt = $pdo->query("
    SELECT u.id, u.name, u.phone,
        SUM(d.status IN ('ASSIGNED','PICKED_UP','IN_TRANSIT')) AS active_jobs
    FROM users u
    LEFT JOIN deliveries d ON d.rider_id = u.id
    WHERE u.role = 'rider' AND u.is_active = 1
    GROUP BY u.id, u.name, u.phone
    ORDER BY active_jobs ASC
");
$riders = $ridersStmt->fetchAll();

$pageTitle = 'Dispatcher Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Dispatcher</span>
    <h1>Fleet overview</h1>
    <p>Monitor requests, assign riders, and keep deliveries moving.</p>
  </div>
  <a href="/reflex/dispatcher/deliveries.php" class="btn btn-primary"><i class="bi bi-truck me-1"></i> All Deliveries</a>
</div>

<div class="row g-3">
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-ink"><i class="bi bi-box-seam"></i></div>
      <div class="stat-label">Total Deliveries</div>
      <div class="stat-value"><?= $stats['total'] ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-amber"><i class="bi bi-hourglass-split"></i></div>
      <div class="stat-label">Pending</div>
      <div class="stat-value"><?= $stats['pending'] ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-blue"><i class="bi bi-truck"></i></div>
      <div class="stat-label">In Transit</div>
      <div class="stat-value"><?= $stats['in_transit'] ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-green"><i class="bi bi-check-circle"></i></div>
      <div class="stat-label">Delivered</div>
      <div class="stat-value"><?= $stats['delivered'] ?></div>
    </div>
  </div>
</div>

<div class="row g-4 mt-1">
  <div class="col-lg-7">
    <div class="reflex-card">
      <div class="reflex-card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="section-title mb-0">Needs attention &mdash; unassigned</h2>
          <a href="/reflex/dispatcher/deliveries.php?status=PENDING" class="btn btn-sm btn-outline-primary">View all</a>
        </div>

        <?php if (empty($needsAttention)): ?>
          <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-check2-circle"></i></div>
            <h5>All caught up</h5>
            <p class="mb-0">Every delivery currently has a rider assigned.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="reflex-table">
              <thead><tr><th>ID</th><th>Retailer</th><th>Destination</th><th></th></tr></thead>
              <tbody>
              <?php foreach ($needsAttention as $d): ?>
                <tr>
                  <td class="delivery-id">#<?= $d['id'] ?></td>
                  <td><?= e($d['retailer_name']) ?></td>
                  <td><?= e($d['delivery_location']) ?></td>
                  <td><a href="/reflex/dispatcher/assign_rider.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-primary">Assign rider</a></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="reflex-card">
      <div class="reflex-card-body">
        <h2 class="section-title">Available riders</h2>
        <?php if (empty($riders)): ?>
          <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-bicycle"></i></div>
            <h5>No riders available</h5>
            <p class="mb-0">Add rider accounts to start assigning deliveries.</p>
          </div>
        <?php else: ?>
          <?php foreach ($riders as $r): ?>
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
              <div>
                <div class="fw-semibold"><?= e($r['name']) ?></div>
                <div class="text-muted small"><?= e($r['phone']) ?></div>
              </div>
              <span class="badge-status <?= (int) $r['active_jobs'] === 0 ? 'badge-delivered' : 'badge-in-transit' ?>">
                <?= (int) $r['active_jobs'] ?> active
              </span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
