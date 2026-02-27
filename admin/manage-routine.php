<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('admin');

$examId = (int)($_GET['exam_id'] ?? 0);

// ── Add routine row ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_routine'])) {
    $eid   = (int)$_POST['exam_id'];
    $sid   = (int)$_POST['subject_id'];
    $date  = $_POST['exam_date']   ?? '';
    $start = $_POST['start_time']  ?? '';
    $end   = $_POST['end_time']    ?? '';
    if ($eid && $sid && $date && $start && $end) {
        $s = $conn->prepare("INSERT INTO exam_routine (exam_id,subject_id,exam_date,start_time,end_time) VALUES(?,?,?,?,?)");
        $s->bind_param("iisss",$eid,$sid,$date,$start,$end); $s->execute(); $s->close();
        redirectWith("manage-routine.php?exam_id=$eid",'success',"Schedule entry added.");
    } else { setFlash('danger','All fields required.'); }
}

// ── Delete routine row ────────────────────────────────────────────────────────
if (isset($_GET['del_routine'])) {
    $rid = (int)$_GET['del_routine'];
    $s = $conn->prepare("DELETE FROM exam_routine WHERE routine_id=?");
    $s->bind_param("i",$rid); $s->execute(); $s->close();
    redirectWith("manage-routine.php?exam_id=$examId",'success',"Entry removed.");
}

$pageTitle  = 'Exam Routine';
$activeMenu = 'routine';

$exams   = $conn->query("SELECT * FROM exams ORDER BY exam_id DESC");
$exam    = null;
$routine = [];
$subjects = [];

if ($examId) {
    $s = $conn->prepare("SELECT * FROM exams WHERE exam_id=? LIMIT 1");
    $s->bind_param("i",$examId); $s->execute();
    $exam = $s->get_result()->fetch_assoc(); $s->close();

    if ($exam) {
        // Subjects for this exam's semester
        $sem = $exam['semester'];
        $subjects = $conn->query("SELECT * FROM subjects WHERE semester=$sem ORDER BY subject_name");

        // Existing routine
        $r = $conn->query(
            "SELECT er.*, s.subject_name, s.subject_code
             FROM exam_routine er JOIN subjects s ON er.subject_id=s.subject_id
             WHERE er.exam_id=$examId ORDER BY er.exam_date, er.start_time"
        );
        while ($row = $r->fetch_assoc()) $routine[] = $row;
    }
}

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="data-card">
            <div class="data-card-header"><h6 class="data-card-title">Select Exam</h6></div>
            <div class="data-card-body">
                <form method="GET">
                    <select name="exam_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Select an Exam --</option>
                        <?php while ($e = $exams->fetch_assoc()): ?>
                        <option value="<?=$e['exam_id']?>" <?=$examId==$e['exam_id']?'selected':''?>>
                            <?=htmlspecialchars($e['exam_name'])?> (Sem <?=$e['semester']?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($exam): ?>
<div class="row g-4">
    <!-- Add Entry -->
    <div class="col-md-4">
        <div class="data-card">
            <div class="data-card-header"><h6 class="data-card-title">Add Schedule Entry</h6></div>
            <div class="data-card-body">
                <form method="POST">
                    <input type="hidden" name="exam_id" value="<?=$examId?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">-- Select Subject --</option>
                            <?php if ($subjects): while ($sub=$subjects->fetch_assoc()): ?>
                            <option value="<?=$sub['subject_id']?>"><?=htmlspecialchars($sub['subject_name'])?> (<?=htmlspecialchars($sub['subject_code'])?>)</option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Exam Date</label>
                        <input type="date" name="exam_date" class="form-control"
                               min="<?=$exam['start_date']?>" max="<?=$exam['end_date']?>" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-bold">Start Time</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">End Time</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" name="add_routine" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-plus-lg me-1"></i>Add Entry
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Routine Table -->
    <div class="col-md-8">
        <div class="data-card">
            <div class="data-card-header">
                <h6 class="data-card-title">
                    <?=htmlspecialchars($exam['exam_name'])?> — Schedule
                    <small class="text-muted ms-2"><?=date('M d',strtotime($exam['start_date']))?> – <?=date('M d Y',strtotime($exam['end_date']))?></small>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Date</th><th>Subject</th><th>Code</th><th>Time</th><th></th></tr></thead>
                    <tbody>
                    <?php if (!empty($routine)): foreach ($routine as $row): ?>
                    <tr>
                        <td><?=date('D, M d Y',strtotime($row['exam_date']))?></td>
                        <td><?=htmlspecialchars($row['subject_name'])?></td>
                        <td><code><?=htmlspecialchars($row['subject_code'])?></code></td>
                        <td><?=date('h:i A',strtotime($row['start_time']))?> – <?=date('h:i A',strtotime($row['end_time']))?></td>
                        <td>
                            <a href="?exam_id=<?=$examId?>&del_routine=<?=$row['routine_id']?>"
                               class="btn btn-sm btn-outline-danger" data-confirm="Remove this entry?">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No schedule entries yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include('../include/layout_end.php'); ?>
