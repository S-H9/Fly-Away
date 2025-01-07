<?php
session_start();

// Database connection
$db_host = "localhost";
$db_user = "root";        
$db_pass = "";            
$db_name = "fly_away";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle booking submission
if (isset($_POST['book_flight'])) {
    $flight_id = $_POST['flight_id'];
    $user_id = $_SESSION['user_id'];
    
    $sql = "INSERT INTO bookings (user_id, flight_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $flight_id);
    
    if ($stmt->execute()) {
        $success_message = "Flight booked successfully!";
    } else {
        $error_message = "Error booking flight. Please try again.";
    }
}

// Fetch user's booked flights
$sql = "SELECT f.*, b.booking_id, b.status, b.booking_date 
        FROM flights f 
        INNER JOIN bookings b ON f.flight_id = b.flight_id 
        WHERE b.user_id = ? 
        ORDER BY b.booking_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Fetch available flights
$available_sql = "SELECT * FROM flights 
                 WHERE flight_id NOT IN (
                     SELECT flight_id FROM bookings WHERE user_id = ?
                 )";
$available_stmt = $conn->prepare($available_sql);
$available_stmt->bind_param("i", $_SESSION['user_id']);
$available_stmt->execute();
$available_flights = $available_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fly Away - Flights</title>
    <link rel="stylesheet" href="Profile2.css">
    <style>
        /* Your existing styles remain the same */
       /* Add this CSS in the style section */
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
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="imges/img.png" alt="Fly Away Logo">
        </div>
        <div class="nav-links">
            <a href="HomePage.php">Home</a>
            <a href="Book.php">Book</a>
            <a href="#" class="active">Flights</a>
            <div class="profile-button" onclick="toggleProfile()">
                <?php 
                $profile_sql = "SELECT profile_image FROM users WHERE id = ?";
                $profile_stmt = $conn->prepare($profile_sql);
                $profile_stmt->bind_param("i", $_SESSION['user_id']);
                $profile_stmt->execute();
                $profile_result = $profile_stmt->get_result();
                $profile_image = $profile_result->fetch_assoc()['profile_image'] ?? 'imges/img2.jpg';
                ?>
                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="profile-img">
            </div>
        </div>
    </nav>

    <div class="flights-container">
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- My Bookings Section -->
        <div class="flights-section">
            <h2 class="section-title">My Bookings</h2>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($booking = $result->fetch_assoc()): ?>
                    <div class="flight-card">
                        <div class="flight-info">
                            <div class="flight-route">
                                <div class="flight-time"><?php echo date('H:i', strtotime($booking['departure_time'])); ?></div>
                                <div class="flight-city"><?php echo htmlspecialchars($booking['departure_city']); ?></div>
                            </div>
                            <div class="flight-duration">
                                <?php 
                                $dept = strtotime($booking['departure_time']);
                                $arr = strtotime($booking['arrival_time']);
                                $duration = round(abs($arr - $dept) / 3600, 1);
                                echo $duration . 'h';
                                ?>
                            </div>
                            <div class="flight-route">
                                <div class="flight-time"><?php echo date('H:i', strtotime($booking['arrival_time'])); ?></div>
                                <div class="flight-city"><?php echo htmlspecialchars($booking['arrival_city']); ?></div>
                            </div>
                        </div>
                        <div class="flight-price">$<?php echo number_format($booking['price'], 2); ?></div>
                        <div class="booking-status status-<?php echo $booking['status']; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: white; text-align: center;">No bookings found.</p>
            <?php endif; ?>
        </div>

        <!-- Available Flights Section -->
        <div class="flights-section">
            <h2 class="section-title">Available Flights</h2>
            <?php if ($available_flights->num_rows > 0): ?>
                <?php while ($flight = $available_flights->fetch_assoc()): ?>
                    <div class="flight-card">
                        <div class="flight-info">
                            <div class="flight-route">
                                <div class="flight-time"><?php echo date('H:i', strtotime($flight['departure_time'])); ?></div>
                                <div class="flight-city"><?php echo htmlspecialchars($flight['departure_city']); ?></div>
                            </div>
                            <div class="flight-duration">
                                <?php 
                                $dept = strtotime($flight['departure_time']);
                                $arr = strtotime($flight['arrival_time']);
                                $duration = round(abs($arr - $dept) / 3600, 1);
                                echo $duration . 'h';
                                ?>
                            </div>
                            <div class="flight-route">
                                <div class="flight-time"><?php echo date('H:i', strtotime($flight['arrival_time'])); ?></div>
                                <div class="flight-city"><?php echo htmlspecialchars($flight['arrival_city']); ?></div>
                            </div>
                        </div>
                        <div class="flight-price">$<?php echo number_format($flight['price'], 2); ?></div>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="flight_id" value="<?php echo $flight['flight_id']; ?>">
                            <button type="submit" name="book_flight" class="book-button">Book Now</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: white; text-align: center;">No available flights found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Profile Popup (Your existing code) -->

    <script>
        function toggleProfile() {
            const popup = document.getElementById('profilePopup');
            popup.style.display = popup.style.display === 'none' ? 'block' : 'none';
        }

        function signOut() {
            window.location.href = 'logout.php';
        }
    </script>
</body>
</html>