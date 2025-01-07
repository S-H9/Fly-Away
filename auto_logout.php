<?php
session_start();

// Only process if there's an active session
if (isset($_SESSION['user_id'])) {
    // Clear all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }

    // Destroy the session
    session_destroy();
}

// Return success status
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?>