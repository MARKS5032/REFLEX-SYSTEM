<?php
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('retailer');

$user = currentUser();
$pdo = getDbConnection();

// Stats scoped to this retailer only (RULE 1).
$statsStmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'PENDING') AS pending,
        SUM(status IN ('ASSIGNED')) AS assigned,
        SUM(status IN ('PICKED_UP','IN_TRANSIT')) AS in_transit,
        SUM(status = 'DELIVERED') AS delivered
    FROM deliveries
    WHERE retailer_id = ?
");
$statsStmt->execute([$user['id']]);
$stats = $statsStmt->fetch();
$stats = array_map(fn($v) => (int) $v, $stats);

$recentStmt = $pdo->prepare("
    SELECT d.*, u.name AS rider_name
    FROM deliveries d
    LEFT JOIN users u ON u.id = d.rider_id
    WHERE d.retailer_id = ?
    ORDER BY d.created_at DESC
    LIMIT 5
");
$recentStmt->execute([$user['id']]);
$recent = $recentStmt->fetchAll();

$pageTitle = 'Retailer Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Retailer</span>
    <h1>Welcome back, <?= e(explode(' ', $user['name'])[0]) ?></h1>
    <p>Here's what's happening with your deliveries today.</p>
  </div>
  <a href="/reflex/retailer/create_delivery.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> New Delivery</a>
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

<div class="reflex-card mt-4">
  <div class="reflex-card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="section-title mb-0">Recent deliveries</h2>
      <a href="/reflex/retailer/deliveries.php" class="btn btn-sm btn-outline-primary">View all</a>
    </div>

    <?php if (empty($recent)): ?>
      <div class="empty-state">
        <div class="empty-icon"><i class="bi bi-inbox"></i></div>
        <h5>No deliveries yet</h5>
        <p class="mb-3">Create your first delivery to see it tracked here.</p>
        <a href="/reflex/retailer/create_delivery.php" class="btn btn-primary btn-sm">Create a delivery</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="reflex-table">
          <thead>
            <tr>
              <th>ID</th><th>Customer</th><th>Destination</th><th>Rider</th><th>Status</th><th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($recent as $d): ?>
            <tr>
              <td class="delivery-id">#<?= $d['id'] ?></td>
              <td><?= e($d['customer_name']) ?></td>
              <td><?= e($d['delivery_location']) ?></td>
              <td><?= $d['rider_name'] ? e($d['rider_name']) : '<span class="text-muted">Unassigned</span>' ?></td>
              <td><span class="<?= statusBadgeClass($d['status']) ?>"><?= statusLabel($d['status']) ?></span></td>
              <td><a href="/reflex/retailer/delivery_view.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
