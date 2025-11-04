<?php
// login.php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // Use user_id instead of id
    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) {
        die("SQL Error: " . $conn->error); // shows the real SQL error if query fails
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        // Verify hashed password
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];  // store user_id in session
            header("Location: menu.php");
            exit;
        } else {
            echo "Invalid credentials. <a href='register.php'>Go back</a>";
        }
    } else {
        echo "User not found. <a href='register.php'>Register</a>";
    }
} else {
    header("Location: register.php");
}
?>
