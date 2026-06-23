<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized session access.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$item_name = $_POST['name'] ?? '';
$item_price = intval($_POST['price'] ?? 0);
$item_type = $_POST['type'] ?? '';
$item_stat = $_POST['stat'] ?? '';
$item_img = $_POST['img'] ?? '';

if (empty($item_name) || $item_price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item details.']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "rpg");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database failure.']);
    exit();
}

$space_stmt = $conn->prepare("SELECT COUNT(*) as current_count FROM inventory WHERE user_id = ?");
$space_stmt->bind_param("i", $user_id);
$space_stmt->execute();
$space_result = $space_stmt->get_result()->fetch_assoc();
$space_stmt->close();

if ($space_result && $space_result['current_count'] >= 8) { 
    echo json_encode(['success' => false, 'message' => 'Your backpack is completely full! Sell or use items first.']);
    $conn->close();
    exit();
}
    
$stmt = $conn->prepare("SELECT gold FROM characters WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$result || $result['gold'] < $item_price) {
    echo json_encode(['success' => false, 'message' => 'Not enough gold! Go defeat some monsters.']);
    $conn->close();
    exit();
}

$conn->autocommit(FALSE);

try {
    $deduct_stmt = $conn->prepare("UPDATE characters SET gold = gold - ? WHERE user_id = ?");
    $deduct_stmt->bind_param("ii", $item_price, $user_id);
    $deduct_stmt->execute();
    $deduct_stmt->close();

    $inv_stmt = $conn->prepare("INSERT INTO inventory (user_id, item_name, item_type, item_stat, item_img, is_equipped) VALUES (?, ?, ?, ?, ?, 0)");
    $inv_stmt->bind_param("issss", $user_id, $item_name, $item_type, $item_stat, $item_img);
    $inv_stmt->execute();
    $inv_stmt->close();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Transaction system error execution failure.']);
}

$conn->close();
?>