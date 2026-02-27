<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_subject'])) {
        $name = trim($_POST['subject_name'] ?? '');
        $code = trim($_POST['subject_code'] ?? '');
        $sem  = (int)$_POST['semester'];
        if ($name && $code && $sem) {
            $s = $conn->prepare("INSERT INTO subjects (subject_name,subject_code,semester) VALUES(?,?,?)");
            $s->bind_param("ssi",$name,$code,$sem); $s->execute(); $s->close();
            redirectWith('manage-subjects.php','success',"Subject '$name' created.");
        } else { setFlash('danger','All fields required.'); }
    }
    if (isset($_POST['edit_subject'])) {
        $id   = (int)$_POST['subject_id'];
        $name = trim($_POST['subject_name'] ?? '');
        $code = trim($_POST['subject_code'] ?? '');
        $sem  = (int)$_POST['semester'];
        $s = $conn->prepare("UPDATE subjects SET subject_name=?,subject_code=?,semester=? WHERE subject_id=?");
        $s->bind_param("ssii",$name,$code,$sem,$id); $s->execute(); $s->close();
        redirectWith('manage-subjects.php','success',"Subject updated.");
    }
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $s = $conn->prepare("DELETE FROM subjects WHERE subject_id=?");
    $s->bind_param("i",$id); $s->execute(); $s->close();
    redirectWith('manage-subjects.php','success',"Subject deleted.");
}

$pageTitle  = 'Manage Subjects';
$activeMenu = 'subjects';
$semFilter  = (int)($_GET['sem'] ?? 0);
$sql = "SELECT * FROM subjects";
if ($semFilter) $sql .= " WHERE semester=$semFilter";
$sql .= " ORDER BY semester, subject_name";
$subjects = $conn->query($sql);
include('../include/layout.php');
?>

<?php renderFlash(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-1 flex-wrap">
        <a href="manage-subjects.php" class="btn btn-sm <?=$semFilter===0?'btn-primary':'btn-outline-secondary'?>">All</a>
        <?php for($s=1;$s<=8;$s++): ?>
        <a href="?sem=<?=$s?>" class="btn btn-sm <?=$semFilter===$s?'btn-primary':'btn-outline-secondary'?>">Sem <?=$s?></a>
        <?php endfor; ?>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createSubModal">
        <i class="bi bi-plus-lg me-1"></i>Add Subject
    </button>
</div>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Subject Name</th><th>Code</th><th>Semester</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($subjects && $subjects->num_rows > 0): $i=1; while ($row=$subjects->fetch_assoc()): ?>
            <tr>
                <td><?=$i++?></td>
                <td><?=htmlspecialchars($row['subject_name'])?></td>
                <td><code><?=htmlspecialchars($row['subject_code'])?></code></td>
                <td>Semester <?=$row['semester']?></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1"
                            data-bs-toggle="modal" data-bs-target="#editSubModal<?=$row['subject_id']?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <a href="?delete=<?=$row['subject_id']?>" class="btn btn-sm btn-outline-danger"
                       data-confirm="Delete this subject?"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
            <!-- Edit Modal -->
            <div class="modal fade" id="editSubModal<?=$row['subject_id']?>" tabindex="-1">
                <div class="modal-dialog"><div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <form method="POST"><div class="modal-body row g-3">
                        <input type="hidden" name="subject_id" value="<?=$row['subject_id']?>">
                        <div class="col-12"><label class="form-label fw-bold">Subject Name</label>
                            <input type="text" name="subject_name" class="form-control" value="<?=htmlspecialchars($row['subject_name'])?>" required></div>
                        <div class="col-6"><label class="form-label fw-bold">Subject Code</label>
                            <input type="text" name="subject_code" class="form-control" value="<?=htmlspecialchars($row['subject_code'])?>" required></div>
                        <div class="col-6"><label class="form-label fw-bold">Semester</label>
                            <select name="semester" class="form-select" required>
                                <?php for($s=1;$s<=8;$s++): ?>
                                <option value="<?=$s?>" <?=$row['semester']==$s?'selected':''?>>Semester <?=$s?></option>
                                <?php endfor; ?>
                            </select></div>
                    </div><div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_subject" class="btn btn-primary">Save Changes</button>
                    </div></form>
                </div></div>
            </div>
            <?php endwhile; else: ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No subjects found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createSubModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Subject</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST"><div class="modal-body row g-3">
            <div class="col-12"><label class="form-label fw-bold">Subject Name</label>
                <input type="text" name="subject_name" class="form-control" placeholder="e.g. Data Structures" required></div>
            <div class="col-6"><label class="form-label fw-bold">Subject Code</label>
                <input type="text" name="subject_code" class="form-control" placeholder="e.g. CS301" required></div>
            <div class="col-6"><label class="form-label fw-bold">Semester</label>
                <select name="semester" class="form-select" required>
                    <?php for($s=1;$s<=8;$s++): ?><option value="<?=$s?>">Semester <?=$s?></option><?php endfor; ?>
                </select></div>
        </div><div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="create_subject" class="btn btn-primary">Add Subject</button>
        </div></form>
    </div></div>
</div>

<?php include('../include/layout_end.php'); ?>
