<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $class = strtolower($_POST['class'] ?? '');

    if (empty($email) || empty($username) || empty($password) || empty($gender) || empty($class)) {
        die("Please fill in all fields.");
    }

    $conn = new mysqli("localhost", "root", "", "rpg");
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $conn->autocommit(FALSE);

    try {
        $userStmt = $conn->prepare("INSERT INTO users (email, username, password_hash) VALUES (?, ?, ?)");
        $userStmt->bind_param("sss", $email, $username, $password_hash);
        $userStmt->execute();

        $userId = $conn->insert_id;
        $userStmt->close();

        $hp = 100; $mana = 100; $stamina = 100; $gold = 25;

        if ($class === 'warrior') {
            $hp = 120; $stamina = 120; $mana = 40;
        } elseif ($class === 'wizard') {
            $hp = 80; $stamina = 60; $mana = 160;
        } elseif ($class === 'ranger') {
            $hp = 100; $stamina = 100; $mana = 70;
        }

        // 3. Insert Character Profile
        $charStmt = $conn->prepare("INSERT INTO characters (user_id, name, gender, class, gold, hp, max_hp, mana, max_mana, stamina, max_stamina) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $charStmt->bind_param("isssiiiiiii", $userId, $username, $gender, $class, $gold, $hp, $hp, $mana, $mana, $stamina, $stamina);
        $charStmt->execute();
        $charStmt->close();

        $starter_name = "";
        $starter_stat = "";
        $starter_img = "";

        switch (strtolower($class)) {
            case 'warrior':
                $starter_name = "Wooden Sword";
                $starter_stat = "+5 ATK";
                $starter_img = "img/weapon/woord.png";
                break;
            case 'ranger':
                $starter_name = "Wood Bow";
                $starter_stat = "+6 ATK";
                $starter_img = "img/weapon/boord.png";
                break;
            case 'wizard':
                $starter_name = "Wooden Staff";
                $starter_stat = "+8 MATK";
                $starter_img = "img/weapon/waff.png";
                break;
        }

        $inv_stmt = $conn->prepare("INSERT INTO inventory (user_id, item_name, item_type, item_stat, item_img, is_equipped) VALUES (?, ?, 'Weapon', ?, ?, 1)");
        $inv_stmt->bind_param("isss", $userId, $starter_name, $starter_stat, $starter_img);
        $inv_stmt->execute();
        $inv_stmt->close();

        $conn->commit();

        echo "Registration successful! Redirecting you back to home page...";
        header("Refresh: 2; url=index.php");

    } catch (Exception $e) {
        $conn->rollback();
        if ($conn->errno == 1062) { 
            die("Username or Email already taken.");
        } else {
            die("Registration failed: " . $e->getMessage());
        }
    } finally {
        $conn->close();
    }
}
?>