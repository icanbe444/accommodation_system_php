<?php
session_start();

// Set timezone to Nigeria (GMT+1)
date_default_timezone_set('Africa/Lagos');

require_once(__DIR__ . '/../includes/functions.php');

$hotels_query = "SELECT * FROM hotels ORDER BY name ASC";
$hotels_result = $conn->query($hotels_query);
$hotels = $hotels_result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_hotel') {
    $guest_name = trim($_POST['guest_name'] ?? '');
    $guest_email = trim($_POST['guest_email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $check_in = trim($_POST['check_in'] ?? '');
    $check_out = trim($_POST['check_out'] ?? '');
    $hotel_id = intval($_POST['hotel_id'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? '');

    // Validation
    if (!$guest_name || !$guest_email || !$phone || !$check_in || !$check_out || !$hotel_id || !$payment_method) {
        $_SESSION['error_message'] = "Please fill in all required fields.";
    } else {
        // Get hotel details
        $hotel = null;
        foreach ($hotels as $h) {
            if ($h['id'] == $hotel_id) {
                $hotel = $h;
                break;
            }
        }

        if (!$hotel) {
            $_SESSION['error_message'] = "Selected hotel not found.";
        } else {
            // Calculate nights and total price
            $check_in_date = new DateTime($check_in);
            $check_out_date = new DateTime($check_out);
            $nights = $check_out_date->diff($check_in_date)->days;

            if ($nights <= 0) {
                $_SESSION['error_message'] = "Check-out date must be after check-in date.";
            } else {
                $total_price = $hotel['price_per_night'] * $nights;

                // Insert booking with current Nigerian time
                $created_at = date('Y-m-d H:i:s'); // This will be in Nigeria time (GMT+1)
                
                $query = "INSERT INTO bookings (guest_name, guest_email, phone, check_in, check_out, hotel_id, total_price, payment_method, status, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssssidss", $guest_name, $guest_email, $phone, $check_in, $check_out, $hotel_id, $total_price, $payment_method, $created_at);

                if ($stmt->execute()) {
                    $booking_id = $stmt->insert_id;
                    
                    // Send booking acknowledgment email
                    require_once(__DIR__ . '/../includes/email_functions.php');
                    sendBookingAcknowledgment(
                        [
                            'id' => $booking_id,
                            'guest_name' => $guest_name,
                            'guest_email' => $guest_email,
                            'phone' => $phone,
                            'check_in' => $check_in,
                            'check_out' => $check_out,
                            'total_price' => $total_price,
                            'payment_method' => $payment_method
                        ],
                        $hotel
                    );

                    header("Location: " . BASE_URL . "?page=payment&id=" . $booking_id);
                    exit;
                } else {
                    $_SESSION['error_message'] = "Error creating booking. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Accommodation - TPAIS</title>
</head>
<body>
    <div class="container" style="max-width: 600px; margin: 2rem auto;">
        <div class="card">
            <h2>Book Your Accommodation</h2>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="book_hotel">

                <div class="form-group">
                    <label for="guest_name">Full Name *</label>
                    <input type="text" id="guest_name" name="guest_name" required>
                </div>

                <div class="form-group">
                    <label for="guest_email">Email Address *</label>
                    <input type="email" id="guest_email" name="guest_email" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="check_in">Check-in Date *</label>
                    <input type="date" id="check_in" name="check_in" required>
                </div>

                <div class="form-group">
                    <label for="check_out">Check-out Date *</label>
                    <input type="date" id="check_out" name="check_out" required>
                </div>

                <div class="form-group">
                    <label for="hotel_id">Select Hotel *</label>
                    <select id="hotel_id" name="hotel_id" onchange="calculatePrice()" required>
                        <option value="">-- Select a Hotel --</option>
                        <?php foreach ($hotels as $hotel): ?>
                            <option value="<?php echo $hotel['id']; ?>" data-price="<?php echo $hotel['price_per_night']; ?>">
                                <?php echo htmlspecialchars($hotel['name']); ?> - ₦<?php echo number_format($hotel['price_per_night'], 2); ?>/night
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nights">Number of Nights</label>
                    <input type="number" id="nights" name="nights" readonly>
                </div>

                <div class="form-group">
                    <label for="total_price">Total Price</label>
                    <input type="text" id="total_price" name="total_price" readonly>
                </div>

                <div class="form-group">
                    <label for="payment_method">Payment Method *</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">-- Select Payment Method --</option>
                        <option value="paystack">Paystack (Card Payment)</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Proceed to Payment</button>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary" style="width: 100%; text-align: center; display: block; margin-top: 1rem;">Cancel</a>
            </form>
        </div>
    </div>

    <script>
        function calculatePrice() {
            const checkInInput = document.getElementById('check_in').value;
            const checkOutInput = document.getElementById('check_out').value;
            const hotelSelect = document.getElementById('hotel_id');
            const nightsInput = document.getElementById('nights');
            const totalPriceInput = document.getElementById('total_price');

            if (checkInInput && checkOutInput && hotelSelect.value) {
                const checkInDate = new Date(checkInInput);
                const checkOutDate = new Date(checkOutInput);
                const nights = Math.floor((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));

                if (nights > 0) {
                    const selectedOption = hotelSelect.options[hotelSelect.selectedIndex];
                    const pricePerNight = parseFloat(selectedOption.getAttribute('data-price'));
                    const totalPrice = nights * pricePerNight;

                    nightsInput.value = nights;
                    totalPriceInput.value = '₦' + totalPrice.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                } else {
                    nightsInput.value = 0;
                    totalPriceInput.value = '';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', calculatePrice);
        document.getElementById('check_in').addEventListener('change', calculatePrice);
        document.getElementById('check_out').addEventListener('change', calculatePrice);
        document.getElementById('hotel_id').addEventListener('change', calculatePrice);
    </script>
</body>
</html>
