<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('admin');

$pageTitle  = 'Manage Students';
$activeMenu = 'students';

$semFilter = (int)($_GET['sem'] ?? 0);
$sql = "SELECT st.*, u.full_name, u.email, u.status
        FROM students st JOIN users u ON st.user_id=u.user_id";
if ($semFilter) $sql .= " WHERE st.semester=$semFilter";
$sql .= " ORDER BY st.roll_number";
$students = $conn->query($sql);

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-1 flex-wrap">
        <a href="manage-students.php" class="btn btn-sm <?=$semFilter===0?'btn-primary':'btn-outline-secondary'?>">All</a>
        <?php for($s=1;$s<=8;$s++): ?>
        <a href="?sem=<?=$s?>" class="btn btn-sm <?=$semFilter===$s?'btn-primary':'btn-outline-secondary'?>">Sem <?=$s?></a>
        <?php endfor; ?>
    </div>
</div>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Roll No</th><th>Name</th><th>Email</th><th>Program</th><th>Semester</th><th>Year</th><th>Status</th></tr></thead>
            <tbody>
            <?php if ($students && $students->num_rows > 0): $i=1; while ($row=$students->fetch_assoc()): ?>
            <tr>
                <td><?=$i++?></td>
                <td><strong><?=htmlspecialchars($row['roll_number'])?></strong></td>
                <td><?=htmlspecialchars($row['full_name'])?></td>
                <td><?=htmlspecialchars($row['email'])?></td>
                <td><?=htmlspecialchars($row['program'])?></td>
                <td>Sem <?=$row['semester']?></td>
                <td><?=$row['admission_year']?></td>
                <td><span class="badge bg-<?=statusBadge($row['status'])?>"><?=ucfirst($row['status'])?></span></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No students found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('../include/layout_end.php'); ?>
