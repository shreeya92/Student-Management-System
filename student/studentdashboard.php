<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('student');

$pageTitle  = 'Student Dashboard';
$activeMenu = 'dashboard';
$userId     = (int)$_SESSION['user_id'];

$student = $conn->query(
    "SELECT st.*, u.full_name, u.email FROM students st
     JOIN users u ON st.user_id=u.user_id WHERE st.user_id=$userId LIMIT 1"
)->fetch_assoc();

$sid = $student ? $student['student_id'] : 0;
$sem = $student ? $student['semester']   : 0;

$admitCards = (int)$conn->query(
    "SELECT COUNT(*) c FROM admit_cards WHERE student_id=$sid AND eligibility_status=1"
)->fetch_assoc()['c'];

$results = (int)$conn->query(
    "SELECT COUNT(*) c FROM results r JOIN exams e ON r.exam_id=e.exam_id
     WHERE r.student_id=$sid AND e.status='published'"
)->fetch_assoc()['c'];

// Latest result
$latestResult = $conn->query(
    "SELECT r.*, e.exam_name, e.semester FROM results r
     JOIN exams e ON r.exam_id=e.exam_id
     WHERE r.student_id=$sid AND e.status='published'
     ORDER BY r.result_id DESC LIMIT 1"
)->fetch_assoc();

// Upcoming exams (for student's semester)
$upcoming = $conn->query(
    "SELECT DISTINCT e.exam_name, e.start_date, e.status
     FROM exams e
     JOIN exam_routine er ON er.exam_id=e.exam_id
     JOIN subjects s ON s.subject_id=er.subject_id AND s.semester=$sem
     WHERE e.start_date >= CURDATE()
     ORDER BY e.start_date LIMIT 5"
);

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<?php if ($student): ?>
<!-- Welcome banner -->
<div class="alert alert-primary d-flex align-items-center gap-3 mb-4" style="border-radius:12px;">
    <div style="font-size:36px;">🎓</div>
    <div>
        <strong class="fs-5"><?=htmlspecialchars($student['full_name'])?></strong><br>
        <span class="small"><?=htmlspecialchars($student['program'])?> — Semester <?=$sem?> — Roll: <?=htmlspecialchars($student['roll_number'])?></span>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-card-text"></i></div>
            <div><div class="stat-value"><?=$admitCards?></div><div class="stat-label">Admit Cards</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-trophy"></i></div>
            <div><div class="stat-value"><?=$results?></div><div class="stat-label">Results Available</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-calendar3"></i></div>
            <div><div class="stat-value"><?=$upcoming ? $upcoming->num_rows : 0?></div><div class="stat-label">Upcoming Exams</div></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Latest Result -->
    <div class="col-md-5">
        <div class="data-card">
            <div class="data-card-header">
                <h6 class="data-card-title"><i class="bi bi-trophy me-2"></i>Latest Result</h6>
                <a href="view-result.php" class="btn btn-sm btn-outline-primary">All Results</a>
            </div>
            <div class="data-card-body">
                <?php if ($latestResult): ?>
                <h5><?=htmlspecialchars($latestResult['exam_name'])?></h5>
                <p class="text-muted mb-3">Semester <?=$latestResult['semester']?></p>
                <div class="d-flex gap-4">
                    <div>
                        <div class="stat-value"><?=number_format($latestResult['total_marks'], 1)?></div>
                        <div class="stat-label">Total Marks</div>
                    </div>
                    <div>
                        <div class="stat-value"><?=number_format($latestResult['gpa'], 2)?></div>
                        <div class="stat-label">GPA</div>
                    </div>
                    <div>
                        <span class="badge bg-<?=$latestResult['result_status']==='pass'?'success':'danger'?> fs-6 px-3 py-2">
                            <?=strtoupper($latestResult['result_status'])?>
                        </span>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-muted">No published results yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Upcoming Exams -->
    <div class="col-md-7">
        <div class="data-card">
            <div class="data-card-header">
                <h6 class="data-card-title"><i class="bi bi-calendar3 me-2"></i>Upcoming Exams</h6>
                <a href="view-routine.php" class="btn btn-sm btn-outline-primary">Full Routine</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Exam</th><th>Start Date</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php
                    $upcoming = $conn->query(
                        "SELECT DISTINCT e.exam_name, e.start_date, e.status
                         FROM exams e JOIN exam_routine er ON er.exam_id=e.exam_id
                         JOIN subjects s ON s.subject_id=er.subject_id AND s.semester=$sem
                         WHERE e.start_date >= CURDATE() ORDER BY e.start_date LIMIT 5"
                    );
                    if ($upcoming && $upcoming->num_rows > 0): while ($row=$upcoming->fetch_assoc()): ?>
                    <tr>
                        <td><?=htmlspecialchars($row['exam_name'])?></td>
                        <td><?=date('M d, Y',strtotime($row['start_date']))?></td>
                        <td><span class="badge bg-<?=statusBadge($row['status'])?>"><?=ucfirst($row['status'])?></span></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="3" class="text-center text-muted py-3">No upcoming exams.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning">Student profile not found. Please contact admin.</div>
<?php endif; ?>

<?php include('../include/layout_end.php'); ?>
