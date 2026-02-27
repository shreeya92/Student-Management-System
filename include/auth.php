<?php
// ── Start session if not already started ─────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Require the user to be logged in.
 * Redirect to login page if no session exists.
 */
function requireLogin(string $loginPath = '../loginpage.php'): void {
    if (empty($_SESSION['user_id'])) {
        header("Location: $loginPath");
        exit();
    }
}

/**
 * Require the user to have a specific role.
 * Redirect to login if role doesn't match.
 */
function requireRole(string $role, string $loginPath = '../loginpage.php'): void {
    requireLogin($loginPath);
    if ($_SESSION['user_role'] !== $role) {
        header("Location: $loginPath");
        exit();
    }
}

/**
 * Check if a user is logged in (returns bool).
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Return the current user's role.
 */
function currentRole(): string {
    return $_SESSION['user_role'] ?? '';
}
?>
