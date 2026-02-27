<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('student');

$pageTitle  = 'Exam Routine';
$activeMenu = 'routine';
$userId     = (int)$_SESSION['user_id'];

$student = $conn->query(
    "SELECT st.semester FROM students st WHERE st.user_id=$userId LIMIT 1"
)->fetch_assoc();
$sem = $student ? $student['semester'] : 0;

$examId  = (int)($_GET['exam_id'] ?? 0);
$exams   = $conn->query(
    "SELECT DISTINCT e.* FROM exams e
     JOIN exam_routine er ON er.exam_id=e.exam_id
     JOIN subjects s ON s.subject_id=er.subject_id AND s.semester=$sem
     ORDER BY e.exam_id DESC"
);
$routine = [];
$exam    = null;

if ($examId) {
    $exam = $conn->query("SELECT * FROM exams WHERE exam_id=$examId LIMIT 1")->fetch_assoc();
    if ($exam) {
        $r = $conn->query(
            "SELECT er.exam_date, er.start_time, er.end_time, s.subject_name, s.subject_code
             FROM exam_routine er JOIN subjects s ON er.subject_id=s.subject_id
             WHERE er.exam_id=$examId AND s.semester=$sem
             ORDER BY er.exam_date, er.start_time"
        );
        while ($row=$r->fetch_assoc()) $routine[] = $row;
    }
}

include('../include/layout.php');
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="data-card">
            <div class="data-card-body">
                <form method="GET">
                    <label class="form-label fw-bold">Select Exam</label>
                    <select name="exam_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Select Exam --</option>
                        <?php while ($e=$exams->fetch_assoc()): ?>
                        <option value="<?=$e['exam_id']?>" <?=$examId==$e['exam_id']?'selected':''?>>
                            <?=htmlspecialchars($e['exam_name'])?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($exam): ?>
<div class="data-card">
    <div class="data-card-header">
        <h6 class="data-card-title">
            <?=htmlspecialchars($exam['exam_name'])?>
            <span class="badge bg-<?=statusBadge($exam['status'])?> ms-2"><?=ucfirst($exam['status'])?></span>
        </h6>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary no-print">
            <i class="bi bi-printer me-1"></i>Print
        </button>
    </div>
    <div class="table-responsive print-section">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Day</th><th>Subject</th><th>Code</th><th>Start</th><th>End</th></tr></thead>
            <tbody>
            <?php if (!empty($routine)): foreach ($routine as $row): ?>
            <tr>
                <td><?=date('M d, Y',strtotime($row['exam_date']))?></td>
                <td><?=date('l',strtotime($row['exam_date']))?></td>
                <td><?=htmlspecialchars($row['subject_name'])?></td>
                <td><code><?=htmlspecialchars($row['subject_code'])?></code></td>
                <td><?=date('h:i A',strtotime($row['start_time']))?></td>
                <td><?=date('h:i A',strtotime($row['end_time']))?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No schedule available.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include('../include/layout_end.php'); ?>
