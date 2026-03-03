<?php
session_start();

// Set timezone to Nigeria (GMT+1)
date_default_timezone_set('Africa/Lagos');

if (!isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "?page=login");
    exit;
}

require_once(__DIR__ . '/../../includes/functions.php');

// Get all bookings
$bookings_query = "SELECT b.*, h.name as hotel_name FROM bookings b LEFT JOIN hotels h ON b.hotel_id = h.id ORDER BY b.id DESC";
$bookings_result = $conn->query($bookings_query);
$bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);

// Set headers for CSV download - MUST be before any output
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="bookings_' . date('Y-m-d_H-i-s') . '.csv"');

// Prevent any output buffering
if (ob_get_level()) ob_end_clean();

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write header row
fputcsv($output, array(
    'Booking ID',
    'Guest Name',
    'Email',
    'Phone',
    'Hotel',
    'Check-in Date',
    'Check-out Date',
    'Total Price (NGN)',
    'Payment Method',
    'Status',
    'Created At'
));

// Write data rows
foreach ($bookings as $booking) {
    // Format created_at to a readable date in Nigeria timezone
    $created_at = isset($booking['created_at']) ? date('Y-m-d H:i:s', strtotime($booking['created_at'])) : 'N/A';
    
    fputcsv($output, array(
        $booking['id'],
        $booking['guest_name'],
        $booking['guest_email'],
        $booking['phone'],
        $booking['hotel_name'],
        $booking['check_in'],
        $booking['check_out'],
        number_format($booking['total_price'], 2),  // Just the number without symbol
        $booking['payment_method'],
        $booking['status'],
        $created_at
    ));
}

fclose($output);
exit;
?>
