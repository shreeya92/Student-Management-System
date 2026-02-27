<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('student');

$pageTitle  = 'My Results';
$activeMenu = 'results';
$userId     = (int)$_SESSION['user_id'];

$student = $conn->query(
    "SELECT st.*, u.full_name FROM students st
     JOIN users u ON st.user_id=u.user_id WHERE st.user_id=$userId LIMIT 1"
)->fetch_assoc();
$sid = $student ? $student['student_id'] : 0;

$viewExamId = (int)($_GET['exam_id'] ?? 0);

// All published results for this student
$results = $conn->query(
    "SELECT r.*, e.exam_name, e.semester, e.year FROM results r
     JOIN exams e ON r.exam_id=e.exam_id
     WHERE r.student_id=$sid AND e.status='published'
     ORDER BY e.year DESC, e.semester DESC"
);

// Subject-wise marks if viewing detail
$subjectMarks = [];
if ($viewExamId) {
    $m = $conn->query(
        "SELECT m.marks_obtained, m.grade, s.subject_name, s.subject_code
         FROM marks m JOIN subjects s ON m.subject_id=s.subject_id
         WHERE m.student_id=$sid AND m.exam_id=$viewExamId AND m.status='submitted'
         ORDER BY s.subject_name"
    );
    while ($row = $m->fetch_assoc()) $subjectMarks[] = $row;
}

include('../include/layout.php');
?>

<?php renderFlash(); ?>

<?php if ($student): ?>

<!-- Results List -->
<div class="row g-4">
    <div class="col-md-4">
        <div class="data-card">
            <div class="data-card-header"><h6 class="data-card-title">Published Results</h6></div>
            <div class="list-group list-group-flush">
            <?php if ($results && $results->num_rows > 0): while ($row=$results->fetch_assoc()): ?>
            <a href="?exam_id=<?=$row['exam_id']?>"
               class="list-group-item list-group-item-action <?=$viewExamId==$row['exam_id']?'active':''?> d-flex justify-content-between align-items-center px-3 py-3">
                <div>
                    <div class="fw-bold small"><?=htmlspecialchars($row['exam_name'])?></div>
                    <div class="small <?=$viewExamId==$row['exam_id']?'text-white-50':'text-muted'?>">Sem <?=$row['semester']?> — <?=$row['year']?></div>
                </div>
                <span class="badge bg-<?=$row['result_status']==='pass'?'success':'danger'?>">
                    <?=strtoupper($row['result_status'])?>
                </span>
            </a>
            <?php endwhile; else: ?>
            <div class="p-3 text-muted text-center">No results yet.</div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($viewExamId): ?>
    <?php
    $resultDetail = $conn->query(
        "SELECT r.*, e.exam_name, e.semester, e.year FROM results r
         JOIN exams e ON r.exam_id=e.exam_id
         WHERE r.student_id=$sid AND r.exam_id=$viewExamId LIMIT 1"
    )->fetch_assoc();
    ?>
    <div class="col-md-8">
        <div class="data-card print-section">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold"><?=htmlspecialchars($resultDetail['exam_name'])?></h5>
                    <span class="text-muted small">Semester <?=$resultDetail['semester']?> — <?=$resultDetail['year']?></span>
                </div>
                <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
            </div>

            <!-- Summary row -->
            <div class="p-4 border-bottom">
                <div class="row g-3">
                    <div class="col-4 text-center">
                        <div style="font-size:28px;font-weight:800;color:#1F4E79"><?=number_format($resultDetail['total_marks'],1)?></div>
                        <div class="text-muted small">Total Marks</div>
                    </div>
                    <div class="col-4 text-center">
                        <div style="font-size:28px;font-weight:800;color:#2E75B6"><?=number_format($resultDetail['gpa'],2)?></div>
                        <div class="text-muted small">GPA</div>
                    </div>
                    <div class="col-4 text-center">
                        <span class="badge fs-5 px-4 py-2 bg-<?=$resultDetail['result_status']==='pass'?'success':'danger'?>">
                            <?=strtoupper($resultDetail['result_status'])?>
                        </span>
                        <div class="text-muted small mt-1">Result</div>
                    </div>
                </div>
            </div>

            <!-- Subject-wise marks -->
            <div class="p-4">
                <h6 class="fw-bold mb-3">Subject-wise Marks</h6>
                <?php if (!empty($subjectMarks)): ?>
                <table class="table table-bordered table-sm">
                    <thead class="table-dark">
                        <tr><th>Subject</th><th>Code</th><th>Marks</th><th>Grade</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($subjectMarks as $m): ?>
                    <tr>
                        <td><?=htmlspecialchars($m['subject_name'])?></td>
                        <td><code><?=htmlspecialchars($m['subject_code'])?></code></td>
                        <td><?=number_format($m['marks_obtained'],1)?> / 100</td>
                        <td>
                            <span class="badge bg-<?=in_array($m['grade'],['A+','A','B+'])?'success':(in_array($m['grade'],['B','C','D'])?'warning text-dark':'danger')?> ">
                                <?=$m['grade']?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted">Subject-wise marks not available.</p>
                <?php endif; ?>

                <p class="text-muted small mt-3 mb-0">
                    Published on: <?=date('M d, Y',strtotime($resultDetail['published_date']))?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php else: ?>
<div class="alert alert-warning">Student profile not found.</div>
<?php endif; ?>

<?php include('../include/layout_end.php'); ?>
