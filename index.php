<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin')   { header("Location: admin/dashboardadmin.php"); exit(); }
    if ($role === 'teacher') { header("Location: teacher/teacherdashboard.php"); exit(); }
    if ($role === 'student') { header("Location: student/studentdashboard.php"); exit(); }
}
header("Location: loginpage.php");
exit();
