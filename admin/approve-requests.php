<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('admin');

// ── Handle approve / reject ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['request_id'])) {
    $requestId = (int)$_POST['request_id'];
    $action    = $_POST['action'];

    // Fetch the request
    $stmt = $conn->prepare("SELECT * FROM student_registration_requests WHERE request_id = ? LIMIT 1");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$req) { redirectWith('approve-requests.php', 'danger', 'Request not found.'); }

    if ($action === 'approve' && $req['status'] === 'pending') {
        // 1. Create user account with a default password (admin should share it)
        $defaultPass = password_hash('sems@1234', PASSWORD_BCRYPT, ['cost' => 12]);
        $stmtU = $conn->prepare(
            "INSERT INTO users (full_name, email, password, role, status, created_at)
             VALUES (?, ?, ?, 'student', 'approved', NOW())"
        );
        $stmtU->bind_param("sss", $req['full_name'], $req['email'], $defaultPass);
        $stmtU->execute();
        $userId = $conn->insert_id;
        $stmtU->close();

        // 2. Create student profile from POST data
        $roll     = 'STU' . str_pad($userId, 5, '0', STR_PAD_LEFT);
        $program  = $_POST['program']        ?? 'Unknown';
        $semester = (int)($_POST['semester'] ?? 1);
        $admYear  = (int)($_POST['adm_year'] ?? date('Y'));
        $docPath  = $req['document_path'];

        $stmtS = $conn->prepare(
            "INSERT INTO students (user_id, roll_number, program, semester, admission_year, document_path)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmtS->bind_param("ississ", $userId, $roll, $program, $semester, $admYear, $docPath);
        $stmtS->execute();
        $stmtS->close();

        // 3. Update request status
        $stmtR = $conn->prepare("UPDATE student_registration_requests SET status='approved' WHERE request_id=?");
        $stmtR->bind_param("i", $requestId);
        $stmtR->execute();
        $stmtR->close();

        redirectWith('approve-requests.php', 'success', "Request approved. Account created. Default password: sems@1234");

    } elseif ($action === 'reject' && $req['status'] === 'pending') {
        $stmtR = $conn->prepare("UPDATE student_registration_requests SET status='rejected' WHERE request_id=?");
        $stmtR->bind_param("i", $requestId);
        $stmtR->execute();
        $stmtR->close();
        redirectWith('approve-requests.php', 'warning', "Request rejected.");
    }
}

$pageTitle  = 'Registration Requests';
$activeMenu = 'requests';

$filter  = $_GET['filter'] ?? 'pending';
$allowed = ['pending','approved','rejected','all'];
if (!in_array($filter, $allowed)) $filter = 'pending';

$sql = "SELECT * FROM student_registration_requests";
if ($filter !== 'all') $sql .= " WHERE status = '$filter'";
$sql .= " ORDER BY submitted_at DESC";
$requests = $conn->query($sql);

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<div class="data-card">
    <div class="data-card-header">
        <h6 class="data-card-title"><i class="bi bi-person-check me-2"></i>Registration Requests</h6>
        <div class="d-flex gap-2">
            <?php foreach (['pending'=>'warning','approved'=>'success','rejected'=>'danger','all'=>'secondary'] as $f => $cls): ?>
            <a href="?filter=<?= $f ?>" class="btn btn-sm btn-<?= $filter === $f ? $cls : 'outline-' . $cls ?>">
                <?= ucfirst($f) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Document</th><th>Submitted</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php if ($requests && $requests->num_rows > 0): ?>
                <?php $i = 1; while ($row = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <?php if ($row['document_path']): ?>
                            <a href="../<?= htmlspecialchars($row['document_path']) ?>" target="_blank" class="btn btn-xs btn-outline-primary btn-sm">
                                <i class="bi bi-file-earmark me-1"></i>View
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M d, Y', strtotime($row['submitted_at'])) ?></td>
                    <td><span class="badge bg-<?= statusBadge($row['status']) ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                        <!-- Approve Modal Trigger -->
                        <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveModal<?= $row['request_id'] ?>">
                            <i class="bi bi-check-lg"></i> Approve
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-sm btn-danger" data-confirm="Reject this request?">
                                <i class="bi bi-x-lg"></i> Reject
                            </button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Approve Modal -->
                <?php if ($row['status'] === 'pending'): ?>
                <div class="modal fade" id="approveModal<?= $row['request_id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Approve: <?= htmlspecialchars($row['full_name']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Program</label>
                                        <select name="program" class="form-select" required>
                                            <?php foreach (['BCA','BBA','BSc CSIT','BIM','BE Computer','BE IT','MBA','MCA','MSc CSIT'] as $p): ?>
                                                <option><?= $p ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label fw-bold">Semester</label>
                                            <select name="semester" class="form-select" required>
                                                <?php for ($s=1;$s<=8;$s++): ?><option value="<?=$s?>"><?=$s?></option><?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-bold">Admission Year</label>
                                            <input type="number" name="adm_year" class="form-control"
                                                   value="<?= date('Y') ?>" min="2000" max="<?= date('Y') ?>">
                                        </div>
                                    </div>
                                    <p class="mt-3 text-muted small mb-0">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Default password will be set to: <strong>sems@1234</strong> — please inform the student.
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Approve & Create Account</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No <?= $filter ?> requests found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('../include/layout_end.php'); ?>
