<?php
$hotels = get_hotels();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $name = $_POST['name'] ?? '';
            $location = $_POST['location'] ?? '';
            $price_per_night = $_POST['price_per_night'] ?? '';
            $distance_to_venue = $_POST['distance_to_venue'] ?? '';
            $rating = $_POST['rating'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if ($name && $location && $price_per_night) {
                if (create_hotel($name, $location, $price_per_night, $distance_to_venue, $rating, $description)) {
                    $_SESSION['success_message'] = "Hotel created successfully.";
                } else {
                    $_SESSION['error_message'] = "Failed to create hotel.";
                }
            } else {
                $_SESSION['error_message'] = "Please fill in required fields.";
            }
            header("Location: " . BASE_URL . "?page=admin-hotels");
            exit;
        } elseif ($_POST['action'] === 'update') {
            $hotel_id = $_POST['hotel_id'] ?? '';
            $data = [
                'name' => $_POST['name'] ?? '',
                'location' => $_POST['location'] ?? '',
                'price_per_night' => $_POST['price_per_night'] ?? '',
                'distance_to_venue' => $_POST['distance_to_venue'] ?? '',
                'rating' => $_POST['rating'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];
            
            if (update_hotel($hotel_id, $data)) {
                $_SESSION['success_message'] = "Hotel updated successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to update hotel.";
            }
            header("Location: " . BASE_URL . "?page=admin-hotels");
            exit;
        } elseif ($_POST['action'] === 'delete') {
            $hotel_id = $_POST['hotel_id'] ?? '';
            if (delete_hotel($hotel_id)) {
                $_SESSION['success_message'] = "Hotel deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to delete hotel.";
            }
            header("Location: " . BASE_URL . "?page=admin-hotels");
            exit;
        }
    }
}

// Refresh hotels list
$hotels = get_hotels();
?>

<h2>Manage Hotels</h2>

<button onclick="openAddHotelModal()" class="btn" style="margin-bottom: 1.5rem;">+ Add New Hotel</button>

<div style="overflow-x: auto;">
    <table>
        <thead>
            <tr>
                <th>Hotel Name</th>
                <th>Location</th>
                <th>Price/Night</th>
                <th>Distance to Venue</th>
                <th>Rating</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($hotels) > 0): ?>
                <?php foreach ($hotels as $hotel): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($hotel['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($hotel['location']); ?></td>
                        <td>$<?php echo number_format($hotel['price_per_night'], 2); ?></td>
                        <td><?php echo $hotel['distance_to_venue'] ? $hotel['distance_to_venue'] . ' km' : 'N/A'; ?></td>
                        <td><?php echo $hotel['rating'] ? $hotel['rating'] . '★' : 'N/A'; ?></td>
                        <td>
                            <button onclick="editHotel(<?php echo $hotel['id']; ?>)" class="btn" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Edit</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                                <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem;">No hotels found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Hotel Modal -->
<div id="hotelModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; margin: 5% auto; padding: 2rem; width: 90%; max-width: 500px; border-radius: 4px; max-height: 90vh; overflow-y: auto;">
        <h3 id="modalTitle">Add New Hotel</h3>
        <form id="hotelForm" method="POST">
            <input type="hidden" name="action" id="hotelAction" value="create">
            <input type="hidden" name="hotel_id" id="hotelId">
            
            <div class="form-group">
                <label>Hotel Name *</label>
                <input type="text" id="hotelName" name="name" required>
            </div>
            <div class="form-group">
                <label>Location *</label>
                <input type="text" id="hotelLocation" name="location" required>
            </div>
            <div class="form-group">
                <label>Price per Night ($) *</label>
                <input type="number" id="hotelPrice" name="price_per_night" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Distance to Venue (km)</label>
                <input type="number" id="hotelDistance" name="distance_to_venue" step="0.01">
            </div>
            <div class="form-group">
                <label>Rating (1-5)</label>
                <input type="number" id="hotelRating" name="rating" step="0.1" min="1" max="5">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="hotelDescription" name="description" rows="4"></textarea>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn" style="flex: 1;">Save Hotel</button>
                <button type="button" onclick="closeHotelModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddHotelModal() {
    document.getElementById('modalTitle').textContent = 'Add New Hotel';
    document.getElementById('hotelAction').value = 'create';
    document.getElementById('hotelForm').reset();
    document.getElementById('hotelModal').style.display = 'block';
}

function editHotel(hotelId) {
    document.getElementById('modalTitle').textContent = 'Edit Hotel';
    document.getElementById('hotelAction').value = 'update';
    document.getElementById('hotelId').value = hotelId;
    document.getElementById('hotelModal').style.display = 'block';
    // In a real app, fetch hotel data via AJAX and populate fields
}

function closeHotelModal() {
    document.getElementById('hotelModal').style.display = 'none';
}
</script>
