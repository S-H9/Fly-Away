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
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    }
    // Check password match
    else if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    }
    // Validate password strength
    else if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    }
    else {
        // Check if username or email already exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Username or email already exists";
        } else {
            $profile_image = "imges/default-profile.jpg"; // Default image

            // Handle profile image upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_image']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);

                if (in_array(strtolower($filetype), $allowed)) {
                    $new_filename = uniqid() . '.' . $filetype;
                    $upload_path = 'uploads/' . $new_filename;

                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                        $profile_image = $upload_path;
                    }
                }
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (username, email, password, profile_image) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $profile_image);

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
        .register-container input:not([type="file"]) {
            width: calc(100% - 40px); padding: 10px;
            margin: 10px 0; border: none; border-radius: 5px;
            font-size: 16px; color: #333; background-color: #f9f9f9;
        }
        .profile-upload {
            margin: 20px 0;
            text-align: center;
        }
        .profile-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 10px auto;
            overflow: hidden;
            border: 3px solid #4caf50;
        }
        .profile-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .file-input-wrapper {
            position: relative;
            margin: 10px 0;
        }
        .file-input-wrapper input[type="file"] {
            display: none;
        }
        .file-input-wrapper label {
            display: inline-block;
            padding: 8px 15px;
            background-color: #4caf50;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .register-container button {
            background-color: #4caf50; color: white;
            border: none; border-radius: 5px; padding: 10px 20px;
            font-size: 16px; cursor: pointer; margin-top: 10px;
            transition: background-color 0.3s ease;
            width: calc(100% - 40px);
        }
        .register-container button:hover { background-color: #45a049; }
        .error { color: #ff6666; margin-bottom: 15px; }
        @media (max-width: 480px) {
            .register-container { width: 90%; padding: 15px; }
            .register-container button { font-size: 14px; padding: 8px 15px; }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <img src="imges/img.png" alt="Fly Away Logo">
        <h1>Register</h1>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            
            <div class="profile-upload">
                <div class="profile-preview">
                    <img id="preview-img" src="imges/default-profile.jpg" alt="Profile Preview">
                </div>
                <div class="file-input-wrapper">
                    <label for="profile_image">Choose Profile Picture</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*">
                </div>
            </div>

            <button type="submit">Create Account</button>
        </form>
        <p>Already have an account? <a href="index.php" style="color: #4caf50; text-decoration: none;">Login</a></p>
    </div>

    <script>
        // Preview profile image before upload
        document.getElementById('profile_image').onchange = function(evt) {
            const [file] = this.files;
            if (file) {
                document.getElementById('preview-img').src = URL.createObjectURL(file);
            }
        };
    </script>
</body>
</html>