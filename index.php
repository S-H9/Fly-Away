<?php
session_start();

// Check if user is already logged in
// if (isset($_SESSION['user_id'])) {
//     header("Location: HomePage.php");
//     exit();
// }

// Database connection
$db_host = "localhost";
$db_user = "root";         // default XAMPP username
$db_pass = "";            // default XAMPP password is empty
$db_name = "fly_away";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT user_id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a new session
            $_SESSION['user_id'] = $user['user_id'];  // Make sure column name is correct (user_id instead of id)
            $_SESSION['username'] = $user['username'];
            
            header("Location: HomePage.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fly Away</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <img src="images/img.png" alt="Fly Away Logo"> <!-- Fixed images folder path -->
        <h1>Login</h1>
        <?php if (isset($error)) { ?>
            <p style="color: #ff6b6b;"><?php echo $error; ?></p>
        <?php } ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Sign up</a></p>
    </div>
</body>
</html>
