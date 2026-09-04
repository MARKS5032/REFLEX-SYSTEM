<?php
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('rider');

$user = currentUser();
$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /reflex/rider/deliveries.php');
    exit;
}

$id = filter_input(INPUT_POST, 'delivery_id', FILTER_VALIDATE_INT);
$targetStatus = $_POST['target_status'] ?? '';

if (!$id || !verifyCsrf()) {
    flash('error', 'Your request could not be processed. Please try again.');
    header('Location: /reflex/rider/deliveries.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM deliveries WHERE id = ?');
$stmt->execute([$id]);
$delivery = $stmt->fetch();

// RULE 2: rider can only update deliveries assigned to them.
if (!$delivery || (int) $delivery['rider_id'] !== (int) $user['id']) {
    flash('error', 'That delivery is not assigned to you.');
    header('Location: /reflex/rider/deliveries.php');
    exit;
}

// RULE 5 + status-flow enforcement: no skipping steps, no moving backwards,
// DELIVERED is terminal. isValidStatusTransition() guarantees single forward steps.
if (!isValidStatusTransition($delivery['status'], $targetStatus)) {
    flash('error', 'That status change is not allowed. Statuses must move forward one step at a time.');
    header('Location: /reflex/rider/delivery_view.php?id=' . $id);
    exit;
}

try {
    $pdo->beginTransaction();

    $update = $pdo->prepare('UPDATE deliveries SET status = ? WHERE id = ?');
    $update->execute([$targetStatus, $id]);

    $hist = $pdo->prepare("
        INSERT INTO delivery_status_history (delivery_id, status, changed_by, note)
        VALUES (?, ?, ?, ?)
    ");
    $hist->execute([$id, $targetStatus, $user['id'], 'Status updated by rider']);

    $pdo->commit();

    flash('success', 'Delivery #' . $id . ' marked as ' . statusLabel($targetStatus) . '.');
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Update status error: ' . $e->getMessage());
    flash('error', 'Something went wrong while updating the status. Please try again.');
}

header('Location: /reflex/rider/delivery_view.php?id=' . $id);
exit;
