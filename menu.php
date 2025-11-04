<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    // if not logged in, redirect to register/login
    header("Location: register.php");
    exit;
}

// show today's menu
$today = date('Y-m-d');

// Fixed SQL using subqueries for ratings
$stmt = $conn->prepare("
    SELECT m.menu_id, m.title, m.description, m.image, m.menu_date,
           (SELECT AVG(r.rating) FROM ratings r WHERE r.menu_id = m.menu_id) AS avg_rating,
           (SELECT COUNT(r.rating) FROM ratings r WHERE r.menu_id = m.menu_id) AS count_rating,
           (SELECT r.rating FROM ratings r WHERE r.menu_id = m.menu_id AND r.user_id = ? LIMIT 1) AS my_rating
    FROM menu m
    WHERE m.menu_date = ?
    ORDER BY m.menu_id
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param('is', $user_id, $today);
$stmt->execute();
$res = $stmt->get_result();
$menus = $res->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Today's Menu - Campus Eats</title>
<link rel="stylesheet" href="assets.css">
<style>
.stars .star { cursor:pointer; font-size:20px; color:#ccc; }
.stars .star.active { color: gold; }
.menu-item { display:flex; gap:12px; margin-bottom:16px; border:1px solid #ddd; padding:12px; border-radius:8px; }
.menu-item img { width:120px; height:90px; object-fit:cover; border-radius:4px; }
.menu-item .meta { flex:1; }
.menu-item .title { font-weight:bold; margin-bottom:4px; }
.menu-item .desc { font-size:14px; color:#555; }
.btn { padding:6px 12px; background:#007BFF; color:white; text-decoration:none; border-radius:4px; margin-left:4px; }
.btn:hover { background:#0056b3; }
</style>
<script>
function rate(menuId, stars){
    fetch('rate.php', {
        method:'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({menu_id:menuId, rating:stars})
    }).then(r=>r.json()).then(data=>{
        if(data.success){
            document.querySelector('#avg-'+menuId).textContent = data.avg;
            document.querySelector('#count-'+menuId).textContent = data.count;
            document.querySelectorAll('#stars-'+menuId+' .star').forEach((el, idx)=>{
                el.classList.toggle('active', idx < stars);
            });
        } else {
            alert('Error: '+(data.message||'Could not rate'));
        }
    });
}
</script>
</head>
<body>
<div style="max-width:980px;margin:24px auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
        <h2>Today's Menu (<?=htmlspecialchars($today)?>)</h2>
        <div>
            <a class="btn" href="logout.php">Logout</a>
            <a class="btn" href="index.php">Landing</a>
        </div>
    </div>

    <?php if(empty($menus)): ?>
        <div class="card">No menu posted for today. Check back later.</div>
    <?php else: ?>
        <div class="menu-list">
            <?php foreach($menus as $m): ?>
            <div class="menu-item">
                <img src="<?=htmlspecialchars($m['image'] ?: 'https://via.placeholder.com/120x90?text=Dish')?>" alt="Dish">
                <div class="meta">
                    <div class="title"><?=htmlspecialchars($m['title'])?></div>
                    <div class="desc"><?=htmlspecialchars($m['description'])?></div>
                    <div style="margin-top:8px">
                        Avg: <span id="avg-<?= $m['menu_id'] ?>"><?= $m['count_rating'] ? number_format($m['avg_rating'],2) : '—' ?></span>
                        | Votes: <span id="count-<?= $m['menu_id'] ?>"><?= $m['count_rating'] ?></span>
                    </div>
                </div>

                <div style="width:160px;text-align:center">
                    <div class="small">Your Rating</div>
                    <div id="stars-<?=$m['menu_id'] ?>" class="stars" style="justify-content:center;margin-top:8px">
                        <?php
                        $my = intval($m['my_rating']);
                        for($i=1;$i<=5;$i++){
                            $cls = $i <= $my ? 'star active' : 'star';
                            echo "<span class=\"$cls\" onclick=\"rate({$m['menu_id']},{$i})\">★</span>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
