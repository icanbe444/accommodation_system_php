<?php
$password = "GENERATE_HASH_PASSWORD";
$hash = password_hash($password, PASSWORD_BCRYPT);
echo $hash;
?>
