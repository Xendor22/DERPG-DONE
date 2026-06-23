<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "rpg");
$user_id = $_SESSION['user_id'];
$item_id = intval($_POST['item_id']);

$check_stmt = $conn->prepare("SELECT item_type FROM inventory WHERE id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $item_id, $user_id);
$check_stmt->execute();
$item = $check_stmt->get_result()->fetch_assoc();
$check_stmt->close();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit();
}

$item_type = $item['item_type'];

$conn->begin_transaction();

$unequip_stmt = $conn->prepare("UPDATE inventory SET is_equipped = 0 WHERE user_id = ? AND item_type = ?");
$unequip_stmt->bind_param("is", $user_id, $item_type);
$unequip_stmt->execute();
$unequip_stmt->close();

$equip_stmt = $conn->prepare("UPDATE inventory SET is_equipped = 1 WHERE id = ? AND user_id = ?");
$equip_stmt->bind_param("ii", $item_id, $user_id);
$equip_stmt->execute();
$equip_stmt->close();

$conn->commit();
$conn->close();

echo json_encode(['success' => true]);
exit();
?>