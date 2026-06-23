<?php
session_start();

// 1. Connect to your database
$conn = new mysqli("localhost", "root", "", "rpg");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 2. Handle Entry Deletion Request
if (isset($_GET['delete_user_id'])) {
    $delete_id = intval($_GET['delete_user_id']);
    
    // Because your table uses 'ON DELETE CASCADE', deleting the user automatically cleans up their character profile!
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: admin.php");
    exit();
}

// 3. Handle Stat Modification Save (Edit Form Submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_stats') {
    $char_id = intval($_POST['character_id']);
    $new_level = intval($_POST['level']);
    $new_gold = intval($_POST['gold']);
    $new_exp = intval($_POST['exp']);
    $new_max_exp = intval($_POST['max_exp']);
    $new_hp = intval($_POST['hp']);
    $new_max_hp = intval($_POST['max_hp']);
    $new_mana = intval($_POST['mana']);
    $new_max_mana = intval($_POST['max_mana']);
    $new_stamina = intval($_POST['stamina']);
    $new_max_stamina = intval($_POST['max_stamina']);

    // Updated statement to track all components added during recent features updates
    $stmt = $conn->prepare("UPDATE characters SET level = ?, gold = ?, exp = ?, max_exp = ?, hp = ?, max_hp = ?, mana = ?, max_mana = ?, stamina = ?, max_stamina = ? WHERE id = ?");
    $stmt->bind_param("iiiiiiiiiii", $new_level, $new_gold, $new_exp, $new_max_exp, $new_hp, $new_max_hp, $new_mana, $new_max_mana, $new_stamina, $new_max_stamina, $char_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin.php");
    exit();
}

// 4. Fetch ALL users joined with their custom character profiles
$query = "SELECT users.id AS u_id, users.username, users.email, 
                 characters.id AS c_id, characters.gender, characters.class, 
                 characters.level, characters.gold, characters.exp, characters.max_exp,
                 characters.hp, characters.max_hp, characters.mana, characters.max_mana,
                 characters.stamina, characters.max_stamina 
          FROM users 
          LEFT JOIN characters ON users.id = characters.user_id";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RPG Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #111;
            color: #fff;
            padding: 30px;
        }
        h1 { color: #ffd700; border-bottom: 2px solid #333; padding-bottom: 10px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #222;
        }
        th, td {
            padding: 12px;
            border: 1px solid #444;
            text-align: left;
            font-size: 13px;
        }
        th { background-color: #3a2c1a; color: #ffd700; }
        tr:hover { background-color: #2a2a2a; }
        .btn {
            padding: 6px 12px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            border-radius: 3px;
            cursor: pointer;
            border: none;
            display: inline-block;
            margin-right: 5px;
        }
        .btn-delete { background: #d40000; color: #fff; }
        .btn-delete:hover { background: #a30000; }
        .btn-save { background: #19d400; color: #000; }
        .btn-save:hover { background: #129c00; }
        .stat-group {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .stat-slash {
            color: #aaa;
            font-weight: bold;
        }
        input[type="number"] {
            width: 55px;
            background: #333;
            color: #fff;
            border: 1px solid #555;
            padding: 4px;
            border-radius: 3px;
        }
        .nav-back { margin-bottom: 20px; display: inline-block; color: #00bcd4; text-decoration: none; }
    </style>
</head>
<body>

    <a href="town.php" class="nav-back">← Back to Town Hub</a>
    <h1>Game Master Database Administration</h1>

    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Class Profile</th>
                <th>Level</th>
                <th>Gold Balance</th>
                <th>Experience (Current / Max)</th>
                <th>Health (Current / Max)</th>
                <th>Mana (Current / Max)</th>
                <th>Stamina (Current / Max)</th>
                <th>Database Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['u_id']; ?></td>
                <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                
                <?php if ($row['c_id']): ?>
                    <td><?php echo ucfirst($row['gender']) . " " . ucfirst($row['class']); ?></td>
                    <form action="admin.php" method="POST">
                        <input type="hidden" name="action" value="edit_stats">
                        <input type="hidden" name="character_id" value="<?php echo $row['c_id']; ?>">
                        
                        <td><input type="number" name="level" value="<?php echo $row['level']; ?>" min="1"></td>
                        <td><input type="number" name="gold" value="<?php echo $row['gold']; ?>" min="0">g</td>
                        
                        <td>
                            <div class="stat-group">
                                <input type="number" name="exp" value="<?php echo $row['exp']; ?>" min="0">
                                <span class="stat-slash">/</span>
                                <input type="number" name="max_exp" value="<?php echo $row['max_exp']; ?>" min="1">
                            </div>
                        </td>

                        <td>
                            <div class="stat-group">
                                <input type="number" name="hp" value="<?php echo $row['hp']; ?>" min="0">
                                <span class="stat-slash">/</span>
                                <input type="number" name="max_hp" value="<?php echo $row['max_hp']; ?>" min="1">
                            </div>
                        </td>

                        <td>
                            <div class="stat-group">
                                <input type="number" name="mana" value="<?php echo $row['mana']; ?>" min="0">
                                <span class="stat-slash">/</span>
                                <input type="number" name="max_mana" value="<?php echo $row['max_mana']; ?>" min="1">
                            </div>
                        </td>

                        <td>
                            <div class="stat-group">
                                <input type="number" name="stamina" value="<?php echo $row['stamina']; ?>" min="0">
                                <span class="stat-slash">/</span>
                                <input type="number" name="max_stamina" value="<?php echo $row['max_stamina']; ?>" min="1">
                            </div>
                        </td>
                        
                        <td>
                            <button type="submit" class="btn btn-save">SAVE STATS</button>
                            <a href="admin.php?delete_user_id=<?php echo $row['u_id']; ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Are you absolutely sure you want to ban and wipe this account?')">DELETE</a>
                        </td>
                    </form>
                <?php else: ?>
                    <td colspan="7" style="color: #777; font-style: italic;">No character profile created yet.</td>
                    <td>
                        <a href="admin.php?delete_user_id=<?php echo $row['u_id']; ?>" 
                           class="btn btn-delete" 
                           onclick="return confirm('Delete this incomplete account?')">DELETE</a>
                    </td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
<?php
$conn->close();
?>