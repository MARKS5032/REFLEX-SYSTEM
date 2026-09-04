<?php
/**
 * REFLEX - Shared helper functions
 */

/** Shortcut for htmlspecialchars() output escaping. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/* ------------------------------------------------------------------ */
/* Flash messages (success / error banners that survive one redirect) */
/* ------------------------------------------------------------------ */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/* ------------------------------------------------------------------ */
/* Validation                                                          */
/* ------------------------------------------------------------------ */

function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Basic Kenyan-friendly phone validation: digits, optional +, 7-15 chars. */
function isValidPhone(string $phone): bool
{
    return (bool) preg_match('/^\+?[0-9]{7,15}$/', trim($phone));
}

/* ------------------------------------------------------------------ */
/* Delivery status flow                                                */
/* ------------------------------------------------------------------ */

const STATUS_FLOW = ['PENDING', 'ASSIGNED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'];

/**
 * Returns true if moving from $from to $to is a legal, forward, single-step
 * transition. Enforces RULE: no skipping steps, no moving backwards, and
 * DELIVERED can never move to any other status (RULE 5).
 */
function isValidStatusTransition(string $from, string $to): bool
{
    $fromIndex = array_search($from, STATUS_FLOW, true);
    $toIndex   = array_search($to, STATUS_FLOW, true);

    if ($fromIndex === false || $toIndex === false) {
        return false;
    }
    if ($from === 'DELIVERED') {
        return false; // terminal state, cannot change further
    }
    // Only allow moving exactly one step forward.
    return $toIndex === $fromIndex + 1;
}

/** Human-readable label for a status value. */
function statusLabel(string $status): string
{
    return match ($status) {
        'PENDING'    => 'Pending',
        'ASSIGNED'   => 'Assigned',
        'PICKED_UP'  => 'Picked Up',
        'IN_TRANSIT' => 'In Transit',
        'DELIVERED'  => 'Delivered',
        default      => $status,
    };
}

/** Bootstrap-based badge class for a status value. */
function statusBadgeClass(string $status): string
{
    return match ($status) {
        'PENDING'    => 'badge-status badge-pending',
        'ASSIGNED'   => 'badge-status badge-assigned',
        'PICKED_UP'  => 'badge-status badge-picked-up',
        'IN_TRANSIT' => 'badge-status badge-in-transit',
        'DELIVERED'  => 'badge-status badge-delivered',
        default      => 'badge-status',
    };
}

/** The next status in the flow, or null if already DELIVERED. */
function nextStatus(string $status): ?string
{
    $index = array_search($status, STATUS_FLOW, true);
    if ($index === false || $index === count(STATUS_FLOW) - 1) {
        return null;
    }
    return STATUS_FLOW[$index + 1];
}
