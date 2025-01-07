<?php
// Place this at the beginning of HomePage.php
session_start();

// Function to check if session is valid
function checkSession() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
    
    // Optional: Check last activity time
    $inactive = 1800; // 30 minutes
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
        // Session has expired
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
}

// Check session on page load
checkSession();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fly-Away - Home</title>
    <link rel="stylesheet" href="HomePage.css">
    <link rel="stylesheet" href="Profile2.css">
    <script src="Profile2.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
        </div>
        <div class="nav-links">
            <a href="#" class="active">Home</a>
            <a href="Book.html">Book</a>
            <a href="Flights.html">Flights</a>
            <a href="#" onclick="toggleProfile()">Profile</a>
        </div>
    </nav>

    <div class="quick-bar">
        <form class="search-form">
            <input type="text" placeholder="From">
            <input type="text" placeholder="To">
            <input type="date" placeholder="Date">
            <select>
                <option>1 Passenger</option>
                <option>2 Passengers</option>
                <option>3 Passengers</option>
            </select>
            <button type="submit">Search Flights</button>
        </form>
    </div>

    <div class="destinations">
        <div class="destination-card">
            <img src="/api/placeholder/400/300" alt="Destination 1">
            <h3>Popular Destination 1</h3>
            <p>Starting from $299</p>
        </div>
        <div class="destination-card">
            <img src="/api/placeholder/400/300" alt="Destination 2">
            <h3>Popular Destination 2</h3>
            <p>Starting from $399</p>
        </div>
        <div class="destination-card">
            <img src="/api/placeholder/400/300" alt="Destination 3">
            <h3>Popular Destination 3</h3>
            <p>Starting from $499</p>
        </div>
    </div>
<!------------------------------------------------------------------------------------------->
    <!-- Add this HTML just before the closing </body> tag in all pages -->
<div id="profilePopup" class="profile-popup">
    <div class="popup-content">
        <span class="close-btn" onclick="toggleProfile()">&times;</span>
        <h2>Profile Settings</h2>
        <form id="profileForm" class="profile-form">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" value="John Doe">
            </div>
            <div class="form-group">
                <label for="currentPassword">Current Password</label>
                <input type="password" id="currentPassword">
            </div>
            <div class="form-group">
                <label for="newPassword">New Password</label>
                <input type="password" id="newPassword">
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm New Password</label>
                <input type="password" id="confirmPassword">
            </div>
            <div class="button-group">
                <button type="submit" class="save-btn">Save Changes</button>
                <button type="button" class="signout-btn" onclick="signOut()">Sign Out</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>