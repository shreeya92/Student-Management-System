<?php
$host = "localhost";
$user = "root";
$password = "";
$db ="Student_Management_System";
$port= 3366;
$conn = mysqli_connect($host, $user, $password, $db, $port);
if ($conn){
    echo "Database connected successfully";
}else{
    echo "Connection failed";
}
mysqli_close($conn);