<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Load today's & recent menus with avg rating & votes
$stmt = $conn->prepare("
    SELECT m.*, 
      (SELECT AVG(r.rating) FROM ratings r WHERE r.menu_id = m.menu_id) AS avg_rating,
      (SELECT COUNT(*) FROM ratings r WHERE r.menu_id = m.menu_id) AS count_rating
    FROM menu m
    ORDER BY m.menu_date DESC
");
$stmt->execute();
$res = $stmt->get_result();
$menus = $res->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets.css">
    <style>
        .menuItem { border:1px solid #f0f0f0; border-radius:8px; padding:10px; margin-bottom:8px; }
        .small { font-size:12px; color:#555; }
    </style>
</head>
<body>
<div style="max-width:1000px;margin:30px auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <h2>Admin Dashboard</h2>
        <a class="btn" href="logout.php">Logout</a>
    </div>

    <div style="display:flex;gap:20px;margin-top:20px;">
        <!-- Add / Update Menu -->
        <div style="flex:1;">
            <div class="card">
                <h4>Add Menu Item</h4>
                <form id="addMenuForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_menu">
                    <input class="input" name="menu_date" type="date" value="<?=date('Y-m-d')?>" required>
                    <input class="input" name="title" placeholder="Dish title" required>
                    <textarea class="input" name="description" placeholder="Description"></textarea>
                    <label class="small">Image (optional)</label>
                    <input class="input" name="image" type="file" accept="image/*">
                    <button class="btn primary" type="submit">Add Menu Item</button>
                </form>
                <div id="msg" style="margin-top:10px;color:green;"></div>
            </div>
        </div>

        <!-- Menu List -->
        <div style="flex:1;">
            <div class="card">
                <h4>Today's & Recent Menu</h4>
                <div id="menuList">
                    <?php foreach($menus as $m): ?>
                        <div class="menuItem" data-id="<?= $m['menu_id'] ?>">
                            <strong><?=htmlspecialchars($m['title'])?></strong>
                            <span class="small">(<?=htmlspecialchars($m['menu_date'])?>)</span>
                            <div class="small"><?=htmlspecialchars($m['description'])?></div>
                            <div style="margin-top:6px;">
                                Avg: <?= $m['avg_rating'] ? number_format($m['avg_rating'],2) : '—' ?> | Votes: <?= $m['count_rating'] ?>
                            </div>
                            <button class="btn deleteBtn" style="margin-top:6px;">Delete</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const addForm = document.getElementById('addMenuForm');
const msgDiv = document.getElementById('msg');
const menuList = document.getElementById('menuList');

// Add menu via AJAX
addForm.addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);

    fetch('admin_action.php', {method:'POST', body:formData})
        .then(res => res.json())
        .then(data => {
            if(data.success){
                msgDiv.innerHTML = "Menu added successfully!";
                addForm.reset();
                menuList.insertAdjacentHTML('afterbegin', data.html);
            } else {
                msgDiv.innerHTML = "Error: " + data.message;
            }
        })
        .catch(err => console.error(err));
});

// Delete menu via AJAX (also deletes ratings)
menuList.addEventListener('click', function(e){
    if(e.target.classList.contains('deleteBtn')){
        const menuItem = e.target.closest('.menuItem');
        const menuId = menuItem.dataset.id;
        if(!confirm("Are you sure you want to delete this menu?")) return;

        const formData = new FormData();
        formData.append('action','delete_menu');
        formData.append('menu_id',menuId);

        fetch('admin_action.php', {method:'POST', body:formData})
            .then(res => res.json())
            .then(data => {
                console.log(data); // Debug
                if(data.success){
                    menuItem.remove();
                } else {
                    alert("Error deleting menu: " + data.message);
                }
            })
            .catch(err => console.error(err));
    }
});
</script>
</body>
</html>
