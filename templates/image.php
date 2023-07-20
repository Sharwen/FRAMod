<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
</head>
<body>

<form action="upload.php" method="post" enctype="multipart/form-data">
    <label>Select Image File:</label>
    <input type="file" name="image"><br>
    <br>
    

    <label>Enter Student Name:</label>
    <input type="name" name="sname"><br>
    <br>
    

    <label>Enter Student Matrix No:</label>
    <input type="text" name="matrix"><br>
    <br>


    <input type="submit" name="submit" value="Upload">
</form>

<?php  
// Database configuration  
$dbHost     = "localhost";  
$dbUsername = "root";  
$dbPassword = "123";  
$dbName     = "register";  
  
// Create database connection  
$db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);  
  
// Check connection  
if ($db->connect_error) {  
    die("Connection failed: " . $db->connect_error);  
}
?>

</body>
</html>

