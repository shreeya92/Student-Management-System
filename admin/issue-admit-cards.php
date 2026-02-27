<?php
session_start();
include('../include/auth.php');
include('../include/db.php');
include('../include/functions.php');
requireRole('admin');

$examId = (int)($_GET['exam_id'] ?? 0);

// ── Bulk issue / toggle eligibility ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_all'])) {
    $eid = (int)$_POST['exam_id'];
    // Get exam semester
    $er = $conn->query("SELECT semester FROM exams WHERE exam_id=$eid LIMIT 1")->fetch_assoc();
    if ($er) {
        $sem = $er['semester'];
        $students = $conn->query("SELECT student_id FROM students WHERE semester=$sem");
        $today = date('Y-m-d');
        while ($st = $students->fetch_assoc()) {
            $sid = $st['student_id'];
            $s = $conn->prepare(
                "INSERT INTO admit_cards (student_id,exam_id,issue_date,eligibility_status)
                 VALUES(?,?,?,1) ON DUPLICATE KEY UPDATE issue_date=VALUES(issue_date), eligibility_status=1"
            );
            $s->bind_param("iis",$sid,$eid,$today); $s->execute(); $s->close();
        }
        redirectWith("issue-admit-cards.php?exam_id=$eid",'success',"Admit cards issued to all eligible students.");
    }
}

if (isset($_GET['toggle'], $_GET['sid'])) {
    $eid = (int)$_GET['exam_id'];
    $sid = (int)$_GET['sid'];
    $cur = $conn->query("SELECT eligibility_status FROM admit_cards WHERE student_id=$sid AND exam_id=$eid LIMIT 1")->fetch_assoc();
    $newVal = $cur ? ($cur['eligibility_status'] ? 0 : 1) : 1;
    $today  = date('Y-m-d');
    $s = $conn->prepare("INSERT INTO admit_cards (student_id,exam_id,issue_date,eligibility_status) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE eligibility_status=?");
    $s->bind_param("iisii",$sid,$eid,$today,$newVal,$newVal); $s->execute(); $s->close();
    redirectWith("issue-admit-cards.php?exam_id=$eid",'success',"Eligibility updated.");
}

$pageTitle  = 'Issue Admit Cards';
$activeMenu = 'admitcards';
$exams = $conn->query("SELECT * FROM exams ORDER BY exam_id DESC");
$exam  = null; $students = [];

if ($examId) {
    $e = $conn->query("SELECT * FROM exams WHERE exam_id=$examId LIMIT 1")->fetch_assoc();
    $exam = $e;
    if ($exam) {
        $sem = $exam['semester'];
        $r = $conn->query(
            "SELECT st.student_id, u.full_name, st.roll_number, st.program,
                    ac.eligibility_status, ac.issue_date
             FROM students st
             JOIN users u ON st.user_id=u.user_id AND u.status='approved'
             LEFT JOIN admit_cards ac ON ac.student_id=st.student_id AND ac.exam_id=$examId
             WHERE st.semester=$sem ORDER BY st.roll_number"
        );
        while ($row = $r->fetch_assoc()) $students[] = $row;
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
                        <option value="">-- Select Exam --</option>
                        <?php while ($e=$exams->fetch_assoc()): ?>
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

<?php if ($exam && !empty($students)): ?>
<div class="data-card">
    <div class="data-card-header">
        <h6 class="data-card-title">
            Students — <?=htmlspecialchars($exam['exam_name'])?> (Semester <?=$exam['semester']?>)
        </h6>
        <form method="POST" class="d-inline">
            <input type="hidden" name="exam_id" value="<?=$examId?>">
            <button type="submit" name="issue_all" class="btn btn-success btn-sm"
                    onclick="return confirm('Issue admit cards to ALL students in this semester?')">
                <i class="bi bi-card-text me-1"></i>Issue All
            </button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Roll No</th><th>Name</th><th>Program</th><th>Issued</th><th>Eligibility</th><th>Toggle</th></tr></thead>
            <tbody>
            <?php foreach ($students as $row): ?>
            <tr>
                <td><?=htmlspecialchars($row['roll_number'])?></td>
                <td><?=htmlspecialchars($row['full_name'])?></td>
                <td><?=htmlspecialchars($row['program'])?></td>
                <td><?=$row['issue_date'] ? date('M d Y',strtotime($row['issue_date'])) : '<span class="text-muted">—</span>'?></td>
                <td>
                    <?php if ($row['issue_date'] === null): ?>
                        <span class="badge bg-secondary">Not Issued</span>
                    <?php elseif ($row['eligibility_status']): ?>
                        <span class="badge bg-success">Eligible</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Ineligible</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="?exam_id=<?=$examId?>&toggle=1&sid=<?=$row['student_id']?>"
                       class="btn btn-sm btn-outline-secondary">
                        <?=$row['eligibility_status'] ? 'Revoke' : 'Issue'?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php elseif ($exam): ?>
<div class="alert alert-info">No students found for Semester <?=$exam['semester']?>.</div>
<?php endif; ?>

<?php include('../include/layout_end.php'); ?>
