<?php
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('retailer');

$user = currentUser();
$pdo = getDbConnection();

$errors = [];
$old = [
    'customer_name' => '', 'customer_phone' => '', 'pickup_location' => '',
    'delivery_location' => '', 'package_description' => '', 'notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        foreach ($old as $key => $_) {
            $old[$key] = trim($_POST[$key] ?? '');
        }

        // RULE 8 & 9: required fields + basic phone validation.
        if ($old['customer_name'] === '') $errors[] = 'Customer name is required.';
        if ($old['customer_phone'] === '') {
            $errors[] = 'Customer phone is required.';
        } elseif (!isValidPhone($old['customer_phone'])) {
            $errors[] = 'Please enter a valid customer phone number.';
        }
        if ($old['pickup_location'] === '') $errors[] = 'Pickup location is required.';
        if ($old['delivery_location'] === '') $errors[] = 'Delivery location is required.';
        if ($old['package_description'] === '') $errors[] = 'Package description is required.';

        if (!$errors) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("
                    INSERT INTO deliveries
                        (retailer_id, customer_name, customer_phone, pickup_location, delivery_location, package_description, notes, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING')
                ");
                $stmt->execute([
                    $user['id'], $old['customer_name'], $old['customer_phone'],
                    $old['pickup_location'], $old['delivery_location'],
                    $old['package_description'], $old['notes'] !== '' ? $old['notes'] : null,
                ]);
                $deliveryId = (int) $pdo->lastInsertId();

                $hist = $pdo->prepare("
                    INSERT INTO delivery_status_history (delivery_id, status, changed_by, note)
                    VALUES (?, 'PENDING', ?, 'Delivery created')
                ");
                $hist->execute([$deliveryId, $user['id']]);

                $pdo->commit();

                flash('success', 'Delivery #' . $deliveryId . ' created. A dispatcher will assign a rider shortly.');
                header('Location: /reflex/retailer/delivery_view.php?id=' . $deliveryId);
                exit;
            } catch (Throwable $e) {
                $pdo->rollBack();
                error_log('Create delivery error: ' . $e->getMessage());
                $errors[] = 'Something went wrong while saving your delivery. Please try again.';
            }
        }
    }
}

$pageTitle = 'New Delivery';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Retailer</span>
    <h1>Create a delivery</h1>
    <p>Fill in the customer and package details. A dispatcher will assign a rider.</p>
  </div>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger reflex-alert mb-3">
    <?php foreach ($errors as $err): ?><div><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($err) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="reflex-card">
  <div class="reflex-card-body">
    <form method="post" action="/reflex/retailer/create_delivery.php" class="reflex-form" novalidate>
      <?= csrfField() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label for="customer_name">Customer name</label>
          <input type="text" class="form-control" id="customer_name" name="customer_name" required value="<?= e($old['customer_name']) ?>" placeholder="e.g. John Otieno">
        </div>
        <div class="col-md-6">
          <label for="customer_phone">Customer phone</label>
          <input type="tel" class="form-control" id="customer_phone" name="customer_phone" required value="<?= e($old['customer_phone']) ?>" placeholder="0712345678">
        </div>
        <div class="col-md-6">
          <label for="pickup_location">Pickup location</label>
          <input type="text" class="form-control" id="pickup_location" name="pickup_location" required value="<?= e($old['pickup_location']) ?>" placeholder="e.g. Reflex Store, Kisumu">
        </div>
        <div class="col-md-6">
          <label for="delivery_location">Delivery location</label>
          <input type="text" class="form-control" id="delivery_location" name="delivery_location" required value="<?= e($old['delivery_location']) ?>" placeholder="e.g. Maseno University">
        </div>
        <div class="col-12">
          <label for="package_description">Package description</label>
          <input type="text" class="form-control" id="package_description" name="package_description" required value="<?= e($old['package_description']) ?>" placeholder="e.g. Electronics accessories">
        </div>
        <div class="col-12">
          <label for="notes">Delivery notes <span class="text-muted fw-normal">(optional)</span></label>
          <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="e.g. Call customer before arrival."><?= e($old['notes']) ?></textarea>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4">Create delivery</button>
        <a href="/reflex/retailer/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
