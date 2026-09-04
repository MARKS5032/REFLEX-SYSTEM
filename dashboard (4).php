<?php
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('rider');

$user = currentUser();
$pdo = getDbConnection();

$statsStmt = $pdo->prepare("
    SELECT
        SUM(status IN ('ASSIGNED','PICKED_UP','IN_TRANSIT')) AS active,
        SUM(status = 'ASSIGNED') AS assigned,
        SUM(status = 'IN_TRANSIT') AS in_transit,
        SUM(status = 'DELIVERED') AS delivered
    FROM deliveries
    WHERE rider_id = ?
");
$statsStmt->execute([$user['id']]);
$stats = $statsStmt->fetch();
$stats = array_map(fn($v) => (int) $v, $stats);

// RULE 2: a rider can only view deliveries assigned to them.
$activeStmt = $pdo->prepare("
    SELECT d.*, ret.name AS retailer_name
    FROM deliveries d
    JOIN users ret ON ret.id = d.retailer_id
    WHERE d.rider_id = ? AND d.status IN ('ASSIGNED','PICKED_UP','IN_TRANSIT')
    ORDER BY FIELD(d.status,'IN_TRANSIT','PICKED_UP','ASSIGNED'), d.updated_at ASC
");
$activeStmt->execute([$user['id']]);
$active = $activeStmt->fetchAll();

$pageTitle = 'Rider Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Rider</span>
    <h1>Welcome back, <?= e(explode(' ', $user['name'])[0]) ?></h1>
    <p>Here are your current jobs.</p>
  </div>
  <a href="/reflex/rider/deliveries.php" class="btn btn-primary"><i class="bi bi-bicycle me-1"></i> My Jobs</a>
</div>

<div class="row g-3">
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-blue"><i class="bi bi-lightning-charge"></i></div>
      <div class="stat-label">Active Jobs</div>
      <div class="stat-value"><?= $stats['active'] ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-amber"><i class="bi bi-clipboard-check"></i></div>
      <div class="stat-label">Newly Assigned</div>
      <div class="stat-value"><?= $stats['assigned'] ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-ink"><i class="bi bi-truck"></i></div>
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

<div class="reflex-card mt-4">
  <div class="reflex-card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="section-title mb-0">Your active jobs</h2>
      <a href="/reflex/rider/deliveries.php" class="btn btn-sm btn-outline-primary">View all</a>
    </div>

    <?php if (empty($active)): ?>
      <div class="empty-state">
        <div class="empty-icon"><i class="bi bi-cup-hot"></i></div>
        <h5>No active jobs right now</h5>
        <p class="mb-0">New assignments from your dispatcher will show up here.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="reflex-table">
          <thead><tr><th>ID</th><th>Customer</th><th>Pickup</th><th>Destination</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($active as $d): ?>
            <tr>
              <td class="delivery-id">#<?= $d['id'] ?></td>
              <td><?= e($d['customer_name']) ?></td>
              <td><?= e($d['pickup_location']) ?></td>
              <td><?= e($d['delivery_location']) ?></td>
              <td><span class="<?= statusBadgeClass($d['status']) ?>"><?= statusLabel($d['status']) ?></span></td>
              <td><a href="/reflex/rider/delivery_view.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-primary">Open</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
