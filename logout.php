<?php
require_once __DIR__ . '/auth/auth.php';
logoutUser();
header('Location: /reflex/login.php');
exit;
