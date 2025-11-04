<?php
// index.php - landing
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Campus Eats</title>
  <link rel="stylesheet" href="assets.css">
</head>
<body>
  <div class="container">
    <div class="left">
      <div class="tagline">
        Taste. Rate. Improve.<br>
        Explore today's menu and tell us what you think.
      </div>
    </div>

    <div class="right">
      <div>
        <div class="h1">Campus Eats <small>Your Canteen. Your Ratings. Your Voice</small></div>
        <p class="small">Hungry? Discover today’s menu & rate your meal!</p>
      </div>

      <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <a href="admin_login.php" class="btn">Admin Login</a>
        <a href="register.php" class="btn primary">User Register / Login</a>
      </div>

      <div style="margin-top:14px; color:var(--muted); font-size:13px;">
       💡 Did you know?<br>
        Every Tuesday’s Biryani Special gets the most ⭐ ratings!
      </div>
    </div>
  </div>
</body>
</html>
