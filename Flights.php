<?php
    session_start();

    // Check if user is admin
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] == 'admin') {
        header('Location: userFlights.php');
        exit();
    }


    // Database connection with improved security
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

    // Session security
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $error_message = '';
    $success_message = '';

    // Handle flight cancellation
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking'])) {
        $booking_id = filter_var($_POST['booking_id'], FILTER_VALIDATE_INT);
        if ($booking_id) {
            try {
                $cancel_sql = "UPDATE bookings SET status = 'cancelled' 
                            WHERE booking_id = ? AND user_id = ? AND status = 'confirmed'";
                $cancel_stmt = $conn->prepare($cancel_sql);
                $cancel_stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
                
                if ($cancel_stmt->execute()) {
                    $success_message = "Booking cancelled successfully.";
                } else {
                    throw new Exception("Unable to cancel booking.");
                }
            } catch (Exception $e) {
                $error_message = $e->getMessage();
            }
        }
    }

    // Fetch user's bookings with complete flight information
    $bookings_sql = "SELECT f.*, b.booking_id, b.status, b.booking_date, b.class, b.seat_number, b.price as booking_price 
                    FROM flights f 
                    INNER JOIN bookings b ON f.flight_id = b.flight_id 
                    WHERE b.user_id = ? 
                    ORDER BY b.booking_date DESC";
    try {
        $bookings_stmt = $conn->prepare($bookings_sql);
        $bookings_stmt->bind_param("i", $_SESSION['user_id']);
        $bookings_stmt->execute();
        $bookings_result = $bookings_stmt->get_result();
    } catch (Exception $e) {
        $error_message = "Error fetching bookings.";
    }

    // Fetch user profile image
    $profile_sql = "SELECT profile_image FROM users WHERE user_id = ?";
    $profile_stmt = $conn->prepare($profile_sql);
    $profile_stmt->bind_param("i", $_SESSION['user_id']);
    $profile_stmt->execute();
    $profile_result = $profile_stmt->get_result();
    $profile_image = $profile_result->fetch_assoc()['profile_image'] ?? 'images/default-profile.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fly Away - My Flights</title>
    <link rel="stylesheet" href="css/flights.css">

    <!-- Shortcut Icon -->
    <link rel="icon" href="imges/img.png" type="image/x-icon">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(to right, #0d628a, #48a7d4);
            min-height: 100vh;
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
        }

        .nav-links a:hover, .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .profile-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid white;
            transition: transform 0.3s ease;
        }

        .profile-button:hover {
            transform: scale(1.1);
        }

        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .flights-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .flights-section {
            margin: 2rem auto;
        }

        .section-title {
            color: white;
            margin-bottom: 1.5rem;
            padding: 1rem 1.5rem;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            font-size: 1.5rem;
            backdrop-filter: blur(5px);
        }

        .flight-card {
            background-color: rgba(255, 255, 255, 0.95);
            margin-bottom: 1.5rem;
            padding: 2rem;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .flight-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .flight-info {
            display: flex;
            gap: 3rem;
            align-items: center;
            flex: 1;
        }

        .flight-route {
            text-align: center;
            min-width: 120px;
        }

        .flight-time {
            font-size: 1.4rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .flight-city {
            font-size: 1rem;
            color: #7f8c8d;
        }

        .flight-duration {
            color: #7f8c8d;
            font-size: 1rem;
            position: relative;
            padding: 0 1rem;
        }

        .flight-duration::before,
        .flight-duration::after {
            content: '';
            position: absolute;
            height: 1px;
            background-color: #ddd;
            width: 50px;
            top: 50%;
        }

        .flight-duration::before {
            right: 100%;
        }

        .flight-duration::after {
            left: 100%;
        }

        .flight-price {
            font-size: 1.6rem;
            font-weight: bold;
            color: #2980b9;
            margin: 0 2rem;
        }

        .booking-status {
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            min-width: 120px;
            text-align: center;
        }

        .status-pending {
            background-color: #ffd700;
            color: #000;
        }

        .status-confirmed {
            background-color: #2ecc71;
            color: white;
        }

        .status-cancelled {
            background-color: #e74c3c;
            color: white;
        }

        .book-button {
            background-color: #2980b9;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .book-button:hover {
            background-color: #3498db;
            transform: translateY(-2px);
        }

        .success-message,
        .error-message {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: bold;
        }

        .success-message {
            background-color: #2ecc71;
            color: white;
        }

        .error-message {
            background-color: #e74c3c;
            color: white;
        }

        @media (max-width: 768px) {
            .flights-container {
                padding: 1rem;
            }

            .flight-card {
                flex-direction: column;
                gap: 1.5rem;
                padding: 1.5rem;
            }

            .flight-info {
                gap: 1.5rem;
            }

            .flight-route {
                min-width: auto;
            }

            .flight-price {
                margin: 1rem 0;
            }

            .book-button {
                width: 100%;
            }
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
        <div class="logo">
            <img src="images/logo.png" alt="Fly Away Logo">
        </div>
        <div class="nav-links">
            <a href="homepage.php">Home</a>
            <a href="book.php">Book</a>
            <a href="#" class="active">My Flights</a>
            <div class="profile-button">
                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="profile-img">
            </div>
        </div>
    </nav>

    <div class="flights-container">
        <?php if ($error_message): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <div class="flights-section">
            <h2 class="section-title">My Bookings</h2>
            <?php if ($bookings_result && $bookings_result->num_rows > 0): ?>
                <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                    <div class="flight-card">
                        <div class="flight-info">
                            <div class="flight-route">
                                <div class="flight-time"><?php echo date('H:i', strtotime($booking['departure_time'])); ?></div>
                                <div class="flight-city"><?php echo htmlspecialchars($booking['departure_city']); ?></div>
                            </div>
                            <div class="flight-details">
                                <div class="flight-duration">
                                    <?php 
                                    $dept = new DateTime($booking['departure_time']);
                                    $arr = new DateTime($booking['arrival_time']);
                                    $duration = $dept->diff($arr);
                                    echo $duration->format('%hh %im');
                                    ?>
                                </div>
                                <div class="flight-date">
                                    <?php echo date('d M Y', strtotime($booking['departure_time'])); ?>
                                </div>
                                <div class="booking-info">
                                    Class: <?php echo ucfirst(htmlspecialchars($booking['class'])); ?> |
                                    Seat: <?php echo htmlspecialchars($booking['seat_number']); ?>
                                </div>
                            </div>
                            <div class="flight-route">
                                <div class="flight-time"><?php echo date('H:i', strtotime($booking['arrival_time'])); ?></div>
                                <div class="flight-city"><?php echo htmlspecialchars($booking['arrival_city']); ?></div>
                            </div>
                        </div>
                        <div class="flight-price">$<?php echo number_format($booking['booking_price'], 2); ?></div>
                        <div class="booking-actions">
                            <div class="booking-status status-<?php echo strtolower($booking['status']); ?>">
                                <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                            </div>
                            <?php if ($booking['status'] === 'confirmed' && strtotime($booking['departure_time']) > time()): ?>
                                <form method="POST" class="cancel-form" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <button type="submit" name="cancel_booking" class="cancel-button">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>No bookings found. Ready to plan your next trip?</p>
                    <a href="book.php" class="book-now-button">Book a Flight</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle profile button click
        const profileButton = document.querySelector('.profile-button');
        if (profileButton) {
            profileButton.addEventListener('click', function() {
                window.location.href = 'profile.php';
            });
        }

        // Add animation to status messages
        const messages = document.querySelectorAll('.message');
        messages.forEach(message => {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 300);
            }, 5000);
        });
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