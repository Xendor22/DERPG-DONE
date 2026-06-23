<?php
session_start();

// If the player is already logged in, instantly bypass this page and send them home!
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    <style>
        /* Retro warning error box layout */
        .login-error-msg {
            background: #ffcccc;
            color: #d40000;
            border: 2px solid #d40000;
            padding: 10px;
            font-size: 8px;
            font-family: 'Press Start 2P', cursive, sans-serif;
            margin-bottom: 15px;
            text-align: center;
            box-shadow: 2px 2px 0px #000;
        }
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>

<button class="back-btn" onclick="location.href='index.php'">←</button>

<div class="login-container">
    <h1>Login</h1>

    <div id="loginError" class="login-error-msg hidden"></div>

    <form id="loginForm" onsubmit="submitLogin(event)">
        <input type="text" name="username_email" placeholder="Username or Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" class="login-btn">LOGIN</button>
    </form>
    
    <p class="register-link">
        No account? <a href="register.html">Register</a>
    </p>
</div>

<script>
function submitLogin(event) {
    event.preventDefault(); // Stop standard form refresh redirect 

    const form = document.getElementById('loginForm');
    const errorBox = document.getElementById('loginError');
    const formData = new FormData(form);

    fetch('process_login.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success: Send them forward into the main screen routing matrix
            window.location.href = data.redirect;
        } else {
            // Failure: Render error message inside your container box frame
            errorBox.innerText = "❌ " + data.message;
            errorBox.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Auth Connection Error:', error);
        errorBox.innerText = "❌ Connection system failure.";
        errorBox.classList.remove('hidden');
    });
}
</script>
</body>
</html>