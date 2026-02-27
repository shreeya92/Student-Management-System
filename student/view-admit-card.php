<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('student');

$pageTitle  = 'My Admit Cards';
$activeMenu = 'admitcard';
$userId     = (int)$_SESSION['user_id'];

$student = $conn->query(
    "SELECT st.*, u.full_name, u.email FROM students st
     JOIN users u ON st.user_id=u.user_id WHERE st.user_id=$userId LIMIT 1"
)->fetch_assoc();

$sid = $student ? $student['student_id'] : 0;

$admitCards = $conn->query(
    "SELECT ac.*, e.exam_name, e.start_date, e.end_date, e.semester, e.year
     FROM admit_cards ac
     JOIN exams e ON ac.exam_id=e.exam_id
     WHERE ac.student_id=$sid ORDER BY ac.admit_id DESC"
);

// Exam routine for a selected exam
$viewExamId = (int)($_GET['view'] ?? 0);
$routine    = [];
if ($viewExamId) {
    $r = $conn->query(
        "SELECT er.exam_date, er.start_time, er.end_time, s.subject_name, s.subject_code
         FROM exam_routine er JOIN subjects s ON er.subject_id=s.subject_id
         WHERE er.exam_id=$viewExamId ORDER BY er.exam_date, er.start_time"
    );
    while ($row = $r->fetch_assoc()) $routine[] = $row;
}

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<?php if ($student): ?>

<?php if ($viewExamId && !empty($routine)): ?>
<!-- ── Printable Admit Card ───────────────────────────────────────────────── -->
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="view-admit-card.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
        <i class="bi bi-printer me-1"></i>Print Admit Card
    </button>
</div>

<?php
$examInfo = $conn->query("SELECT * FROM exams WHERE exam_id=$viewExamId LIMIT 1")->fetch_assoc();
$acInfo   = $conn->query("SELECT * FROM admit_cards WHERE student_id=$sid AND exam_id=$viewExamId LIMIT 1")->fetch_assoc();
?>

<div class="data-card print-section" style="max-width:720px;margin:auto;">
    <div class="p-4 border-bottom" style="background:#1F4E79;color:#fff;border-radius:12px 12px 0 0;">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4 class="mb-1 fw-bold">SEMS — Exam Admit Card</h4>
                <p class="mb-0 opacity-75 small">Student Exam Management System</p>
            </div>
            <div class="text-end">
                <div class="fw-bold"><?=date('M d, Y',strtotime($acInfo['issue_date'] ?? 'now'))?></div>
                <div class="small opacity-75">Issue Date</div>
            </div>
        </div>
    </div>
    <div class="p-4">
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted fw-bold" style="width:120px">Name:</td><td><?=htmlspecialchars($student['full_name'])?></td></tr>
                    <tr><td class="text-muted fw-bold">Roll No:</td><td><strong><?=htmlspecialchars($student['roll_number'])?></strong></td></tr>
                    <tr><td class="text-muted fw-bold">Program:</td><td><?=htmlspecialchars($student['program'])?></td></tr>
                    <tr><td class="text-muted fw-bold">Semester:</td><td><?=$student['semester']?></td></tr>
                </table>
            </div>
            <div class="col-sm-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted fw-bold" style="width:120px">Exam:</td><td><?=htmlspecialchars($examInfo['exam_name'])?></td></tr>
                    <tr><td class="text-muted fw-bold">Year:</td><td><?=$examInfo['year']?></td></tr>
                    <tr><td class="text-muted fw-bold">From:</td><td><?=date('M d, Y',strtotime($examInfo['start_date']))?></td></tr>
                    <tr><td class="text-muted fw-bold">To:</td><td><?=date('M d, Y',strtotime($examInfo['end_date']))?></td></tr>
                </table>
            </div>
        </div>

        <h6 class="fw-bold mb-2">Examination Schedule</h6>
        <table class="table table-bordered table-sm">
            <thead class="table-dark">
                <tr><th>Date</th><th>Day</th><th>Subject</th><th>Code</th><th>Time</th></tr>
            </thead>
            <tbody>
            <?php foreach ($routine as $row): ?>
            <tr>
                <td><?=date('M d, Y',strtotime($row['exam_date']))?></td>
                <td><?=date('l',strtotime($row['exam_date']))?></td>
                <td><?=htmlspecialchars($row['subject_name'])?></td>
                <td><code><?=htmlspecialchars($row['subject_code'])?></code></td>
                <td><?=date('h:i A',strtotime($row['start_time']))?> – <?=date('h:i A',strtotime($row['end_time']))?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p class="text-muted small mt-3 mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Please bring this admit card and a valid photo ID to each exam.
        </p>
    </div>
</div>

<?php else: ?>
<!-- ── List of Admit Cards ────────────────────────────────────────────────── -->
<div class="data-card">
    <div class="data-card-header">
        <h6 class="data-card-title"><i class="bi bi-card-text me-2"></i>My Admit Cards</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Exam</th><th>Semester</th><th>Exam Period</th><th>Issue Date</th><th>Eligibility</th><th>Action</th></tr></thead>
            <tbody>
            <?php if ($admitCards && $admitCards->num_rows > 0): while ($row=$admitCards->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($row['exam_name'])?></td>
                <td>Semester <?=$row['semester']?></td>
                <td><?=date('M d',strtotime($row['start_date']))?> – <?=date('M d Y',strtotime($row['end_date']))?></td>
                <td><?=$row['issue_date'] ? date('M d Y',strtotime($row['issue_date'])) : '—'?></td>
                <td>
                    <span class="badge bg-<?=$row['eligibility_status']?'success':'danger'?>">
                        <?=$row['eligibility_status']?'Eligible':'Not Eligible'?>
                    </span>
                </td>
                <td>
                    <?php if ($row['eligibility_status']): ?>
                    <a href="?view=<?=$row['exam_id']?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i>View & Print
                    </a>
                    <?php else: ?>
                    <span class="text-muted small">Contact admin</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No admit cards issued yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="alert alert-warning">Student profile not found.</div>
<?php endif; ?>

<?php include('../include/layout_end.php'); ?>
