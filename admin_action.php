<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success'=>false,'message'=>"Not logged in"]);
    exit;
}

$action = $_POST['action'] ?? '';

if($action === 'add_menu'){
    $date = $_POST['menu_date'] ?? '';
    $title = $_POST['title'] ?? '';
    $desc = $_POST['description'] ?? '';

    $imagePath = null;
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

    if(!empty($_FILES['image']['tmp_name'])){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = $uploadDir . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        if(move_uploaded_file($_FILES['image']['tmp_name'],$fileName)){
            $imagePath = $fileName;
        }
    }

    $stmt = $conn->prepare("INSERT INTO menu (menu_date,title,description,image) VALUES (?,?,?,?)");
    if(!$stmt){
        echo json_encode(['success'=>false,'message'=>$conn->error]);
        exit;
    }

    $stmt->bind_param('ssss', $date,$title,$desc,$imagePath);
    if($stmt->execute()){
        $menu_id = $stmt->insert_id;
        $html = '<div class="menuItem" data-id="'.$menu_id.'" style="border:1px solid #f0f0f0;border-radius:8px;padding:10px;margin-bottom:8px;">
            <strong>'.htmlspecialchars($title).'</strong>
            <span class="small">('.htmlspecialchars($date).')</span>
            <div class="small">'.htmlspecialchars($desc).'</div>
            <div style="margin-top:6px;">Avg: — | Votes: 0</div>
            <button class="btn deleteBtn" style="margin-top:6px;">Delete</button>
        </div>';
        echo json_encode(['success'=>true,'html'=>$html]);
    } else {
        echo json_encode(['success'=>false,'message'=>$stmt->error]);
    }
    exit;
}

elseif($action === 'delete_menu'){
    $menu_id = intval($_POST['menu_id']);

    // Delete ratings first
    $stmt = $conn->prepare("DELETE FROM ratings WHERE menu_id=?");
    if($stmt){
        $stmt->bind_param('i',$menu_id);
        $stmt->execute();
    }

    // Delete menu image if exists
    $stmt = $conn->prepare("SELECT image FROM menu WHERE menu_id=?");
    if($stmt){
        $stmt->bind_param('i',$menu_id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        if($r && $r['image']) @unlink($r['image']);
    }

    // Delete menu
    $stmt = $conn->prepare("DELETE FROM menu WHERE menu_id=?");
    if($stmt){
        $stmt->bind_param('i',$menu_id);
        if($stmt->execute()){
            echo json_encode(['success'=>true]);
        } else {
            echo json_encode(['success'=>false,'message'=>$stmt->error]);
        }
    } else {
        echo json_encode(['success'=>false,'message'=>$conn->error]);
    }
    exit;
}

else {
    echo json_encode(['success'=>false,'message'=>'Unknown action']);
}
?>
