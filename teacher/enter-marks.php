<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('teacher');

$userId = (int)$_SESSION['user_id'];
$tRow   = $conn->query("SELECT teacher_id FROM teachers WHERE user_id=$userId LIMIT 1")->fetch_assoc();
$tid    = $tRow ? (int)$tRow['teacher_id'] : 0;

// ── Save marks (draft) ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_marks'])) {
    $eid = (int)$_POST['exam_id'];
    $sid = (int)$_POST['subject_id'];
    $marksData = $_POST['marks'] ?? [];

    foreach ($marksData as $studentId => $obtainedStr) {
        $studentId = (int)$studentId;
        $obtained  = floatval($obtainedStr);
        if ($obtained < 0) $obtained = 0;
        if ($obtained > 100) $obtained = 100;
        $grade = calculateGrade($obtained, 100);

        $s = $conn->prepare(
            "INSERT INTO marks (exam_id,student_id,subject_id,teacher_id,marks_obtained,grade,status)
             VALUES(?,?,?,?,?,'$grade','draft')
             ON DUPLICATE KEY UPDATE marks_obtained=VALUES(marks_obtained),grade=VALUES(grade),status='draft',teacher_id=?"
        );
        $s->bind_param("iiiidi",$eid,$studentId,$sid,$tid,$obtained,$tid);
        $s->execute(); $s->close();
    }
    redirectWith("enter-marks.php?exam_id=$eid&subject_id=$sid",'success',"Marks saved as draft.");
}

// ── Submit marks (lock) ───────────────────────────────────────────────────────
if (isset($_GET['submit_marks'])) {
    $eid = (int)$_GET['exam_id'];
    $sid = (int)$_GET['subject_id'];
    $s = $conn->prepare("UPDATE marks SET status='submitted' WHERE exam_id=? AND subject_id=? AND teacher_id=?");
    $s->bind_param("iii",$eid,$sid,$tid); $s->execute(); $s->close();
    redirectWith("enter-marks.php?exam_id=$eid&subject_id=$sid",'success',"Marks submitted and locked.");
}

$pageTitle  = 'Enter Marks';
$activeMenu = 'marks';

$examId    = (int)($_GET['exam_id']    ?? 0);
$subjectId = (int)($_GET['subject_id'] ?? 0);

$exams    = $conn->query("SELECT * FROM exams WHERE status IN ('scheduled','completed') ORDER BY exam_id DESC");
$subjects = [];
$students = [];
$existingMarks = [];
$marksStatus   = 'draft';
$exam = null;

if ($examId) {
    $exam = $conn->query("SELECT * FROM exams WHERE exam_id=$examId LIMIT 1")->fetch_assoc();
    if ($exam) {
        $sem = $exam['semester'];
        $subRes = $conn->query(
            "SELECT DISTINCT s.* FROM subjects s
             JOIN exam_routine er ON er.subject_id=s.subject_id
             WHERE er.exam_id=$examId ORDER BY s.subject_name"
        );
        while ($sub = $subRes->fetch_assoc()) $subjects[] = $sub;
    }
}

if ($examId && $subjectId && $exam) {
    $sem = $exam['semester'];
    $stRes = $conn->query(
        "SELECT st.student_id, u.full_name, st.roll_number FROM students st
         JOIN users u ON st.user_id=u.user_id
         WHERE st.semester=$sem AND u.status='approved' ORDER BY st.roll_number"
    );
    while ($st = $stRes->fetch_assoc()) $students[] = $st;

    $mRes = $conn->query(
        "SELECT student_id, marks_obtained, grade, status FROM marks
         WHERE exam_id=$examId AND subject_id=$subjectId AND teacher_id=$tid"
    );
    while ($m = $mRes->fetch_assoc()) {
        $existingMarks[$m['student_id']] = $m;
        $marksStatus = $m['status'];
    }
}

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<div class="row g-4">
    <!-- Selectors -->
    <div class="col-12">
        <div class="data-card">
            <div class="data-card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Select Exam</label>
                        <select name="exam_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Select Exam --</option>
                            <?php while ($e=$exams->fetch_assoc()): ?>
                            <option value="<?=$e['exam_id']?>" <?=$examId==$e['exam_id']?'selected':''?>>
                                <?=htmlspecialchars($e['exam_name'])?> (Sem <?=$e['semester']?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php if (!empty($subjects)): ?>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Select Subject</label>
                        <select name="subject_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Select Subject --</option>
                            <?php foreach ($subjects as $sub): ?>
                            <option value="<?=$sub['subject_id']?>" <?=$subjectId==$sub['subject_id']?'selected':''?>>
                                <?=htmlspecialchars($sub['subject_name'])?> (<?=htmlspecialchars($sub['subject_code'])?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" name="exam_id" value="<?=$examId?>">
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Marks Table -->
    <?php if (!empty($students) && $subjectId): ?>
    <div class="col-12">
        <div class="data-card">
            <div class="data-card-header">
                <h6 class="data-card-title">
                    Mark Entry
                    <?php if ($marksStatus === 'submitted'): ?>
                    <span class="badge bg-success ms-2">Submitted & Locked</span>
                    <?php else: ?>
                    <span class="badge bg-warning text-dark ms-2">Draft</span>
                    <?php endif; ?>
                </h6>
                <?php if ($marksStatus === 'submitted'): ?>
                <span class="text-success fw-bold"><i class="bi bi-lock-fill me-1"></i>Marks locked</span>
                <?php else: ?>
                <a href="?exam_id=<?=$examId?>&subject_id=<?=$subjectId?>&submit_marks=1"
                   class="btn btn-success btn-sm"
                   onclick="return confirm('Submit and lock these marks? You cannot edit after submission.')">
                    <i class="bi bi-check-circle me-1"></i>Submit Marks
                </a>
                <?php endif; ?>
            </div>
            <form method="POST">
                <input type="hidden" name="exam_id" value="<?=$examId?>">
                <input type="hidden" name="subject_id" value="<?=$subjectId?>">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Roll No</th><th>Student Name</th><th>Marks (0–100)</th><th>Grade</th></tr></thead>
                        <tbody>
                        <?php foreach ($students as $st):
                            $existing = $existingMarks[$st['student_id']] ?? null;
                            $marksVal = $existing ? $existing['marks_obtained'] : '';
                            $gradeVal = $existing ? $existing['grade'] : '—';
                            $locked   = $marksStatus === 'submitted';
                        ?>
                        <tr>
                            <td><?=htmlspecialchars($st['roll_number'])?></td>
                            <td><?=htmlspecialchars($st['full_name'])?></td>
                            <td>
                                <input type="number"
                                       name="marks[<?=$st['student_id']?>]"
                                       class="form-control form-control-sm marks-input"
                                       value="<?=$marksVal?>"
                                       min="0" max="100" step="0.5"
                                       <?=$locked?'readonly disabled':''?>
                                       style="max-width:100px">
                            </td>
                            <td class="grade-cell"><?=$gradeVal?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($marksStatus !== 'submitted'): ?>
                <div class="p-3 border-top">
                    <button type="submit" name="save_marks" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Save as Draft
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <?php elseif ($examId && $subjectId): ?>
    <div class="col-12"><div class="alert alert-info">No students found for this exam's semester.</div></div>
    <?php endif; ?>
</div>

<script>
// Live grade calculation
document.querySelectorAll('.marks-input').forEach(function(inp) {
    inp.addEventListener('input', function () {
        const val = parseFloat(this.value);
        const cell = this.closest('tr').querySelector('.grade-cell');
        if (isNaN(val)) { cell.textContent = '—'; return; }
        const pct = val;
        let g = 'F';
        if (pct >= 90) g = 'A+';
        else if (pct >= 80) g = 'A';
        else if (pct >= 70) g = 'B+';
        else if (pct >= 60) g = 'B';
        else if (pct >= 50) g = 'C';
        else if (pct >= 40) g = 'D';
        cell.textContent = g;
    });
});
</script>

<?php include('../include/layout_end.php'); ?>
