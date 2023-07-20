<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $newPassword = $_POST["new_password"];

    $host = "localhost";
    $username = "root";
    $password = "123";
    $dbname = "register";

    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    
    $sql = "UPDATE users SET password = '$newPassword', confirm_password = '$newPassword' WHERE username = '$username'";
    if ($conn->query($sql) === true) {
        // Password updated successfully
        echo "<script>alert('Password reset successfully!'); window.location.href = 'login.php';</script>";
        exit;
    } else {
        echo "Error  password: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
        }
        .forgot-password-box {
            width: 300px;
            background-color: #fff;
            margin: 0 auto;
            margin-top: 150px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .forgot-password-box h2 {
            text-align: center;
            color: #333;
        }
        .forgot-password-box input[type="text"],
        .forgot-password-box input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .forgot-password-box input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .forgot-password-box input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="forgot-password-box">
        <h2>Forgot Password</h2>
        <form method="POST" action="forgot_password.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="submit" value="Reset Password">
        </form>
    </div>
</body>
</html>
