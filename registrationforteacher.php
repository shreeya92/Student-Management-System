<?php
session_start();
// var_dump();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
  <style>
        label::after{
            content: "*";
            color: red;
        }
         
        body {
            font-family: Arial, sans-serif;
                background-image: url(project.jpg);
    background-repeat: no-repeat;
    background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 85vh;
        }

        .container {
            background: #e7cce3;
            padding: 25px;
            width: 380px;
            border-radius: 9px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            margin-bottom: 9px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #444;
        }

        input[type="text"],
        input[type="contact"],
        input[type="gender"],
        input[type="email"],
        input[type="date"],
        input[type="password"] {
            width: 100%;
            padding: 7px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 12px;
        }

        .gender-box {
            margin-bottom: 9px;
        }

       input#Submit-btn {
            width: 100%;
            padding: 12px;
            border: none;
            background: #4CAF50;
            color: rgb(233, 243, 234);
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        

        input#Submit-btn:hover {
            background: #43A047;
        }

         .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
    </head>
<body>

    <div class="container">

        <form method="POST" action="register.php" id="signupForm">
            <h2 algin="center">Register/ Sign Up</h2>
             <?php
            if(isset($_SESSION['error'])){
             foreach($_SESSION['error'] as $error){
                echo $error;
                echo "<br>";
             }
            }
            ?>
            <span class="error" ></span><br>

            <label for="uname">Username</label>
            <input type="text" name="uname" id="uname" class="uname"><br><br>

            <label for="email">Email</label>
            <input type="text" name="email" id="email" class="email"><br><br>

             <label>Gender</label>
            <div class="gender-box">
                <input type="radio" name="gender" value="male"> Male
                <input type="radio" name="gender" value="female"> Female
            </div>

            <label for="dob">Date of Birth</label>
            <input type="date" name="dob" id="dob" class="dob"><br><br>

            <label for="contact">Contact</label>
            <input type="text" name="contact" class="contact" id="contact"><br><br>


            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter Password"><br><br>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password"><br><br>

            <input type="submit" value="Submit" class="Submit-btn" id="Submit-btn">
       </form>
    </div>
    <script>
       document.getElementById('signupForm').onsubmit = function (e){
           //alert("Form submitted");
           e.preventDefault();
           //console.log("Form Submitted");
           let errDiv = document.querySelector('.error');
           let errorMessages = [];

           let Username= document.getElementById('uname').value.trim();
           let Email= document.getElementById('email').value.trim();
           let Gender = document.querySelector('input[name="gender"]:checked');
           let DOB= document.getElementById('dob').value.trim();
           let Contact= document.getElementById('contact').value.trim();
           let Password= document.getElementById('password').value.trim();
           let ConfirmPassword= document.getElementById('confirm_password').value.trim();

            if(Username === ""){
            errorMessages.push("Username is required.");
           }else if(Username.length < 5){
            errorMessages.push("Username must be at least 5 characters long.");
           }

           const passwordPattern = /^[a-zA-Z0-9]+@[a-zA-Z]+\.[a-zA-Z]{2,3}$/;
           if(Email === ""){
            errorMessages.push("Email is required.");
           }else if(!passwordPattern.test(Email)){
             errorMessages.push("Email format is invalid.");
           }

        //    if(Email === ""){
        //     errorMessages.push("Email is required.");
        //    }else if(!Email.includes("@") || !Email.includes(".")){
        //     errorMessages.push("Email must contain '@' symbol and'.' symbol.");
        //    }

            console.log(Gender);
           if(Gender === null){
            errorMessages.push("Gender is required.");
           }

           if(DOB === ""){
            errorMessages.push("Date of Birth is required.");
           }else{
            const today = new Date();
            const birthDate = new Date(DOB);
            let age = today.getFullYear() - birthDate.getFullYear();

            if(age < 25){
                errorMessages.push("You must be at least 25 years old.");
            }
           }

            if(Contact === ""){
            errorMessages.push("Contact is required.");
           }else if(isNaN(Contact) || Contact.length < 10){
            errorMessages.push("Contact Number must be numeric and at least 10 digitals long.");
           }
           
           console.log(Password);
           function validatePassword(Password) {
            const errorMessages = [];

            const passwordRegex = /^(?=.*\d)(?=.*@)[A-Za-z\d@]{8,}$/;

            if (Password === "") {
                errorMessages.push("Password is required.");
            } else if (!passwordRegex.test(Password)) {
               errorMessages.push("Password must be at least 8 characters long and contain at least one number and one '@' symbol.");
            }
           }

        //    const password = /^[a-zA-Z0-9]+@[a-zA-Z]$/;
        //    if(Password === ""){
        //     errorMessages.push("Password is required.");
        //    }else if(Password.length < 5){
        //     errorMessages.push("Password must be at least 8 characters long.");
        //    }else if(!password.test(Password)){
        //      errorMessages.push("Password format is invalid.");
        //    }
        //    }else if(!Email.include("Special character") || !Email.include("Number")){
        //        errorMessages.push("Password must contain special character '@' and '0-9'.");
    
           
           
            if(ConfirmPassword === ""){
            errorMessages.push("ConfirmPassword is required.");
           }else if(!ConfirmPassword===Password){
            errorMessages.push("ConfirmPassword is not same as Password");
           }

            if(errorMessages.length > 0){
            errDiv.innerHTML = errorMessages.join("<br>");
           }else{
            errDiv.innerHTML = "Registration Success";
           }
       }
    </script>
</body>
</html>



