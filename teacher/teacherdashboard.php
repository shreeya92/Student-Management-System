<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('teacher');

$pageTitle  = 'Teacher Dashboard';
$activeMenu = 'dashboard';
$teacherId  = $_SESSION['user_id'];

// Get teacher_id from teachers table
$t = $conn->query("SELECT teacher_id FROM teachers WHERE user_id=$teacherId LIMIT 1")->fetch_assoc();
$tid = $t ? $t['teacher_id'] : 0;

$draftMarks = (int)$conn->query("SELECT COUNT(*) c FROM marks WHERE teacher_id=$tid AND status='draft'")->fetch_assoc()['c'];
$submittedMarks = (int)$conn->query("SELECT COUNT(*) c FROM marks WHERE teacher_id=$tid AND status='submitted'")->fetch_assoc()['c'];

// Upcoming exams
$upcoming = $conn->query(
    "SELECT DISTINCT e.exam_id, e.exam_name, e.semester, e.start_date, e.status
     FROM exams e WHERE e.status IN ('scheduled','completed') ORDER BY e.start_date LIMIT 5"
);

// Today's schedule
$today = date('Y-m-d');
$todaySchedule = $conn->query(
    "SELECT er.start_time, er.end_time, s.subject_name, e.exam_name
     FROM exam_routine er
     JOIN subjects s ON er.subject_id=s.subject_id
     JOIN exams e ON er.exam_id=e.exam_id
     WHERE er.exam_date='$today' ORDER BY er.start_time"
);

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-pencil-square"></i></div>
            <div>
                <div class="stat-value"><?=$draftMarks?></div>
                <div class="stat-label">Marks Pending Submission</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="stat-value"><?=$submittedMarks?></div>
                <div class="stat-label">Marks Submitted</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="stat-value"><?=$todaySchedule ? $todaySchedule->num_rows : 0?></div>
                <div class="stat-label">Exams Today</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="data-card">
            <div class="data-card-header">
                <h6 class="data-card-title"><i class="bi bi-journal-text me-2"></i>Active Exams</h6>
                <a href="enter-marks.php" class="btn btn-sm btn-primary">Enter Marks</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Exam</th><th>Semester</th><th>Start Date</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ($upcoming && $upcoming->num_rows > 0): while ($row=$upcoming->fetch_assoc()): ?>
                    <tr>
                        <td><?=htmlspecialchars($row['exam_name'])?></td>
                        <td>Sem <?=$row['semester']?></td>
                        <td><?=date('M d Y',strtotime($row['start_date']))?></td>
                        <td><span class="badge bg-<?=statusBadge($row['status'])?>"><?=ucfirst($row['status'])?></span></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No active exams.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="data-card">
            <div class="data-card-header">
                <h6 class="data-card-title"><i class="bi bi-clock me-2"></i>Today's Exam Schedule</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Time</th><th>Subject</th><th>Exam</th></tr></thead>
                    <tbody>
                    <?php
                    $todaySchedule = $conn->query(
                        "SELECT er.start_time, er.end_time, s.subject_name, e.exam_name
                         FROM exam_routine er
                         JOIN subjects s ON er.subject_id=s.subject_id
                         JOIN exams e ON er.exam_id=e.exam_id
                         WHERE er.exam_date='$today' ORDER BY er.start_time"
                    );
                    if ($todaySchedule && $todaySchedule->num_rows > 0):
                        while ($row=$todaySchedule->fetch_assoc()): ?>
                    <tr>
                        <td><?=date('h:i A',strtotime($row['start_time']))?></td>
                        <td><?=htmlspecialchars($row['subject_name'])?></td>
                        <td><?=htmlspecialchars($row['exam_name'])?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="3" class="text-center text-muted py-3">No exams today.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../include/layout_end.php'); ?>
