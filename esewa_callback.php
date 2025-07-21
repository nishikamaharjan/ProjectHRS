<?php
session_start();
include 'config.php';

// Get data from POST or GET
$data = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
    $data = $_POST['data'];
} elseif (isset($_GET['data'])) {
    $data = $_GET['data'];
}

if ($data) {
    $response = json_decode(base64_decode($data), true);
    $booking_id = null;
    // Extract booking_id from transaction_uuid
    if (isset($response['transaction_uuid'])) {
        if (preg_match('/BOOKING-(\\d+)-/', $response['transaction_uuid'], $matches)) {
            $booking_id = $matches[1];
        }
    }

    if ($booking_id) {
        if ($response['status'] === 'COMPLETE') {
            // Payment success
            $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE booking_id = ?");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            $stmt->close();
            header('Location: booking_confirmation.php');
            exit;
        } else {
            // Payment failed
            $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            $stmt->close();
            echo '<h2>Payment Failed or Cancelled</h2>';
            echo '<p>Your payment was not successful. Please try booking again.</p>';
            echo '<a href="room.php">Back to Rooms</a>';
            exit;
        }
    } else {
        // Invalid transaction UUID
        echo 'Invalid transaction UUID.';
        exit;
    }
} else {
    // No data parameter received
    echo 'Invalid callback or no data received.';
    exit;
} 