<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('admin');

$pageTitle  = 'Admin Dashboard';
$activeMenu = 'dashboard';

// ── Stats ────────────────────────────────────────────────────────────────────
$stats = [];
$queries = [
    'total_students' => "SELECT COUNT(*) c FROM users WHERE role='student' AND status='approved'",
    'total_teachers' => "SELECT COUNT(*) c FROM users WHERE role='teacher' AND status='approved'",
    'pending_req'    => "SELECT COUNT(*) c FROM student_registration_requests WHERE status='pending'",
    'active_exams'   => "SELECT COUNT(*) c FROM exams WHERE status='scheduled'",
    'published'      => "SELECT COUNT(*) c FROM exams WHERE status='published'",
    'total_subjects' => "SELECT COUNT(*) c FROM subjects",
];
foreach ($queries as $key => $sql) {
    $r = $conn->query($sql);
    $stats[$key] = $r ? $r->fetch_assoc()['c'] : 0;
}

// Recent registration requests
$recentReqs = $conn->query(
    "SELECT * FROM student_registration_requests ORDER BY submitted_at DESC LIMIT 6"
);

// Recent exams
$recentExams = $conn->query(
    "SELECT * FROM exams ORDER BY exam_id DESC LIMIT 5"
);

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label'=>'Total Students',  'value'=>$stats['total_students'], 'icon'=>'bi-mortarboard',  'color'=>'blue'],
        ['label'=>'Total Teachers',  'value'=>$stats['total_teachers'], 'icon'=>'bi-person-badge', 'color'=>'green'],
        ['label'=>'Pending Requests','value'=>$stats['pending_req'],    'icon'=>'bi-person-check', 'color'=>'orange'],
        ['label'=>'Active Exams',    'value'=>$stats['active_exams'],   'icon'=>'bi-journal-text', 'color'=>'purple'],
        ['label'=>'Published Results','value'=>$stats['published'],     'icon'=>'bi-trophy',       'color'=>'teal'],
        ['label'=>'Total Subjects',  'value'=>$stats['total_subjects'], 'icon'=>'bi-book',         'color'=>'red'],
    ];
    foreach ($cards as $c): ?>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon <?= $c['color'] ?>"><i class="bi <?= $c['icon'] ?>"></i></div>
            <div>
                <div class="stat-value"><?= $c['value'] ?></div>
                <div class="stat-label"><?= $c['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <!-- Recent Registration Requests -->
    <div class="col-lg-7">
        <div class="data-card">
            <div class="data-card-header">
                <h6 class="data-card-title"><i class="bi bi-person-check me-2"></i>Recent Registration Requests</h6>
                <a href="approve-requests.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Email</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ($recentReqs && $recentReqs->num_rows > 0): ?>
                        <?php while ($row = $recentReqs->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['submitted_at'])) ?></td>
                            <td><span class="badge bg-<?= statusBadge($row['status']) ?>"><?= ucfirst($row['status']) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">No requests yet</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Exams -->
    <div class="col-lg-5">
        <div class="data-card">
            <div class="data-card-header">
                <h6 class="data-card-title"><i class="bi bi-journal-text me-2"></i>Recent Exams</h6>
                <a href="manage-exams.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Exam</th><th>Semester</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ($recentExams && $recentExams->num_rows > 0): ?>
                        <?php while ($row = $recentExams->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['exam_name']) ?></td>
                            <td>Sem <?= $row['semester'] ?></td>
                            <td><span class="badge bg-<?= statusBadge($row['status']) ?>"><?= ucfirst($row['status']) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No exams yet</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../include/layout_end.php'); ?>
