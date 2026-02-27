<?php
// include/layout.php
// Usage: include('../include/layout.php');
// Variables expected: $pageTitle (string), $activeMenu (string), $role (string)
$role      = $_SESSION['user_role'] ?? '';
$userName  = $_SESSION['user_name']  ?? 'User';
$basePath  = ($role === 'admin' || $role === 'teacher' || $role === 'student') ? '../' : '';

// Build nav items per role
$navItems = [];
if ($role === 'admin') {
    $navItems = [
        'section' => 'Main',
        'links'   => [
            ['href' => 'dashboardadmin.php',   'icon' => 'bi-speedometer2',    'label' => 'Dashboard',          'key' => 'dashboard'],
            ['href' => 'approve-requests.php', 'icon' => 'bi-person-check',    'label' => 'Registration Requests','key' => 'requests'],
            ['href' => 'manage-users.php',     'icon' => 'bi-people',          'label' => 'Manage Users',        'key' => 'users'],
            ['href' => 'manage-students.php',  'icon' => 'bi-mortarboard',     'label' => 'Students',            'key' => 'students'],
            ['href' => 'manage-teachers.php',  'icon' => 'bi-person-badge',    'label' => 'Teachers',            'key' => 'teachers'],
            ['href' => 'manage-exams.php',     'icon' => 'bi-journal-text',    'label' => 'Exams',               'key' => 'exams'],
            ['href' => 'manage-subjects.php',  'icon' => 'bi-book',            'label' => 'Subjects',            'key' => 'subjects'],
            ['href' => 'manage-routine.php',   'icon' => 'bi-calendar3',       'label' => 'Exam Routine',        'key' => 'routine'],
            ['href' => 'issue-admit-cards.php','icon' => 'bi-card-text',       'label' => 'Admit Cards',         'key' => 'admitcards'],
            ['href' => 'publish-results.php',  'icon' => 'bi-trophy',          'label' => 'Results',             'key' => 'results'],
        ]
    ];
} elseif ($role === 'teacher') {
    $navItems = [
        'section' => 'Main',
        'links'   => [
            ['href' => 'teacherdashboard.php', 'icon' => 'bi-speedometer2',  'label' => 'Dashboard',     'key' => 'dashboard'],
            ['href' => 'enter-marks.php',      'icon' => 'bi-pencil-square', 'label' => 'Enter Marks',   'key' => 'marks'],
            ['href' => 'view-students.php',    'icon' => 'bi-people',        'label' => 'My Students',   'key' => 'students'],
            ['href' => 'view-routine.php',     'icon' => 'bi-calendar3',     'label' => 'Exam Routine',  'key' => 'routine'],
        ]
    ];
} elseif ($role === 'student') {
    $navItems = [
        'section' => 'Main',
        'links'   => [
            ['href' => 'studentdashboard.php',  'icon' => 'bi-speedometer2', 'label' => 'Dashboard',     'key' => 'dashboard'],
            ['href' => 'view-admit-card.php',   'icon' => 'bi-card-text',    'label' => 'Admit Card',    'key' => 'admitcard'],
            ['href' => 'view-result.php',       'icon' => 'bi-trophy',       'label' => 'My Results',    'key' => 'results'],
            ['href' => 'view-routine.php',      'icon' => 'bi-calendar3',    'label' => 'Exam Routine',  'key' => 'routine'],
            ['href' => 'profile.php',           'icon' => 'bi-person-circle','label' => 'My Profile',    'key' => 'profile'],
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'SEMS') ?> — SEMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= $basePath ?>assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- ── Sidebar ──────────────────────────────────────────────────────────── -->
<aside class="sidebar">
    <a class="sidebar-brand d-block" href="#">
        <div class="brand-title">SEMS</div>
        <div class="brand-sub">Exam Management</div>
    </a>

    <nav class="sidebar-nav">
        <?php if (!empty($navItems['links'])): ?>
        <div class="nav-section-label"><?= $navItems['section'] ?></div>
        <?php foreach ($navItems['links'] as $link): ?>
            <a href="<?= $link['href'] ?>"
               class="nav-link <?= ($activeMenu ?? '') === $link['key'] ? 'active' : '' ?>">
                <i class="bi <?= $link['icon'] ?>"></i>
                <?= $link['label'] ?>
            </a>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="nav-section-label mt-2">Account</div>
        <a href="<?= $basePath ?>logout.php" class="nav-link">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center"
                 style="width:34px;height:34px;flex-shrink:0">
                <i class="bi bi-person-fill text-white"></i>
            </div>
            <div>
                <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                <div class="user-role"><?= ucfirst($role) ?></div>
            </div>
        </div>
    </div>
</aside>

<!-- ── Main Content ─────────────────────────────────────────────────────── -->
<div class="main-content">
    <div class="topbar">
        <span class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></span>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary"><?= ucfirst($role) ?></span>
            <a href="<?= $basePath ?>logout.php" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </div>
    <div class="page-body">
