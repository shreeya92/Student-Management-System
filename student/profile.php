<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('student');

$pageTitle  = 'My Profile';
$activeMenu = 'profile';
$userId     = (int)$_SESSION['user_id'];

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $user = $conn->query("SELECT password FROM users WHERE user_id=$userId LIMIT 1")->fetch_assoc();
    if (!password_verify($current, $user['password'])) {
        setFlash('danger', 'Current password is incorrect.');
    } elseif (strlen($new) < 8) {
        setFlash('danger', 'New password must be at least 8 characters.');
    } elseif ($new !== $confirm) {
        setFlash('danger', 'New passwords do not match.');
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
        $s = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $s->bind_param("si", $hash, $userId); $s->execute(); $s->close();
        setFlash('success', 'Password changed successfully.');
    }
    header("Location: profile.php"); exit();
}

$student = $conn->query(
    "SELECT st.*, u.full_name, u.email, u.status, u.created_at
     FROM students st JOIN users u ON st.user_id=u.user_id WHERE st.user_id=$userId LIMIT 1"
)->fetch_assoc();

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<?php if ($student): ?>
<div class="row g-4">
    <div class="col-md-5">
        <div class="data-card">
            <div class="p-4 text-center border-bottom" style="background:linear-gradient(135deg,#1F4E79,#2E75B6);border-radius:12px 12px 0 0;">
                <div class="rounded-circle bg-white mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:80px;height:80px;font-size:36px;color:#1F4E79;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h5 class="text-white mb-1"><?=htmlspecialchars($student['full_name'])?></h5>
                <p class="text-white-50 small mb-0"><?=htmlspecialchars($student['program'])?></p>
            </div>
            <div class="data-card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted fw-bold" style="width:130px">Roll Number</td>
                        <td><strong><?=htmlspecialchars($student['roll_number'])?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold">Email</td>
                        <td><?=htmlspecialchars($student['email'])?></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold">Program</td>
                        <td><?=htmlspecialchars($student['program'])?></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold">Semester</td>
                        <td><?=$student['semester']?></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold">Admission Year</td>
                        <td><?=$student['admission_year']?></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold">Status</td>
                        <td><span class="badge bg-<?=statusBadge($student['status'])?>"><?=ucfirst($student['status'])?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold">Joined</td>
                        <td><?=date('M d, Y', strtotime($student['created_at']))?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="data-card">
            <div class="data-card-header"><h6 class="data-card-title"><i class="bi bi-lock me-2"></i>Change Password</h6></div>
            <div class="data-card-body">
                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                        <div class="form-text">Minimum 8 characters.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="bi bi-lock me-1"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning">Profile not found.</div>
<?php endif; ?>

<?php include('../include/layout_end.php'); ?>
