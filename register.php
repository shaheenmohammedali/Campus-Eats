<?php
// register.php - show register/login forms or process registration if POST
session_start();
include 'db.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    if (!$email || !$pass) {
        $err = 'Email & password required';
    } else {
        // check if user already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) {
            die("SQL Error (check exists): " . $conn->error);
        }
        $stmt->bind_param('s',$email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows) {
            $err = "User already exists";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (name,email,password,role,created_at) VALUES (?,?,?,?,NOW())");
            if (!$ins) {
                die("SQL Error (insert): " . $conn->error);
            }
            // role default: 'user'
            $role = 'user';
            $ins->bind_param('ssss',$name, $email, $hash, $role);
            if ($ins->execute()) {
                // auto login after registration
                $_SESSION['user_id'] = $ins->insert_id;
                header("Location: menu.php");
                exit;
            } else {
                $err = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>User Register / Login - Campus Eats</title>
  <link rel="stylesheet" href="assets.css">
</head>
<body>
  <div style="max-width:420px;margin:40px auto;">
    <div class="card">
      <h3>Register</h3>
      <?php if($err): ?><div style="color:red;margin-bottom:10px;"><?=$err?></div><?php endif; ?>
      <form method="post">
        <input class="input" name="name" placeholder="Full name (optional)">
        <input class="input" name="email" placeholder="Email" required>
        <input class="input" name="password" type="password" placeholder="Password" required>
        <input type="hidden" name="action" value="register">
        <button class="btn primary" type="submit">Register & Go to Menu</button>
      </form>
      <hr style="margin:12px 0">
      <h3>Already registered?</h3>
      <form method="post" action="login.php">
        <input class="input" name="email" placeholder="Email" required>
        <input class="input" name="password" type="password" placeholder="Password" required>
        <button class="btn" type="submit">Login</button>
      </form>

      <p style="margin-top:12px;color:var(--muted);font-size:13px;">Or go back to <a href="index.php">landing</a></p>
    </div>
  </div>
</body>
</html>
