<?php
session_start();

// Include database connection
require_once 'database_connection.php'; // Adjust path as needed

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Prepare and execute query to get user details
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT name, email, profile_image FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Return user details as JSON
        echo json_encode([
            'name' => htmlspecialchars($row['name']),
            'email' => htmlspecialchars($row['email']),
            'profile_image' => !empty($row['profile_image']) 
                ? htmlspecialchars($row['profile_image']) 
                : 'imges/img4.jpeg'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
}
$conn->close();
?>