<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('admin');

$pageTitle  = 'Manage Teachers';
$activeMenu = 'teachers';

$teachers = $conn->query(
    "SELECT t.*, u.full_name, u.email, u.status, u.created_at
     FROM teachers t JOIN users u ON t.user_id=u.user_id
     ORDER BY u.full_name"
);

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<div class="d-flex justify-content-end mb-3">
    <a href="manage-users.php" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i>Add Teacher via Users
    </a>
</div>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Department</th><th>Designation</th><th>Status</th><th>Joined</th></tr></thead>
            <tbody>
            <?php if ($teachers && $teachers->num_rows > 0): $i=1; while ($row=$teachers->fetch_assoc()): ?>
            <tr>
                <td><?=$i++?></td>
                <td><?=htmlspecialchars($row['full_name'])?></td>
                <td><?=htmlspecialchars($row['email'])?></td>
                <td><?=htmlspecialchars($row['department'] ?: '—')?></td>
                <td><?=htmlspecialchars($row['designation'] ?: '—')?></td>
                <td><span class="badge bg-<?=statusBadge($row['status'])?>"><?=ucfirst($row['status'])?></span></td>
                <td><?=date('M d Y',strtotime($row['created_at']))?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No teachers found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('../include/layout_end.php'); ?>
