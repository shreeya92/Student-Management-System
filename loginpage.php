
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
          
        body {
            font-family: Arial, sans-serif;
            max-width: 300px;
            margin: 50px auto;
            border-radius: 10px;
        }

        .container{
            border-radius: 10px;
            
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        .btn {
            background: #4CAF50;
            color: white;
            padding: 8px 15px;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .btn:hover {
            background: #45a049;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">

        <form method="POST" action="login.php" id="signupForm">
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
    <label for="name">Email</label>
    <input type="text" name="email" class="email" id="email"><br>

    <label for="password">Password</label>
    <input type="password" name="password" class="password" id="password"><br>

    <label>
    <input type="checkbox" name="Remember me" value="Remember me">Remember me <br>
    </label>
    
    <button type="submit" class="btn" name="submit-btn">Submit</button>
    </form>
    
     <script>
       document.getElementById('signupForm').onsubmit = function (e){
           //alert("Form submitted");
           e.preventDefault();
           //console.log("Form Submitted");
           let errDiv = document.querySelector('.error');
           let errorMessages = [];

           let Email= document.getElementById('email').value.trim();
           let Password= document.getElementById('password').value.trim();

           const passwordPattern = /^[a-zA-Z0-9]+@[a-zA-Z]+\.[a-zA-Z]{2,3}$/;
            if(Email === ""){
            errorMessages.push("Email is required.");
        //    }else if(Email.length < 5){
        //     errorMessages.push("Invalid username. ");
           }else if(!passwordPattern.test(Email)){
             errorMessages.push("Format is invalid.");
           }

           if(Password === ""){
            errorMessages.push("Password is required.");
           }else if(Password.length < 5){
            errorMessages.push("Incorrect Password.");
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