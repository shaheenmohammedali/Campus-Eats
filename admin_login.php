<?php
// admin_login.php
session_start();
include 'db.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // ✅ use correct columns
    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) {
        die("SQL Error: " . $conn->error); // helps debug if query fails
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        // ✅ check role and password
        if ($row['role'] === 'admin' && password_verify($pass, $row['password'])) {
            $_SESSION['admin_id'] = $row['user_id'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $err = "Admin access denied or wrong password";
        }
    } else {
        $err = "No such user";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Login</title>
  <link rel="stylesheet" href="assets.css">
</head>
<body>
  <div style="max-width:420px;margin:60px auto;">
    <div class="card">
      <h3>Admin Login</h3>
      <?php if($err): ?>
        <div style="color:red;margin-bottom:10px;"><?=$err?></div>
      <?php endif; ?>
      <form method="post">
        <input class="input" name="email" type="email" placeholder="Admin email" required>
        <input class="input" name="password" type="password" placeholder="Password" required>
        <button class="btn primary" type="submit">Login as Admin</button>
      </form>
      <p style="margin-top:12px"><a href="index.php">Back to landing</a></p>
    </div>
  </div>
</body>
</html>
