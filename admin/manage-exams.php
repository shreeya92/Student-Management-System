<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('admin');

// ── Create ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_exam'])) {
    $name  = trim($_POST['exam_name'] ?? '');
    $sem   = (int)$_POST['semester'];
    $year  = (int)$_POST['year'];
    $start = $_POST['start_date'] ?? '';
    $end   = $_POST['end_date']   ?? '';
    if ($name && $sem && $year && $start && $end) {
        $s = $conn->prepare("INSERT INTO exams (exam_name,semester,year,start_date,end_date,status) VALUES(?,?,?,?,?,'scheduled')");
        $s->bind_param("siiss",$name,$sem,$year,$start,$end); $s->execute(); $s->close();
        redirectWith('manage-exams.php','success',"Exam created successfully.");
    } else { setFlash('danger','All fields are required.'); }
}

// ── Update status ─────────────────────────────────────────────────────────────
if (isset($_GET['set_status'], $_GET['eid'])) {
    $eid   = (int)$_GET['eid'];
    $newSt = in_array($_GET['set_status'],['scheduled','completed','published']) ? $_GET['set_status'] : null;
    if ($newSt) {
        $s = $conn->prepare("UPDATE exams SET status=? WHERE exam_id=?");
        $s->bind_param("si",$newSt,$eid); $s->execute(); $s->close();
        redirectWith('manage-exams.php','success',"Exam status updated to $newSt.");
    }
}

// ── Delete ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $eid = (int)$_GET['delete'];
    $s = $conn->prepare("DELETE FROM exams WHERE exam_id=?");
    $s->bind_param("i",$eid); $s->execute(); $s->close();
    redirectWith('manage-exams.php','success',"Exam deleted.");
}

$pageTitle  = 'Manage Exams';
$activeMenu = 'exams';
$exams = $conn->query("SELECT * FROM exams ORDER BY exam_id DESC");
include('../include/layout.php');
?>

<?php renderFlash(); ?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createExamModal">
        <i class="bi bi-plus-lg me-1"></i>Create Exam
    </button>
</div>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Exam Name</th><th>Semester</th><th>Year</th><th>Dates</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($exams && $exams->num_rows > 0): $i=1; while ($row=$exams->fetch_assoc()): ?>
            <tr>
                <td><?=$i++?></td>
                <td><?=htmlspecialchars($row['exam_name'])?></td>
                <td>Semester <?=$row['semester']?></td>
                <td><?=$row['year']?></td>
                <td><?=date('M d',$d=strtotime($row['start_date']))?> – <?=date('M d Y',strtotime($row['end_date']))?></td>
                <td><span class="badge bg-<?=statusBadge($row['status'])?>"><?=ucfirst($row['status'])?></span></td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        <?php if ($row['status']==='scheduled'): ?>
                        <a href="?eid=<?=$row['exam_id']?>&set_status=completed" class="btn btn-sm btn-info">Complete</a>
                        <?php elseif ($row['status']==='completed'): ?>
                        <a href="?eid=<?=$row['exam_id']?>&set_status=published" class="btn btn-sm btn-success">Publish</a>
                        <?php endif; ?>
                        <a href="manage-routine.php?exam_id=<?=$row['exam_id']?>" class="btn btn-sm btn-outline-primary">Routine</a>
                        <a href="issue-admit-cards.php?exam_id=<?=$row['exam_id']?>" class="btn btn-sm btn-outline-secondary">Admit Cards</a>
                        <a href="?delete=<?=$row['exam_id']?>" class="btn btn-sm btn-outline-danger"
                           data-confirm="Delete this exam and all related data?">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No exams yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Exam Modal -->
<div class="modal fade" id="createExamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Exam</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Exam Name</label>
                        <input type="text" name="exam_name" class="form-control" placeholder="e.g. Mid-Term Examination 2026" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">Semester</label>
                        <select name="semester" class="form-select" required>
                            <?php for($s=1;$s<=8;$s++): ?><option value="<?=$s?>">Semester <?=$s?></option><?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">Year</label>
                        <input type="number" name="year" class="form-control" value="<?=date('Y')?>" min="2000" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_exam" class="btn btn-primary">Create Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../include/layout_end.php'); ?>
