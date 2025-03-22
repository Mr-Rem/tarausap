<?php
$password = "March29XX25"; // Change this to your desired password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
echo $hashed_password;
?>
