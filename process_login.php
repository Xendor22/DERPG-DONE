<?php
// 1. Force absolute silence on notice warnings so they don't break JSON parsing strings
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

// Clear out any accidental echo strings, spaces, or hidden characters loaded prior to this point
if (ob_get_length()) ob_clean();

$conn = new mysqli("localhost", "root", "", "rpg");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

$username_email = isset($_POST['username_email']) ? trim($_POST['username_email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($username_email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    exit();
}

$stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username_email, $username_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Assign landing destinations smoothly
        $redirect = ($user['username'] === 'xendor599') ? 'admin.php' : 'index.php';
        
        echo json_encode(['success' => true, 'redirect' => $redirect]);
        $stmt->close();
        $conn->close();
        exit(); // Halt script immediately to prevent any trailing whitespace from appending
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect password. Try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No account matches those details.']);
}

$stmt->close();
$conn->close();
exit();
?>