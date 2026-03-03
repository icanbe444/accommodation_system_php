<?php
$booking_id = $_GET['id'] ?? '';
$booking = $booking_id ? get_booking($booking_id) : null;

if (!$booking) {
    $_SESSION['error_message'] = "Booking not found.";
    header("Location: " . BASE_URL);
    exit;
}
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div class="card">
        <h3 style="color: #28a745; text-align: center;">✓ Booking Confirmed</h3>
        
        <p style="text-align: center; margin: 1.5rem 0; font-size: 1.1rem;">
            Thank you for your booking! A confirmation email has been sent to <strong><?php echo htmlspecialchars($booking['guest_email']); ?></strong>
        </p>

        <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 4px; margin: 1.5rem 0;">
            <h4 style="margin-bottom: 1rem;">Booking Details</h4>
            
            <p><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></p>
            <p><strong>Guest Name:</strong> <?php echo htmlspecialchars($booking['guest_name']); ?></p>
            <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?></p>
            <p><strong>Check-in Date:</strong> <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></p>
            <p><strong>Number of Nights:</strong> <?php echo $booking['nights']; ?></p>
            <p><strong>Price per Night:</strong> $<?php echo number_format($booking['price_per_night'], 2); ?></p>
            <p style="border-top: 2px solid var(--primary); padding-top: 1rem; margin-top: 1rem;">
                <strong style="font-size: 1.2rem;">Total Price: $<?php echo number_format($booking['total_price'], 2); ?></strong>
            </p>
        </div>

        <div style="background-color: #e7f3ff; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
            <p style="margin: 0;">
                <strong>Next Step:</strong> Please proceed to payment to confirm your reservation. You will receive payment instructions via email.
            </p>
        </div>

        <a href="<?php echo BASE_URL; ?>" class="btn" style="width: 100%; text-align: center;">Back to Home</a>
    </div>
</div>
