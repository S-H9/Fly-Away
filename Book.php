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

$error_message = '';
$success_message = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_flight'])) {
    $flight_id = $_POST['flight_id'];
    $user_id = $_SESSION['user_id'];
    $passengers = $_POST['passengers'];
    $class = $_POST['class'];
    
    // Check if flight is still available
    $check_sql = "SELECT COUNT(*) as booked_seats FROM bookings WHERE flight_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $flight_id);
    $check_stmt->execute();
    $booked_seats = $check_stmt->get_result()->fetch_assoc()['booked_seats'];
    
    if ($booked_seats + $passengers <= 60) {
        $insert_sql = "INSERT INTO bookings (user_id, flight_id, passengers, class, status) 
                      VALUES (?, ?, ?, ?, 'confirmed')";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iiis", $user_id, $flight_id, $passengers, $class);
        
        if ($insert_stmt->execute()) {
            $success_message = "Flight booked successfully!";
        } else {
            $error_message = "Error booking flight. Please try again.";
        }
    } else {
        $error_message = "Not enough seats available.";
    }
}

// Fetch all available flights
$flights_sql = "SELECT f.*, 
               (SELECT COUNT(*) FROM bookings b WHERE b.flight_id = f.flight_id) as booked_seats
               FROM flights f 
               WHERE f.departure_time > NOW()
               ORDER BY f.departure_time ASC";
$flights_result = $conn->query($flights_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>

<style>

/* Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: linear-gradient(135deg, #0d628a, #48a7d4);
    min-height: 100vh;
}

/* Navbar */
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
}

.nav-links a:hover, 
.nav-links a.active {
    background-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

/* Profile Button */
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

/* Main Container */
.booking-container {
    width: 90%;
    max-width: 1200px;
    margin: 2rem auto;
    background-color: rgba(255, 255, 255, 0.95);
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

/* Flight Cards */
.flights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.flight-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.flight-card:hover {
    transform: translateY(-5px);
}

.flight-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #eee;
}

.flight-cities {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
    font-weight: bold;
}

.arrow {
    color: #0d628a;
    font-size: 1.5rem;
}

.flight-date {
    color: #666;
    font-size: 0.9rem;
}

/* Flight Details */
.flight-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.label {
    color: #666;
    font-size: 0.9rem;
}

.value {
    font-weight: bold;
    color: #333;
}

.price {
    color: #0d628a;
    font-size: 1.3rem;
}

/* Booking Options */
.booking-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

select {
    padding: 0.8rem;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    width: 100%;
    outline: none;
    transition: border-color 0.3s ease;
}

select:focus {
    border-color: #0d628a;
}

.book-button {
    width: 100%;
    padding: 1rem;
    background: #0d628a;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.book-button:hover {
    background: #48a7d4;
    transform: translateY(-2px);
}

/* Status Messages */
.message {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    text-align: center;
    font-weight: bold;
}

.error {
    background-color: #fee2e2;
    color: #dc2626;
    border-left: 4px solid #dc2626;
}

.success {
    background-color: #dcfce7;
    color: #16a34a;
    border-left: 4px solid #16a34a;
}

.sold-out {
    text-align: center;
    padding: 1rem;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 8px;
    font-weight: bold;
}

/* Responsive Design */
@media (max-width: 768px) {
    .booking-container {
        width: 95%;
        padding: 1rem;
    }

    .flights-grid {
        grid-template-columns: 1fr;
    }

    .flight-details {
        grid-template-columns: 1fr;
    }

    .booking-options {
        grid-template-columns: 1fr;
    }

    .nav-links {
        gap: 1rem;
    }

    .nav-links a {
        padding: 0.6rem 1rem;
        font-size: 1rem;
    }
}

</style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fly Away - Book</title>
    <link rel="stylesheet" href="Profile2.css">
    <!-- Your existing CSS remains the same -->
</head>
<body>


    <nav class="navbar">
        <div class="logo">
            <img src="imges/img.png" alt="Fly Away Logo">
        </div>
        <div class="nav-links">
            <a href="HomePage.php">Home</a>
            <a href="#" class="active">Book</a>
            <a href="Flights.php">My Flights</a>
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

    <div class="booking-container">
        <h2>Available Flights</h2>
        
        <?php if ($error_message): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <div class="flights-grid">
            <?php if ($flights_result->num_rows > 0): ?>
                <?php while ($flight = $flights_result->fetch_assoc()): ?>
                    <?php $available_seats = 60 - $flight['booked_seats']; ?>
                    <div class="flight-card">
                        <div class="flight-header">
                            <div class="flight-cities">
                                <span class="departure"><?php echo htmlspecialchars($flight['departure_city']); ?></span>
                                <span class="arrow">✈️</span>
                                <span class="arrival"><?php echo htmlspecialchars($flight['arrival_city']); ?></span>
                            </div>
                            <div class="flight-date">
                                <?php echo date('F j, Y', strtotime($flight['departure_time'])); ?>
                            </div>
                        </div>
                        
                        <div class="flight-details">
                            <div class="detail-item">
                                <span class="label">Departure</span>
                                <span class="value"><?php echo date('H:i', strtotime($flight['departure_time'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Arrival</span>
                                <span class="value"><?php echo date('H:i', strtotime($flight['arrival_time'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Available Seats</span>
                                <span class="value"><?php echo $available_seats; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Price</span>
                                <span class="value price">$<?php echo number_format($flight['price'], 2); ?></span>
                            </div>
                        </div>

                        <?php if ($available_seats > 0): ?>
                            <form method="POST" class="booking-form">
                                <input type="hidden" name="flight_id" value="<?php echo $flight['flight_id']; ?>">
                                <div class="booking-options">
                                    <select name="passengers" required class="passenger-select">
                                        <?php for($i = 1; $i <= min(5, $available_seats); $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?> Passenger<?php echo $i > 1 ? 's' : ''; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select name="class" required class="class-select">
                                        <option value="economy">Economy</option>
                                        <option value="business">Business</option>
                                        <option value="first">First Class</option>
                                    </select>
                                </div>
                                <button type="submit" name="book_flight" class="book-button">Book Now</button>
                            </form>
                        <?php else: ?>
                            <div class="sold-out">Sold Out</div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-flights">No flights available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Your existing profile popup code -->
</body>
</html>