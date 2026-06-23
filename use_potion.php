<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized request']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "rpg");
$user_id = $_SESSION['user_id'];
$item_id = intval($_POST['item_id']);

$item_stmt = $conn->prepare("SELECT item_name, item_type, item_stat FROM inventory WHERE id = ? AND user_id = ?");
$item_stmt->bind_param("ii", $item_id, $user_id);
$item_stmt->execute();
$item = $item_stmt->get_result()->fetch_assoc();
$item_stmt->close();

if (!$item || $item['item_type'] !== 'Potion') {
    echo json_encode(['success' => false, 'message' => 'Valid potion not found']);
    exit();
}

preg_match('/\d+/', $item['item_stat'], $matches);
$heal_amount = isset($matches[0]) ? intval($matches[0]) : 0;

$conn->begin_transaction();

if (strpos($item['item_name'], 'Health') !== false) {
    $update_stmt = $conn->prepare("UPDATE characters SET hp = LEAST(max_hp, hp + ?) WHERE user_id = ?");
} elseif (strpos($item['item_name'], 'Mana') !== false) {
    $update_stmt = $conn->prepare("UPDATE characters SET mana = LEAST(max_mana, mana + ?) WHERE user_id = ?");
} else {
    $update_stmt = $conn->prepare("UPDATE characters SET stamina = LEAST(max_stamina, stamina + ?) WHERE user_id = ?");
}

$update_stmt->bind_param("ii", $heal_amount, $user_id);
$update_stmt->execute();
$update_stmt->close();

$delete_stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
$delete_stmt->bind_param("i", $item_id);
$delete_stmt->execute();
$delete_stmt->close();

$conn->commit();
$conn->close();

echo json_encode(['success' => true]);
exit();
?>