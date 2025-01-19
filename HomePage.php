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
// Enhanced session security function
function checkSession() {
    // Set secure session cookie parameters
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }

    // Session expiration and security checks
    $inactive = 1800; // 30 minutes
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

    // Regenerate session ID periodically
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

    <!-- Shortcut Icon -->
    <link rel="icon" href="imges/img.png" type="image/x-icon">

    
    <!-- Preload critical assets -->
    <link rel="preload" href="imges/img.png" as="image">
    <link rel="stylesheet" href="Profile2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="Profile2.js" defer></script>

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
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

body {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    min-height: 100vh;
    line-height: 1.6;
    color: var(--text-dark);
}

/* Modern Navbar */
.navbar {
    background-color: rgba(255, 255, 255, 0.95);
    padding: 1rem 2rem;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    box-shadow: var(--shadow-md);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.navbar-content {
    max-width: var(--container-width);
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Logo Styles */
.logo {
    width: 60px;
    height: 60px;
    background-color: var(--accent-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: transform var(--transition-speed) ease;
}

.logo:hover {
    transform: scale(1.05);
}

.logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Navigation Links */
.nav-links {
    display: flex;
    gap: 2rem;
    align-items: center;
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

/* Profile Button */
.profile-button {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    border: 3px solid var(--primary-color);
    transition: all var(--transition-speed) ease;
}

.profile-button:hover {
    transform: scale(1.1);
    box-shadow: 0 0 15px rgba(11, 88, 124, 0.3);
}

.profile-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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
    background-color: rgba(255, 255, 255, 0.95);
    margin: -4rem auto 2rem;
    padding: 2rem;
    border-radius: var(--border-radius);
    width: 90%;
    max-width: var(--container-width);
    box-shadow: var(--shadow-lg);
    position: relative;
    z-index: 3;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.search-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    align-items: end;
}

.input-group {
    position: relative;
}

.input-group i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary-color);
    pointer-events: none;
}

.search-form input,
.search-form select {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all var(--transition-speed) ease;
    background-color: white;
}

.search-form input:focus,
.search-form select:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(11, 88, 124, 0.1);
}

.search-form button {
    padding: 1rem;
    background-color: var(--primary-color);
    color: var(--text-light);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1rem;
    transition: all var(--transition-speed) ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.search-form button:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
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
    height: 100%;
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

/* Responsive Design */
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

    .profile-button {
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

/* Print Styles */
@media print {
    .navbar,
    .quick-bar,
    .hero-section {
        display: none;
    }

    .destinations {
        grid-template-columns: 1fr;
    }

    .destination-card {
        break-inside: avoid;
        page-break-inside: avoid;
        box-shadow: none;
        border: 1px solid #ddd;
    }
}


.profile-pic {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #0b587c;
    transition: transform 0.3s ease;
    mar
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

/* Footer Styles */
.footer {
    background: #0b587c;
    color: #fff;
    padding: 2rem 1rem;
    text-align: center;
}

.footer-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    max-width: 1200px;
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
    color: #48a7d4;
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
    color: #fff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-section ul li a:hover {
    color: #48a7d4;
}

.footer-bottom {
    margin-top: 2rem;
    font-size: 0.9rem;
    color: #ddd;
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer-container {
        flex-direction: column;
        align-items: center;
    }

    .footer-section {
        text-align: center;
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
            <div class="nav-links"  style="display: flex; align-items: center; position:absolute; left:1100px; top:20px;">
                <a href="#" class="active">Home</a>
                <a href="Book.php">Book</a>
                <a href="Flights.php">Flights</a>
                
            </div>
            <div class="profile-pic" >
                <?php
                    $user_id = $_SESSION['user_id'];
                    $profile_sql = "SELECT profile_image FROM users WHERE user_id = ?";
                    $stmt = $conn->prepare($profile_sql);
                    if ($stmt) {
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        
                        // Remove the duplicate 'uploads/' prefix since it's already in the database path
                        $profile_image = !empty($user['profile_image']) ? $user['profile_image'] : 'imges/img4.jpeg';
                        echo '<img src="' . htmlspecialchars($profile_image) . '" alt="Profile Picture">';
                    }
                ?>
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
            <form class="search-form" action="search-flights.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <div class="input-group">
                    <i class="fas fa-plane-departure"></i>
                    <input type="text" name="departure" placeholder="From" required 
                           pattern="[A-Za-z\s]+" title="Please enter a valid city name">
                </div>
                <div class="input-group">
                    <i class="fas fa-plane-arrival"></i>
                    <input type="text" name="arrival" placeholder="To" required 
                           pattern="[A-Za-z\s]+" title="Please enter a valid city name">
                </div>
                <div class="input-group">
                    <i class="fas fa-calendar"></i>
                    <input type="date" name="date" required 
                           min="<?= date('Y-m-d') ?>" 
                           max="<?= date('Y-m-d', strtotime('+1 year')) ?>">
                </div>
                <div class="input-group">
                    <i class="fas fa-users"></i>
                    <select name="passengers" required>
                        <option value="">Select Passengers</option>
                        <?php for($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> Passenger<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit">Search Flights <i class="fas fa-search"></i></button>
            </form>
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
        // Enhanced carousel functionality
        document.addEventListener('DOMContentLoaded', function() {
            let currentSlide = 0;
            const slides = document.querySelectorAll('.carousel img');
            
            function showSlide(index) {
                slides.forEach(slide => slide.classList.remove('active'));
                currentSlide = (index + slides.length) % slides.length;
                slides[currentSlide].classList.add('active');
            }

            function nextSlide() {
                showSlide(currentSlide + 1);
            }

            // Preload next image
            function preloadNextImage() {
                const nextIndex = (currentSlide + 1) % slides.length;
                const nextImage = new Image();
                nextImage.src = slides[nextIndex].src;
            }

            setInterval(() => {
                nextSlide();
                preloadNextImage();
            }, 5000);

            // Touch support for carousel
            let touchStartX = 0;
            const carousel = document.querySelector('.carousel');

            carousel.addEventListener('touchstart', e => {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            carousel.addEventListener('touchend', e => {
                const touchEndX = e.changedTouches[0].screenX;
                const diff = touchStartX - touchEndX;

                if (Math.abs(diff) > 50) {
                    if (diff > 0) {
                        showSlide(currentSlide + 1);
                    } else {
                        showSlide(currentSlide - 1);
                    }
                }
            }, { passive: true });
        });

        // Form validation
        document.querySelector('.search-form').addEventListener('submit', function(e) {
            const departure = this.querySelector('input[name="departure"]').value;
            const arrival = this.querySelector('input[name="arrival"]').value;

            if (departure.toLowerCase() === arrival.toLowerCase()) {
                e.preventDefault();
                alert('Departure and arrival cities cannot be the same');
            }
        });
    </script>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>About Us</h3>
            <p>Fly Away is dedicated to providing seamless and enjoyable flight booking experiences. Your journey starts here!</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="homepage.php">Home</a></li>
                <li><a href="book.php">Book a Flight</a></li>
                <li><a href="flights.php">Available Flights</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Us</h3>
            <p>Email: support@flyaway.com</p>
            <p>Phone: +966 534 567 890</p>
            <p>Address: JUC UQU, CS</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 Fly Away-JUC. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>