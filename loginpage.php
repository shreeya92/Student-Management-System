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
    </style>
</head>
<body>
    <div class="container">

        <form action="" id="signupForm">
            <h2 algin="center">Register/ Sign Up</h2>
            <span class="error" ></span><br>
    <label for="name">Username</label>
    <input type="text" name="uname" class="uname" id="uname"><br>

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

           let Username= document.getElementById('uname').value.trim();
           let Password= document.getElementById('password').value.trim();

            if(Username === ""){
            errorMessages.push("Username is required.");
           }else if(Username.length < 5){
            errorMessages.push("Invalid username. ");
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