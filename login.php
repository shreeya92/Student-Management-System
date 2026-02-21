<?php
session_start();
$error = [];

if(isset($_POST) && !empty($_POST)) {
    if($_POST["submit"]== "Submit"){
       
        if(empty( $_POST['email'])){
          
            $error["email"] = "Email is required";
        // }elseif(strlen($_POST["email"]) < 8){
        //     $error["email"] = " Must be 8 character long";
        }elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
            $error['email']= "Please enter valid email";
        }

        $password = $_POST['password'];
        $hashedPasswordFromDB = $row['password']; // fetched from database

        if (password_verify($password, $hashedPasswordFromDB)) {
          $error["password"] = "Invalid password";
        }

        if(!empty($error)){
            $_SESSION['error'] = $error;
            header('location:loginpage.php');
         }
    }
}