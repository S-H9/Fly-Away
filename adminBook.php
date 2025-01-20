<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: Book.php');
    exit();
}

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

$success_message = '';
$error_message = '';

// Handle flight management actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_flight'])) {
        // Add new flight
        $departure_city = $conn->real_escape_string($_POST['departure_city']);
        $arrival_city = $conn->real_escape_string($_POST['arrival_city']);
        $departure_time = $conn->real_escape_string($_POST['departure_time']);
        $arrival_time = $conn->real_escape_string($_POST['arrival_time']);
        $price = floatval($_POST['price']);

        $sql = "INSERT INTO flights (departure_city, arrival_city, departure_time, arrival_time, price) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssd", $departure_city, $arrival_city, $departure_time, $arrival_time, $price);
        
        if ($stmt->execute()) {
            $success_message = "Flight added successfully!";
        } else {
            $error_message = "Error adding flight: " . $conn->error;
        }
    } elseif (isset($_POST['edit_flight'])) {
        // Edit existing flight
        $flight_id = intval($_POST['flight_id']);
        $departure_city = $conn->real_escape_string($_POST['departure_city']);
        $arrival_city = $conn->real_escape_string($_POST['arrival_city']);
        $departure_time = $conn->real_escape_string($_POST['departure_time']);
        $arrival_time = $conn->real_escape_string($_POST['arrival_time']);
        $price = floatval($_POST['price']);

        $sql = "UPDATE flights SET departure_city=?, arrival_city=?, departure_time=?, arrival_time=?, price=? 
                WHERE flight_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdi", $departure_city, $arrival_city, $departure_time, $arrival_time, $price, $flight_id);
        
        if ($stmt->execute()) {
            $success_message = "Flight updated successfully!";
        } else {
            $error_message = "Error updating flight: " . $conn->error;
        }
    } elseif (isset($_POST['delete_flight'])) {
        // Delete flight
        $flight_id = intval($_POST['flight_id']);
        
        $sql = "DELETE FROM flights WHERE flight_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $flight_id);
        
        if ($stmt->execute()) {
            $success_message = "Flight deleted successfully!";
        } else {
            $error_message = "Error deleting flight: " . $conn->error;
        }
    }
}

// Fetch all flights
$flights_sql = "SELECT * FROM flights ORDER BY departure_time ASC";
$flights_result = $conn->query($flights_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Flight Management</title>
    <style>
     
       /* Base styles */
       :root {
        --primary-color: #0b587c;
        --secondary-color: #48a7d4;
        --danger-color: #dc3545;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --text-light: #ffffff;
        --text-dark: #333333;
        --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        min-height: 100vh;
        padding-top: 80px;
        color: var(--text-dark);
    }

    /* Navbar */
    .navbar {
        background-color: rgba(255, 255, 255, 0.95);
        padding: 1rem 2rem;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        box-shadow: var(--shadow-md);
        backdrop-filter: blur(10px);
    }

    .navbar-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* .logo-container {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .logo-container img {
        height: 50px;
        width: auto;
    } */

    .nav-links {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .nav-links a {
        text-decoration: none;
        color: var(--text-dark);
        padding: 0.8rem 1.5rem;
        border-radius: 25px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .nav-links a:hover {
        background-color: rgba(11, 88, 124, 0.1);
    }

    .nav-links a.active {
        background-color: var(--primary-color);
        color: var(--text-light);
    }

    /* Main Container */
    .booking-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem;
        background-color: rgba(255, 255, 255, 0.95);
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
    }

    .booking-container h1 {
        color: var(--primary-color);
        margin-bottom: 2rem;
        text-align: center;
    }

    /* Forms */
    .admin-controls {
        background-color: #f8f9fa;
        padding: 2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }

    .flight-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .flight-form input {
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
    }

    .flight-form button {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 0.8rem;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .flight-form button:hover {
        background-color: var(--secondary-color);
        transform: translateY(-2px);
    }

    /* Table */
    .flight-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
    }

    .flight-table th,
    .flight-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .flight-table th {
        background-color: var(--primary-color);
        color: white;
    }

    .flight-table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    /* Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .edit-btn,
    .delete-btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .edit-btn {
        background-color: var(--warning-color);
        color: var(--text-dark);
    }

    .delete-btn {
        background-color: var(--danger-color);
        color: white;
    }

    .edit-btn:hover,
    .delete-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
    }

    /* Messages */
    .message {
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 1rem;
        text-align: center;
    }

    .success {
        background-color: var(--success-color);
        color: white;
    }

    .error {
        background-color: var(--danger-color);
        color: white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .booking-container {
            margin: 1rem;
            padding: 1rem;
        }

        .flight-form {
            grid-template-columns: 1fr;
        }

        .flight-table {
            display: block;
            overflow-x: auto;
        }

        .navbar {
            padding: 0.5rem 1rem;
        }

        .nav-links {
            gap: 0.5rem;
        }

        .nav-links a {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
    }
    .logo {
    width: 60px;  /* Increased from 50px */
    height: 60px; /* Increased from 50px */
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.profile-pic {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #0b587c;
    transition: transform 0.3s ease;
    
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
    </style>
</head>
<body>
<nav class="navbar">
    <div class="navbar-container">
        <div class="logo">
            <img src="imges/img.png" alt="Fly Away Logo" style="width: 60px; height: 60px;">
        </div>
        <div style="display: flex; align-items: center; position:absolute; left:1100px; top:20px;">
            <div class="nav-links" style="margin-right: 20px;">
                <a href="homepage.php">Home</a>
                <a href="#" class="active">Book</a>
                <a href="userFlights.php">Flights</a>
                
            </div>
            <div class="profile-pic"  style= "margin-left : -5px">
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
                        echo '<img src="' . htmlspecialchars($profile_image) . '" alt="Profile Picture">';
                    }
                ?>
            </div>
        </div>
    </div>
</nav>
    <div class="booking-container">
        <h1>Flight Management</h1>

        <?php if ($success_message): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="admin-controls">
            <h2>Add New Flight</h2>
            <form method="POST" class="flight-form">
                <input type="text" name="departure_city" placeholder="Departure City" required>
                <input type="text" name="arrival_city" placeholder="Arrival City" required>
                <input type="datetime-local" name="departure_time" required>
                <input type="datetime-local" name="arrival_time" required>
                <input type="number" name="price" step="0.01" placeholder="Price" required>
                <button type="submit" name="add_flight" class="book-button">Add Flight</button>
            </form>
        </div>

        <h2>Existing Flights</h2>
        <table class="flight-table">
            <thead>
                <tr>
                    <th>Flight ID</th>
                    <th>Departure City</th>
                    <th>Arrival City</th>
                    <th>Departure Time</th>
                    <th>Arrival Time</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($flight = $flights_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($flight['flight_id']); ?></td>
                        <td><?php echo htmlspecialchars($flight['departure_city']); ?></td>
                        <td><?php echo htmlspecialchars($flight['arrival_city']); ?></td>
                        <td><?php echo htmlspecialchars($flight['departure_time']); ?></td>
                        <td><?php echo htmlspecialchars($flight['arrival_time']); ?></td>
                        <td>$<?php echo htmlspecialchars(number_format($flight['price'], 2)); ?></td>
                        <td class="action-buttons">
                            <button onclick="editFlight(<?php echo htmlspecialchars(json_encode($flight)); ?>)" 
                                    class="edit-btn">Edit</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="flight_id" value="<?php echo $flight['flight_id']; ?>">
                                <button type="submit" name="delete_flight" 
                                        class="delete-btn" 
                                        onclick="return confirm('Are you sure you want to delete this flight?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        function editFlight(flight) {
            // Create edit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.className = 'flight-form';
            
            form.innerHTML = `
                <input type="hidden" name="flight_id" value="${flight.flight_id}">
                <input type="text" name="departure_city" value="${flight.departure_city}" required>
                <input type="text" name="arrival_city" value="${flight.arrival_city}" required>
                <input type="datetime-local" name="departure_time" 
                       value="${flight.departure_time.slice(0, 16)}" required>
                <input type="datetime-local" name="arrival_time" 
                       value="${flight.arrival_time.slice(0, 16)}" required>
                <input type="number" name="price" step="0.01" value="${flight.price}" required>
                <button type="submit" name="edit_flight" class="book-button">Update Flight</button>
            `;

            // Replace the add flight form with edit form
            document.querySelector('.flight-form').replaceWith(form);
        }
    </script>
</body>
</html>