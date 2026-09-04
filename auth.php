<?php
/**
 * REFLEX - Authentication & Authorization helpers
 *
 * Include this file at the top of every protected page:
 *   require_once __DIR__ . '/../auth/auth.php';
 *   requireRole('retailer');
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

const VALID_ROLES = ['retailer', 'dispatcher', 'rider'];

/** Is anyone currently logged in? */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

/** Returns the logged-in user's basic session data, or null. */
function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['role'],
    ];
}

/** Path (relative to site root) of the dashboard for a given role. */
function dashboardPathFor(string $role): string
{
    return match ($role) {
        'retailer'   => '/reflex/retailer/dashboard.php',
        'dispatcher' => '/reflex/dispatcher/dashboard.php',
        'rider'      => '/reflex/rider/dashboard.php',
        default      => '/reflex/login.php',
    };
}

/** Force login: redirect to login page if not authenticated. */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: /reflex/login.php');
        exit;
    }
}

/**
 * Force login AND restrict to one specific role.
 * Prevents retailers/riders/dispatchers from viewing each other's dashboards
 * (RULE: unauthorized users must be redirected or shown an appropriate error).
 */
function requireRole(string $role): void
{
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        header('Location: /reflex/auth/unauthorized.php');
        exit;
    }
}

/** Log a user in: sets session data. Caller must have already verified the password. */
function loginUser(array $user): void
{
    session_regenerate_id(true); // prevent session fixation
    $_SESSION['user_id']    = (int) $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role']       = $user['role'];
}

/** Log out and destroy the session. */
function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie('PHPSESSID', '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/* ------------------------------------------------------------------ */
/* CSRF protection                                                    */
/* ------------------------------------------------------------------ */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
