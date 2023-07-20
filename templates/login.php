<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            background-image: url('imgg.jpg');
         

        }
        .login-box {
            width: 300px;
            background-color: #;
            margin: 0 auto;
            margin-top: 150px;
            padding: 20px;
            border: 1px solid #fff;
            border-radius: 5px;
        }
        .login-box h2 {
            text-align: center;
            color: #fff;
        }
        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .login-box input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .login-box input[type="submit"]:hover {
            background-color: #45a049;
        }
        .login-box p {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <form method="POST" action="login.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="register.php">Register</a></p>
        <p class="forgot-password"><a href="forgot_password.php">Forgot Password?</a></p>
    </div>

<?php
$host = "localhost";
$username = "root";
$password = "123";
$dbname = "register";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('Invalid username or password. Please try again.');</script>";
    }
}

$conn->close();
?>

</body>
</html>
