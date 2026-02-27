<?php
/**
 * Calculate letter grade from marks and full mark.
 */
function calculateGrade(float $marks, float $fullMark = 100): string {
    if ($fullMark <= 0) return 'N/A';
    $pct = ($marks / $fullMark) * 100;
    if ($pct >= 90) return 'A+';
    if ($pct >= 80) return 'A';
    if ($pct >= 70) return 'B+';
    if ($pct >= 60) return 'B';
    if ($pct >= 50) return 'C';
    if ($pct >= 40) return 'D';
    return 'F';
}

/**
 * Compute GPA (0.0 – 4.0) from percentage.
 */
function computeGPA(float $totalObtained, float $totalFull): float {
    if ($totalFull <= 0) return 0.0;
    $pct = ($totalObtained / $totalFull) * 100;
    if ($pct >= 90) return 4.0;
    if ($pct >= 80) return 3.7;
    if ($pct >= 70) return 3.3;
    if ($pct >= 60) return 3.0;
    if ($pct >= 50) return 2.0;
    if ($pct >= 40) return 1.0;
    return 0.0;
}

/**
 * Return a Bootstrap badge class for a given status string.
 */
function statusBadge(string $status): string {
    return match ($status) {
        'approved', 'published', 'submitted', 'pass' => 'success',
        'pending',  'scheduled', 'draft'             => 'warning',
        'rejected', 'fail'                           => 'danger',
        'completed'                                  => 'info',
        default                                      => 'secondary',
    };
}

/**
 * Sanitize output to prevent XSS.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Flash message helpers using session.
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render flash message as Bootstrap alert (call in page body).
 */
function renderFlash(): void {
    $flash = getFlash();
    if ($flash) {
        echo '<div class="alert alert-' . e($flash['type']) . ' alert-dismissible fade show" role="alert">';
        echo e($flash['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}

/**
 * Redirect with a flash message.
 */
function redirectWith(string $url, string $type, string $message): void {
    setFlash($type, $message);
    header("Location: $url");
    exit();
}
?>
