<?php
session_start();
$success = '';
$errors  = [];
if (isset($_SESSION['reg_error']))   { $errors  = $_SESSION['reg_error'];   unset($_SESSION['reg_error']); }
if (isset($_SESSION['reg_success'])) { $success = $_SESSION['reg_success']; unset($_SESSION['reg_success']); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — SEMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper" style="align-items:flex-start;padding-top:40px;">
    <div class="auth-card" style="max-width:540px;">
        <div class="text-center mb-4">
            <div class="auth-logo">SEMS</div>
            <p class="auth-subtitle">Student Registration Request</p>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="register-process.php" enctype="multipart/form-data" novalidate>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="Your full name" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="your@email.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Program <span class="text-danger">*</span></label>
                    <select name="program" class="form-select" required>
                        <option value="">-- Select Program --</option>
                        <?php
                        $programs = ['BCA','BBA','BSc CSIT','BIM','BE Computer','BE IT','MBA','MCA','MSc CSIT'];
                        $sel = $_POST['program'] ?? '';
                        foreach ($programs as $pr) {
                            echo '<option value="' . htmlspecialchars($pr) . '"' . ($sel === $pr ? ' selected' : '') . '>' . htmlspecialchars($pr) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                    <select name="semester" class="form-select" required>
                        <option value="">--</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>" <?= (($_POST['semester'] ?? '') == $i) ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Admission Year <span class="text-danger">*</span></label>
                    <input type="number" name="admission_year" class="form-control"
                           value="<?= htmlspecialchars($_POST['admission_year'] ?? date('Y')) ?>"
                           min="2000" max="<?= date('Y') + 1 ?>" placeholder="2024">
                </div>
                <div class="col-12">
                    <label class="form-label">Supporting Document <span class="text-danger">*</span>
                        <small class="text-muted">(PDF, JPG, PNG — max 2MB)</small>
                    </label>
                    <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-4 py-2 fw-bold">
                <i class="bi bi-send me-2"></i>Submit Registration Request
            </button>
        </form>

        <p class="text-center text-muted small mt-3 mb-0">
            Already have an account? <a href="loginpage.php" class="text-primary fw-semibold text-decoration-none">Login</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
