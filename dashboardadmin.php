<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Student Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            background-color: #f4f6f9;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2 {
            margin-bottom: 30px;
            text-align: center;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
            border-radius: 5px;
            transition: 0.3s;
        }

        .sidebar ul li a:hover {
            background-color: #1abc9c;
        }

        /* Main Content */
        .main {
            margin-left: 250px;
            width: 100%;
            padding: 20px;
        }

        .topbar {
            background: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .topbar h3 {
            color: #2c3e50;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        /* Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            text-align: center;
        }

        .card h2 {
            margin-bottom: 10px;
            color: #3498db;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #3498db;
            color: white;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        @media(max-width: 768px){
            .sidebar {
                width: 200px;
            }
            .main {
                margin-left: 200px;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Admission Form</a></li>
            <li><a href="#">Verify Students</a></li>
            <li><a href="#">Verify Teachers</a></li>
            <li><a href="#">Routine</a></li>
            <li><a href="#">Result</a></li>
            <li><a href="#">Reports</a></li>
            <li><a href="#">Settings</a></li>
            
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="topbar">
            <h3>Welcome, Admin</h3>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        
        <div class="cards">
            <div class="card">
                <h2>1000</h2>
                <p>Total Students</p>
            </div>

            <div class="card">
                <h2>25</h2>
                <p>Total Teachers</p>
            </div>

            <div class="card">
                <h2>15</h2>
                <p>Total Classes</p>
            </div>

            <div class="card">
                <h2>10</h2>
                <p>Pending Approvals</p>
            </div>

             <div class="card">
                <h2>3</h2>
                <p>Course</p>
            </div>

             <div class="card">
                <h2>95%</h2>
                <p>Results</p>
            </div>
        </div>

        

    <script>
        function logout() {
            alert("Logging out...");
            // Redirect example
            window.location.href = "login.html";
        }
    </script>

</body>
</html>