<?php
// Add these functions to your existing email_functions.php file

function sendInvoiceEmailWithPDF($booking, $hotel) {
    $guest_email = $booking['guest_email'];
    $guest_name = $booking['guest_name'];
    $invoice_link = (defined('BASE_URL') ? BASE_URL : 'https://accommodation.tpais-events.com/' ) . '?page=view_invoice&id=' . $booking['id'] . '&type=invoice';
    
    $subject = "Invoice - TPAIS Accommodation Booking #" . $booking['id'];
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #0099CC; color: white; padding: 20px; text-align: center; border-radius: 4px 4px 0 0; }
            .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .footer { background-color: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 4px 4px; }
            .button { display: inline-block; background-color: #0099CC; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Invoice - TPAIS Accommodation</h2>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($guest_name) . ",</p>
                
                <p>Thank you for booking with TPAIS Accommodation! Your invoice is ready.</p>
                
                <p><strong>Booking Details:</strong></p>
                <ul>
                    <li>Booking ID: #" . $booking['id'] . "</li>
                    <li>Hotel: " . htmlspecialchars($hotel['name']) . "</li>
                    <li>Check-in: " . $booking['check_in'] . "</li>
                    <li>Check-out: " . $booking['check_out'] . "</li>
                    <li>Total Amount: ₦" . number_format($booking['total_price'], 2) . "</li>
                </ul>
                
                <p><strong>Please make payment to:</strong></p>
                <ul>
                    <li><strong>GTB Account:</strong> 0156645648</li>
                    <li><strong>Diamond Bank Account:</strong> 0050125800</li>
                </ul>
                
                <p>After making the transfer, please send the payment receipt to: <strong>contactus@tpais-events.com</strong></p>
                
                <center>
                    <a href='" . $invoice_link . "' class='button'>View Invoice</a>
                </center>
                
                <p>You can also print or save the invoice as a PDF from the link above.</p>
                
                <p>If you have any questions, please don't hesitate to contact us.</p>
                
                <p>Best regards,  
TPAIS Accommodation Team</p>
            </div>
            <div class='footer'>
                <p>Email: contactus@tpais-events.com</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: contactus@tpais-events.com\r\n";
    $headers .= "Reply-To: contactus@tpais-events.com\r\n";
    
    return mail($guest_email, $subject, $message, $headers);
}

function sendReceiptEmailWithPDF($booking, $hotel) {
    $guest_email = $booking['guest_email'];
    $guest_name = $booking['guest_name'];
    $receipt_link = (defined('BASE_URL') ? BASE_URL : 'https://accommodation.tpais-events.com/' ) . '?page=view_invoice&id=' . $booking['id'] . '&type=receipt';
    
    $subject = "Payment Receipt - TPAIS Accommodation Booking #" . $booking['id'];
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #0099CC; color: white; padding: 20px; text-align: center; border-radius: 4px 4px 0 0; }
            .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .footer { background-color: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 4px 4px; }
            .button { display: inline-block; background-color: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
            .success { color: #28a745; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Payment Receipt - TPAIS Accommodation</h2>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($guest_name) . ",</p>
                
                <p><span class='success'>✓ Payment Received!</span></p>
                
                <p>Thank you for your payment! Your booking is now confirmed. Your receipt is below.</p>
                
                <p><strong>Booking Details:</strong></p>
                <ul>
                    <li>Booking ID: #" . $booking['id'] . "</li>
                    <li>Hotel: " . htmlspecialchars($hotel['name']) . "</li>
                    <li>Check-in: " . $booking['check_in'] . "</li>
                    <li>Check-out: " . $booking['check_out'] . "</li>
                    <li>Total Amount Paid: ₦" . number_format($booking['total_price'], 2) . "</li>
                    <li>Payment Method: " . ucfirst($booking['payment_method']) . "</li>
                </ul>
                
                <center>
                    <a href='" . $receipt_link . "' class='button'>View Receipt</a>
                </center>
                
                <p>You can also print or save the receipt as a PDF from the link above.</p>
                
                <p>We look forward to hosting you! If you have any questions, please don't hesitate to contact us.</p>
                
                <p>Best regards,  
TPAIS Accommodation Team</p>
            </div>
            <div class='footer'>
                <p>Email: contactus@tpais-events.com</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: contactus@tpais-events.com\r\n";
    $headers .= "Reply-To: contactus@tpais-events.com\r\n";
    
    return mail($guest_email, $subject, $message, $headers);
}
?>
