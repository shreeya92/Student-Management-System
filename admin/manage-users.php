<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('admin');

// ── Change status ─────────────────────────────────────────────────────────────
if (isset($_GET['status_change'], $_GET['uid'])) {
    $uid    = (int)$_GET['uid'];
    $newSt  = in_array($_GET['status_change'], ['approved','rejected','pending']) ? $_GET['status_change'] : null;
    if ($newSt && $uid !== (int)$_SESSION['user_id']) {
        $s = $conn->prepare("UPDATE users SET status=? WHERE user_id=?");
        $s->bind_param("si", $newSt, $uid);
        $s->execute(); $s->close();
        redirectWith('manage-users.php', 'success', "User status updated to $newSt.");
    }
}

// ── Add teacher via modal ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    $name   = trim($_POST['full_name']   ?? '');
    $email  = trim($_POST['email']       ?? '');
    $dept   = trim($_POST['department']  ?? '');
    $desig  = trim($_POST['designation'] ?? '');
    $pass   = password_hash('sems@1234', PASSWORD_BCRYPT, ['cost'=>12]);
    $err    = [];
    if (!$name)                                    $err[] = "Name required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = "Valid email required.";

    if (empty($err)) {
        // Check duplicate email
        $chk = $conn->prepare("SELECT user_id FROM users WHERE email=? LIMIT 1");
        $chk->bind_param("s",$email); $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $err[] = "Email already exists.";
        } else {
            $conn->begin_transaction();
            $stU = $conn->prepare("INSERT INTO users (full_name,email,password,role,status) VALUES(?,?,'$pass','teacher','approved')");
            $stU->bind_param("ss",$name,$email); $stU->execute();
            $uid = $conn->insert_id; $stU->close();
            $stT = $conn->prepare("INSERT INTO teachers (user_id,department,designation) VALUES(?,?,?)");
            $stT->bind_param("iss",$uid,$dept,$desig); $stT->execute(); $stT->close();
            $conn->commit();
            redirectWith('manage-users.php', 'success', "Teacher account created. Default password: sems@1234");
        }
    }
    if (!empty($err)) { setFlash('danger', implode(' ', $err)); }
}

$pageTitle  = 'Manage Users';
$activeMenu = 'users';

$roleFilter = $_GET['role'] ?? 'all';
$sql = "SELECT u.user_id, u.full_name, u.email, u.role, u.status, u.created_at FROM users u";
if (in_array($roleFilter, ['admin','teacher','student'])) {
    $sql .= " WHERE u.role='$roleFilter'";
}
$sql .= " ORDER BY u.created_at DESC";
$users = $conn->query($sql);

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
        <?php foreach (['all','admin','teacher','student'] as $r): ?>
        <a href="?role=<?=$r?>" class="btn btn-sm <?= $roleFilter===$r ? 'btn-primary' : 'btn-outline-secondary' ?>">
            <?= ucfirst($r) ?>s
        </a>
        <?php endforeach; ?>
    </div>
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
        <i class="bi bi-person-plus me-1"></i>Add Teacher
    </button>
</div>

<div class="data-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($users && $users->num_rows > 0): $i=1; while ($row=$users->fetch_assoc()): ?>
            <tr>
                <td><?=$i++?></td>
                <td><?=htmlspecialchars($row['full_name'])?></td>
                <td><?=htmlspecialchars($row['email'])?></td>
                <td><span class="badge bg-secondary"><?=ucfirst($row['role'])?></span></td>
                <td><span class="badge bg-<?=statusBadge($row['status'])?>"><?=ucfirst($row['status'])?></span></td>
                <td><?=date('M d Y', strtotime($row['created_at']))?></td>
                <td>
                    <?php if ($row['user_id'] !== (int)$_SESSION['user_id']): ?>
                    <?php if ($row['status'] !== 'approved'): ?>
                    <a href="?uid=<?=$row['user_id']?>&status_change=approved" class="btn btn-xs btn-sm btn-success me-1">Approve</a>
                    <?php endif; ?>
                    <?php if ($row['status'] !== 'rejected'): ?>
                    <a href="?uid=<?=$row['user_id']?>&status_change=rejected"
                       class="btn btn-xs btn-sm btn-danger"
                       onclick="return confirm('Reject this user?')">Reject</a>
                    <?php endif; ?>
                    <?php else: ?>
                    <span class="text-muted small">You</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Teacher Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">Department</label>
                        <input type="text" name="department" class="form-control" placeholder="e.g. Computer Science">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">Designation</label>
                        <input type="text" name="designation" class="form-control" placeholder="e.g. Lecturer">
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>Default password: <strong>sems@1234</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_teacher" class="btn btn-success">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../include/layout_end.php'); ?>
