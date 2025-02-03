<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: Flights.php');
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user_flight'])) {
        // Delete user's flight booking
        $booking_id = intval($_POST['booking_id']);
        $sql = "DELETE FROM bookings WHERE booking_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $booking_id);
            if ($stmt->execute()) {
                $success_message = "Flight booking deleted successfully!";
            } else {
                $error_message = "Error deleting flight booking: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    } elseif (isset($_POST['update_booking'])) {
        // Update booking details
        $booking_id = intval($_POST['booking_id']);
        $flight_id = intval($_POST['flight_id']);
        $class = htmlspecialchars($_POST['class']);
        $seat_number = htmlspecialchars($_POST['seat_number']);

        // Fetch current flight details to recalculate price
        $price_sql = "SELECT price FROM flights WHERE flight_id = ?";
        $price_stmt = $conn->prepare($price_sql);
        if ($price_stmt) {
            $price_stmt->bind_param("i", $flight_id);
            $price_stmt->execute();
            $price_result = $price_stmt->get_result();
            $flight_details = $price_result->fetch_assoc();

            $price_multiplier = [
                'economy' => 1,
                'business' => 1.5,
                'first' => 2
            ];

            $final_price = $flight_details['price'] * $price_multiplier[$class] ?? 1;

            // Update booking
            $update_sql = "UPDATE bookings 
                           SET flight_id = ?, class = ?, seat_number = ?, price = ?
                           WHERE booking_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt) {
                $update_stmt->bind_param("issdi", $flight_id, $class, $seat_number, $final_price, $booking_id);
                if ($update_stmt->execute()) {
                    $success_message = "Booking updated successfully!";
                } else {
                    $error_message = "Error updating booking: " . $update_stmt->error;
                }
                $update_stmt->close();
            } else {
                $error_message = "Error preparing update statement: " . $conn->error;
            }
            $price_stmt->close();
        } else {
            $error_message = "Error preparing price query: " . $conn->error;
        }
    }
}

// Fetch all flights for dropdown
$flights_query = "SELECT flight_id, departure_city, arrival_city, departure_time FROM flights";
$flights_result = $conn->query($flights_query);

// Fetch all user flight bookings with flight details
$bookings_sql = "SELECT b.*, f.departure_city, f.arrival_city, f.departure_time, f.arrival_time, 
                       u.username, u.email
                FROM bookings b
                JOIN flights f ON b.flight_id = f.flight_id
                JOIN users u ON b.user_id = u.user_id
                ORDER BY f.departure_time ASC";
$bookings_result = $conn->query($bookings_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Flight Bookings</title>
    <link rel="stylesheet" href="css/book.css">
    <style>
/* General Styles */
body {
    background: linear-gradient(135deg, #0b587c, #48a7d4);
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    color: #333;
}

/* Navbar Styles */



.navbar {
    position: fixed;
    top: 0;
    width: 100%;
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 1rem;
    z-index: 1000;
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
        gap: 2rem;
        align-items: center;
    }

  
/* Add/update these CSS rules */
:root {
    --primary-color: #0b587c;
    --text-light: #ffffff;
}

.nav-links a {
    text-decoration: none;
    color: #333;
    padding: 8px 16px;
    border-radius: 25px;  /* Make it more rounded like adminBook */
    transition: all 0.3s ease;
}

.nav-links a:hover {
    background-color: rgba(11, 88, 124, 0.1);
}

.nav-links a.active {
    background-color: #0b587c;  /* Use direct color instead of CSS variable */
    color: white;
}
    

/* Main Container */
.user-flights-container {
    margin: 120px auto 20px;
    max-width: 1200px;
    background: rgba(255, 255, 255, 0.95);
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.user-flights-container h1 {
    text-align: center;
    color: #0b587c;
    margin-bottom: 1.5rem;
}

/* Table Styles */
.flights-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.flights-table th, 
.flights-table td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

.flights-table th {
    background-color: #0b587c;
    color: white;
    font-weight: bold;
}

.flights-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.flights-table tr:hover {
    background-color: #f1f1f1;
}

/* Buttons */
.action-buttons {
    display: flex;
    gap: 10px;
}

.edit-btn, 
.delete-btn {
    padding: 5px 15px;
    border: none;
    border-radius: 5px;
    font-size: 0.9rem;
    cursor: pointer;
    color: white;
    transition: background-color 0.3s;
}

.edit-btn {
    background-color: #4caf50;
}

.edit-btn:hover {
    background-color: #45a049;
}

.delete-btn {
    background-color: #f44336;
}

.delete-btn:hover {
    background-color: #e41f1f;
}

/* Success and Error Messages */
.message {
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 1rem;
    text-align: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.success {
    background-color: #4caf50;
    color: white;
}

.error {
    background-color: #f44336;
    color: white;
}

/* Modal Styles */
#editModal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1500;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    max-width: 90%;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    animation: fadeIn 0.3s;
}

.modal-content h2 {
    margin-top: 0;
    color: #0b587c;
    text-align: center;
}

.modal-content input, 
.modal-content select {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.modal-content button {
    width: 48%;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
}

.modal-content .edit-btn {
    background-color: #4caf50;
    color: white;
}

.modal-content .edit-btn:hover {
    background-color: #45a049;
}

.modal-content .cancel-btn {
    background-color: #f44336;
    color: white;
}

.modal-content .cancel-btn:hover {
    background-color: #e41f1f;
}

/* Fade-in animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .nav-links {
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        
        
    }

    .flights-table {
        font-size: 0.85rem;
    }

    .action-buttons {
        flex-direction: column;
        gap: 5px;
    }

    .edit-btn, .delete-btn {
        width: 100%;
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
                <a href="adminBook.php">Book</a>
                <a href="#" class="active" style="background-color: #0b587c; color: white; padding: 14px 16px; border-radius: 25px;">Flights</a>
                
            </div>
            <?php include 'profilePopup.php'; ?>

        </div>
    </div>
</nav>

    <div class="user-flights-container">
        <h1>User Flight Bookings</h1>

        <?php if ($success_message): ?>
            <div class="message success"><?= htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <table class="flights-table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Departure City</th>
                    <th>Arrival City</th>
                    <th>Departure Time</th>
                    <th>Arrival Time</th>
                    <th>Class</th>
                    <th>Seat</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['booking_id']); ?></td>
                        <td><?= htmlspecialchars($booking['username']); ?></td>
                        <td><?= htmlspecialchars($booking['email']); ?></td>
                        <td><?= htmlspecialchars($booking['departure_city']); ?></td>
                        <td><?= htmlspecialchars($booking['arrival_city']); ?></td>
                        <td><?= htmlspecialchars($booking['departure_time']); ?></td>
                        <td><?= htmlspecialchars($booking['arrival_time']); ?></td>
                        <td><?= htmlspecialchars($booking['class']); ?></td>
                        <td><?= htmlspecialchars($booking['seat_number']); ?></td>
                        <td>$<?= number_format($booking['price'], 2); ?></td>
                        <td class="action-buttons">
                            <button onclick="openEditModal(<?= htmlspecialchars(json_encode($booking)); ?>)" 
                                    class="edit-btn">Edit</button>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                <input type="hidden" name="booking_id" value="<?= $booking['booking_id']; ?>">
                                <button type="submit" name="delete_user_flight" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Edit Booking</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="booking_id" id="editBookingId">
                <label>Flight:</label>
                <select name="flight_id" id="editFlightId" required>
                    <?php 
                    mysqli_data_seek($flights_result, 0); // Reset pointer
                    while ($flight = $flights_result->fetch_assoc()): ?>
                        <option value="<?= $flight['flight_id']; ?>">
                            <?= htmlspecialchars($flight['departure_city'] . ' to ' . $flight['arrival_city'] . ' - ' . $flight['departure_time']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Class:</label>
                <select name="class" id="editClass" required>
                    <option value="economy">Economy</option>
                    <option value="business">Business</option>
                    <option value="first">First Class</option>
                </select>

                <label>Seat Number:</label>
                <input type="text" name="seat_number" id="editSeat" required>

                <div style="display:flex; justify-content:space-between; margin-top:1rem;">
                    <button type="submit" name="update_booking" class="edit-btn">Update Booking</button>
                    <button type="button" onclick="closeEditModal()" 
                            style="background:#888; color:white; padding:10px; border:none; border-radius:5px;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(booking) {
            document.getElementById('editBookingId').value = booking.booking_id;
            document.getElementById('editFlightId').value = booking.flight_id;
            document.getElementById('editClass').value = booking.class;
            document.getElementById('editSeat').value = booking.seat_number;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>
