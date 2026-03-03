<?php ob_start(); // Start output buffering  
session_start();  

// Database configuration 
$db_host = 'DB_HOST'; 
$db_user = 'tpaisde3_accommodation_user'; 
$db_pass = 'DB_PASSWORD'; 
$db_name = 'DB_DATABASE';  

// Connect to database 
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);  

if ($conn->connect_error) {     
    die("Connection failed: " . $conn->connect_error); 
}  

$conn->set_charset("utf8");  

define('BASE_URL', 'https://accommodation.tpais-events.com/'  );  

$page = isset($_GET['page']) ? $_GET['page'] : 'home'; 
$is_admin = isset($_SESSION['admin_id']) ? true : false;  

require_once 'includes/functions.php'; 
require_once 'includes/header.php';  

switch ($page) {     
    case 'booking':         
        require_once 'pages/booking.php';         
        break;     
    case 'payment':         
        require_once 'pages/payment.php';         
        break;     
    case 'confirmation':         
        require_once 'pages/confirmation.php';         
        break;     
    case 'login':         
        require_once 'pages/login.php';         
        break;     
    case 'view_invoice':         
        require_once 'pages/view_invoice.php';         
        break;     
    case 'admin':         
        if (!$is_admin) {             
            header('Location: ' . BASE_URL . '?page=login');             
            exit;         
        }         
        require_once 'pages/admin/dashboard.php';         
        break;     
    case 'admin-bookings':         
        if (!$is_admin) {             
            header('Location: ' . BASE_URL . '?page=login');             
            exit;         
        }         
        require_once 'pages/admin/bookings.php';         
        break;     
    case 'admin-hotels':         
        if (!$is_admin) {             
            header('Location: ' . BASE_URL . '?page=login');             
            exit;         
        }         
        require_once 'pages/admin/hotels.php';         
        break;     
    case 'logout':         
        session_destroy();         
        header('Location: ' . BASE_URL);         
        exit;     
    default:         
        require_once 'pages/home.php'; 
}  

require_once 'includes/footer.php';  

ob_end_flush(); // Send buffered output 
?>
