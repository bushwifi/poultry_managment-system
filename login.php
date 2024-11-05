<?php
session_start();

$host = 'localhost'; 
$db = 'poultry_management'; 
$user = 'root'; 
$pass = ''; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcoded username and password for testing
    if ($username === 'gikandi' && $password === 'gikandi') {
        $_SESSION['username'] = $username; // Store username in session
        header("Location: dashboard.php"); // Redirect to a dashboard page
        exit();
    } else {
        echo "Invalid username or password!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link to your CSS file -->
</head>
<body>
    <div class="screen-1">
        <div class="logo">
        
        </div>
        <form action="" method="POST">
            <div class="email">
                
                <input type="text" name="username" placeholder="username" required>
            </div>
            <div class="password">
               
                <input type="password" name="password" placeholder="password" required>
            </div>
            <button class="login" type="submit">Login</button>
        </form>
        <div class="footer">
           
        </div>
    </div>
</body>
</html>
