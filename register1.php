<?php
session_start();
$error = [];
// die(1);
print_r($_POST);
if(isset($_POST) && !empty($_POST)) {
    if($_POST["submit"]== "Submit"){
       
        if(empty( $_POST['username'])){
          
            $error["username"] = "Username is required";
        }elseif(strlen($_POST["username"]) < 8){
            $error["username"] = "Username Must be 8 character long";
        }

        if(empty( $_POST['email'])){
          
            $error["email"] = "Email is required";
        }elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
            $error['email']= "Please enter valid email";
        }

        if(empty($_POST['address'])){
            $error["address"] = "Address is required";
        }

        if(empty( $_POST['dob'])){
          
            $error["dob"] = "DOB is required";
        }else{
            $pattern = "/^\d{2}\/\d{2}\/\d{4}$/";
            if(preg_match($pattern, $_POST['dob'])){
                $error["dob"] = "DOB must follow dd/mm/yyyy pattern";
            }
        }

        if(empty( $_POST['gender'])){
            
            $error["gender"] = "Gender is required";
        }

        if(empty( $_POST['contact'])){
           
            $error["contact"] = "Contact is required";
        }else{
            $phone =$_POST['contact'];
            $pattern ='/^(98|97|96)[0-9]{8}$/';
            if(strlen($phone) !==10){
                $error["contact"] = "Phone number must be 10 digit long";
            }elseif(!preg_match($pattern, $phone)){
               $error["contact"] = "Phone number must be start with 98|97|96";
            }
        }

         if(empty( $_POST['faculty'])){
            
            $error["faculty"] = "Faculty is required";
        }

        if(empty($_POST['password'])){
            $error["password"]= "Password is required";
        }else{
            $pattern = '/^(?=.*\d)(?=.*@)[A-Za-z\d@]{8,}$/';
            if(strlen($_POST["password"]) < 8){
                $error["password"] = "Password must be at least 8 character long";
            }else if(!preg_match($pattern, $_POST['password'])){
                $error["password"] = "Password pattern is invalid";
            }
        }

        if(empty($_POST['confirm_password'])){
            $error["confirm_password"] = "Confirm Password is required";
        }else if($_POST['confirm_password'] !== $_POST['password']){
            $error["confirm_password"] = "Confirm Password is not same as Password";
        }

         if(!empty($error)){
            $_SESSION['error'] = $error;
            header('location:registrationforstudent.php');
         }else{
            unset($_SESSION['error']);
            $conn = new mysqli("localhost","root","","Student_Management_System", 3366);
            if($conn->connect_error){
                die("Connection Failed: ". $conn->connect_error);   
            }
            $username = $_POST['username'];
            $email = $_POST['email'];
            $address = $_POST['address'];
            $dob = $_POST['dob'];
            $gender = $_POST['gender'];
            $contact = $_POST['contact'];
            $faculty = $_POST['faculty'];
            $password = $_POST['password'];
            $sql = "INSERT INTO student (username, email, address,  dob, gender, contact, faculty, password) VALUES ('$username', '$email', '$address', '$dob', '$gender', '$contact',
             '$faculty','$password')";
            if($conn->query($sql) === TRUE){
                $_SESSION['username'] = $username;
                header("location:dashboard.php");
            }else{
                echo "Error: ". $sql . "<br>" . $conn->error;
            }
            $conn->close();
        }
    }else{
            echo "Form Submitted successfully";
       }
}else{
    // $_SESSION['error'] = 
    echo"<a href='registrationforstudent.php'>Please Register First </a>";
}
// session_destroy();
?>