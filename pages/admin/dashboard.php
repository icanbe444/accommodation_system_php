<?php
session_start();

// Set timezone to Nigeria (GMT+1)
date_default_timezone_set('Africa/Lagos');

if (!isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "?page=login");
    exit;
}

require_once(__DIR__ . '/../../includes/functions.php');
require_once(__DIR__ . '/../../includes/email_functions.php');

// Handle export
if (isset($_GET['action']) && $_GET['action'] === 'export_bookings') {
    require_once(__DIR__ . '/export_bookings.php');
    exit;
}

// Handle send invoice/receipt
if (isset($_GET['action']) && $_GET['action'] === 'send_invoice') {
    $booking_id = $_GET['id'] ?? '';
    
    if ($booking_id) {
        $booking_query = "SELECT b.*, h.name as hotel_name, h.price_per_night, h.location FROM bookings b 
                        LEFT JOIN hotels h ON b.hotel_id = h.id WHERE b.id = ?";
        $booking_stmt = $conn->prepare($booking_query);
        $booking_stmt->bind_param("i", $booking_id);
        $booking_stmt->execute();
        $booking_result = $booking_stmt->get_result();
        $booking = $booking_result->fetch_assoc();
        
        if ($booking) {
            $hotel_query = "SELECT * FROM hotels WHERE id = ?";
            $hotel_stmt = $conn->prepare($hotel_query);
            $hotel_stmt->bind_param("i", $booking['hotel_id']);
            $hotel_stmt->execute();
            $hotel_result = $hotel_stmt->get_result();
            $hotel = $hotel_result->fetch_assoc();
            
            sendInvoiceEmailWithPDF($booking, $hotel);
            $_SESSION['success_message'] = "Invoice sent to " . $booking['guest_email'];
        }
        
        header("Location: " . BASE_URL . "?page=admin&menu=bookings");
        exit;
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'send_receipt') {
    $booking_id = $_GET['id'] ?? '';
    
    if ($booking_id) {
        $booking_query = "SELECT b.*, h.name as hotel_name, h.price_per_night, h.location FROM bookings b 
                        LEFT JOIN hotels h ON b.hotel_id = h.id WHERE b.id = ?";
        $booking_stmt = $conn->prepare($booking_query);
        $booking_stmt->bind_param("i", $booking_id);
        $booking_stmt->execute();
        $booking_result = $booking_stmt->get_result();
        $booking = $booking_result->fetch_assoc();
        
        if ($booking) {
            $hotel_query = "SELECT * FROM hotels WHERE id = ?";
            $hotel_stmt = $conn->prepare($hotel_query);
            $hotel_stmt->bind_param("i", $booking['hotel_id']);
            $hotel_stmt->execute();
            $hotel_result = $hotel_stmt->get_result();
            $hotel = $hotel_result->fetch_assoc();
            
            sendReceiptEmailWithPDF($booking, $hotel);
            $_SESSION['success_message'] = "Receipt sent to " . $booking['guest_email'];
        }
        
        header("Location: " . BASE_URL . "?page=admin&menu=bookings");
        exit;
    }
}

$action = $_GET['action'] ?? '';
$edit_id = $_GET['id'] ?? '';
$menu = $_GET['menu'] ?? 'analytics';
$page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$items_per_page = 10;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_booking') {
            $id = $_POST['id'];
            $guest_name = $_POST['guest_name'];
            $guest_email = $_POST['guest_email'];
            $phone = $_POST['phone'];
            $check_in = $_POST['check_in'];
            $check_out = $_POST['check_out'];
            $hotel_id = $_POST['hotel_id'];
            $status = $_POST['status'];
            
            $query = "UPDATE bookings SET guest_name = ?, guest_email = ?, phone = ?, check_in = ?, check_out = ?, hotel_id = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssi", $guest_name, $guest_email, $phone, $check_in, $check_out, $hotel_id, $status, $id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Booking updated successfully!";
                header("Location: " . BASE_URL . "?page=admin&menu=bookings");
                exit;
            }
        } elseif ($_POST['action'] === 'delete_booking') {
            $id = $_POST['id'];
            $query = "DELETE FROM bookings WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Booking deleted successfully!";
                header("Location: " . BASE_URL . "?page=admin&menu=bookings");
                exit;
            }
        } elseif ($_POST['action'] === 'cancel_booking') {
            $id = $_POST['id'];
            $status = 'cancelled';
            $query = "UPDATE bookings SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $status, $id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Booking cancelled successfully!";
                header("Location: " . BASE_URL . "?page=admin&menu=bookings");
                exit;
            }
        } elseif ($_POST['action'] === 'update_hotel') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $location = $_POST['location'];
            $price_per_night = $_POST['price_per_night'];
            $description = $_POST['description'];
            
            $query = "UPDATE hotels SET name = ?, location = ?, price_per_night = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssdsi", $name, $location, $price_per_night, $description, $id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Hotel updated successfully!";
                header("Location: " . BASE_URL . "?page=admin&menu=hotels");
                exit;
            }
        } elseif ($_POST['action'] === 'add_hotel') {
            $name = $_POST['name'];
            $location = $_POST['location'];
            $price_per_night = $_POST['price_per_night'];
            $description = $_POST['description'];
            
            $query = "INSERT INTO hotels (name, location, price_per_night, description) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssds", $name, $location, $price_per_night, $description);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Hotel added successfully!";
                header("Location: " . BASE_URL . "?page=admin&menu=hotels");
                exit;
            }
        } elseif ($_POST['action'] === 'delete_hotel') {
            $id = $_POST['id'];
            $query = "DELETE FROM hotels WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Hotel deleted successfully!";
                header("Location: " . BASE_URL . "?page=admin&menu=hotels");
                exit;
            }
        }
    }
}

// Get all bookings for analytics
$all_bookings_query = "SELECT b.*, h.name as hotel_name FROM bookings b LEFT JOIN hotels h ON b.hotel_id = h.id ORDER BY b.id DESC";
$all_bookings_result = $conn->query($all_bookings_query);
$all_bookings = $all_bookings_result->fetch_all(MYSQLI_ASSOC);

// Calculate analytics
$total_bookings = count($all_bookings);
$total_revenue = 0;
$confirmed_count = 0;
$pending_count = 0;
$cancelled_count = 0;

foreach ($all_bookings as $booking) {
    $total_revenue += $booking['total_price'];
    if ($booking['status'] === 'confirmed') $confirmed_count++;
    if ($booking['status'] === 'pending') $pending_count++;
    if ($booking['status'] === 'cancelled') $cancelled_count++;
}

// Get bookings by hotel
$bookings_by_hotel = array();
$bookings_by_payment = array();

foreach ($all_bookings as $booking) {
    $hotel = $booking['hotel_name'] ?? 'Unknown';
    $payment = $booking['payment_method'] ?? 'Not specified';
    
    if (!isset($bookings_by_hotel[$hotel])) {
        $bookings_by_hotel[$hotel] = 0;
    }
    $bookings_by_hotel[$hotel]++;
    
    if (!isset($bookings_by_payment[$payment])) {
        $bookings_by_payment[$payment] = 0;
    }
    $bookings_by_payment[$payment]++;
}

// Get bookings for current page with search
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
    $bookings_query = "SELECT b.*, h.name as hotel_name FROM bookings b 
                     LEFT JOIN hotels h ON b.hotel_id = h.id 
                     WHERE b.guest_name LIKE ? OR b.guest_email LIKE ? OR b.phone LIKE ? OR b.id LIKE ?
                     ORDER BY b.id DESC";
    $search_param = '%' . $search_query . '%';
    $stmt = $conn->prepare($bookings_query);
    $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $bookings_result = $stmt->get_result();
    $bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);
} else {
    $bookings_query = "SELECT b.*, h.name as hotel_name FROM bookings b LEFT JOIN hotels h ON b.hotel_id = h.id ORDER BY b.id DESC";
    $bookings_result = $conn->query($bookings_query);
    $bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);
}

// Pagination for bookings
$total_bookings_page = count($bookings);
$total_pages = ceil($total_bookings_page / $items_per_page);
$start = ($page - 1) * $items_per_page;
$bookings_paginated = array_slice($bookings, $start, $items_per_page);

// Get hotels with pagination
$hotels_page = isset($_GET['hotels_page']) ? max(1, intval($_GET['hotels_page'])) : 1;
$hotels_query = "SELECT * FROM hotels ORDER BY name ASC";
$hotels_result = $conn->query($hotels_query);
$hotels = $hotels_result->fetch_all(MYSQLI_ASSOC);

$total_hotels = count($hotels);
$total_hotels_pages = ceil($total_hotels / $items_per_page);
$hotels_start = ($hotels_page - 1) * $items_per_page;
$hotels_paginated = array_slice($hotels, $hotels_start, $items_per_page);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TPAIS Accommodation</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f5f5;
        color: #333;
    }

    .dashboard-container {
        padding: 0;
        max-width: 100%;
        width: 100%;
        margin: 0;
    }

    /* HORIZONTAL TAB MENU */
    .tab-menu {
        display: flex;
        gap: 10px;
        margin-bottom: 0;
        border-bottom: 2px solid #ddd;
        flex-wrap: wrap;
        padding: 0 20px;
        background-color: white;
    }

    .tab-button {
        padding: 12px 20px;
        background-color: transparent;
        border: none;
        cursor: pointer;
        font-weight: 500;
        border-radius: 0;
        transition: all 0.3s ease;
        text-decoration: none;
        color: #333;
        display: inline-block;
        border-bottom: 3px solid transparent;
    }

    .tab-button:hover {
        background-color: #f0f0f0;
    }

    .tab-button.active {
        background-color: transparent;
        color: #0099CC;
        border-bottom: 3px solid #0099CC;
    }

    /* ANALYTICS SECTION */
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
        padding: 20px;
        background-color: #f5f5f5;
    }

    .analytics-card {
        background-color: white;
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid #0099CC;
    }

    .analytics-card h3 {
        color: #0099CC;
        font-size: 14px;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .analytics-card p {
        font-size: 32px;
        font-weight: bold;
        color: #0099CC;
        margin: 0;
    }

    /* TABLE STYLES */
    .table-container {
        background-color: white;
        padding: 20px;
        border-radius: 0;
        box-shadow: none;
        overflow-x: auto;
        width: 100%;
        margin-bottom: 0;
        border-top: 1px solid #ddd;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        table-layout: auto;
        min-width: 100%;
    }

    th {
        background-color: #0099CC;
        color: white;
        padding: 12px 15px;
        text-align: left;
        font-weight: bold;
        white-space: nowrap;
    }

    td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* COLUMN WIDTH HINTS (not strict) */
    table th:nth-child(1),
    table td:nth-child(1) { min-width: 70px; } /* Booking ID */

    table th:nth-child(2),
    table td:nth-child(2) { min-width: 120px; } /* Guest Name */

    table th:nth-child(3),
    table td:nth-child(3) { min-width: 150px; } /* Email */

    table th:nth-child(4),
    table td:nth-child(4) { min-width: 110px; } /* Phone */

    table th:nth-child(5),
    table td:nth-child(5) { min-width: 120px; } /* Hotel */

    table th:nth-child(6),
    table td:nth-child(6) { min-width: 100px; } /* Check-in */

    table th:nth-child(7),
    table td:nth-child(7) { min-width: 100px; } /* Check-out */

    table th:nth-child(8),
    table td:nth-child(8) { min-width: 100px; } /* Amount */

    table th:nth-child(9),
    table td:nth-child(9) { min-width: 110px; } /* Payment */

    table th:nth-child(10),
    table td:nth-child(10) { min-width: 90px; } /* Status */

    table th:nth-child(11),
    table td:nth-child(11) { min-width: 130px; } /* Created At */

    table th:nth-child(12),
    table td:nth-child(12) { min-width: 150px; } /* Actions */

    tr:hover {
        background-color: #f9f9f9;
    }

    /* ACTION BUTTONS */
    .action-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3px;
        white-space: normal;
    }

    .btn-small {
        padding: 3px 6px;
        font-size: 10px;
        border: none;
        border-radius: 2px;
        cursor: pointer;
        text-decoration: none;
        display: block;
        transition: all 0.2s ease;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .btn-small-edit {
        background-color: #0099CC;
        color: white;
    }

    .btn-small-edit:hover {
        background-color: #0077a3;
    }

    .btn-small-delete {
        background-color: #dc3545;
        color: white;
    }

    .btn-small-delete:hover {
        background-color: #c82333;
    }

    .btn-small-cancel {
        background-color: #ff9800;
        color: white;
    }

    .btn-small-cancel:hover {
        background-color: #e68900;
    }

    .btn-small-invoice {
        background-color: #17a2b8;
        color: white;
    }

    .btn-small-invoice:hover {
        background-color: #138496;
    }

    .btn-small-receipt {
        background-color: #28a745;
        color: white;
    }

    .btn-small-receipt:hover {
        background-color: #218838;
    }

    /* SEARCH BOX */
    .search-box {
        margin-bottom: 20px;
        padding: 20px;
        background-color: white;
        border-top: 1px solid #ddd;
    }

    .search-box input {
        padding: 10px;
        width: 300px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
    }

    .search-box button {
        padding: 10px 20px;
        background-color: #0099CC;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-left: 5px;
    }

    /* FORM STYLES */
    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .btn {
        padding: 10px 20px;
        background-color: #0099CC;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }

    .btn:hover {
        background-color: #0077a3;
    }

    .btn-secondary {
        background-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .btn-danger {
        background-color: #dc3545;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    /* PAGINATION */
    .pagination {
        margin-top: 20px;
        text-align: center;
        padding: 20px;
        background-color: white;
        border-top: 1px solid #ddd;
    }

    .pagination a,
    .pagination span {
        padding: 8px 12px;
        margin: 0 2px;
        background-color: #f0f0f0;
        border: 1px solid #ddd;
        border-radius: 3px;
        text-decoration: none;
        color: #0099CC;
        display: inline-block;
    }

    .pagination a:hover {
        background-color: #0099CC;
        color: white;
    }

    .pagination span.active {
        background-color: #0099CC;
        color: white;
        border-color: #0099CC;
    }

    .pagination span.disabled {
        color: #999;
        cursor: not-allowed;
    }

    /* SUCCESS/ERROR MESSAGES */
    .alert {
        padding: 15px 20px;
        margin-bottom: 0;
        border-radius: 0;
        border-bottom: 1px solid #ddd;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: none;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: none;
    }

    /* EXPORT BUTTON */
    .export-button {
        margin-bottom: 20px;
        padding: 20px;
        background-color: white;
        border-top: 1px solid #ddd;
    }

    .export-button a {
        padding: 10px 20px;
        background-color: #28a745;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        display: inline-block;
    }

    .export-button a:hover {
        background-color: #218838;
    }
</style>





</head>
<body>

<div class="dashboard-container">
    <!-- MESSAGES -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- HORIZONTAL TAB MENU -->
    <div class="tab-menu">
        <a href="<?php echo BASE_URL; ?>?page=admin&menu=analytics" class="tab-button <?php echo ($menu === 'analytics') ? 'active' : ''; ?>">📊 Analytics</a>
        <a href="<?php echo BASE_URL; ?>?page=admin&menu=bookings" class="tab-button <?php echo ($menu === 'bookings') ? 'active' : ''; ?>">📅 Bookings</a>
        <a href="<?php echo BASE_URL; ?>?page=admin&menu=hotels" class="tab-button <?php echo ($menu === 'hotels') ? 'active' : ''; ?>">🏨 Hotels</a>
        <?php if ($_SESSION['admin_role'] === 'admin'): ?>
            <a href="<?php echo BASE_URL; ?>?page=admin&menu=users" class="tab-button <?php echo ($menu === 'users') ? 'active' : ''; ?>">👥 Users</a>
        <?php endif; ?>
    </div>

    <!-- ANALYTICS SECTION -->
    <?php if ($menu === 'analytics'): ?>
        <div class="analytics-grid">
            <div class="analytics-card" style="border-left-color: #0099CC;">
                <h3>Total Bookings</h3>
                <p><?php echo $total_bookings; ?></p>
            </div>
            <div class="analytics-card" style="border-left-color: #28a745;">
                <h3>Total Revenue</h3>
                <p>₦<?php echo number_format($total_revenue, 2); ?></p>
            </div>
            <div class="analytics-card" style="border-left-color: #28a745;">
                <h3>Confirmed Bookings</h3>
                <p><?php echo $confirmed_count; ?></p>
            </div>
            <div class="analytics-card" style="border-left-color: #ff9800;">
                <h3>Pending Bookings</h3>
                <p><?php echo $pending_count; ?></p>
            </div>
            <div class="analytics-card" style="border-left-color: #dc3545;">
                <h3>Cancelled Bookings</h3>
                <p><?php echo $cancelled_count; ?></p>
            </div>
        </div>

        <!-- BOOKINGS BY HOTEL -->
        <div class="table-container" style="margin-bottom: 30px;">
            <h3 style="margin-bottom: 15px;">Bookings by Hotel</h3>
            <table>
                <tr>
                    <th>Hotel Name</th>
                    <th style="text-align: right;">Number of Bookings</th>
                </tr>
                <?php foreach ($bookings_by_hotel as $hotel => $count): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hotel); ?></td>
                        <td style="text-align: right;"><?php echo $count; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- BOOKINGS BY PAYMENT METHOD -->
        <div class="table-container">
            <h3 style="margin-bottom: 15px;">Bookings by Payment Method</h3>
            <table>
                <tr>
                    <th>Payment Method</th>
                    <th style="text-align: right;">Number of Bookings</th>
                </tr>
                <?php foreach ($bookings_by_payment as $payment => $count): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payment); ?></td>
                        <td style="text-align: right;"><?php echo $count; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>

    <!-- BOOKINGS SECTION -->
    <?php if ($menu === 'bookings'): ?>
        <div class="search-box">
            <form method="GET" style="display: inline;">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="menu" value="bookings">
                <input type="text" name="search" placeholder="Search by name, email, phone, or booking ID" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="export-button">
            <a href="<?php echo BASE_URL; ?>?page=admin&action=export_bookings">📥 Export to CSV</a>
        </div>

        <div class="table-container">
            <table>
                <tr>
                    <th>Booking ID</th>
                    <th>Guest Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Hotel</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Amount (₦)</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($bookings_paginated as $booking): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['guest_email']); ?></td>
                        <td><?php echo htmlspecialchars($booking['phone'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($booking['hotel_name'] ?? 'N/A'); ?></td>
                        <td><?php echo isset($booking['check_in']) ? $booking['check_in'] : ''; ?></td>
                        <td><?php echo isset($booking['check_out']) ? $booking['check_out'] : ''; ?></td>
                        <td style="text-align: right;">₦<?php echo number_format($booking['total_price'] ?? 0, 2); ?></td>
                        <td><?php echo htmlspecialchars($booking['payment_method'] ?? 'N/A'); ?></td>
                        <td>
                            <span style="padding: 4px 8px; border-radius: 3px; background-color: <?php echo ($booking['status'] === 'confirmed') ? '#d4edda' : (($booking['status'] === 'pending') ? '#fff3cd' : '#f8d7da'); ?>; color: <?php echo ($booking['status'] === 'confirmed') ? '#155724' : (($booking['status'] === 'pending') ? '#856404' : '#721c24'); ?>;">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($booking['created_at'] ?? 'now')); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo BASE_URL; ?>?page=admin&menu=bookings&action=edit_booking&id=<?php echo $booking['id']; ?>" class="btn-small btn-small-edit">Edit</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this booking?');">
                                    <input type="hidden" name="action" value="delete_booking">
                                    <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" class="btn-small btn-small-delete">Delete</button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Cancel this booking?');">
                                    <input type="hidden" name="action" value="cancel_booking">
                                    <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" class="btn-small btn-small-cancel">Cancel</button>
                                </form>
                                <a href="<?php echo BASE_URL; ?>?page=admin&action=send_invoice&id=<?php echo $booking['id']; ?>" class="btn-small btn-small-invoice">Invoice</a>
                                <a href="<?php echo BASE_URL; ?>?page=admin&action=send_receipt&id=<?php echo $booking['id']; ?>" class="btn-small btn-small-receipt">Receipt</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?php echo BASE_URL; ?>?page=admin&menu=bookings&page_num=1">First</a>
                    <a href="<?php echo BASE_URL; ?>?page=admin&menu=bookings&page_num=<?php echo $page - 1; ?>">Previous</a>
                <?php else: ?>
                    <span class="disabled">First</span>
                    <span class="disabled">Previous</span>
                <?php endif; ?>

                <?php 
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                ?>

                <?php if ($start_page > 1): ?>
                    <span>...</span>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>?page=admin&menu=bookings&page_num=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <span>...</span>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="<?php echo BASE_URL; ?>?page=admin&menu=bookings&page_num=<?php echo $page + 1; ?>">Next</a>
                    <a href="<?php echo BASE_URL; ?>?page=admin&menu=bookings&page_num=<?php echo $total_pages; ?>">Last</a>
                <?php else: ?>
                    <span class="disabled">Next</span>
                    <span class="disabled">Last</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- EDIT BOOKING FORM -->
        <?php if ($action === 'edit_booking' && $edit_id): ?>
            <?php
            $booking_query = "SELECT * FROM bookings WHERE id = ?";
            $stmt = $conn->prepare($booking_query);
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();
            ?>
            <div class="table-container" style="margin-top: 30px;">
                <h3 style="margin-bottom: 20px;">Edit Booking #<?php echo $booking['id']; ?></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_booking">
                    <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                    
                    <div class="form-group">
                        <label>Guest Name</label>
                        <input type="text" name="guest_name" value="<?php echo htmlspecialchars($booking['guest_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Guest Email</label>
                        <input type="email" name="guest_email" value="<?php echo htmlspecialchars($booking['guest_email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($booking['phone']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Check-in Date</label>
                        <input type="date" name="check_in" value="<?php echo $booking['check_in']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Check-out Date</label>
                        <input type="date" name="check_out" value="<?php echo $booking['check_out']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Hotel</label>
                        <select name="hotel_id" required>
                            <?php foreach ($hotels as $hotel): ?>
                                <option value="<?php echo $hotel['id']; ?>" <?php echo ($hotel['id'] == $booking['hotel_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($hotel['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="pending" <?php echo ($booking['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo ($booking['status'] === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="cancelled" <?php echo ($booking['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Update Booking</button>
                    <a href="<?php echo BASE_URL; ?>?page=admin&menu=bookings" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- HOTELS SECTION -->
    <?php if ($menu === 'hotels'): ?>
        <div class="table-container" style="margin-bottom: 30px;">
            <h3 style="margin-bottom: 20px;">Add New Hotel</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_hotel">
                
                <div class="form-group">
                    <label>Hotel Name</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" required>
                </div>
                
                <div class="form-group">
                    <label>Price Per Night (₦)</label>
                    <input type="number" name="price_per_night" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>
                
                <button type="submit" class="btn">Add Hotel</button>
            </form>
        </div>

        <div class="table-container">
            <h3 style="margin-bottom: 20px;">All Hotels</h3>
            <table>
                <tr>
                    <th>Hotel Name</th>
                    <th>Location</th>
                    <th style="text-align: right;">Price Per Night (₦)</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($hotels_paginated as $hotel): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hotel['name']); ?></td>
                        <td><?php echo htmlspecialchars($hotel['location']); ?></td>
                        <td style="text-align: right;">₦<?php echo number_format($hotel['price_per_night'], 2); ?></td>
                        <td><?php echo htmlspecialchars(substr($hotel['description'] ?? '', 0, 50)); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo BASE_URL; ?>?page=admin&menu=hotels&action=edit_hotel&id=<?php echo $hotel['id']; ?>" class="btn-small btn-small-edit">Edit</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this hotel?');">
                                    <input type="hidden" name="action" value="delete_hotel">
                                    <input type="hidden" name="id" value="<?php echo $hotel['id']; ?>">
                                    <button type="submit" class="btn-small btn-small-delete">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- HOTELS PAGINATION -->
        <?php if ($total_hotels_pages > 1): ?>
            <div class="pagination">
                <?php if ($hotels_page > 1): ?>
                    <a href="<?php echo BASE_URL; ?>?page=admin&menu=hotels&hotels_page=1">First</a>
                    <a href="<?php echo BASE_URL; ?>?page=admin&menu=hotels&hotels_page=<?php echo $hotels_page - 1; ?>">Previous</a>
                <?php else: ?>
                    <span class="disabled">First</span>
                    <span class="disabled">Previous</span>
                <?php endif; ?>

                <?php 
                $start_page = max(1, $hotels_page - 2);
                $end_page = min($total_hotels_pages, $hotels_page + 2);
                ?>

                <?php if ($start_page > 1): ?>
                    <span>...</span>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <?php if ($i === $hotels_page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>?page=admin&menu=hotels&hotels_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end_page < $total_hotels_pages): ?>
                    <span>...</span>
                <?php endif; ?>

                <?php if ($hotels_page < $total_hotels_pages): ?>
                    <a href="<?php echo BASE_URL; ?>?page=admin&menu=hotels&hotels_page=<?php echo $hotels_page + 1; ?>">Next</a>
                    <a href="<?php echo BASE_URL; ?>?page=admin&menu=hotels&hotels_page=<?php echo $total_hotels_pages; ?>">Last</a>
                <?php else: ?>
                    <span class="disabled">Next</span>
                    <span class="disabled">Last</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- EDIT HOTEL FORM -->
        <?php if ($action === 'edit_hotel' && $edit_id): ?>
            <?php
            $hotel_query = "SELECT * FROM hotels WHERE id = ?";
            $stmt = $conn->prepare($hotel_query);
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $hotel = $result->fetch_assoc();
            ?>
            <div class="table-container" style="margin-top: 30px;">
                <h3 style="margin-bottom: 20px;">Edit Hotel: <?php echo htmlspecialchars($hotel['name']); ?></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_hotel">
                    <input type="hidden" name="id" value="<?php echo $hotel['id']; ?>">
                    
                    <div class="form-group">
                        <label>Hotel Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($hotel['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($hotel['location']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Price Per Night (₦)</label>
                        <input type="number" name="price_per_night" step="0.01" value="<?php echo $hotel['price_per_night']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description"><?php echo htmlspecialchars($hotel['description']); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Update Hotel</button>
                    <a href="<?php echo BASE_URL; ?>?page=admin&menu=hotels" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- USERS SECTION (ADMIN ONLY) -->
    <?php if ($menu === 'users' && $_SESSION['admin_role'] === 'admin'): ?>
        <?php require_once(__DIR__ . '/users.php'); ?>
    <?php endif; ?>
</div>

</body>
</html>
