<?php
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('dispatcher');

$pdo = getDbConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    flash('error', 'Invalid delivery reference.');
    header('Location: /reflex/dispatcher/deliveries.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT d.*, ret.name AS retailer_name, ret.phone AS retailer_phone,
           r.name AS rider_name, r.phone AS rider_phone
    FROM deliveries d
    JOIN users ret ON ret.id = d.retailer_id
    LEFT JOIN users r ON r.id = d.rider_id
    WHERE d.id = ?
");
$stmt->execute([$id]);
$delivery = $stmt->fetch();

// Dispatchers can view any delivery (fleet-wide oversight role).
if (!$delivery) {
    flash('error', 'Delivery not found.');
    header('Location: /reflex/dispatcher/deliveries.php');
    exit;
}

$histStmt = $pdo->prepare("
    SELECT h.*, u.name AS changed_by_name, u.role AS changed_by_role
    FROM delivery_status_history h
    JOIN users u ON u.id = h.changed_by
    WHERE h.delivery_id = ?
    ORDER BY h.created_at ASC
");
$histStmt->execute([$id]);
$history = $histStmt->fetchAll();

$currentIndex = array_search($delivery['status'], STATUS_FLOW, true);
$canAssign = in_array($delivery['status'], ['PENDING', 'ASSIGNED'], true);

$pageTitle = 'Delivery #' . $delivery['id'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Dispatcher</span>
    <h1>Delivery <span class="delivery-id">#<?= $delivery['id'] ?></span></h1>
    <p>Created <?= date('d M Y, H:i', strtotime($delivery['created_at'])) ?> by <?= e($delivery['retailer_name']) ?></p>
  </div>
  <div class="d-flex gap-2">
    <?php if ($canAssign): ?>
      <a href="/reflex/dispatcher/assign_rider.php?id=<?= $delivery['id'] ?>" class="btn btn-primary"><?= $delivery['rider_name'] ? 'Reassign rider' : 'Assign rider' ?></a>
    <?php endif; ?>
    <a href="/reflex/dispatcher/deliveries.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
  </div>
</div>

<div class="reflex-card mb-4">
  <div class="reflex-card-body">
    <h2 class="section-title">Progress</h2>
    <ul class="route-tracker">
      <?php foreach (STATUS_FLOW as $i => $s): ?>
        <?php $cls = $i < $currentIndex ? 'is-complete' : ($i === $currentIndex ? 'is-current' : ''); ?>
        <li class="<?= $cls ?>">
          <span class="waypoint"><?= $i < $currentIndex ? '<i class="bi bi-check-lg"></i>' : $i + 1 ?></span>
          <span class="waypoint-label"><?= statusLabel($s) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="reflex-card mb-4">
      <div class="reflex-card-body">
        <h2 class="section-title">Delivery details</h2>
        <div class="row g-3">
          <div class="col-sm-6"><div class="text-muted small">Status</div><span class="<?= statusBadgeClass($delivery['status']) ?>"><?= statusLabel($delivery['status']) ?></span></div>
          <div class="col-sm-6"><div class="text-muted small">Package</div><div><?= e($delivery['package_description']) ?></div></div>
          <div class="col-sm-6"><div class="text-muted small">Pickup location</div><div><?= e($delivery['pickup_location']) ?></div></div>
          <div class="col-sm-6"><div class="text-muted small">Delivery location</div><div><?= e($delivery['delivery_location']) ?></div></div>
          <?php if ($delivery['notes']): ?>
          <div class="col-12"><div class="text-muted small">Notes</div><div><?= nl2br(e($delivery['notes'])) ?></div></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="reflex-card">
      <div class="reflex-card-body">
        <h2 class="section-title">Status history</h2>
        <?php foreach (array_reverse($history) as $h): ?>
          <div class="d-flex justify-content-between border-bottom py-2">
            <div>
              <span class="<?= statusBadgeClass($h['status']) ?>"><?= statusLabel($h['status']) ?></span>
              <div class="text-muted small mt-1"><?= $h['note'] ? e($h['note']) : '' ?></div>
            </div>
            <div class="text-end">
              <div class="small fw-semibold"><?= e($h['changed_by_name']) ?> <span class="text-muted">(<?= e($h['changed_by_role']) ?>)</span></div>
              <div class="text-muted small"><?= date('d M, H:i', strtotime($h['created_at'])) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="reflex-card mb-4">
      <div class="reflex-card-body">
        <h2 class="section-title">Retailer</h2>
        <div class="text-muted small">Name</div><div class="mb-2"><?= e($delivery['retailer_name']) ?></div>
        <div class="text-muted small">Phone</div><div><?= e($delivery['retailer_phone']) ?></div>
      </div>
    </div>

    <div class="reflex-card mb-4">
      <div class="reflex-card-body">
        <h2 class="section-title">Customer</h2>
        <div class="text-muted small">Name</div><div class="mb-2"><?= e($delivery['customer_name']) ?></div>
        <div class="text-muted small">Phone</div><div><?= e($delivery['customer_phone']) ?></div>
      </div>
    </div>

    <div class="reflex-card">
      <div class="reflex-card-body">
        <h2 class="section-title">Assigned rider</h2>
        <?php if ($delivery['rider_name']): ?>
          <div class="text-muted small">Name</div><div class="mb-2"><?= e($delivery['rider_name']) ?></div>
          <div class="text-muted small">Phone</div><div><?= e($delivery['rider_phone']) ?></div>
        <?php else: ?>
          <div class="empty-state py-3">
            <div class="empty-icon"><i class="bi bi-person-x"></i></div>
            <h5 style="font-size:0.95rem;">Not yet assigned</h5>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
