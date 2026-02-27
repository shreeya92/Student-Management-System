<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('admin');

// ── Generate results for a completed exam ────────────────────────────────────
if (isset($_GET['generate'])) {
    $eid = (int)$_GET['generate'];
    $exam = $conn->query("SELECT * FROM exams WHERE exam_id=$eid AND status='completed' LIMIT 1")->fetch_assoc();

    if ($exam) {
        $sem      = $exam['semester'];
        // Count subjects for this exam (to compute full marks = subjects * 100)
        $subCount = (int)$conn->query(
            "SELECT COUNT(DISTINCT subject_id) c FROM exam_routine WHERE exam_id=$eid"
        )->fetch_assoc()['c'];
        $fullTotal = $subCount * 100;

        $students = $conn->query(
            "SELECT st.student_id FROM students st
             JOIN users u ON st.user_id=u.user_id
             WHERE st.semester=$sem AND u.status='approved'"
        );
        $today = date('Y-m-d');
        $generated = 0;

        while ($st = $students->fetch_assoc()) {
            $sid = $st['student_id'];
            // Sum submitted marks
            $r = $conn->query(
                "SELECT SUM(marks_obtained) total FROM marks
                 WHERE exam_id=$eid AND student_id=$sid AND status='submitted'"
            )->fetch_assoc();
            $total = (float)($r['total'] ?? 0);
            $gpa   = computeGPA($total, max($fullTotal, 1));
            $pct   = $fullTotal > 0 ? ($total / $fullTotal) * 100 : 0;
            $status = $pct >= 40 ? 'pass' : 'fail';

            $s = $conn->prepare(
                "INSERT INTO results (student_id,exam_id,total_marks,gpa,result_status,published_date)
                 VALUES(?,?,?,?,'$status','$today')
                 ON DUPLICATE KEY UPDATE total_marks=VALUES(total_marks),gpa=VALUES(gpa),
                 result_status=VALUES(result_status)"
            );
            $s->bind_param("iidd",$sid,$eid,$total,$gpa); $s->execute(); $s->close();
            $generated++;
        }

        // Mark exam as published
        $conn->query("UPDATE exams SET status='published' WHERE exam_id=$eid");
        redirectWith('publish-results.php','success',"Results generated and published for $generated students.");
    } else {
        redirectWith('publish-results.php','danger',"Exam not found or not yet marked as completed.");
    }
}

$pageTitle  = 'Results Management';
$activeMenu = 'results';
$exams = $conn->query("SELECT * FROM exams ORDER BY exam_id DESC");

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<div class="data-card">
    <div class="data-card-header">
        <h6 class="data-card-title"><i class="bi bi-trophy me-2"></i>Exam Results Overview</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Exam</th><th>Semester</th><th>Year</th><th>Status</th><th>Students Passed</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($exams && $exams->num_rows > 0): while ($row=$exams->fetch_assoc()): ?>
            <?php
            $passCount = (int)$conn->query(
                "SELECT COUNT(*) c FROM results WHERE exam_id={$row['exam_id']} AND result_status='pass'"
            )->fetch_assoc()['c'];
            $totalRes = (int)$conn->query(
                "SELECT COUNT(*) c FROM results WHERE exam_id={$row['exam_id']}"
            )->fetch_assoc()['c'];
            ?>
            <tr>
                <td><?=htmlspecialchars($row['exam_name'])?></td>
                <td>Sem <?=$row['semester']?></td>
                <td><?=$row['year']?></td>
                <td><span class="badge bg-<?=statusBadge($row['status'])?>"><?=ucfirst($row['status'])?></span></td>
                <td>
                    <?php if ($totalRes > 0): ?>
                        <span class="text-success fw-bold"><?=$passCount?></span> / <?=$totalRes?> passed
                    <?php else: ?>
                        <span class="text-muted">No results yet</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['status'] === 'completed'): ?>
                    <a href="?generate=<?=$row['exam_id']?>" class="btn btn-sm btn-primary"
                       onclick="return confirm('Generate and publish results for this exam?')">
                        <i class="bi bi-play-fill me-1"></i>Generate & Publish
                    </a>
                    <?php elseif ($row['status'] === 'published'): ?>
                    <a href="view-results-detail.php?exam_id=<?=$row['exam_id']?>" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-eye me-1"></i>View Results
                    </a>
                    <?php else: ?>
                    <span class="text-muted small">Mark exam as Completed first</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No exams found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('../include/layout_end.php'); ?>
