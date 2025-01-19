<?php
session_start();


// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] == 'admin') {
    header('Location: adminBook.php');
    exit();
}


// echo $_SESSION['user_type'];
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

// Session security
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error_message = '';
$success_message = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_flight'])) {
    if (!isset($_POST['flight_id'], $_POST['class'], $_POST['seat_number'])) {
        $error_message = "Missing required booking information.";
    } else {
        $flight_id = filter_var($_POST['flight_id'], FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user_id'];
        $class = filter_var($_POST['class'], FILTER_SANITIZE_STRING);
        $seat_number = filter_var($_POST['seat_number'], FILTER_SANITIZE_STRING);
        
        if (!$flight_id || !in_array($class, ['economy', 'business', 'first'])) {
            $error_message = "Invalid booking information provided.";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Check if seat is available
                $check_sql = "SELECT COUNT(*) as seat_taken FROM bookings 
                            WHERE flight_id = ? AND seat_number = ? AND status = 'confirmed'";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("is", $flight_id, $seat_number);
                $check_stmt->execute();
                $is_taken = $check_stmt->get_result()->fetch_assoc()['seat_taken'];
                
                if ($is_taken) {
                    throw new Exception("Selected seat is no longer available. Please choose another seat.");
                }
                
                // Calculate price based on class
                $price_sql = "SELECT price FROM flights WHERE flight_id = ? AND departure_time > NOW()";
                $price_stmt = $conn->prepare($price_sql);
                $price_stmt->bind_param("i", $flight_id);
                $price_stmt->execute();
                $result = $price_stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception("Selected flight is no longer available.");
                }
                
                $base_price = $result->fetch_assoc()['price'];
                
                // Apply class multiplier
                $price_multiplier = [
                    'economy' => 1,
                    'business' => 1.5,
                    'first' => 2
                ];
                $final_price = $base_price * $price_multiplier[$class];
                
                // Insert booking
                $insert_sql = "INSERT INTO bookings (user_id, flight_id, class, seat_number, price, status) 
                              VALUES (?, ?, ?, ?, ?, 'confirmed')";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iissd", $user_id, $flight_id, $class, $seat_number, $final_price);
                
                if ($insert_stmt->execute()) {
                    $conn->commit();
                    $success_message = "Flight booked successfully! Your seat {$seat_number} has been reserved.";
                } else {
                    throw new Exception("Error booking flight. Please try again.");
                }
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = $e->getMessage();
            }
        }
    }
}

// Fetch available flights with proper timezone handling
$flights_sql = "SELECT f.*, 
               (SELECT COUNT(*) FROM bookings b WHERE b.flight_id = f.flight_id AND b.status = 'confirmed') as booked_seats,
               GROUP_CONCAT(CASE WHEN b.status = 'confirmed' THEN b.seat_number END) as taken_seats
               FROM flights f 
               LEFT JOIN bookings b ON f.flight_id = b.flight_id
               WHERE f.departure_time > NOW()
               GROUP BY f.flight_id
               ORDER BY f.departure_time ASC";
$flights_result = $conn->query($flights_sql);

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
    <title>Fly Away - Book Your Flight</title>
    <link rel="stylesheet" href="css/book.css">

    <!-- Shortcut Icon -->
    <link rel="icon" href="imges/img.png" type="image/x-icon">

    <style>

/* Base Styles */
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
padding: 2rem;
background-color: rgba(255, 255, 255, 0.95);
border-radius: 20px;
box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

/* Messages */
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
color: #0b587c;
font-size: 1.5rem;
}

.flight-details, .flight-time {
color: #666;
font-size: 0.9rem;
}

/* Seat Selection */
.seat-selection {
margin-top: 1.5rem;
width: 100%;
overflow-x: auto; /* Allows horizontal scrolling if needed */
}

.seat-map {
display: flex;
flex-direction: column;
gap: 0.5rem;
margin: 1rem 0;
min-width: fit-content; /* Ensures content doesn't compress */
padding: 1rem;
}

.seat-row {
display: flex;
justify-content: center;
gap: 0.5rem;
min-height: 40px; /* Minimum height for seats */
}

.seat {
min-width: 35px;
width: 35px;
height: 35px;
border: 2px solid #0b587c;
border-radius: 6px;
cursor: pointer;
display: flex;
align-items: center;
justify-content: center;
font-size: 0.8rem;
transition: all 0.3s ease;
flex-shrink: 0; /* Prevents seat compression */
}

.aisle {
min-width: 20px;
width: 20px;
flex-shrink: 0; /* Prevents aisle compression */
}

/* Make the seat map container scrollable on small screens */
@media (max-width: 768px) {
.seat-selection {
max-width: 100%;
}

.seat-map {
margin: 1rem auto;
}

.seat {
min-width: 30px;
width: 30px;
height: 30px;
}
}

@media (max-width: 480px) {
.seat {
min-width: 25px;
width: 25px;
height: 25px;
font-size: 0.7rem;
}

.aisle {
min-width: 15px;
width: 15px;
}
}

/* Form Elements */
.form-group {
margin-bottom: 1.5rem;
}

label {
display: block;
margin-bottom: 0.5rem;
font-weight: bold;
color: #333;
}

select {
width: 100%;
padding: 0.8rem;
border: 2px solid #ddd;
border-radius: 8px;
font-size: 1rem;
margin-bottom: 1rem;
outline: none;
transition: border-color 0.3s ease;
}

select:focus {
border-color: #0b587c;
}

/* Price Display */
.price-display {
margin: 1rem 0;
padding: 1rem;
background: #f8f9fa;
border-radius: 8px;
text-align: right;
font-weight: bold;
}

/* Book Button */
.book-button {
width: 100%;
padding: 1rem;
background: #0b587c;
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
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

.nav-links {
gap: 1rem;
}

.nav-links a {
padding: 0.6rem 1rem;
font-size: 1rem;
}

.seat {
width: 30px;
height: 30px;
font-size: 0.7rem;
}
}

@media (max-width: 480px) {
.navbar {
padding: 1rem;
}

.nav-links a {
padding: 0.5rem;
font-size: 0.9rem;
}

.flight-cities {
font-size: 1rem;
}

.seat {
width: 25px;
height: 25px;
font-size: 0.6rem;
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
            <a href="#" class="active">Book</a>
            <a href="Flights.php">Flights</a>
            <div class="profile-button">
                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="profile-img">
            </div>
        </div>
    </nav>

    <div class="booking-container">
        <h1>Available Flights</h1>
        
        <?php if ($error_message): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <div class="flights-grid">
            <?php if ($flights_result && $flights_result->num_rows > 0): ?>
                <?php while ($flight = $flights_result->fetch_assoc()): ?>
                    <div class="flight-card">
                        <div class="flight-header">
                            <div class="flight-cities">
                                <span><?php echo htmlspecialchars($flight['departure_city']); ?></span>
                                <span class="arrow">✈️</span>
                                <span><?php echo htmlspecialchars($flight['arrival_city']); ?></span>
                            </div>
                            <div class="flight-time">
                                <div>Departure: <?php echo date('d M Y, H:i', strtotime($flight['departure_time'])); ?></div>
                                <div>Arrival: <?php echo date('d M Y, H:i', strtotime($flight['arrival_time'])); ?></div>
                            </div>
                        </div>
                            
                        <form method="POST" class="booking-form" onsubmit="return validateBooking(this);">
                            <input type="hidden" name="flight_id" value="<?php echo $flight['flight_id']; ?>">
                            
                            <div class="form-group">
                                <label for="class-select-<?php echo $flight['flight_id']; ?>">Select Class:</label>
                                <select id="class-select-<?php echo $flight['flight_id']; ?>" 
                                        name="class" 
                                        required 
                                        onchange="updatePrice(this, <?php echo $flight['price']; ?>)">
                                    <option value="economy">Economy ($<?php echo number_format($flight['price'], 2); ?>)</option>
                                    <option value="business">Business ($<?php echo number_format($flight['price'] * 1.5, 2); ?>)</option>
                                    <option value="first">First Class ($<?php echo number_format($flight['price'] * 2, 2); ?>)</option>
                                </select>
                            </div>

                            <div class="seat-selection">
                                <label>Select Seat:</label>
                                <span class="selected-seat-display"></span>
                                <div class="seat-map">
                                    <?php 
                                    $taken_seats = explode(',', $flight['taken_seats'] ?? '');
                                    $taken_seats = array_filter($taken_seats); // Remove empty values
                                    
                                    for ($row = 'A'; $row <= 'F'; $row++) {
                                        echo "<div class='seat-row'>";
                                        for ($col = 1; $col <= 10; $col++) {
                                            $seat = $row . $col;
                                            $is_taken = in_array($seat, $taken_seats);
                                            $class = $is_taken ? 'seat taken' : 'seat';
                                            echo "<div class='$class' data-seat='$seat' title='".($is_taken ? 'Seat taken' : 'Available')."'>$seat</div>";
                                            if ($col === 5) echo "<div class='aisle'></div>";
                                        }
                                        echo "</div>";
                                    }
                                    ?>
                                </div>
                                <input type="hidden" name="seat_number" required>
                            </div>

                            <div class="price-display">
                                Total Price: $<span class="total-price"><?php echo number_format($flight['price'], 2); ?></span>
                            </div>

                            <button type="submit" name="book_flight" class="book-button">Book Flight</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-flights">No flights available at this time.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function validateBooking(form) {
        const selectedSeat = form.querySelector('input[name="seat_number"]').value;
        if (!selectedSeat) {
            alert('Please select a seat before booking.');
            return false;
        }
        return true;
    }

    document.querySelectorAll('.seat:not(.taken)').forEach(seat => {
        seat.addEventListener('click', function() {
            const form = this.closest('form');
            form.querySelectorAll('.seat').forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
            const seatNumber = this.dataset.seat;
            form.querySelector('input[name="seat_number"]').value = seatNumber;
            form.querySelector('.selected-seat-display').textContent = ` - Selected: ${seatNumber}`;
        });
    });

    function updatePrice(select, basePrice) {
        const multipliers = {
            'economy': 1,
            'business': 1.5,
            'first': 2
        };
        const form = select.closest('form');
        const priceDisplay = form.querySelector('.total-price');
        const finalPrice = basePrice * multipliers[select.value];
        priceDisplay.textContent = finalPrice.toFixed(2);
    }
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