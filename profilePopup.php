<?php
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

// Fetch user profile image if not already fetched
if (!isset($profile_image)) {
    $user_id = $_SESSION['user_id'] ?? null;
    if ($user_id && isset($conn)) {
        $profile_sql = "SELECT profile_image FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($profile_sql);
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $profile_image = !empty($user['profile_image']) ? $user['profile_image'] : 'imges/img4.jpeg';
        }
    } else {
        $profile_image = 'imges/img4.jpeg'; // Default image
    }
}
?>

<!-- Profile Container HTML -->
<style>
    .profile-container {
        position: relative;
    }

    .profile-pic {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--primary-color, #0b587c);
        transition: transform 0.3s ease;
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
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        padding: 10px;
        width: 150px;
        display: none;
        z-index: 1000;
    }

    .profile-popup a {
        display: block;
        padding: 8px;
        text-decoration: none;
        color: #333;
        font-size: 14px;
        border-radius: 3px;
        transition: background-color 0.3s ease;
    }

    .profile-popup a:hover {
        background-color: #f0f0f0;
    }

    @media (max-width: 768px) {
        .profile-pic {
            width: 40px;
            height: 40px;
        }
    }

    @media (max-width: 480px) {
        .profile-pic {
            width: 35px;
            height: 35px;
        }
    }
</style>

<div class="profile-container">
    <div class="profile-pic" onclick="toggleProfilePopup()">
        <img src="<?php echo e($profile_image); ?>" alt="Profile Picture">
    </div>
    <div class="profile-popup" id="profilePopup">
        <a href="Profile.php">View Profile</a>
        <a href="Logout.php">Log Out</a>
    </div>
</div>

<script>
    function toggleProfilePopup() {
        const popup = document.getElementById('profilePopup');
        popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
    }

    // Close popup when clicking outside
    document.addEventListener('click', function(event) {
        const popup = document.getElementById('profilePopup');
        const profileContainer = event.target.closest('.profile-container');
        const isClickInside = profileContainer !== null;
        
        if (!isClickInside && popup.style.display === 'block') {
            popup.style.display = 'none';
        }
    });
</script>