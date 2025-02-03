<?php
session_start();

// Security headers
header("Content-Security-Policy: default-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com;");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Database connection
$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'fly_away'
];

try {
    $conn = new mysqli($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['name']);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error. Please try again later.");
}

// Function to safely output data
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $user_id = $_SESSION['user_id'];
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Handle file upload
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $newFilename = uniqid() . '.' . $ext;
            $destination = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                $profile_image = $destination;
            }
        }
    }

    try {
        $conn->begin_transaction();
        
        $sql = "UPDATE users SET username = ?, email = ?";
        $params = [$username, $email];
        $types = "ss";
        
        if ($profile_image) {
            $sql .= ", profile_image = ?";
            $params[] = $profile_image;
            $types .= "s";
        }
        
        $sql .= " WHERE user_id = ?";
        $params[] = $user_id;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        $conn->commit();
        
        $message = "Profile updated successfully!";
        $messageType = 'success';
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error updating profile: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Fly-Away</title>
    <link rel="icon" href="imges/img.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="profile-styles.css">


    <style>


:root {
    --primary-color: #0b587c;
    --secondary-color: #48a7d4;
    --accent-color: #1a365d;
    --text-light: #ffffff;
    --text-dark: #333333;
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.1);
    --transition-speed: 0.3s;
    --border-radius: 12px;
    --container-width: 1200px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    min-height: 100vh;
    line-height: 1.6;
    color: var(--text-dark);
    padding-top: 80px;
}

.navbar {
    background-color: rgba(255, 255, 255, 0.95);
    padding: 1rem 2rem;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    box-shadow: var(--shadow-md);
    backdrop-filter: blur(10px);
}

.navbar-content {
    max-width: var(--container-width);
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.nav-links a {
    color: var(--text-dark);
    text-decoration: none;
    font-size: 1.1rem;
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    transition: all var(--transition-speed) ease;
    position: relative;
    font-weight: 500;
}

.nav-links a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background-color: var(--primary-color);
    transition: all var(--transition-speed) ease;
    transform: translateX(-50%);
}

.nav-links a:hover::after {
    width: 70%;
}

.nav-links a.active {
    background-color: var(--primary-color);
    color: var(--text-light);
}

.profile-container {
    position: relative;
}

.profile-pic {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid var(--primary-color);
    transition: transform var(--transition-speed) ease;
    cursor: pointer;
}

.profile-pic img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-pic:hover {
    transform: scale(1.1);
    box-shadow: 0 0 10px rgba(11, 88, 124, 0.3);
}

.profile-popup {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    box-shadow: var(--shadow-md);
    border-radius: var(--border-radius);
    padding: 10px;
    width: 150px;
    display: none;
    z-index: 1000;
}

.profile-popup a {
    display: block;
    padding: 8px;
    text-decoration: none;
    color: var(--text-dark);
    font-size: 14px;
    border-radius: 3px;
    transition: background-color var(--transition-speed) ease;
}

.profile-popup a:hover {
    background-color: #f0f0f0;
}

.main-content {
    max-width: var(--container-width);
    margin: 2rem auto;
    padding: 0 2rem;
}

.profile-header {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    margin-bottom: 2rem;
}

.profile-cover {
    height: 200px;
    background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
}

.profile-info-card {
    padding: 2rem;
    margin-top: -30px;
    display: flex;
    align-items: flex-end;
    gap: 2rem;
}

.profile-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid white;
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    flex-grow: 1;
}

.user-info h1 {
    font-size: 2rem;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.user-info .email {
    color: var(--primary-color);
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.user-info .member-since {
    color: #666;
    font-size: 0.9rem;
}

.content-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-md);
}

.content-section h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-item .label {
    color: #666;
    font-size: 0.9rem;
}

.info-item .value {
    color: var(--text-dark);
    font-size: 1.1rem;
    font-weight: 500;
}

/* Edit mode styles */
.edit-mode input {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: all var(--transition-speed) ease;
}

.edit-mode input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(11, 88, 124, 0.1);
}

.edit-buttons {
    grid-column: 1 / -1;
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.save-btn, .cancel-btn {
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1rem;
    transition: all var(--transition-speed) ease;
}

.save-btn {
    background-color: var(--primary-color);
    color: white;
    flex: 1;
}

.cancel-btn {
    background-color: #e0e0e0;
    color: var(--text-dark);
    flex: 1;
}

.save-btn:hover, .cancel-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.save-btn:hover {
    background-color: var(--secondary-color);
}

.cancel-btn:hover {
    background-color: #d0d0d0;
}

.toggle-edit {
    background: none;
    border: none;
    color: var(--primary-color);
    cursor: pointer;
    padding: 0.5rem;
    font-size: 1.2rem;
    transition: all var(--transition-speed) ease;
}

.toggle-edit:hover {
    color: var(--secondary-color);
    transform: scale(1.1);
}

.message {
    padding: 1rem;
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
    text-align: center;
    animation: slideIn 0.3s ease-out;
}

.message.success {
    background-color: #d1fae5;
    color: #065f46;
    border-left: 4px solid #059669;
}

.message.error {
    background-color: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #dc2626;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* File input styling */
input[type="file"] {
    padding: 0.5rem;
    border: 1px dashed #ddd;
    border-radius: var(--border-radius);
    width: 100%;
}

input[type="file"]::-webkit-file-upload-button {
    background-color: var(--primary-color);
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    margin-right: 1rem;
    transition: all var(--transition-speed) ease;
}

input[type="file"]::-webkit-file-upload-button:hover {
    background-color: var(--secondary-color);
}

/* Responsive Design */
@media (max-width: 768px) {
    .navbar {
        padding: 1rem;
    }

    .nav-links {
        gap: 0.5rem;
    }

    .nav-links a {
        padding: 0.6rem 1rem;
        font-size: 1rem;
    }

    .main-content {
        padding: 0 1rem;
    }

    .profile-info-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 1rem;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .edit-buttons {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .nav-links a {
        padding: 0.5rem;
        font-size: 0.9rem;
    }

    .profile-pic {
        width: 35px;
        height: 35px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
    }

    .user-info h1 {
        font-size: 1.5rem;
    }
}


    </style>


</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="/" class="logo">
                <img src="imges/img.png" alt="Fly Away Logo" width="60" height="60">
            </a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="Book.php">Book</a>
                <a href="Flights.php">My Flights</a>
                <div class="profile-container">
                    <div class="profile-pic" onclick="toggleProfilePopup()">
                        <?php
                        $profile_image = $user_data['profile_image'] ?? 'imges/img4.jpeg';
                        echo '<img src="' . e($profile_image) . '" alt="Profile Picture">';
                        ?>
                    </div>
                    <div class="profile-popup" id="profilePopup">
                        <a href="Profile.php" class="active">View Profile</a>
                        <a href="Logout.php">Log Out</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <div class="profile-header">
            <!-- <div class="profile-cover"></div> -->
            <div class="profile-info-card">
                <div class="profile-avatar">
                    <img src="<?= e($user_data['profile_image'] ?? 'imges/img4.jpeg') ?>" alt="Profile Picture">
                </div>
                <div class="user-info">
                    <h1><?= e($user_data['username']) ?></h1>
                    <p class="email"><?= e($user_data['email']) ?></p>
                    <p class="member-since">Member since: <?= e(date('F j, Y', strtotime($user_data['created_at']))) ?></p>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <div class="content-section">
                <form id="profileForm" method="POST" enctype="multipart/form-data" class="edit-mode">
                    <h2>
                        <i class="fas fa-user"></i> Personal Information
                        <button type="button" class="toggle-edit" onclick="toggleEditMode()">
                            <i class="fas fa-edit"></i>
                        </button>
                    </h2>
                    
                    <div class="info-grid" id="viewMode">
                        <div class="info-item">
                            <span class="label">Username:</span>
                            <span class="value"><?= e($user_data['username']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Email:</span>
                            <span class="value"><?= e($user_data['email']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Account Type:</span>
                            <span class="value"><?= e(ucfirst($user_data['user_type'] ?? 'Standard')) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Member Since:</span>
                            <span class="value"><?= e(date('F j, Y', strtotime($user_data['created_at']))) ?></span>
                        </div>
                    </div>

                    <div class="info-grid" id="editMode" style="display: none;">
                        <div class="info-item">
                            <span class="label">Username:</span>
                            <input type="text" name="username" value="<?= e($user_data['username']) ?>" required>
                        </div>
                        <div class="info-item">
                            <span class="label">Email:</span>
                            <input type="email" name="email" value="<?= e($user_data['email']) ?>" required>
                        </div>
                        <div class="info-item">
                            <span class="label">Profile Image:</span>
                            <input type="file" name="profile_image" accept="image/*">
                        </div>
                        <div class="edit-buttons">
                            <button type="submit" name="update_profile" class="save-btn">Save Changes</button>
                            <button type="button" class="cancel-btn" onclick="toggleEditMode()">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleProfilePopup() {
            const popup = document.getElementById('profilePopup');
            popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
        }

        document.addEventListener('click', function(event) {
            const popup = document.getElementById('profilePopup');
            const profileContainer = document.querySelector('.profile-container');
            if (!profileContainer.contains(event.target)) {
                popup.style.display = 'none';
            }
        });

        function toggleEditMode() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');
            
            if (viewMode.style.display === 'none') {
                viewMode.style.display = 'grid';
                editMode.style.display = 'none';
            } else {
                viewMode.style.display = 'none';
                editMode.style.display = 'grid';
            }
        }
    </script>
</body>
</html>