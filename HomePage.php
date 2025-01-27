<?php
session_start();

// Enhanced session cookie parameters
session_set_cookie_params([
    'lifetime' => 1800, // 30 minutes
    'path' => '/',
    'domain' => '',
    'secure' => true, // Ensure cookies are only sent over HTTPS
    'httponly' => true, // Prevent JavaScript access to cookies
    'samesite' => 'Strict' // Prevent CSRF attacks
]);

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

// Enhanced session security function
function checkSession() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }

    // Session expiration (30 minutes)
    $inactive = 1800;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
        session_unset();
        session_destroy();
        header("Location: index.php?timeout=1");
        exit();
    }

    // CSRF Protection
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Regenerate session ID every 5 minutes
    if (!isset($_SESSION['regenerate_time']) || (time() - $_SESSION['regenerate_time']) > 300) {
        session_regenerate_id(true);
        $_SESSION['regenerate_time'] = time();
    }

    $_SESSION['last_activity'] = time();
}

// Initialize rate limiting
function checkRateLimit() {
    $max_requests = 100; // Maximum requests per minute
    $time_window = 60; // 1 minute

    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [
            'count' => 1,
            'start_time' => time()
        ];
    } else {
        if (time() - $_SESSION['rate_limit']['start_time'] > $time_window) {
            $_SESSION['rate_limit'] = [
                'count' => 1,
                'start_time' => time()
            ];
        } else {
            $_SESSION['rate_limit']['count']++;
            if ($_SESSION['rate_limit']['count'] > $max_requests) {
                http_response_code(429);
                exit('Too many requests');
            }
        }
    }
}

// Get the selected date from the filter (default to today's date)
$filterDate = $_GET['filter_date'] ?? date('Y-m-d');

// Fetch flights for the selected date
$flight_sql = "SELECT departure_city, arrival_city, departure_time, arrival_time 
               FROM flights 
               WHERE DATE(departure_time) = ? 
               ORDER BY departure_time ASC";
$stmt = $conn->prepare($flight_sql);
if ($stmt) {
    $stmt->bind_param("s", $filterDate);
    $stmt->execute();
    $flight_result = $stmt->get_result();
    $dailyFlights = [];
    if ($flight_result->num_rows > 0) {
        while ($row = $flight_result->fetch_assoc()) {
            $dailyFlights[] = $row;
        }
    }
    $stmt->close();
} else {
    die("Error preparing statement: " . $conn->error);
}

// Function to safely output data
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Validate session and check rate limit
checkSession();
checkRateLimit();

// Define popular destinations
$popularDestinations = [
    [
        'city' => 'Paris',
        'country' => 'France',
        'image' => 'imges/img7.jpg',
        'price' => 599,
        'description' => 'Experience the city of love'
    ],
    [
        'city' => 'Tokyo',
        'country' => 'Japan',
        'image' => 'imges/img5.jpg',
        'price' => 899,
        'description' => 'Discover modern Asia'
    ],
    [
        'city' => 'New York',
        'country' => 'USA',
        'image' => 'imges/img6.webp',
        'price' => 499,
        'description' => 'The city that never sleeps'
    ]
];

// Define carousel images
$carouselImages = [
    ['src' => 'imges/img2.jpg', 'alt' => 'Beautiful Beach'],
    ['src' => 'imges/img4.jpeg', 'alt' => 'Mountain Range'],
    ['src' => 'imges/img3.jpg', 'alt' => 'Cityscape'],
    ['src' => 'imges/img9.webp', 'alt' => 'Desert Landscape'],
    ['src' => 'imges/img8.jpg', 'alt' => 'Rainforest'],
    ['src' => 'imges/img10.webp', 'alt' => 'Mountains']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Fly-Away - Your premier destination for booking flights and planning your next adventure">
    <meta name="theme-color" content="#0b587c">
    <title>Fly-Away - Your Journey Begins Here</title>
    <link rel="icon" href="imges/img.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
       /* Modern CSS Reset and Variables */
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

/* Base Styles */
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
}

/* Navbar Styles */
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

/* Profile Container */
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

/* Hero Section */
.hero-section {
    margin-top: 80px;
    height: 70vh;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.carousel {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.carousel img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 1s ease-in-out;
}

.carousel img.active {
    opacity: 1;
}

.hero-text {
    position: relative;
    z-index: 2;
    background-color: rgba(0, 0, 0, 0.6);
    padding: 3rem 4rem;
    border-radius: var(--border-radius);
    text-align: center;
    max-width: 700px;
    color: var(--text-light);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    animation: fadeIn 1s ease-out;
}

.hero-text h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.hero-text p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.hero-text button {
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    background-color: var(--primary-color);
    color: var(--text-light);
    border: none;
    border-radius: 30px;
    cursor: pointer;
    transition: all var(--transition-speed) ease;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.hero-text button:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

/* Quick Search Bar */
.quick-bar {
    background-color: rgba(255, 255, 255, 0.98);
    margin: -4rem auto 2rem;
    padding: 2.5rem;
    border-radius: 20px;
    width: 85%;
    max-width: var(--container-width);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    position: relative;
    z-index: 3;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.search-form {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.input-group {
    position: relative;
}

.input-group i {
    position: absolute;
    left: 1.2rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary-color);
    pointer-events: none;
    font-size: 1.2rem;
    transition: color var(--transition-speed) ease;
}

.search-form input {
    width: 100%;
    padding: 1rem 1rem 1rem 3.2rem;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all var(--transition-speed) ease;
    background-color: white;
    color: #333;
}

.search-form input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(11, 88, 124, 0.1);
    outline: none;
}

.search-form input:focus + i {
    color: var(--secondary-color);
}

.search-form button {
    grid-column: 1 / -1;
    padding: 1.2rem;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all var(--transition-speed) ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
}

.search-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(11, 88, 124, 0.2);
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
}

.search-form button i {
    font-size: 1.2rem;
    transition: transform var(--transition-speed) ease;
}

.search-form button:hover i {
    transform: translateX(4px);
}

/* Error Message */
.message.error {
    background-color: #fee2e2;
    color: #dc2626;
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    text-align: center;
    font-weight: 500;
    border-left: 4px solid #dc2626;
    animation: slideIn 0.3s ease-out;
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

/* Destination Cards */
.destinations {
    max-width: var(--container-width);
    margin: 0 auto;
    padding: 2rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.destination-card {
    background-color: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: all var(--transition-speed) ease;
}

.destination-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-lg);
}

.destination-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform var(--transition-speed) ease;
}

.destination-card:hover img {
    transform: scale(1.1);
}

.destination-info {
    padding: 1.5rem;
}

.destination-info h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--text-dark);
}

.destination-info p {
    color: var(--primary-color);
    font-weight: 600;
    font-size: 1.1rem;
}

.price {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1rem;
}

.book-now {
    padding: 0.5rem 1rem;
    background-color: var(--primary-color);
    color: var(--text-light);
    border: none;
    border-radius: 20px;
    cursor: pointer;
    transition: all var(--transition-speed) ease;
    font-size: 1rem;
}

.book-now:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
}

/* Footer Styles */
.footer {
    background: var(--primary-color);
    color: var(--text-light);
    padding: 2rem 1rem;
    text-align: center;
}

.footer-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    max-width: var(--container-width);
    margin: 0 auto;
    gap: 2rem;
}

.footer-section {
    flex: 1 1 calc(25% - 1rem);
    min-width: 250px;
}

.footer-section h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: var(--secondary-color);
}

.footer-section p,
.footer-section ul {
    font-size: 1rem;
    line-height: 1.5;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin: 0.5rem 0;
}

.footer-section ul li a {
    color: var(--text-light);
    text-decoration: none;
    transition: color var(--transition-speed) ease;
}

.footer-section ul li a:hover {
    color: var(--secondary-color);
}

.footer-bottom {
    margin-top: 2rem;
    font-size: 0.9rem;
    color: #ddd;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .search-form {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .navbar {
        padding: 1rem;
    }

    .nav-links {
        gap: 1rem;
    }

    .nav-links a {
        padding: 0.6rem 1rem;
        font-size: 1rem;
    }

    .hero-text {
        padding: 2rem;
        max-width: 90%;
    }

    .hero-text h1 {
        font-size: 2rem;
    }

    .hero-text p {
        font-size: 1rem;
    }

    .quick-bar {
        width: 95%;
        margin-top: -2rem;
        padding: 1.5rem;
    }

    .search-form {
        grid-template-columns: 1fr;
    }

    .destinations {
        padding: 1rem;
        grid-template-columns: 1fr;
    }

    .footer-container {
        flex-direction: column;
        align-items: center;
    }

    .footer-section {
        text-align: center;
    }
}

@media (max-width: 480px) {
    .nav-links a {
        padding: 0.5rem;
        font-size: 0.9rem;
    }

    .logo {
        width: 50px;
        height: 50px;
    }

    .profile-pic {
        width: 35px;
        height: 35px;
    }

    .hero-text h1 {
        font-size: 1.8rem;
    }

    .hero-text p {
        font-size: 0.9rem;
    }

    .destination-card {
        margin: 0.5rem;
    }

    .destination-info h3 {
        font-size: 1.3rem;
    }
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes popupFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.flight-schedule {
    max-width: var(--container-width);
    margin: 2rem auto;
    padding: 2rem;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-md);
}

.flight-schedule h2 {
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
    color: var(--primary-color);
    text-align: center;
}

.flight-schedule table {
    width: 100%;
    border-collapse: collapse;
}

.flight-schedule th, .flight-schedule td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.flight-schedule th {
    background-color: var(--primary-color);
    color: var(--text-light);
    font-weight: 600;
}

.flight-schedule tr:hover {
    background-color: #f9f9f9;
}

.flight-schedule td {
    color: var(--text-dark);
}

.date-filter {
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.date-filter label {
    font-weight: 600;
    color: var(--primary-color);
}

.date-filter input[type="date"] {
    padding: 0.5rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all var(--transition-speed) ease;
}

.date-filter input[type="date"]:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(11, 88, 124, 0.1);
    outline: none;
}

.date-filter button {
    padding: 0.5rem 1rem;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: all var(--transition-speed) ease;
}

.date-filter button:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
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
                <a href="#" class="active">Home</a>
                <a href="Book.php">Book</a>
                <a href="Flights.php">My Flights</a>
                <div class="profile-container">
                    <div class="profile-pic" onclick="toggleProfilePopup()">
                        <?php
                        $user_id = $_SESSION['user_id'];
                        $profile_sql = "SELECT profile_image FROM users WHERE user_id = ?";
                        $stmt = $conn->prepare($profile_sql);
                        if ($stmt) {
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $user = $result->fetch_assoc();
                            $profile_image = !empty($user['profile_image']) ? $user['profile_image'] : 'imges/img4.jpeg';
                            echo '<img src="' . e($profile_image) . '" alt="Profile Picture">';
                        }
                        ?>
                    </div>
                    <div class="profile-popup" id="profilePopup">
                        <a href="Profile.php">View Profile</a>
                        <a href="Settings.php">Settings</a>
                        <a href="Logout.php">Log Out</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    
    <main>
        <section class="hero-section">
            <div class="carousel">
                <?php foreach ($carouselImages as $index => $image): ?>
                    <img 
                        src="<?= e($image['src']) ?>" 
                        alt="<?= e($image['alt']) ?>" 
                        <?= $index === 0 ? 'class="active"' : '' ?> 
                        loading="lazy"
                        width="1200"
                        height="600"
                    >
                <?php endforeach; ?>
            </div>
            <div class="hero-text">
                <h1>Welcome to Fly-Away</h1>
                <p>Your gateway to the world's most exciting destinations!</p>
                <button type="button" onclick="location.href='Book.php'">Start Your Journey</button>
            </div>
        </section>

        <section class="quick-bar">
    <?php if (isset($_GET['error'])): ?>
        <div class="message error" style="margin-bottom: 1rem; padding: 1rem; background-color: #fee2e2; color: #dc2626; border-radius: 8px; text-align: center;">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>
    <form class="search-form" action="Book.php" method="GET" onsubmit="return validateSearch(this);">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="input-group">
            <i class="fas fa-plane-departure"></i>
            <input type="text" name="departure" placeholder="From"  
                   pattern="[A-Za-z\s]+" title="Please enter a valid city name">
        </div>
        <div class="input-group">
            <i class="fas fa-plane-arrival"></i>
            <input type="text" name="arrival" placeholder="To"  
                   pattern="[A-Za-z\s]+" title="Please enter a valid city name">
        </div>
        <div class="input-group">
            <i class="fas fa-calendar"></i>
            <input type="date" name="date"  
                   min="<?php echo date('Y-m-d'); ?>" 
                   max="<?php echo date('Y-m-d', strtotime('+1 year')); ?>">
        </div>
        <button type="submit" name="search">Search Flights <i class="fas fa-search"></i></button>
    </form>
</section>


<section class="flight-schedule">
    <h2>Flight Schedule</h2>
    <form method="GET" action="" class="date-filter">
        <label for="filter-date">Filter by Date:</label>
        <input type="date" id="filter-date" name="filter_date" value="<?= e($_GET['filter_date'] ?? date('Y-m-d')) ?>">
        <button type="submit">Apply Filter</button>
    </form>
    <table>
    <thead>
        <tr>
            <th>Departure City</th>
            <th>Arrival City</th>
            <th>Departure Time</th>
            <th>Arrival Time</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($dailyFlights)): ?>
            <?php foreach ($dailyFlights as $flight): ?>
                <tr>
                    <td><?= e($flight['departure_city']) ?></td>
                    <td><?= e($flight['arrival_city']) ?></td>
                    <td><?= e(date('H:i', strtotime($flight['departure_time']))) ?></td>
                    <td><?= e(date('H:i', strtotime($flight['arrival_time']))) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No flights scheduled for the selected date.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</section>

        <section class="destinations">
            <?php foreach ($popularDestinations as $destination): ?>
                <article class="destination-card">
                    <img 
                        src="<?= e($destination['image']) ?>" 
                        alt="<?= e($destination['city']) ?>" 
                        loading="lazy"
                        width="300"
                        height="200"
                    >
                    <div class="destination-info">
                        <h3><?= e($destination['city']) ?>, <?= e($destination['country']) ?></h3>
                        <p><?= e($destination['description']) ?></p>
                        <div class="price">
                            <span>Starting from $<?= e($destination['price']) ?></span>
                            <button type="button" class="book-now" 
                                    onclick="location.href='Book.php?destination=<?= e($destination['city']) ?>'">
                                Book Now
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
    <script>
        // Toggle profile popup
        function toggleProfilePopup() {
            const popup = document.getElementById('profilePopup');
            popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
        }

        // Close popup when clicking outside
        document.addEventListener('click', function(event) {
            const popup = document.getElementById('profilePopup');
            const profileContainer = document.querySelector('.profile-container');
            if (!profileContainer.contains(event.target)) {
                popup.style.display = 'none';
            }
        });
    </script>
</body>
</html>