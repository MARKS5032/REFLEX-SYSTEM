<?php
/**
 * Expects (optionally) $pageTitle to be set before include.
 * Requires auth.php + functions.php to already be loaded (for currentUser(), e()).
 */
$pageTitle = $pageTitle ?? 'Dashboard';
$user = currentUser();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> · Reflex</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="/reflex/assets/css/style.css">
</head>
<body>
<?php require __DIR__ . '/navbar.php'; ?>
<div class="app-content">
<main class="app-main">
  <div class="container-fluid app-container">
    <?php require __DIR__ . '/alerts.php'; ?>
