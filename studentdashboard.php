<?php
session_start();

// If student not logged in, redirect to registration
if (!isset($_SESSION['username'])) {
    header("location:registrationforstudent.php");
    exit();
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "Student_Management_System", 3366);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Fetch student data from DB using session username
$username = $_SESSION['username'];
$sql = "SELECT * FROM student WHERE username = '$username'";
$result = $conn->query($sql);
$student = $result->fetch_assoc();
$conn->close();

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("location:registrationforstudent.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - SMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

       
        .navbar {
            background: linear-gradient(135deg, #6a1b9a, #ab47bc);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .navbar .logo {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .navbar .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar .nav-right span {
            font-size: 15px;
        }

        .logout-btn {
            background: white;
            color: #6a1b9a;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #f3e5f5;
        }

        .layout {
            display: flex;
            min-height: calc(100vh - 60px);
        }

        .sidebar {
            width: 220px;
            background: white;
            padding: 25px 0;
            box-shadow: 2px 0 8px rgba(0,0,0,0.08);
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li a {
            display: block;
            padding: 14px 25px;
            text-decoration: none;
            color: #555;
            font-size: 15px;
            border-left: 4px solid transparent;
            transition: 0.2s;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: #f3e5f5;
            color: #6a1b9a;
            border-left: 4px solid #6a1b9a;
            font-weight: bold;
        }

        .sidebar ul li a span {
            margin-right: 10px;
        }

        .main {
            flex: 1;
            padding: 30px;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #6a1b9a, #ab47bc);
            color: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .welcome-banner h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .welcome-banner p {
            font-size: 14px;
            opacity: 0.9;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 22px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-top: 4px solid #ab47bc;
        }

        .card .icon {
            font-size: 35px;
            margin-bottom: 10px;
        }

        .card h3 {
            font-size: 22px;
            color: #6a1b9a;
        }

        .card p {
            font-size: 13px;
            color: #888;
            margin-top: 4px;
        }

       
        .profile-box {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .profile-box h3 {
            color: #6a1b9a;
            font-size: 18px;
            margin-bottom: 20px;
            border-bottom: 2px solid #f3e5f5;
            padding-bottom: 10px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .profile-item label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
        }

        .profile-item p {
            font-size: 15px;
            color: #333;
            font-weight: bold;
        }

       
        .notice-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .notice-box h3 {
            color: #6a1b9a;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 2px solid #f3e5f5;
            padding-bottom: 10px;
        }

        .notice-item {
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
            font-size: 14px;
            color: #444;
        }

        .notice-item span {
            font-size: 11px;
            color: #aaa;
            float: right;
        }

        .notice-item:last-child {
            border-bottom: none;
        }

       
        .avatar {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #6a1b9a;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">🎓 Student Management System</div>
    <div class="nav-right">
        <div class="avatar">
            <?php echo strtoupper(substr($student['username'], 0, 1)); ?>
        </div>
        <span>Welcome, <strong><?php echo htmlspecialchars($student['username']); ?></strong></span>
        <a href="?logout=1" class="logout-btn">🚪 Logout</a>
    </div>
</div>

<!-- LAYOUT -->
<div class="layout">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <ul>
            <li><a href="dashboard.php" class="active"><span>🏠</span> Dashboard</a></li>
            <li><a href="#"><span>👤</span> My Profile</a></li>
            <li><a href="#"><span>📋</span> My Results</a></li>
            <li><a href="#"><span>📅</span> Routine</a></li>
            <li><a href="#"><span>🎫</span> Admit Card</a></li>
            <li><a href="#"><span>🎫</span> Report</a></li>
            <li><a href="#"><span>📢</span> Notices</a></li>
            <li><a href="?logout=1"><span>🚪</span> Logout</a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h2>👋 Hello, <?php echo htmlspecialchars($student['username']); ?>!</h2>
            <p>Welcome to your Student Dashboard — <?php echo htmlspecialchars($student['faculty']); ?> Program</p>
        </div>

        <!-- Summary Cards -->
        <div class="cards">
            <div class="card">
                <div class="icon">📋</div>
                <h3>View</h3>
                <p>My Results</p>
            </div>
            <div class="card">
                <div class="icon">📅</div>
                <h3>Class</h3>
                <p>Routine</p>
            </div>
            <div class="card">
                <div class="icon">🎫</div>
                <h3>Admit</h3>
                <p>Card</p>
            </div>
        </div>

        <!-- Student Profile Info -->
        <div class="profile-box">
            <h3>📄 My Profile</h3>
            <div class="profile-grid">
                <div class="profile-item">
                    <label>Username</label>
                    <p><?php echo htmlspecialchars($student['username']); ?></p>
                </div>
                <div class="profile-item">
                    <label>Email</label>
                    <p><?php echo htmlspecialchars($student['email']); ?></p>
                </div>
                <div class="profile-item">
                    <label>Address</label>
                    <p><?php echo htmlspecialchars($student['address']); ?></p>
                </div>
                <div class="profile-item">
                    <label>Gender</label>
                    <p><?php echo ucfirst(htmlspecialchars($student['gender'])); ?></p>
                </div>
                <div class="profile-item">
                    <label>Date of Birth</label>
                    <p><?php echo htmlspecialchars($student['dob']); ?></p>
                </div>
                <div class="profile-item">
                    <label>Contact</label>
                    <p><?php echo htmlspecialchars($student['contact']); ?></p>
                </div>
                <div class="profile-item">
                    <label>Faculty</label>
                    <p><?php echo htmlspecialchars($student['faculty']); ?></p>
                </div>
            </div>
        </div>

        <!-- Notices -->
        <div class="notice-box">
            <h3>📢 Recent Notices</h3>
            <div class="notice-item">
                📌 4th Semester Examination Form submission starts from Falgun 15
                <span>2082-05-01</span>
            </div>
            <div class="notice-item">
                📌 College will remain closed on Falgun 20 due to national holiday
                <span>2082-04-28</span>
            </div>
            <div class="notice-item">
                📌 Project submission deadline extended to Chaitra 1
                <span>2082-04-20</span>
            </div>
        </div>

    </div>
</div>

</body>
</html>