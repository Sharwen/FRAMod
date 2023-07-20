<!DOCTYPE html>
<html>
<head>
    <title>Registration Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            background-image: url('imgg.jpg');
        }
        .register-box {
            width: 300px;
            background-color: #;
            margin: 0 auto;
            margin-top: 150px;
            padding: 20px;
            border: 1px solid #fff;
            border-radius: 5px;
        }
        .register-box h2 {
            text-align: center;
            color: #333;
        }
        .register-box input[type="text"],
        .register-box input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 5px;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .register-box input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .register-box input[type="submit"]:hover {
            background-color: #45a049;
        }
        .register-box p {
            text-align: center;
        }
        .error {
            color: red;
            font-size: 12px;
            margin-top: -10px;
        }
    </style>
    <script>
        function togglePasswordVisibility(fieldId, toggleId) {
            var field = document.getElementById(fieldId);
            var toggle = document.getElementById(toggleId);

            if (field.type === "password") {
                field.type = "text";
                toggle.textContent = "🙈";
            } else {
                field.type = "password";
                toggle.textContent = "👁️";
            }
        }

        function validateForm() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm_password").value;
            var errorLabel = document.getElementById("error_label");

            if (password !== confirmPassword) {
                errorLabel.textContent = "Passwords does not match";
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <div class="register-box">
        <h2>Register</h2>
        <form method="POST" action="register.php" onsubmit="return validateForm()">
            <input type="text" name="username" placeholder="Username" required>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <span class="password-toggle" onclick="togglePasswordVisibility('password', 'password-toggle')">👁️</span>
            </div>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password', 'confirm-password-toggle')">👁️</span>
            </div>
            <span id="error_label" class="error"></span>
            <input type="submit" value="Register">
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>


    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $servername = "localhost";
        $username = "root";
        $password = "123";
        $dbname = "register";

        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Retrieve the form data
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        // Perform any necessary validation on the form data
        if ($password !== $confirmPassword) {
            echo "Passwords do not match";
            exit;
        }

        
        $sql = "INSERT INTO users (username, password, confirm_password) VALUES ('$username', '$password', '$confirmPassword')";

     if ($conn->query($sql) === true) {
    echo "<script>alert('Registration successful!'); window.location.href = 'login.php';</script>";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}


        // Close the database connection
        $conn->close();
    }
    ?>


</body>
</html>
