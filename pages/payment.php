<?php
session_start();
require_once(__DIR__ . '/../includes/functions.php');
require_once(__DIR__ . '/../includes/email_functions.php');

$booking_id = $_GET['id'] ?? '';

if (!$booking_id) {
    $_SESSION['error_message'] = "Booking not found.";
    header("Location: " . BASE_URL);
    exit;
}

// Get booking details from database
$query = "SELECT b.*, h.name as hotel_name, h.price_per_night, h.location FROM bookings b 
          LEFT JOIN hotels h ON b.hotel_id = h.id WHERE b.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    $_SESSION['error_message'] = "Booking not found.";
    header("Location: " . BASE_URL);
    exit;
}

// Get hotel details
$hotel_query = "SELECT * FROM hotels WHERE id = ?";
$hotel_stmt = $conn->prepare($hotel_query);
$hotel_stmt->bind_param("i", $booking['hotel_id']);
$hotel_stmt->execute();
$hotel_result = $hotel_stmt->get_result();
$hotel = $hotel_result->fetch_assoc();

// Calculate nights from check_in and check_out
$check_in_date = new DateTime($booking['check_in']);
$check_out_date = new DateTime($booking['check_out']);
$nights = $check_out_date->diff($check_in_date)->days;

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_payment') {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if ($payment_method === 'bank_transfer') {
        // Bank transfer: mark as confirmed and send invoice email
        $query = "UPDATE bookings SET status = 'confirmed' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $booking_id);
        
        if ($stmt->execute()) {
            // Send invoice email
            sendInvoiceEmail($booking, $hotel);
            
            $_SESSION['success_message'] = "Booking confirmed! Invoice has been sent to your email.";
            header("Location: " . BASE_URL . "?page=confirmation&id=$booking_id");
            exit;
        }
    } elseif ($payment_method === 'paystack') {
        // Paystack: Initialize transaction via API
        $paystack_secret_key = "PAYSTACK_PUBLIC_KEY";
        $amount_in_kobo = intval($booking['total_price'] * 100);
        
        $reference = 'booking_' . $booking_id . '_' . time();
        
        $post_data = array(
            "amount" => $amount_in_kobo,
            "email" => $booking['guest_email'],
            "reference" => $reference,
            "metadata" => array(
                "booking_id" => $booking_id,
                "guest_name" => $booking['guest_name']
            )
        );
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.paystack.co/transaction/initialize" );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . $paystack_secret_key,
            "Content-Type: application/json"
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $result = json_decode($response, true);
        
        if (isset($result['status']) && $result['status'] === true && isset($result['data']['authorization_url'])) {
            // Store payment reference in session for webhook verification
            $_SESSION['paystack_reference'] = $reference;
            $_SESSION['booking_id'] = $booking_id;
            
            // Redirect to Paystack
            header("Location: " . $result['data']['authorization_url']);
            exit;
        } else {
            $_SESSION['error_message'] = "Error initializing Paystack payment. Please try again.";
        }
    }
}
?>

<div style="max-width: 600px; margin: 2rem auto;">
    <div class="card">
        <h2>Payment</h2>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 4px; margin: 1.5rem 0;">
            <h3 style="margin-top: 0;">Booking Summary</h3>
            <p><strong>Guest:</strong> <?php echo htmlspecialchars($booking['guest_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['guest_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?></p>
            <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?></p>
            <p><strong>Check-in:</strong> <?php echo $booking['check_in']; ?></p>
            <p><strong>Check-out:</strong> <?php echo $booking['check_out']; ?></p>
            <p><strong>Nights:</strong> <?php echo $nights; ?></p>
            <p><strong>Price per Night:</strong> ₦<?php echo number_format($booking['price_per_night'], 2); ?></p>
            <p style="border-top: 2px solid var(--primary); padding-top: 1rem; margin-top: 1rem;">
                <strong style="font-size: 1.2rem;">Total: ₦<?php echo number_format($booking['total_price'], 2); ?></strong>
            </p>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="process_payment">

            <div class="form-group">
                <label for="payment_method">Select Payment Method *</label>
                <select id="payment_method" name="payment_method" required onchange="updatePaymentInfo()">
                    <option value="">Choose payment method</option>
                    <option value="paystack">Paystack</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>

            <div id="bankTransferInfo" style="display: none; background-color: #e7f3ff; padding: 1.5rem; border-radius: 4px; margin: 1.5rem 0; border-left: 4px solid var(--primary);">
                <h4 style="color: var(--primary); margin-bottom: 1rem;">Bank Transfer Details</h4>
                
                <p><strong>Account Name:</strong> The Personal Assistant Int'l Service</p>
                
                <p style="margin-top: 1rem;">
                    <strong>GTB Account:</strong>  

                    <span style="font-family: monospace; font-size: 1.1rem; background-color: white; padding: 0.5rem; border-radius: 4px; display: inline-block;">0156645648</span>
                </p>
                
                <p style="margin-top: 1rem;">
                    <strong>Diamond Account:</strong>  

                    <span style="font-family: monospace; font-size: 1.1rem; background-color: white; padding: 0.5rem; border-radius: 4px; display: inline-block;">0050125800</span>
                </p>
                
                <div style="background-color: #fff3cd; padding: 1rem; border-radius: 4px; margin-top: 1.5rem; border-left: 4px solid #ff9800;">
                    <p style="margin: 0;"><strong>Important:</strong></p>
                    <p style="margin: 0.5rem 0 0 0;">
                        After making the transfer, please send the payment receipt to: <strong>contactus@tpais-events.com</strong>
                    </p>
                </div>
            </div>

            <button type="submit" class="btn" style="width: 100%; margin-bottom: 1rem;">Proceed with Payment</button>
            <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary" style="width: 100%; text-align: center; display: block;">Cancel</a>
        </form>
    </div>
</div>

<script>
function updatePaymentInfo() {
    const method = document.getElementById('payment_method').value;
    const bankInfo = document.getElementById('bankTransferInfo');
    
    if (method === 'bank_transfer') {
        bankInfo.style.display = 'block';
    } else {
        bankInfo.style.display = 'none';
    }
}
</script>
