<?php
session_start();
session_destroy();
header("Location: " . (defined('BASE_URL') ? BASE_URL : 'https://accommodation.tpais-events.com/' ));
exit;
?>
