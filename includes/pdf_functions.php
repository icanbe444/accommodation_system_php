<?php
// Simple PDF generation - generates HTML that can be printed as PDF

function generateInvoicePDF($booking, $hotel) {
    // Create HTML content for PDF
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: "Arial", sans-serif; background-color: #f5f5f5; padding: 20px; }
            .container { max-width: 800px; margin: 0 auto; background-color: white; padding: 40px; }
            .header { text-align: center; border-bottom: 3px solid #0099CC; padding-bottom: 30px; margin-bottom: 30px; }
            .logo-section { display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
            .logo { max-width: 80px; height: auto; margin-right: 20px; }
            .company-info { text-align: left; }
            .company-name { font-size: 28px; font-weight: bold; color: #0099CC; margin-bottom: 5px; }
            .company-details { font-size: 12px; color: #666; line-height: 1.6; }
            .invoice-header { display: flex; justify-content: space-between; margin-bottom: 30px; padding: 20px; background-color: #f9f9f9; border-radius: 5px; }
            .invoice-title { font-size: 24px; font-weight: bold; color: #0099CC; margin-bottom: 10px; }
            .invoice-details { font-size: 13px; color: #333; line-height: 1.8; }
            .invoice-details strong { color: #0099CC; }
            .section { margin-bottom: 30px; }
            .section-title { font-size: 14px; font-weight: bold; background-color: #0099CC; color: white; padding: 12px 15px; margin-bottom: 15px; border-radius: 3px; }
            .section-content { padding: 0 15px; }
            .info-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; border-bottom: 1px solid #eee; }
            .info-label { font-weight: bold; color: #0099CC; }
            .info-value { color: #333; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th { background-color: #0099CC; color: white; padding: 12px; text-align: left; font-weight: bold; font-size: 13px; }
            td { padding: 12px; border-bottom: 1px solid #ddd; font-size: 13px; }
            tr:last-child td { border-bottom: none; }
            .total-section { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 20px; }
            .total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
            .total-amount { font-weight: bold; color: #0099CC; font-size: 18px; }
            .bank-section { background-color: #fff3cd; border-left: 4px solid #ff9800; padding: 20px; margin: 20px 0; border-radius: 3px; }
            .bank-title { font-weight: bold; color: #856404; margin-bottom: 10px; font-size: 13px; }
            .bank-details { font-size: 12px; color: #856404; line-height: 1.8; margin-bottom: 10px; }
            .bank-details:last-child { margin-bottom: 0; }
            .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
            .footer-text { margin-bottom: 5px; }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- HEADER WITH LOGO -->
            <div class="header">
                <div class="logo-section">
                    <img src="https://accommodation.tpais-events.com/images/TpaisLogo.png" alt="TPAIS Logo" class="logo">
                    <div class="company-info">
                        <div class="company-name">TPAIS Accommodation</div>
                        <div class="company-details">
                            Email: contactus@tpais-events.com  

                            Phone: +234 (0 ) XXX XXX XXXX
                        </div>
                    </div>
                </div>
            </div>

            <!-- INVOICE HEADER -->
            <div class="invoice-header">
                <div>
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-details">
                        <strong>Invoice #:</strong> ' . $booking['id'] . '  

                        <strong>Date:</strong> ' . date('Y-m-d') . '  

                        <strong>Status:</strong> <span style="color: #ff9800;">PENDING</span>
                    </div>
                </div>
                <div class="invoice-details">
                    <strong>Due Date:</strong> Upon Confirmation  

                    <strong>Payment Status:</strong> Awaiting Payment
                </div>
            </div>

            <!-- GUEST INFORMATION -->
            <div class="section">
                <div class="section-title">GUEST INFORMATION</div>
                <div class="section-content">
                    <div class="info-row">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value">' . htmlspecialchars($booking['guest_name']) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">' . htmlspecialchars($booking['guest_email']) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value">' . htmlspecialchars($booking['phone']) . '</span>
                    </div>
                </div>
            </div>

            <!-- BOOKING DETAILS -->
            <div class="section">
                <div class="section-title">BOOKING DETAILS</div>
                <div class="section-content">
                    <table>
                        <tr>
                            <th>Hotel</th>
                            <th>Nights</th>
                            <th style="text-align: right;">Unit Price</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                        <tr>
                            <td>' . htmlspecialchars($hotel['name']) . '</td>
                            <td>' . calculateNights($booking['check_in'], $booking['check_out']) . '</td>
                            <td style="text-align: right;">₦' . number_format($hotel['price_per_night'], 2) . '</td>
                            <td style="text-align: right;">₦' . number_format($booking['total_price'], 2) . '</td>
                        </tr>
                    </table>
                    
                    <div class="total-section">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>₦' . number_format($booking['total_price'], 2) . '</span>
                        </div>
                        <div class="total-row">
                            <span>Tax:</span>
                            <span>₦0.00</span>
                        </div>
                        <div class="total-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                            <span class="total-amount">TOTAL:</span>
                            <span class="total-amount">₦' . number_format($booking['total_price'], 2) . '</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STAY DATES -->
            <div class="section">
                <div class="section-title">STAY DATES</div>
                <div class="section-content">
                    <div class="info-row">
                        <span class="info-label">Check-in:</span>
                        <span class="info-value">' . date('M d, Y', strtotime($booking['check_in'])) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Check-out:</span>
                        <span class="info-value">' . date('M d, Y', strtotime($booking['check_out'])) . '</span>
                    </div>
                </div>
            </div>

            <!-- PAYMENT INSTRUCTIONS -->
            <div class="section">
                <div class="section-title">PAYMENT INSTRUCTIONS</div>
                <div class="section-content">
                    <p style="margin-bottom: 15px; font-size: 13px; color: #333;">Please make payment to one of the following accounts and send proof of payment to <strong>contactus@tpais-events.com</strong></p>
                    
                    <div class="bank-section">
                        <div class="bank-title">GTB ACCOUNT</div>
                        <div class="bank-details">
                            <strong>Account Number:</strong> 0156645648  

                            <strong>Account Name:</strong> TPAIS
                        </div>
                        
                        <div class="bank-title" style="margin-top: 15px;">DIAMOND BANK ACCOUNT</div>
                        <div class="bank-details">
                            <strong>Account Number:</strong> 0050125800  

                            <strong>Account Name:</strong> TPAIS
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="footer">
                <div class="footer-text">Thank you for choosing TPAIS Accommodation!</div>
                <div class="footer-text">We look forward to hosting you.</div>
            </div>
        </div>
    </body>
    </html>
    ';

    return $html;
}

function generateReceiptPDF($booking, $hotel) {
    // Create HTML content for PDF
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: "Arial", sans-serif; background-color: #f5f5f5; padding: 20px; }
            .container { max-width: 800px; margin: 0 auto; background-color: white; padding: 40px; }
            .header { text-align: center; border-bottom: 3px solid #0099CC; padding-bottom: 30px; margin-bottom: 30px; }
            .logo-section { display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
            .logo { max-width: 80px; height: auto; margin-right: 20px; }
            .company-info { text-align: left; }
            .company-name { font-size: 28px; font-weight: bold; color: #0099CC; margin-bottom: 5px; }
            .company-details { font-size: 12px; color: #666; line-height: 1.6; }
            .receipt-header { display: flex; justify-content: space-between; margin-bottom: 30px; padding: 20px; background-color: #d4edda; border-radius: 5px; border-left: 4px solid #28a745; }
            .receipt-title { font-size: 24px; font-weight: bold; color: #28a745; margin-bottom: 10px; }
            .receipt-details { font-size: 13px; color: #155724; line-height: 1.8; }
            .receipt-details strong { color: #28a745; }
            .section { margin-bottom: 30px; }
            .section-title { font-size: 14px; font-weight: bold; background-color: #0099CC; color: white; padding: 12px 15px; margin-bottom: 15px; border-radius: 3px; }
            .section-content { padding: 0 15px; }
            .info-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; border-bottom: 1px solid #eee; }
            .info-label { font-weight: bold; color: #0099CC; }
            .info-value { color: #333; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th { background-color: #0099CC; color: white; padding: 12px; text-align: left; font-weight: bold; font-size: 13px; }
            td { padding: 12px; border-bottom: 1px solid #ddd; font-size: 13px; }
            tr:last-child td { border-bottom: none; }
            .total-section { background-color: #d4edda; padding: 15px; border-radius: 5px; margin-top: 20px; }
            .total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
            .total-amount { font-weight: bold; color: #28a745; font-size: 18px; }
            .success-message { background-color: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin: 20px 0; border-radius: 3px; }
            .success-text { font-size: 13px; color: #155724; line-height: 1.8; }
            .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
            .footer-text { margin-bottom: 5px; }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- HEADER WITH LOGO -->
            <div class="header">
                <div class="logo-section">
                    <img src="https://accommodation.tpais-events.com/images/TpaisLogo.png" alt="TPAIS Logo" class="logo">
                    <div class="company-info">
                        <div class="company-name">TPAIS Accommodation</div>
                        <div class="company-details">
                            Email: contactus@tpais-events.com  

                            Phone: +234 (0 ) XXX XXX XXXX
                        </div>
                    </div>
                </div>
            </div>

            <!-- RECEIPT HEADER -->
            <div class="receipt-header">
                <div>
                    <div class="receipt-title">✓ PAYMENT RECEIPT</div>
                    <div class="receipt-details">
                        <strong>Receipt #:</strong> ' . $booking['id'] . '  

                        <strong>Date:</strong> ' . date('Y-m-d H:i') . '  

                        <strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">PAID</span>
                    </div>
                </div>
                <div class="receipt-details">
                    <strong>Payment Method:</strong> ' . ucfirst($booking['payment_method']) . '  

                    <strong>Booking Status:</strong> <span style="color: #28a745; font-weight: bold;">CONFIRMED</span>
                </div>
            </div>

            <!-- GUEST INFORMATION -->
            <div class="section">
                <div class="section-title">GUEST INFORMATION</div>
                <div class="section-content">
                    <div class="info-row">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value">' . htmlspecialchars($booking['guest_name']) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">' . htmlspecialchars($booking['guest_email']) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value">' . htmlspecialchars($booking['phone']) . '</span>
                    </div>
                </div>
            </div>

            <!-- BOOKING DETAILS -->
            <div class="section">
                <div class="section-title">BOOKING DETAILS</div>
                <div class="section-content">
                    <table>
                        <tr>
                            <th>Hotel</th>
                            <th>Nights</th>
                            <th style="text-align: right;">Unit Price</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                        <tr>
                            <td>' . htmlspecialchars($hotel['name']) . '</td>
                            <td>' . calculateNights($booking['check_in'], $booking['check_out']) . '</td>
                            <td style="text-align: right;">₦' . number_format($hotel['price_per_night'], 2) . '</td>
                            <td style="text-align: right;">₦' . number_format($booking['total_price'], 2) . '</td>
                        </tr>
                    </table>
                    
                    <div class="total-section">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>₦' . number_format($booking['total_price'], 2) . '</span>
                        </div>
                        <div class="total-row">
                            <span>Tax:</span>
                            <span>₦0.00</span>
                        </div>
                        <div class="total-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                            <span class="total-amount">TOTAL PAID:</span>
                            <span class="total-amount">₦' . number_format($booking['total_price'], 2) . '</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STAY DATES -->
            <div class="section">
                <div class="section-title">STAY DATES</div>
                <div class="section-content">
                    <div class="info-row">
                        <span class="info-label">Check-in:</span>
                        <span class="info-value">' . date('M d, Y', strtotime($booking['check_in'])) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Check-out:</span>
                        <span class="info-value">' . date('M d, Y', strtotime($booking['check_out'])) . '</span>
                    </div>
                </div>
            </div>

            <!-- PAYMENT CONFIRMATION -->
            <div class="success-message">
                <div class="success-text">
                    <strong>✓ Payment Confirmed</strong>  

                    Your payment has been successfully received. Your booking is confirmed. Thank you for your payment!
                </div>
            </div>

            <!-- FOOTER -->
            <div class="footer">
                <div class="footer-text">Thank you for choosing TPAIS Accommodation!</div>
                <div class="footer-text">We look forward to hosting you.</div>
            </div>
        </div>
    </body>
    </html>
    ';

    return $html;
}

function calculateNights($checkIn, $checkOut) {
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    return $checkOutDate->diff($checkInDate)->days;
}
?>
