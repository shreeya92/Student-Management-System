<?php
$conn = new mysqli("localhost", "root", "", "Student_Management_System", 3366);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}
// $conn->select_database("Student_Management_System");
$create = "CREATE TABLE IF NOT EXISTS student (
           id INT (11) AUTO_INCREMENT PRIMARY KEY,
           username VARCHAR (250) NOT NULL,
           email VARCHAR (250) NOT NULL,
           address VARCHAR (250) NOT NULL,
           gender VARCHAR (10) NOT NULL,
           dob VARCHAR (250) NOT NULL,
           faculty VARCHAR (250) NOT NULL, 
           contact INT (10) NOT NULL,
           password VARCHAR (25) NOT NULL,
           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if($conn->query($create)){
    echo "Table Student Created Successfully";
}else{
    echo "Error creating Table:" . $conn->error;
}