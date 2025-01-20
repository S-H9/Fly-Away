<?php
session_start();

// Security headers
header("Content-Security-Policy: default-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com;");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

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

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: homepage.php?error=" . urlencode("Invalid request"));
    exit();
}

// Initialize variables
$departure = $arrival = $date = "";
$flights = [];
$error = "";

// Validate and sanitize input
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $departure = filter_var($_POST['departure'], FILTER_SANITIZE_STRING);
    $arrival = filter_var($_POST['arrival'], FILTER_SANITIZE_STRING);
    $date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($departure) || empty($arrival) || empty($date)) {
        $error = "All fields are required";
    } elseif (strtolower($departure) === strtolower($arrival)) {
        $error = "Departure and arrival cities cannot be the same";
    } elseif (strtotime($date) < strtotime('today')) {
        $error = "Please select a future date";
    } else {
        // Prepare SQL query with parameterized statements
        $query = "SELECT f.*, 
                 (SELECT COUNT(*) FROM bookings b WHERE b.flight_id = f.flight_id AND b.status = 'confirmed') as booked_seats,
                 GROUP_CONCAT(CASE WHEN b.status = 'confirmed' THEN b.seat_number END) as taken_seats
                 FROM flights f 
                 LEFT JOIN bookings b ON f.flight_id = b.flight_id
                 WHERE LOWER(f.departure_city) LIKE LOWER(?) 
                 AND LOWER(f.arrival_city) LIKE LOWER(?) 
                 AND DATE(f.departure_time) = ?
                 AND f.departure_time > NOW()
                 GROUP BY f.flight_id
                 ORDER BY f.departure_time ASC";
        
        try {
            $stmt = $conn->prepare($query);
            $departure_param = "%$departure%";
            $arrival_param = "%$arrival%";
            $stmt->bind_param("sss", $departure_param, $arrival_param, $date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $flights[] = $row;
            }
        } catch (Exception $e) {
            $error = "An error occurred while searching for flights";
        }
    }
}

// Store results in session and redirect
if (!empty($flights)) {
    $_SESSION['search_results'] = $flights;
    header("Location: Book.php?search=true");
    exit();
} elseif (!empty($error)) {
    header("Location: homepage.php?error=" . urlencode($error));
    exit();
} else {
    header("Location: Book.php?error=" . urlencode("No flights found matching your criteria"));
    exit();
}
?>