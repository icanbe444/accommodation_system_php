<?php

// Sanitize input
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

// Get all hotels
function get_hotels() {
    global $conn;
    $result = $conn->query("SELECT * FROM hotels ORDER BY name");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get hotel by ID
function get_hotel($id) {
    global $conn;
    $id = intval($id);
    $result = $conn->query("SELECT * FROM hotels WHERE id = $id");
    return $result->fetch_assoc();
}

// Get all bookings
function get_bookings($search = '') {
    global $conn;
    $query = "SELECT b.*, h.name as hotel_name, h.price_per_night FROM bookings b 
              JOIN hotels h ON b.hotel_id = h.id";
    
    if (!empty($search)) {
        $search = sanitize($search);
        $query .= " WHERE b.guest_name LIKE '%$search%' OR b.guest_email LIKE '%$search%'";
    }
    
    $query .= " ORDER BY b.booking_date DESC";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get booking by ID
function get_booking($id) {
    global $conn;
    $id = intval($id);
    $result = $conn->query("SELECT b.*, h.name as hotel_name, h.price_per_night FROM bookings b 
                            JOIN hotels h ON b.hotel_id = h.id WHERE b.id = $id");
    return $result->fetch_assoc();
}

// Create booking
function create_booking($guest_name, $guest_email, $guest_phone, $hotel_id, $nights, $total_price) {
    global $conn;
    
    $guest_name = sanitize($guest_name);
    $guest_email = sanitize($guest_email);
    $guest_phone = sanitize($guest_phone);
    $hotel_id = intval($hotel_id);
    $nights = intval($nights);
    $total_price = floatval($total_price);
    
    $query = "INSERT INTO bookings (guest_name, guest_email, guest_phone, hotel_id, nights, total_price, booking_date, status, payment_status) 
              VALUES ('$guest_name', '$guest_email', '$guest_phone', $hotel_id, $nights, $total_price, NOW(), 'pending', 'pending')";
    
    if ($conn->query($query)) {
        return $conn->insert_id;
    }
    return false;
}

// Update booking
function update_booking($id, $data) {
    global $conn;
    
    $id = intval($id);
    $updates = [];
    
    if (isset($data['guest_name'])) {
        $updates[] = "guest_name = '" . sanitize($data['guest_name']) . "'";
    }
    if (isset($data['guest_email'])) {
        $updates[] = "guest_email = '" . sanitize($data['guest_email']) . "'";
    }
    if (isset($data['nights'])) {
        $updates[] = "nights = " . intval($data['nights']);
    }
    if (isset($data['total_price'])) {
        $updates[] = "total_price = " . floatval($data['total_price']);
    }
    if (isset($data['status'])) {
        $updates[] = "status = '" . sanitize($data['status']) . "'";
    }
    
    if (empty($updates)) return false;
    
    $query = "UPDATE bookings SET " . implode(", ", $updates) . " WHERE id = $id";
    return $conn->query($query);
}

// Delete booking
function delete_booking($id) {
    global $conn;
    $id = intval($id);
    return $conn->query("DELETE FROM bookings WHERE id = $id");
}

// Create hotel
function create_hotel($name, $location, $price_per_night, $distance_to_venue, $rating, $description) {
    global $conn;
    
    $name = sanitize($name);
    $location = sanitize($location);
    $price_per_night = floatval($price_per_night);
    $distance_to_venue = floatval($distance_to_venue);
    $rating = floatval($rating);
    $description = sanitize($description);
    
    $query = "INSERT INTO hotels (name, location, price_per_night, distance_to_venue, rating, description) 
              VALUES ('$name', '$location', $price_per_night, $distance_to_venue, $rating, '$description')";
    
    return $conn->query($query);
}

// Update hotel
function update_hotel($id, $data) {
    global $conn;
    
    $id = intval($id);
    $updates = [];
    
    if (isset($data['name'])) {
        $updates[] = "name = '" . sanitize($data['name']) . "'";
    }
    if (isset($data['location'])) {
        $updates[] = "location = '" . sanitize($data['location']) . "'";
    }
    if (isset($data['price_per_night'])) {
        $updates[] = "price_per_night = " . floatval($data['price_per_night']);
    }
    if (isset($data['distance_to_venue'])) {
        $updates[] = "distance_to_venue = " . floatval($data['distance_to_venue']);
    }
    if (isset($data['rating'])) {
        $updates[] = "rating = " . floatval($data['rating']);
    }
    if (isset($data['description'])) {
        $updates[] = "description = '" . sanitize($data['description']) . "'";
    }
    
    if (empty($updates)) return false;
    
    $query = "UPDATE hotels SET " . implode(", ", $updates) . " WHERE id = $id";
    return $conn->query($query);
}

// Delete hotel
function delete_hotel($id) {
    global $conn;
    $id = intval($id);
    return $conn->query("DELETE FROM hotels WHERE id = $id");
}

// Verify admin login
function verify_admin($email, $password) {
    global $conn;
    $email = sanitize($email);
    $result = $conn->query("SELECT * FROM users WHERE email = '$email' AND role = 'admin' LIMIT 1");
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}

// Get dashboard stats
function get_dashboard_stats() {
    global $conn;
    
    $stats = [];
    
    // Total bookings
    $result = $conn->query("SELECT COUNT(*) as count FROM bookings");
    $stats['total_bookings'] = $result->fetch_assoc()['count'];
    
    // Total revenue
    $result = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE payment_status = 'paid'");
    $row = $result->fetch_assoc();
    $stats['total_revenue'] = $row['total'] ?? 0;
    
    // Pending bookings
    $result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
    $stats['pending_bookings'] = $result->fetch_assoc()['count'];
    
    // Total hotels
    $result = $conn->query("SELECT COUNT(*) as count FROM hotels");
    $stats['total_hotels'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

// Send email
function send_email($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: contactus@tpais-events.com" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

?>
