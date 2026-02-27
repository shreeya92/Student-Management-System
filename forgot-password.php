<?php
session_start();
include("include/db.php");

function sendOtpEmail(string $toEmail, string $toName, string $otp): bool {
    $subject = "Password Reset OTP — SEMS";
    $message = "Hello $toName,\n\nYour OTP for password reset is: $otp\n\nValid for 10 minutes.\n\nSEMS";
    $headers = "From: no-reply@sems.com\r\nX-Mailer: PHP/" . phpversion();
    return mail($toEmail, $subject, $message, $headers);
}

$step   = $_SESSION['fp_step'] ?? 'email';
$errors = [];
$success = '';

// ── Restart ──────────────────────────────────────────────────────────────────
if (isset($_GET['restart'])) {
    unset($_SESSION['fp_step'], $_SESSION['fp_user_id'], $_SESSION['fp_email'], $_SESSION['fp_attempts']);
    header("Location: forgot-password.php"); exit();
}

// ── Step 1: Email ─────────────────────────────────────────────────────────────
if (isset($_POST['step']) && $_POST['step'] === 'email') {
    $email = trim(filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL));
    if (empty($email))                                   $errors['email'] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors['email'] = "Invalid email address.";

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE email=? AND status='approved' LIMIT 1");
        $stmt->bind_param("s",$email); $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$row) {
            // Generic message prevents email enumeration
            $success = "If that email is registered, an OTP has been sent.";
            $_SESSION['fp_step'] = 'email';
        } else {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $hash = password_hash($otp, PASSWORD_BCRYPT);
            $exp  = date('Y-m-d H:i:s', time() + 600);
            $s2 = $conn->prepare("INSERT INTO password_resets (user_id,token,expires_at) VALUES(?,?,?) ON DUPLICATE KEY UPDATE token=VALUES(token),expires_at=VALUES(expires_at)");
            $s2->bind_param("iss",$row['user_id'],$hash,$exp); $s2->execute(); $s2->close();
            sendOtpEmail($email, $row['full_name'], $otp);
            $_SESSION['fp_user_id']  = $row['user_id'];
            $_SESSION['fp_email']    = $email;
            $_SESSION['fp_step']     = 'otp';
            $_SESSION['fp_attempts'] = 0;
            header("Location: forgot-password.php"); exit();
        }
    }
}

// ── Step 2: OTP ───────────────────────────────────────────────────────────────
if (isset($_POST['step']) && $_POST['step'] === 'otp') {
    if (($_SESSION['fp_attempts']??0) >= 5) {
        unset($_SESSION['fp_step'],$_SESSION['fp_user_id'],$_SESSION['fp_email']);
        header("Location: forgot-password.php"); exit();
    }
    $otp = trim($_POST['otp'] ?? '');
    if (strlen($otp) !== 6 || !ctype_digit($otp)) $errors['otp'] = "Enter the 6-digit OTP.";
    if (empty($errors)) {
        $uid = $_SESSION['fp_user_id'];
        $stmt = $conn->prepare("SELECT token, expires_at FROM password_resets WHERE user_id=? LIMIT 1");
        $stmt->bind_param("i",$uid); $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$row)                                         $errors['otp'] = "OTP not found.";
        elseif (strtotime($row['expires_at']) < time())    $errors['otp'] = "OTP has expired. Request a new one.";
        elseif (!password_verify($otp, $row['token'])) {
            $_SESSION['fp_attempts']++;
            $errors['otp'] = "Invalid OTP. " . (5 - $_SESSION['fp_attempts']) . " attempts left.";
        } else {
            $_SESSION['fp_step'] = 'reset'; $_SESSION['fp_attempts'] = 0;
            header("Location: forgot-password.php"); exit();
        }
    }
}

// ── Resend OTP ────────────────────────────────────────────────────────────────
if (isset($_GET['resend']) && $step === 'otp' && isset($_SESSION['fp_user_id'])) {
    $uid   = $_SESSION['fp_user_id'];
    $email = $_SESSION['fp_email'];
    $uRow  = $conn->query("SELECT full_name FROM users WHERE user_id=$uid LIMIT 1")->fetch_assoc();
    $otp   = str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT);
    $hash  = password_hash($otp, PASSWORD_BCRYPT);
    $exp   = date('Y-m-d H:i:s', time()+600);
    $s2 = $conn->prepare("INSERT INTO password_resets (user_id,token,expires_at) VALUES(?,?,?) ON DUPLICATE KEY UPDATE token=VALUES(token),expires_at=VALUES(expires_at)");
    $s2->bind_param("iss",$uid,$hash,$exp); $s2->execute(); $s2->close();
    sendOtpEmail($email, $uRow['full_name'], $otp);
    $_SESSION['fp_attempts'] = 0;
    $success = "A new OTP has been sent.";
}

// ── Step 3: Reset Password ────────────────────────────────────────────────────
if (isset($_POST['step']) && $_POST['step'] === 'reset') {
    $pw  = $_POST['password']  ?? '';
    $pw2 = $_POST['password2'] ?? '';
    if (strlen($pw) < 8)  $errors['password'] = "Min. 8 characters required.";
    if (empty($errors) && $pw !== $pw2) $errors['password2'] = "Passwords do not match.";
    if (empty($errors)) {
        $uid  = $_SESSION['fp_user_id'];
        $hash = password_hash($pw, PASSWORD_BCRYPT, ['cost'=>12]);
        $s = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $s->bind_param("si",$hash,$uid); $s->execute(); $s->close();
        $conn->query("DELETE FROM password_resets WHERE user_id=$uid");
        unset($_SESSION['fp_step'],$_SESSION['fp_user_id'],$_SESSION['fp_email'],$_SESSION['fp_attempts']);
        $_SESSION['fp_step'] = 'done';
        header("Location: forgot-password.php"); exit();
    }
}

$step = $_SESSION['fp_step'] ?? 'email';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — SEMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
<div class="auth-card" style="max-width:420px">

    <!-- Progress -->
    <?php if ($step !== 'done'): ?>
    <div class="d-flex align-items-center justify-content-center mb-4 gap-2">
        <?php
        $steps = ['email'=>'1','otp'=>'2','reset'=>'3'];
        $active = array_search($step, array_keys($steps));
        $i = 0;
        foreach ($steps as $k => $n):
            $cls = $i < $active ? 'bg-success text-white' : ($i === $active ? 'bg-primary text-white' : 'bg-light text-muted border');
        ?>
        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;<?=$cls?includes('success')?'background:#198754':''.($cls?includes('primary')?'background:#1F4E79':'')??>font-size:13px" class="<?=$cls?>"><?=$i<$active?'✓':$n?></div>
        <?php if ($i < 2): ?><div style="width:40px;height:2px;background:<?=$i<$active?'#198754':'#dee2e6'?>"></div><?php endif; ?>
        <?php $i++; endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="text-center mb-3">
        <a class="auth-logo text-decoration-none" href="loginpage.php">SEMS</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible">
        <?php foreach($errors as $e): ?><div><?=htmlspecialchars($e)?></div><?php endforeach; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-success"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>

    <?php if ($step === 'email'): ?>
    <h5 class="text-center mb-1 fw-bold">Forgot Password?</h5>
    <p class="text-center text-muted small mb-4">Enter your registered email to receive an OTP.</p>
    <form method="POST"><input type="hidden" name="step" value="email">
        <div class="mb-3">
            <label class="form-label fw-bold">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send OTP</button>
    </form>

    <?php elseif ($step === 'otp'): ?>
    <h5 class="text-center mb-1 fw-bold">Enter OTP</h5>
    <p class="text-center text-muted small mb-4">Sent to <strong><?=htmlspecialchars($_SESSION['fp_email']??'')?></strong></p>
    <form method="POST" id="otpForm"><input type="hidden" name="step" value="otp">
        <input type="hidden" name="otp" id="otpHidden">
        <div class="d-flex gap-2 justify-content-center mb-3" id="otpBoxes">
            <?php for($i=0;$i<6;$i++): ?>
            <input type="text" maxlength="1" class="form-control text-center fw-bold fs-4 otp-digit"
                   inputmode="numeric" style="width:48px;height:54px;padding:0">
            <?php endfor; ?>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-2">Verify OTP</button>
    </form>
    <p class="text-center small text-muted">
        <span id="resendTimer"></span>
        <a href="forgot-password.php?resend=1" id="resendLink" style="display:none">Resend OTP</a>
    </p>
    <p class="text-center small"><a href="forgot-password.php?restart=1">Use different email</a></p>

    <?php elseif ($step === 'reset'): ?>
    <h5 class="text-center mb-1 fw-bold">Set New Password</h5>
    <p class="text-center text-muted small mb-4">Choose a strong password.</p>
    <form method="POST"><input type="hidden" name="step" value="reset">
        <div class="mb-3">
            <label class="form-label fw-bold">New Password</label>
            <input type="password" name="password" class="form-control" minlength="8" required placeholder="Min. 8 characters">
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold">Confirm Password</label>
            <input type="password" name="password2" class="form-control" required placeholder="Re-enter password">
        </div>
        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>

    <?php elseif ($step === 'done'): ?>
    <?php unset($_SESSION['fp_step']); ?>
    <div class="text-center py-3">
        <div style="font-size:60px">✅</div>
        <h5 class="fw-bold mt-2">Password Reset!</h5>
        <p class="text-muted">Your password has been updated successfully.</p>
        <a href="loginpage.php" class="btn btn-success w-100">Back to Login</a>
    </div>
    <?php endif; ?>

    <?php if ($step === 'email'): ?>
    <p class="text-center small text-muted mt-3"><a href="loginpage.php">Back to Login</a></p>
    <?php endif; ?>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const digits = document.querySelectorAll('.otp-digit');
const hidden = document.getElementById('otpHidden');
if (digits.length) {
    digits.forEach((box, i) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '');
            if (box.value && i < 5) digits[i+1].focus();
            if (hidden) hidden.value = Array.from(digits).map(d=>d.value).join('');
        });
        box.addEventListener('keydown', e => { if (e.key==='Backspace' && !box.value && i>0) digits[i-1].focus(); });
        box.addEventListener('paste', e => {
            e.preventDefault();
            const p = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
            p.split('').forEach((c,j)=>{ if(digits[j]) digits[j].value=c; });
            if(hidden) hidden.value=p;
            digits[Math.min(p.length,5)].focus();
        });
    });
}
// Resend countdown
const resendLink = document.getElementById('resendLink');
const timerEl    = document.getElementById('resendTimer');
if (timerEl) {
    let s = 60;
    (function tick() {
        if (s > 0) { timerEl.textContent = `Resend in ${s}s`; s--; setTimeout(tick, 1000); }
        else { timerEl.textContent=''; if(resendLink) resendLink.style.display='inline'; }
    })();
}
</script>
</body>
</html>
