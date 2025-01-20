<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database configuration
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

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_payment'])) {
    $flight_id = filter_var($_POST['flight_id'], FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];
    $class = filter_var($_POST['class'], FILTER_SANITIZE_STRING);
    $seat_number = filter_var($_POST['seat_number'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    
    if (!$flight_id || !in_array($class, ['economy', 'business', 'first'])) {
        $error_message = "Invalid booking information.";
    } else {
        $conn->begin_transaction();
        
        try {
            // Verify seat availability
            $check_sql = "SELECT COUNT(*) as seat_taken FROM bookings 
                        WHERE flight_id = ? AND seat_number = ? AND status = 'confirmed'";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("is", $flight_id, $seat_number);
            $check_stmt->execute();
            $is_taken = $check_stmt->get_result()->fetch_assoc()['seat_taken'];
            
            if ($is_taken) {
                throw new Exception("Selected seat is no longer available.");
            }
            
            // Process booking
            $insert_sql = "INSERT INTO bookings (user_id, flight_id, class, seat_number, price, status) 
                          VALUES (?, ?, ?, ?, ?, 'confirmed')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iissd", $user_id, $flight_id, $class, $seat_number, $price);
            
            if ($insert_stmt->execute()) {
                $conn->commit();
                // Add success tracking for analytics
                $booking_id = $conn->insert_id;
                header("Location: Flights.php?booking_id=" . $booking_id);
                exit();
            } else {
                throw new Exception("Error processing payment.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = $e->getMessage();
        }
    }
}

// Get user profile
$profile_sql = "SELECT profile_image, username FROM users WHERE user_id = ?";
$profile_stmt = $conn->prepare($profile_sql);
$profile_stmt->bind_param("i", $_SESSION['user_id']);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$user_data = $profile_result->fetch_assoc();
$profile_image = $user_data['profile_image'] ?? 'imges/default-profile.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - Fly Away</title>
    <link rel="icon" href="imges/img.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
    :root {
        --primary: #0b587c;
        --primary-dark: #094666;
        --primary-light: #48a7d4;
        --secondary: #f8f9fa;
        --success: #059669;
        --danger: #dc2626;
        --warning: #fbbf24;
        --white: #ffffff;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --radius-sm: 0.375rem;
        --radius-md: 0.5rem;
        --radius-lg: 1rem;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    body {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        color: var(--gray-800);
        line-height: 1.5;
    }

    /* Navbar Styles */
    .navbar {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 1rem 2rem;
        position: sticky;
        top: 0;
        z-index: 50;
        box-shadow: var(--shadow-md);
    }

    .nav-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: transform 0.3s ease;
    }

    .logo:hover {
        transform: scale(1.05);
    }

    .nav-links {
        display: flex;
        gap: 2rem;
        align-items: center;
    }

    .nav-links a {
        color: var(--white);
        text-decoration: none;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        transition: all 0.3s ease;
    }

    .nav-links a:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    .profile-button {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--white);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .profile-button:hover {
        transform: scale(1.1);
        border-color: var(--primary-light);
    }

    /* Main Content Styles */
    .main-container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1rem;
        flex: 1;
    }

    .payment-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .payment-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .payment-header h1 {
        color: var(--primary);
        font-size: 1.875rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .payment-header p {
        color: var(--gray-600);
    }

    .flight-summary {
        background: var(--gray-100);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--gray-200);
    }

    .summary-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .detail-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 500;
    }

    .detail-value {
        font-size: 1rem;
        color: var(--gray-800);
        font-weight: 600;
    }

    .payment-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
    }

    .form-group input {
        padding: 0.75rem 1rem;
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-md);
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(11, 88, 124, 0.1);
    }

    .card-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1rem;
    }

    .submit-button {
        background: var(--primary);
        color: var(--white);
        padding: 1rem;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }

    .submit-button:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .submit-button:active {
        transform: translateY(0);
    }

    .error-message {
        background: #fef2f2;
        border-left: 4px solid var(--danger);
        color: var(--danger);
        padding: 1rem;
        border-radius: var(--radius-md);
        margin-bottom: 1rem;
        font-weight: 500;
    }

    /* Footer Styles */
    .footer {
        background: var(--primary-dark);
        color: var(--white);
        padding: 3rem 1rem;
        margin-top: auto;
    }

    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }

    .footer-section h3 {
        color: var(--primary-light);
        margin-bottom: 1rem;
        font-size: 1.25rem;
    }

    .footer-section p {
        color: var(--gray-300);
        margin-bottom: 0.5rem;
    }

    .footer-bottom {
        text-align: center;
        padding-top: 2rem;
        margin-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--gray-400);
        font-size: 0.875rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .nav-container {
            flex-direction: column;
            gap: 1rem;
        }

        .nav-links {
            flex-direction: column;
            width: 100%;
        }

        .nav-links a {
            width: 100%;
            text-align: center;
        }

        .card-grid {
            grid-template-columns: 1fr;
        }

        .payment-card {
            padding: 1.5rem;
        }

        .summary-details {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .navbar {
            padding: 1rem;
        }

        .payment-header h1 {
            font-size: 1.5rem;
        }

        .footer-content {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="imges/img.png" alt="Fly Away Logo" width="60" height="60">
            </div>
            <div class="nav-links">
                <a href="homepage.php">Home</a>
                <a href="Book.php">Book</a>
                <a href="Flights.php">My Flights</a>
                <div class="profile-button">
                    <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" width="45" height="45">
                </div>
            </div>
        </div>
    </nav>

    <main class="main-container">
        <div class="payment-card">
            <div class="payment-header">
                <h1>Complete Your Payment</h1>
                <p>Secure payment processing for your flight booking</p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            






            <div id="flightSummary" class="flight-summary">
                <div class="summary-header">
                    <h2>Flight Summary</h2>
                    <div id="flightTotal" class="total-price"></div>
                </div>
                <div class="summary-details">
                    <!-- Flight details will be populated by JavaScript -->
                </div>
            </div>

            <form id="paymentForm" method="POST" class="payment-form">
                <input type="hidden" name="flight_id">
                <input type="hidden" name="class">
                <input type="hidden" name="seat_number">
                <input type="hidden" name="price">

                <div class="form-group">
                    <label for="cardNumber">Card Number</label>
                    <input 
                        type="text" 
                        id="cardNumber" 
                        pattern="\d{16}" 
                        required 
                        maxlength="16" 
                        placeholder="Enter 16-digit card number"
                        autocomplete="cc-number">
                </div>

                <div class="card-grid">
                    <div class="form-group">
                        <label for="expiryDate">Expiry Date</label>
                        <input 
                            type="text" 
                            id="expiryDate" 
                            pattern="\d{2}/\d{2}" 
                            required 
                            placeholder="MM/YY"
                            autocomplete="cc-exp">
                    </div>
                    
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input 
                            type="text" 
                            id="cvv" 
                            pattern="\d{3}" 
                            required 
                            maxlength="3" 
                            placeholder="Enter 3-digit CVV"
                            autocomplete="cc-csc">
                    </div>
                </div>

                <button type="submit" name="process_payment" class="submit-button">
                    Confirm Payment
                </button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Fly Away is dedicated to providing seamless and enjoyable flight booking experiences. Your journey starts here!</p>
            </div>
            <div class="footer-section">
                <h3>Contact Information</h3>
                <p>Email: support@flyaway.com</p>
                <p>Phone: +966 534 567 890</p>
                <p>Address: JUC UQU, CS</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <p><a href="homepage.php">Home</a></p>
                <p><a href="Book.php">Book a Flight</a></p>
                <p><a href="Flights.php">My Flights</a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Fly Away-JUC. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Format card number input
        const cardNumberInput = document.getElementById('cardNumber');
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });

        // Format expiry date input
        const expiryInput = document.getElementById('expiryDate');
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });

        // Format CVV input
        const cvvInput = document.getElementById('cvv');
        cvvInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });

        // Get flight details from session storage
        const flightDetails = JSON.parse(sessionStorage.getItem('flightDetails'));
        if (!flightDetails) {
            window.location.href = 'Book.php';
            return;
        }

        // Populate flight summary
        const summaryDetails = document.querySelector('.summary-details');
        summaryDetails.innerHTML = `
            <div class="detail-item">
                <span class="detail-label">From</span>
                <span class="detail-value">${flightDetails.departureCity}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">To</span>
                <span class="detail-value">${flightDetails.arrivalCity}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Departure</span>
                <span class="detail-value">${flightDetails.departureTime}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Class</span>
                <span class="detail-value">${flightDetails.class.charAt(0).toUpperCase() + flightDetails.class.slice(1)}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Seat</span>
                <span class="detail-value">${flightDetails.seatNumber}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Total Price</span>
                <span class="detail-value">$${parseFloat(flightDetails.price).toFixed(2)}</span>
            </div>
        `;

        // Set hidden form values
        document.querySelector('input[name="flight_id"]').value = flightDetails.flightId;
        document.querySelector('input[name="class"]').value = flightDetails.class;
        document.querySelector('input[name="seat_number"]').value = flightDetails.seatNumber;
        document.querySelector('input[name="price"]').value = flightDetails.price;

        // Form submission handling
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            // Basic client-side validation
            const cardNumber = cardNumberInput.value.replace(/\s/g, '');
            const expiry = expiryInput.value;
            const cvv = cvvInput.value;

            if (cardNumber.length !== 16) {
                e.preventDefault();
                alert('Please enter a valid 16-digit card number.');
                return;
            }

            if (!/^\d{2}\/\d{2}$/.test(expiry)) {
                e.preventDefault();
                alert('Please enter a valid expiry date (MM/YY).');
                return;
            }

            if (cvv.length !== 3) {
                e.preventDefault();
                alert('Please enter a valid 3-digit CVV.');
                return;
            }
        });
    });
    </script>
</body>
</html>