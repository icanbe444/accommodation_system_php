<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "?page=login");
    exit;
}

require_once(__DIR__ . '/../../includes/functions.php');

// Get all bookings
$bookings_query = "SELECT b.*, h.name as hotel_name, h.price_per_night FROM bookings b LEFT JOIN hotels h ON b.hotel_id = h.id ORDER BY b.id DESC";
$bookings_result = $conn->query($bookings_query);
$bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);
?>

<h2>All Bookings</h2>

<div class="card">
    <div class="table-wrapper">
        <table>
            <tr>
                <th>Guest Name</th>
                <th>Email</th>
                <th>Hotel</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['guest_email']); ?></td>
                    <td><?php echo htmlspecialchars($booking['hotel_name'] ?? 'N/A'); ?></td>
                    <td><?php echo $booking['check_in']; ?></td>
                    <td><?php echo $booking['check_out']; ?></td>
                    <td>₦<?php echo number_format($booking['total_price'], 2); ?></td>
                    <td>
                        <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-weight: 500;
                            background-color: <?php echo ($booking['status'] === 'confirmed') ? '#d4edda' : (($booking['status'] === 'pending') ? '#fff3cd' : '#f8d7da'); ?>;
                            color: <?php echo ($booking['status'] === 'confirmed') ? '#155724' : (($booking['status'] === 'pending') ? '#856404' : '#721c24'); ?>;">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>?page=admin&menu=bookings&action=edit_booking&id=<?php echo $booking['id']; ?>" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
