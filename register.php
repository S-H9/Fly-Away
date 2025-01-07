<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$db_host = "localhost";
$db_user = "root";        
$db_pass = "";            
$db_name = "fly_away";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $check_sql = "SELECT id FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Username already exists";
        } else {
            $insert_sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("ss", $username, $hashed_password);
            if ($stmt->execute()) {
                $_SESSION['registration_success'] = true;
                header("Location: index.php");
                exit();
            } else {
                $error = "Registration failed: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fly Away - Register</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background: linear-gradient(to right, #142840, #48a7d4);
            margin: 0; padding: 0;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; color: #fff;
        }
        .register-container {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px); border-radius: 15px;
            padding: 20px; width: 400px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); text-align: center;
        }
        .register-container img {
            width: 50%; height: auto; margin-bottom: 20px;
        }
        .register-container input {
            width: calc(100% - 40px); padding: 10px;
            margin: 10px 0; border: none; border-radius: 5px;
            font-size: 16px; color: #333; background-color: #f9f9f9;
        }
        .register-container button {
            background-color: #4caf50; color: white;
            border: none; border-radius: 5px; padding: 10px 20px;
            font-size: 16px; cursor: pointer; transition: background-color 0.3s ease;
        }
        .register-container button:hover { background-color: #45a049; }
        @media (max-width: 480px) {
            .register-container { width: 90%; padding: 15px; }
            .register-container button { font-size: 14px; padding: 8px 15px; }
        }
        .error { color: #ff6666; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="register-container">
        <img src="imges/img.png" alt="Fly Away Logo">
        <h1>Register</h1>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Create Account</button>
        </form>
        <p>Already have an account? <a href="index.php" style="color: #4caf50; text-decoration: none;">Login</a></p>
    </div>
</body>
</html>
