<?php
session_start();
include("include/db.php");

$error = [];

if (!isset($_SESSION['login_attempts']))    $_SESSION['login_attempts']    = 0;
if (!isset($_SESSION['first_attempt_time'])) $_SESSION['first_attempt_time'] = time();

// Reset lockout after 15 minutes
if (time() - $_SESSION['first_attempt_time'] > 900) {
    $_SESSION['login_attempts']    = 0;
    $_SESSION['first_attempt_time'] = time();
}

if ($_SESSION['login_attempts'] >= 5) {
    $remaining = 900 - (time() - $_SESSION['first_attempt_time']);
    $error['lockout'] = "Too many failed attempts. Try again in " . ceil($remaining / 60) . " minute(s).";
    $_SESSION['error'] = $error;
    header("Location: loginpage.php");
    exit();
}

if (isset($_POST['submit-btn'])) {
    $email    = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $error['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error['email'] = "Please enter a valid email.";
    }
    if (empty($password)) {
        $error['password'] = "Password is required.";
    }

    if (empty($error)) {
        $stmt = $conn->prepare("SELECT user_id, full_name, password, role, status FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if ($row['status'] !== 'approved') {
                $_SESSION['login_attempts']++;
                $error['email'] = "Your account is not yet approved. Please wait for admin approval.";
            } elseif (password_verify($password, $row['password'])) {
                $_SESSION['login_attempts']    = 0;
                $_SESSION['first_attempt_time'] = time();
                session_regenerate_id(true);

                $_SESSION['user_id']   = $row['user_id'];
                $_SESSION['user_name'] = $row['full_name'];
                $_SESSION['user_role'] = $row['role'];
                unset($_SESSION['error']);

                switch ($row['role']) {
                    case 'admin':   header("Location: admin/dashboardadmin.php");    break;
                    case 'teacher': header("Location: teacher/teacherdashboard.php"); break;
                    case 'student': header("Location: student/studentdashboard.php"); break;
                    default:
                        $error['role'] = "Invalid user role.";
                        $_SESSION['error'] = $error;
                        header("Location: loginpage.php");
                }
                exit();
            } else {
                $_SESSION['login_attempts']++;
                $error['password'] = "Invalid password.";
            }
        } else {
            $_SESSION['login_attempts']++;
            $error['email'] = "No account found with that email.";
        }
        $stmt->close();
    }

    if (!empty($error)) {
        $_SESSION['error'] = $error;
        header("Location: loginpage.php");
        exit();
    }
}
header("Location: loginpage.php");
exit();
