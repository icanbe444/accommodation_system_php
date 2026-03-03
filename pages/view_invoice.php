<?php
session_start();

// Set timezone to Nigeria (GMT+1)
date_default_timezone_set('Africa/Lagos');

require_once(__DIR__ . '/../includes/functions.php');

$booking_id = $_GET['id'] ?? '';
$type = $_GET['type'] ?? 'invoice'; // 'invoice' or 'receipt'

if (!$booking_id) {
    die('Booking not found.');
}

// Get booking details
$booking_query = "SELECT b.*, h.name as hotel_name, h.price_per_night, h.location FROM bookings b 
                LEFT JOIN hotels h ON b.hotel_id = h.id WHERE b.id = ?";
$booking_stmt = $conn->prepare($booking_query);
$booking_stmt->bind_param("i", $booking_id);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
$booking = $booking_result->fetch_assoc();

if (!$booking) {
    die('Booking not found.');
}

// Get hotel details
$hotel_query = "SELECT * FROM hotels WHERE id = ?";
$hotel_stmt = $conn->prepare($hotel_query);
$hotel_stmt->bind_param("i", $booking['hotel_id']);
$hotel_stmt->execute();
$hotel_result = $hotel_stmt->get_result();
$hotel = $hotel_result->fetch_assoc();

function calculateNights($checkIn, $checkOut) {
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    return $checkOutDate->diff($checkInDate)->days;
}

$nights = calculateNights($booking['check_in'], $booking['check_out']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($type); ?> - TPAIS Accommodation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Arial", sans-serif; background-color: #f5f5f5; padding: 10px; }
        .container { max-width: 800px; margin: 0 auto; background-color: white; padding: 20px; }
        
        .header { text-align: center; border-bottom: 2px solid #0099CC; padding-bottom: 10px; margin-bottom: 15px; }
        .logo-section { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 8px; }
        .logo { max-width: 60px; height: auto; }
        .company-name { font-size: 20px; font-weight: bold; color: #0099CC; }
        .company-details { font-size: 10px; color: #666; }
        
        <?php if ($type === 'invoice'): ?>
        .doc-header { background-color: #fff3cd; border-left: 3px solid #ff9800; padding: 10px; margin-bottom: 15px; }
        .doc-title { font-size: 16px; font-weight: bold; color: #ff9800; margin-bottom: 5px; }
        .doc-details { font-size: 11px; color: #856404; line-height: 1.4; }
        <?php else: ?>
        .doc-header { background-color: #d4edda; border-left: 3px solid #28a745; padding: 10px; margin-bottom: 15px; }
        .doc-title { font-size: 16px; font-weight: bold; color: #28a745; margin-bottom: 5px; }
        .doc-details { font-size: 11px; color: #155724; line-height: 1.4; }
        <?php endif; ?>
        
        .section { margin-bottom: 12px; }
        .section-title { font-size: 11px; font-weight: bold; background-color: #0099CC; color: white; padding: 6px; margin-bottom: 8px; }
        
        .info-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 10px; border-bottom: 1px solid #eee; }
        .info-label { font-weight: bold; color: #0099CC; }
        
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 10px; }
        th { background-color: #0099CC; color: white; padding: 6px; text-align: left; font-weight: bold; }
        td { padding: 6px; border-bottom: 1px solid #ddd; }
        
        .total-section { background-color: #f9f9f9; padding: 10px; margin: 10px 0; border-radius: 3px; }
        .total-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 11px; }
        .total-amount { font-weight: bold; color: #0099CC; font-size: 14px; }
        
        .bank-section { background-color: #fff3cd; border-left: 3px solid #ff9800; padding: 10px; margin: 10px 0; }
        .bank-title { font-weight: bold; color: #856404; margin-bottom: 4px; font-size: 10px; }
        .bank-details { font-size: 9px; color: #856404; line-height: 1.4; margin-bottom: 8px; }
        
        .success-message { background-color: #d4edda; border-left: 3px solid #28a745; padding: 10px; margin: 10px 0; }
        .success-text { font-size: 10px; color: #155724; line-height: 1.4; }
        
        .footer { text-align: center; margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 9px; color: #666; }
        
        .print-button { text-align: center; margin: 10px 0; }
        .print-button button { padding: 8px 15px; background-color: #0099CC; color: white; border: none; border-radius: 3px; cursor: pointer; font-weight: bold; font-size: 12px; }
        
        @media print {
            body { background-color: white; padding: 0; }
            .print-button { display: none; }
        }
        
        @page {
            size: A4;
            margin: 10mm;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- HEADER WITH LOGO -->
    <div class="header">
        <div class="logo-section">
            <img src="https://accommodation.tpais-events.com/images/TpaisLogo.png" alt="TPAIS Logo" class="logo">
            <div>
                <div class="company-name">TPAIS Accommodation</div>
                <div class="company-details">contactus@tpais-events.com</div>
            </div>
        </div>
    </div>

    <!-- DOCUMENT HEADER -->
    <div class="doc-header">
        <div class="doc-title"><?php echo ucfirst($type ); ?> #<?php echo $booking['id']; ?></div>
        <div class="doc-details">
            <strong>Date:</strong> <?php echo date('Y-m-d H:i'); ?> | 
            <strong>Status:</strong> <?php echo ($type === 'invoice') ? 'PENDING' : 'PAID'; ?>
        </div>
    </div>

    <!-- GUEST INFORMATION -->
    <div class="section">
        <div class="section-title">GUEST INFORMATION</div>
        <div class="info-row">
            <span class="info-label">Name:</span>
            <span><?php echo htmlspecialchars($booking['guest_name']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span><?php echo htmlspecialchars($booking['guest_email']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span><?php echo htmlspecialchars($booking['phone']); ?></span>
        </div>
    </div>

    <!-- BOOKING DETAILS -->
    <div class="section">
        <div class="section-title">BOOKING DETAILS</div>
        <table>
            <tr>
                <th>Hotel</th>
                <th style="text-align: right;">Nights</th>
                <th style="text-align: right;">Unit Price</th>
                <th style="text-align: right;">Amount</th>
            </tr>
            <tr>
                <td><?php echo htmlspecialchars($hotel['name']); ?></td>
                <td style="text-align: right;"><?php echo $nights; ?></td>
                <td style="text-align: right;">₦<?php echo number_format($hotel['price_per_night'], 2); ?></td>
                <td style="text-align: right;">₦<?php echo number_format($booking['total_price'], 2); ?></td>
            </tr>
        </table>
    </div>

    <!-- STAY DATES -->
    <div class="section">
        <div class="section-title">STAY DATES</div>
        <div class="info-row">
            <span class="info-label">Check-in:</span>
            <span><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Check-out:</span>
            <span><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></span>
        </div>
    </div>

    <!-- TOTAL SECTION -->
    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>₦<?php echo number_format($booking['total_price'], 2); ?></span>
        </div>
        <div class="total-row">
            <span>Tax:</span>
            <span>₦0.00</span>
        </div>
        <div class="total-row" style="margin-top: 6px; padding-top: 6px; border-top: 1px solid #ddd;">
            <span class="total-amount">TOTAL:</span>
            <span class="total-amount">₦<?php echo number_format($booking['total_price'], 2); ?></span>
        </div>
    </div>

    <?php if ($type === 'invoice'): ?>
    <!-- PAYMENT INSTRUCTIONS (FOR INVOICE) -->
    <div class="section">
        <div class="section-title">PAYMENT INSTRUCTIONS</div>
        <div class="bank-section">
            <div class="bank-title">GTB ACCOUNT</div>
            <div class="bank-details">
                <strong>Account #:</strong> 0156645648
            </div>
            
            <div class="bank-title">DIAMOND BANK ACCOUNT</div>
            <div class="bank-details">
                <strong>Account #:</strong> 0050125800
            </div>
            
            <div class="bank-details" style="font-size: 9px; color: #ff9800;">
                Send proof of payment to: contactus@tpais-events.com
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- PAYMENT CONFIRMATION (FOR RECEIPT) -->
    <div class="success-message">
        <div class="success-text">
            <strong>✓ Payment Confirmed</strong>  

            Your booking is confirmed. Thank you!
        </div>
    </div>
    <?php endif; ?>

    <!-- PRINT BUTTON -->
    <div class="print-button">
        <button onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Thank you for choosing TPAIS Accommodation!
    </div>
</div>

</body>
</html>
