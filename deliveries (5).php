<?php
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('rider');

$user = currentUser();
$pdo = getDbConnection();

$statusFilter = $_GET['status'] ?? '';
$validFilters = array_merge([''], STATUS_FLOW);
if (!in_array($statusFilter, $validFilters, true)) {
    $statusFilter = '';
}

// RULE 2: a rider can only view deliveries assigned to them.
$sql = "SELECT d.*, ret.name AS retailer_name FROM deliveries d
        JOIN users ret ON ret.id = d.retailer_id
        WHERE d.rider_id = ?";
$params = [$user['id']];

if ($statusFilter !== '') {
    $sql .= " AND d.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY d.updated_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$deliveries = $stmt->fetchAll();

$pageTitle = 'My Jobs';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Rider</span>
    <h1>My jobs</h1>
    <p>Every delivery ever assigned to you.</p>
  </div>
</div>

<div class="reflex-card">
  <div class="reflex-card-body">
    <div class="d-flex flex-wrap gap-2 mb-3">
      <a href="?status=" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
      <?php foreach (STATUS_FLOW as $s): ?>
        <?php if ($s === 'PENDING') continue; // riders never see PENDING (unassigned) jobs of their own ?>
        <a href="?status=<?= $s ?>" class="btn btn-sm <?= $statusFilter === $s ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= statusLabel($s) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($deliveries)): ?>
      <div class="empty-state">
        <div class="empty-icon"><i class="bi bi-inbox"></i></div>
        <h5>No jobs found</h5>
        <p class="mb-0">Try a different filter, or check back after your dispatcher assigns you a job.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="reflex-table">
          <thead><tr><th>ID</th><th>Retailer</th><th>Customer</th><th>Destination</th><th>Status</th><th>Updated</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($deliveries as $d): ?>
            <tr>
              <td class="delivery-id">#<?= $d['id'] ?></td>
              <td><?= e($d['retailer_name']) ?></td>
              <td><?= e($d['customer_name']) ?></td>
              <td><?= e($d['delivery_location']) ?></td>
              <td><span class="<?= statusBadgeClass($d['status']) ?>"><?= statusLabel($d['status']) ?></span></td>
              <td class="text-muted"><?= date('d M, H:i', strtotime($d['updated_at'])) ?></td>
              <td><a href="/reflex/rider/delivery_view.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary">Open</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
