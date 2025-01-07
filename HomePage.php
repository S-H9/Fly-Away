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
    font-family: 'Poppins', Arial, sans-serif;
}

body {
    background: linear-gradient(135deg, #0b587c, #48a7d4);
    min-height: 100vh;
    color: #333;
}

.navbar {
    background-color: rgba(255, 255, 255, 0.15);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    backdrop-filter: blur(10px);
    position: fixed;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
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
}

.nav-links a {
    color: white;
    text-decoration: none;
    font-size: 1.1rem;
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    transition: all 0.3s ease;
    position: relative;
    font-weight: 500;
    letter-spacing: 0.5px;
}

.nav-links a:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.nav-links a.active {
    background-color: rgba(255, 255, 255, 0.25);
    font-weight: bold;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.hero-section {
    position: relative;
    height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    padding-top: 80px;
    overflow: hidden;
}

.hero-section .carousel {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.hero-section .carousel img {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 1s ease-in-out;
    animation: zoom 20s ease infinite;
}

@keyframes zoom {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.hero-section .carousel img.active {
    opacity: 1;
}

.hero-section .hero-text {
    position: relative;
    z-index: 2;
    background-color: rgba(0, 0, 0, 0.6);
    padding: 3rem;
    border-radius: 15px;
    text-align: center;
    max-width: 80%;
    backdrop-filter: blur(5px);
    transform: translateY(-20%);
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(-20%);
    }
    50% {
        transform: translateY(-22%);
    }
}

.hero-text h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.hero-text p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    color: #f0f0f0;
}

.hero-text button {
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    background: linear-gradient(45deg, #0b587c, #48a7d4);
    border: none;
    border-radius: 25px;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.hero-text button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.quick-bar {
    background-color: rgba(255, 255, 255, 0.95);
    margin: -3rem auto 2rem;
    padding: 2.5rem;
    border-radius: 20px;
    width: 85%;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    position: relative;
    z-index: 3;
}

.search-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    align-items: end;
}

.search-form input,
.search-form select {
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-form input:focus,
.search-form select:focus {
    border-color: #48a7d4;
    box-shadow: 0 0 0 2px rgba(72, 167, 212, 0.2);
    outline: none;
}

.search-form button {
    padding: 1rem;
    background: linear-gradient(45deg, #0b587c, #48a7d4);
    border: none;
    border-radius: 10px;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.destinations {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 2rem;
    padding: 2rem;
}

.destination-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    width: 250px;
}

.destination-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
}

.destination-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.destination-card:hover img {
    transform: scale(1.1);
}

.destination-card h3 {
    padding: 1rem;
    margin: 0;
    font-size: 1.2rem;
    color: #333;
}

.destination-card p {
    padding: 0 1rem 1rem;
    color: #48a7d4;
    font-weight: bold;
}
</style>

<script>
// Add this JavaScript to handle the image carousel
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.carousel img');
    let currentIndex = 0;

    function showNextImage() {
        images[currentIndex].classList.remove('active');
        currentIndex = (currentIndex + 1) % images.length;
        images[currentIndex].classList.add('active');
    }

    // Show first image
    images[0].classList.add('active');
    
    // Change image every 5 seconds
    setInterval(showNextImage, 5000);
});
</script>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="imges/img.png" alt="Fly Away Logo">
        </div>
        <div class="nav-links">
            <a href="#" class="active">Home</a>
            <a href="Book.html">Book</a>
            <a href="Flights.html">Flights</a>
            <a href="#" onclick="toggleProfile()">Profile</a>
        </div>
    </nav>
    <div class="hero-section">
        <div class="carousel">
            <img src="imges/img11.jpg" alt="Beach">
            <img src="imges/img4.jpeg" alt="Mountains">
            <img src="imges/img12.jpg" alt="City">
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