<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
        }

        
        nav {
            background-color: #2c3e50;
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        nav h2 {
            letter-spacing: 1px;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            transition: 0.3s;
        }

        nav ul li a:hover {
            color: #1abc9c;
        }

    
        .hero {
            height: 60vh;
            background: linear-gradient(rgba(44,62,80,0.8), rgba(44,62,80,0.8)),
                        url('https://images.unsplash.com/photo-1523580846011-d3a5bc25702b');
            background-size: cover;
            background-position: center;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .hero button {
            padding: 10px 20px;
            border: none;
            background-color: #1abc9c;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s;
        }

        .hero button:hover {
            background-color: #16a085;
        }

       
        .container {
            padding: 50px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .card p {
            margin-bottom: 20px;
            color: #555;
        }

        .card a {
            text-decoration: none;
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
            transition: 0.3s;
        }

        .card a:hover {
            background-color: #2980b9;
        }

      
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 30px;
        }

        @media(max-width: 768px){
            .hero h1 {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>

    
    <nav>
        <h2>Shine School</h2>
        <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Registration Form</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Login</a></li>
        </ul>
    </nav>

    
    <section class="hero">
        <h1>Welcome to Student Management System</h1>
        <p>Manage student records easily and efficiently</p>
        <button onclick="alert('Get Started Clicked!')">Enroll Now</button>
    </section>

    
   

    <footer>
        <p>© 2026 Student Management System | All Rights Reserved</p>
    </footer>

</body>
</html>