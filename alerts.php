<?php $flashes = getFlashes(); ?>
<?php if (!empty($flashes)): ?>
  <div class="flash-stack mt-3">
    <?php foreach ($flashes as $f): ?>
      <?php $cls = $f['type'] === 'error' ? 'danger' : e($f['type']); ?>
      <div class="alert alert-<?= $cls ?> alert-dismissible fade show reflex-alert" role="alert">
        <?php if ($f['type'] === 'success'): ?><i class="bi bi-check-circle-fill me-2"></i><?php endif; ?>
        <?php if ($f['type'] === 'error'): ?><i class="bi bi-exclamation-triangle-fill me-2"></i><?php endif; ?>
        <?= e($f['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
