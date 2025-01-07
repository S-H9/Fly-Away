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
    <link rel="stylesheet" href="Profile2.css">
    <script src="Profile2.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0b587c, #48a7d4);
            min-height: 100vh;
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
        }

        .logo {
            width: 60px;
            height: 60px;
            background-color: #1a365d;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: bold;
        }

        /* New Profile Button Styles */
        .profile-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .profile-button:hover {
            transform: scale(1.1);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }

        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-section {
            position: relative;
            height: 50vh;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            color: white;
            padding-bottom: 2rem;
            overflow: hidden;
        }

        .hero-section .carousel {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            animation: scrollImages 20s linear infinite;
            z-index: 1;
        }

        .hero-section .carousel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            flex-shrink: 0;
            animation: fadeInOut 10s ease-in-out infinite;
        }

        .hero-section .hero-text {
            position: relative;
            z-index: 2;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            max-width: 80%;
        }

        @keyframes scrollImages {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-100%);
            }
        }

        @keyframes fadeInOut {
            0%, 100% {
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
        }

        .quick-bar {
            margin-top: -5rem;
            background-color: rgba(255, 255, 255, 0.95);
            margin: 2rem auto;
            padding: 2.5rem;
            border-radius: 20px;
            width: 85%;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .destination-card {
            display: inline-block;
            margin: 1rem;
            text-align: center;
        }

        .destination-card img {
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .destination-card h3 {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="imges/img.png" alt="Fly Away Logo">
        </div>
        <div class="nav-links">
            <a href="#" class="active">Home</a>
            <a href="Book.php">Book</a>
            <a href="Flights.php">Flights</a>
            <div class="profile-button" onclick="toggleProfile()">
                <?php
                $profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'imges/img2.jpg';
                echo '<img src="' . htmlspecialchars($profile_image) . '" alt="Profile" class="profile-img">';
                ?>
            </div>
        </div>
    </nav>
    <div class="hero-section">
        <div class="carousel">
            <img src="imges/img2.jpg" alt="Beach">
            <img src="imges/img4.jpeg" alt="Mountains">
            <img src="imges/img3.jpg" alt="City">
            <img src="imges/img9.webp" alt="Desert">
            <img src="imges/img8.jpg" alt="Desert">
            <img src="imges/img10.webp" alt="Desert">
        </div>
        <div class="hero-text">
            <h1>Welcome to Fly-Away</h1>
            <p>Your gateway to the world's most exciting destinations!</p>
            <button>Explore Now</button>
        </div>
    </div>

    <div class="quick-bar">
        <form class="search-form">
            <input type="text" placeholder="From" required>
            <input type="text" placeholder="To" required>
            <input type="date" placeholder="Date" required>
            <select required>
                <option value="">Select Passengers</option>
                <option>1 Passenger</option>
                <option>2 Passengers</option>
                <option>3 Passengers</option>
            </select>
            <button type="submit">Search Flights</button>
        </form>
    </div>

    <div class="destinations">
        <div class="destination-card">
            <img src="imges/img7.jpg" alt="Paris">
            <h3>Paris, France</h3>
            <p>Starting from $599</p>
        </div>
        <div class="destination-card">
            <img src="imges/img5.jpg" alt="Tokyo">
            <h3>Tokyo, Japan</h3>
            <p>Starting from $899</p>
        </div>
        <div class="destination-card">
            <img src="imges/img6.webp" alt="New York">
            <h3>New York, USA</h3>
            <p>Starting from $499</p>
        </div>
    </div>

    <!-- Profile Popup -->
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