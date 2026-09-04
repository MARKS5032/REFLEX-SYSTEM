<?php
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('dispatcher');

$user = currentUser();
$pdo = getDbConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'delivery_id', FILTER_VALIDATE_INT);
if (!$id) {
    flash('error', 'Invalid delivery reference.');
    header('Location: /reflex/dispatcher/deliveries.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT d.*, ret.name AS retailer_name, r.name AS rider_name
    FROM deliveries d
    JOIN users ret ON ret.id = d.retailer_id
    LEFT JOIN users r ON r.id = d.rider_id
    WHERE d.id = ?
");
$stmt->execute([$id]);
$delivery = $stmt->fetch();

if (!$delivery) {
    flash('error', 'Delivery not found.');
    header('Location: /reflex/dispatcher/deliveries.php');
    exit;
}

// Only PENDING/ASSIGNED deliveries can be (re)assigned — once a rider has
// physically picked the package up, reassigning would be unsafe/confusing.
$canAssign = in_array($delivery['status'], ['PENDING', 'ASSIGNED'], true);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (!$canAssign) {
        $errors[] = 'This delivery can no longer be reassigned because it has already been picked up.';
    } else {
        $riderId = filter_input(INPUT_POST, 'rider_id', FILTER_VALIDATE_INT);

        if (!$riderId) {
            $errors[] = 'Please select a rider.';
        } else {
            // RULE 3 & RULE 7: only a dispatcher can assign, and only to a valid, active rider.
            $riderCheck = $pdo->prepare("SELECT id, name FROM users WHERE id = ? AND role = 'rider' AND is_active = 1");
            $riderCheck->execute([$riderId]);
            $rider = $riderCheck->fetch();

            if (!$rider) {
                $errors[] = 'That user is not a valid, active rider.';
            } else {
                try {
                    $pdo->beginTransaction();

                    $wasPending = $delivery['status'] === 'PENDING';
                    $newStatus = $wasPending ? 'ASSIGNED' : $delivery['status'];

                    $update = $pdo->prepare('UPDATE deliveries SET rider_id = ?, status = ? WHERE id = ?');
                    $update->execute([$rider['id'], $newStatus, $id]);

                    $note = $wasPending
                        ? 'Assigned to ' . $rider['name']
                        : 'Reassigned to ' . $rider['name'];

                    $hist = $pdo->prepare("
                        INSERT INTO delivery_status_history (delivery_id, status, changed_by, note)
                        VALUES (?, ?, ?, ?)
                    ");
                    $hist->execute([$id, $newStatus, $user['id'], $note]);

                    $pdo->commit();

                    flash('success', 'Delivery #' . $id . ' assigned to ' . $rider['name'] . '.');
                    header('Location: /reflex/dispatcher/delivery_view.php?id=' . $id);
                    exit;
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    error_log('Assign rider error: ' . $e->getMessage());
                    $errors[] = 'Something went wrong while assigning the rider. Please try again.';
                }
            }
        }
    }
}

// Available riders: active riders, sorted by current workload.
$ridersStmt = $pdo->query("
    SELECT u.id, u.name, u.phone,
        SUM(d.status IN ('ASSIGNED','PICKED_UP','IN_TRANSIT')) AS active_jobs
    FROM users u
    LEFT JOIN deliveries d ON d.rider_id = u.id
    WHERE u.role = 'rider' AND u.is_active = 1
    GROUP BY u.id, u.name, u.phone
    ORDER BY active_jobs ASC, u.name ASC
");
$riders = $ridersStmt->fetchAll();

$pageTitle = 'Assign Rider · Delivery #' . $delivery['id'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Dispatcher</span>
    <h1><?= $delivery['rider_name'] ? 'Reassign rider' : 'Assign rider' ?> &mdash; <span class="delivery-id">#<?= $delivery['id'] ?></span></h1>
    <p>From <?= e($delivery['retailer_name']) ?> to <?= e($delivery['delivery_location']) ?></p>
  </div>
  <a href="/reflex/dispatcher/delivery_view.php?id=<?= $delivery['id'] ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger reflex-alert mb-3">
    <?php foreach ($errors as $err): ?><div><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($err) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!$canAssign): ?>
  <div class="alert alert-warning reflex-alert">
    <i class="bi bi-info-circle-fill me-1"></i>
    This delivery is already <strong><?= strtolower(statusLabel($delivery['status'])) ?></strong> and can no longer be reassigned.
  </div>
<?php endif; ?>

<div class="reflex-card">
  <div class="reflex-card-body">
    <?php if (empty($riders)): ?>
      <!-- Edge case: no rider available -->
      <div class="empty-state">
        <div class="empty-icon"><i class="bi bi-bicycle"></i></div>
        <h5>No riders available</h5>
        <p class="mb-0">There are currently no active riders to assign. Add a rider account, then come back here.</p>
      </div>
    <?php elseif ($canAssign): ?>
      <form method="post" action="/reflex/dispatcher/assign_rider.php" class="reflex-form" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="delivery_id" value="<?= $delivery['id'] ?>">
        <label class="mb-2">Choose a rider</label>
        <div class="list-group mb-3">
          <?php foreach ($riders as $i => $r): ?>
            <label class="list-group-item d-flex justify-content-between align-items-center">
              <span>
                <input class="form-check-input me-2" type="radio" name="rider_id" value="<?= $r['id'] ?>" <?= $i === 0 ? 'checked' : '' ?> required>
                <strong><?= e($r['name']) ?></strong>
                <span class="text-muted small ms-1"><?= e($r['phone']) ?></span>
              </span>
              <span class="badge-status <?= (int) $r['active_jobs'] === 0 ? 'badge-delivered' : 'badge-in-transit' ?>"><?= (int) $r['active_jobs'] ?> active</span>
            </label>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-primary px-4">Confirm assignment</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
