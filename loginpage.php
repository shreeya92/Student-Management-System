<?php
session_start();

// If already logged in, redirect to the right dashboard
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin')   { header("Location: admin/dashboardadmin.php");    exit(); }
    if ($role === 'teacher') { header("Location: teacher/teacherdashboard.php"); exit(); }
    if ($role === 'student') { header("Location: student/studentdashboard.php"); exit(); }
}

// Pull and clear session errors
$errors = [];
if (isset($_SESSION['error'])) {
    $errors = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SEMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Brand -->
        <div class="text-center mb-4">
            <div class="auth-logo">SEMS</div>
            <p class="auth-subtitle">Student Exam Management System</p>
        </div>

        <!-- Session errors from login.php -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php foreach ($errors as $e): ?>
                <div><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login.php" id="loginForm" novalidate>

            <div class="mb-3">
                <label class="form-label" for="email">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control"
                           placeholder="your@email.com"
                           autocomplete="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="text-danger small mt-1" id="emailErr"></div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control"
                           placeholder="••••••••"
                           autocomplete="current-password">
                    <button class="btn btn-outline-secondary" type="button" id="togglePass" tabindex="-1">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <div class="text-danger small mt-1" id="passErr"></div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember_me" id="rememberMe">
                    <label class="form-check-label small" for="rememberMe">Remember me</label>
                </div>
                <a href="forgot-password.php" class="small text-primary text-decoration-none">
                    Forgot password?
                </a>
            </div>

            <button type="submit" name="submit-btn" class="btn btn-primary w-100 py-2 fw-bold">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>
        </form>

        <hr class="my-4">
        <p class="text-center text-muted small mb-0">
            Don't have an account?
            <a href="register.php" class="text-primary fw-semibold text-decoration-none">Register here</a>
        </p>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Toggle password visibility ────────────────────────────────────────────────
document.getElementById('togglePass').addEventListener('click', function () {
    const pwd  = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (pwd.type === 'password') {
        pwd.type   = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        pwd.type   = 'password';
        icon.className = 'bi bi-eye';
    }
});

// ── Client-side validation ────────────────────────────────────────────────────
document.getElementById('loginForm').addEventListener('submit', function (e) {
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const emailErr = document.getElementById('emailErr');
    const passErr  = document.getElementById('passErr');
    const emailPat = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let blocked = false;

    emailErr.textContent = '';
    passErr.textContent  = '';
    document.getElementById('email').classList.remove('is-invalid');
    document.getElementById('password').classList.remove('is-invalid');

    if (!email) {
        emailErr.textContent = 'Email address is required.';
        document.getElementById('email').classList.add('is-invalid');
        blocked = true;
    } else if (!emailPat.test(email)) {
        emailErr.textContent = 'Please enter a valid email address.';
        document.getElementById('email').classList.add('is-invalid');
        blocked = true;
    }

    if (!password) {
        passErr.textContent = 'Password is required.';
        document.getElementById('password').classList.add('is-invalid');
        blocked = true;
    } else if (password.length < 8) {
        passErr.textContent = 'Password must be at least 8 characters.';
        document.getElementById('password').classList.add('is-invalid');
        blocked = true;
    }

    if (blocked) e.preventDefault();
});
</script>
</body>
</html>
