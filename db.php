<?php
$servername = "localhost";
$username = "root";    // default for XAMPP
$password = "";        // default is empty
$dbname = "campus_eats";  // ✅ your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
