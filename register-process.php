<?php
session_start();
include("include/db.php");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php"); exit();
}

$full_name      = trim($_POST['full_name']      ?? '');
$email          = trim($_POST['email']          ?? '');
$program        = trim($_POST['program']        ?? '');
$semester       = (int)($_POST['semester']      ?? 0);
$admission_year = (int)($_POST['admission_year'] ?? 0);

if (empty($full_name))                               $errors[] = "Full name is required.";
if (empty($email))                                   $errors[] = "Email is required.";
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = "Invalid email format.";
if (empty($program))                                 $errors[] = "Program is required.";
if ($semester < 1 || $semester > 8)                 $errors[] = "Valid semester (1-8) is required.";
if ($admission_year < 2000 || $admission_year > date('Y') + 1) $errors[] = "Valid admission year is required.";

// Check email not already submitted
if (empty($errors)) {
    $chk = $conn->prepare("SELECT request_id FROM student_registration_requests WHERE email = ? LIMIT 1");
    $chk->bind_param("s", $email);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) $errors[] = "A request with this email already exists.";
    $chk->close();

    // Also check users table
    $chk2 = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
    $chk2->bind_param("s", $email);
    $chk2->execute();
    if ($chk2->get_result()->num_rows > 0) $errors[] = "An account with this email already exists.";
    $chk2->close();
}

// File upload
$docPath = null;
if (empty($errors)) {
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Please upload a supporting document.";
    } else {
        $file     = $_FILES['document'];
        $maxSize  = 2 * 1024 * 1024; // 2MB
        $allowed  = ['image/jpeg','image/png','application/pdf'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mimeType = mime_content_type($file['tmp_name']);

        if ($file['size'] > $maxSize)              $errors[] = "File size must be under 2MB.";
        elseif (!in_array($mimeType, $allowed))    $errors[] = "Only PDF, JPG, PNG files are allowed.";
        else {
            $newName = bin2hex(random_bytes(16)) . '.' . $ext;
            $destDir = __DIR__ . '/uploads/documents/';
            if (!is_dir($destDir)) mkdir($destDir, 0755, true);
            if (!move_uploaded_file($file['tmp_name'], $destDir . $newName)) {
                $errors[] = "File upload failed. Please try again.";
            } else {
                $docPath = 'uploads/documents/' . $newName;
            }
        }
    }
}

if (!empty($errors)) {
    $_SESSION['reg_error'] = $errors;
    header("Location: register.php");
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO student_registration_requests (full_name, email, document_path, status, submitted_at)
     VALUES (?, ?, ?, 'pending', NOW())"
);
$stmt->bind_param("sss", $full_name, $email, $docPath);
$stmt->execute();
$stmt->close();

// Also store extra data in session so admin can use it on approval
$_SESSION['reg_success'] = "Your registration request has been submitted successfully. Please wait for admin approval before logging in.";
header("Location: register.php");
exit();
