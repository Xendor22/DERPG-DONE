<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Retro RPG World</title>
    <link rel="stylesheet" href="login.css"> 
    <style>
        .button-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            margin-top: 25px;
        }
        .logout-btn {
            background: #d40000 !important; 
        }
        .logout-btn:hover {
            background: #a30000 !important;
        }
    </style>
</head>
<body>

<div class="login-container">
    <?php if ($isLoggedIn): ?>
        <h1>Welcome Back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        
        <div class="button-group">
            <button class="login-btn" onclick="location.href='town.php'">ENTER TOWN</button>
            
            <button class="login-btn logout-btn" onclick="location.href='logout.php'">LOGOUT</button>
        </div>
        
    <?php else: ?>
        <h1>Retro RPG World</h1>
        <p style="font-size: 10px; font-family: 'Press Start 2P';">Create your hero and join the adventure.</p>
        
        <div class="button-group">
            <button class="login-btn" onclick="location.href='login.php'">LOGIN</button>
            <button class="login-btn" style="background:#00bcd4;" onclick="location.href='register.html'">REGISTER</button>
        </div>
    <?php endif; ?>
</div>

</body>
</html>