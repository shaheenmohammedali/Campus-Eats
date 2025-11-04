<?php
include 'db.php';

$name = "Admin";
$email = "campuseats@gmail.com";
$password = password_hash("campuseats123", PASSWORD_DEFAULT); // change to your password
$role = "admin";

$sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $password, $role);

if ($stmt->execute()) {
    echo "Admin account created!";
} else {
    echo "Error: " . $conn->error;
}
?>
