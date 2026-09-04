<?php
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('dispatcher');

$pdo = getDbConnection();

$statusFilter = $_GET['status'] ?? '';
$validFilters = array_merge([''], STATUS_FLOW);
if (!in_array($statusFilter, $validFilters, true)) {
    $statusFilter = '';
}

$sql = "SELECT d.*, ret.name AS retailer_name, r.name AS rider_name
        FROM deliveries d
        JOIN users ret ON ret.id = d.retailer_id
        LEFT JOIN users r ON r.id = d.rider_id
        WHERE 1=1";
$params = [];

if ($statusFilter !== '') {
    $sql .= " AND d.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$deliveries = $stmt->fetchAll();

$pageTitle = 'All Deliveries';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Dispatcher</span>
    <h1>All deliveries</h1>
    <p>Every delivery in the system, across all retailers.</p>
  </div>
</div>

<div class="reflex-card">
  <div class="reflex-card-body">
    <div class="d-flex flex-wrap gap-2 mb-3">
      <a href="?status=" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
      <?php foreach (STATUS_FLOW as $s): ?>
        <a href="?status=<?= $s ?>" class="btn btn-sm <?= $statusFilter === $s ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= statusLabel($s) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($deliveries)): ?>
      <div class="empty-state">
        <div class="empty-icon"><i class="bi bi-inbox"></i></div>
        <h5>No deliveries found</h5>
        <p class="mb-0">Try a different filter.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="reflex-table">
          <thead>
            <tr><th>ID</th><th>Retailer</th><th>Customer</th><th>Destination</th><th>Rider</th><th>Status</th><th>Created</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach ($deliveries as $d): ?>
            <tr>
              <td class="delivery-id">#<?= $d['id'] ?></td>
              <td><?= e($d['retailer_name']) ?></td>
              <td><?= e($d['customer_name']) ?></td>
              <td><?= e($d['delivery_location']) ?></td>
              <td><?= $d['rider_name'] ? e($d['rider_name']) : '<span class="text-muted">Unassigned</span>' ?></td>
              <td><span class="<?= statusBadgeClass($d['status']) ?>"><?= statusLabel($d['status']) ?></span></td>
              <td class="text-muted"><?= date('d M, H:i', strtotime($d['created_at'])) ?></td>
              <td class="d-flex gap-1">
                <a href="/reflex/dispatcher/delivery_view.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                <?php if (in_array($d['status'], ['PENDING', 'ASSIGNED'], true)): ?>
                  <a href="/reflex/dispatcher/assign_rider.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-primary"><?= $d['rider_name'] ? 'Reassign' : 'Assign' ?></a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
