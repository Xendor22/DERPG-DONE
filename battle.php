<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "rpg");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$char_stmt = $conn->prepare("SELECT c.*, i.item_stat FROM characters c LEFT JOIN inventory i ON c.user_id = i.user_id AND i.item_type = 'Weapon' AND i.is_equipped = 1 WHERE c.user_id = ?");
$char_stmt->bind_param("i", $user_id);
$char_stmt->execute();
$char = $char_stmt->get_result()->fetch_assoc();
$char_stmt->close();

if (!$char) {
    die("Character profile context error.");
}

$player_sprite_class = 'pixel-' . strtolower($char['class']) . '-' . strtolower($char['gender']);

$weapon_atk = 0;
if (!empty($char['item_stat'])) {
    preg_match('/\d+/', $char['item_stat'], $matches);
    $weapon_atk = isset($matches[0]) ? intval($matches[0]) : 0;
}
$base_damage = 5 + ($char['level'] * 2) + $weapon_atk; 

// Dynamic Quest Router Initialization
if (isset($_GET['quest'])) {
    $quest_name = trim($_GET['quest']);
    
    // Comprehensive dictionary mapping quests to their unique monster skin profiles and HP scales
    switch($quest_name) {
        case "Slime Infestation":
            $m_name = "Green Slime"; $m_hp = 60; $skin_class = "slime-skin"; break;
        case "Wolf Pack Menace":
            $m_name = "Alpha Wolf"; $m_hp = 110; $skin_class = "wolf-skin"; break;
        case "Goblin Ambush":
            $m_name = "Goblin Scout"; $m_hp = 150; $skin_class = "goblin-skin"; break;
        case "Orc Raider Camp":
            $m_name = "Orc Marauder"; $m_hp = 260; $skin_class = "orc-skin"; break;
        case "The Giant's Awakening":
            $m_name = "Stone Giant"; $m_hp = 600; $skin_class = "giant-skin"; break;
        default:
            $m_name = "Rogue Target"; $m_hp = 100; $skin_class = "goblin-skin"; break;
    }

    $r_gold = isset($_GET['gold']) ? intval($_GET['gold']) : 50;
    $r_exp = isset($_GET['exp']) ? intval($_GET['exp']) : 30;

    $conn->query("DELETE FROM active_battles WHERE user_id = $user_id");
    
    $b_stmt = $conn->prepare("INSERT INTO active_battles (user_id, quest_name, monster_name, monster_max_hp, monster_hp, reward_gold, reward_exp) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $b_stmt->bind_param("isssiii", $user_id, $quest_name, $m_name, $m_hp, $m_hp, $r_gold, $r_exp);
    $b_stmt->execute();
    $b_stmt->close();
    
    header("Location: battle.php");
    exit();
}

$btl_stmt = $conn->prepare("SELECT * FROM active_battles WHERE user_id = ?");
$btl_stmt->bind_param("i", $user_id);
$btl_stmt->execute();
$battle = $btl_stmt->get_result()->fetch_assoc();
$btl_stmt->close();

if (!$battle) {
    header("Location: town.php"); 
    exit();
}

// Generate the appropriate skin based on monster name
switch($battle['monster_name']) {
    case "Green Slime": $m_skin = 'slime-skin'; break;
    case "Alpha Wolf": $m_skin = 'wolf-skin'; break;
    case "Goblin Scout": $m_skin = 'goblin-skin'; break;
    case "Orc Marauder": $m_skin = 'orc-skin'; break;
    case "Stone Giant": $m_skin = 'giant-skin'; break;
    default: $m_skin = 'goblin-skin'; break;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Battle Arena</title>
    <link rel="stylesheet" href="st1.css">
</head>
<body>
<audio id="battleBgm" loop>
    <source src="music/battle_myusic.mp3" type="audio/mpeg">
</audio>
<div class="battle-container">
    <h2><?php echo strtoupper($battle['quest_name']); ?></h2>
    
    <div class="vs-screen">
        <div class="combatant">
            <h3>HERO</h3>
            <div class="sprite-container">
                <div class="character-sprite <?php echo $player_sprite_class; ?>"></div>
            </div>
            <p style="font-size:8px; color:#ffd700; margin-top:10px;">Base Max DMG: <?php echo $base_damage; ?></p>
            <div class="bar-container border-red" style="width:100%;">
                <div id="playerHpBar" class="bar bg-red" style="width: <?php echo ($char['max_hp'] > 0) ? ($char['hp'] / $char['max_hp']) * 100 : 0; ?>%;"></div>
            </div>
            <span id="playerHpText" style="font-size:10px; color:#ff9999;"><?php echo $char['hp']; ?>/<?php echo $char['max_hp']; ?></span>
        </div>

        <h1 style="color:#d40000; font-size: 28px; animation: pulse 1s infinite alternate;">VS</h1>

        <div class="combatant">
            <h3 id="monsterName"><?php echo $battle['monster_name']; ?></h3>
            <div class="sprite-container">
                <div class="monster-sprite <?php echo $m_skin; ?>"></div>
            </div>
            <p style="font-size:8px; color:#aaa; margin-top:10px;">Target Enemy Instance</p>
            <div class="bar-container border-red" style="width:100%;">
                <div id="monsterHpBar" class="bar bg-red" style="width: <?php echo ($battle['monster_max_hp'] > 0) ? ($battle['monster_hp'] / $battle['monster_max_hp']) * 100 : 0; ?>%;"></div>
            </div>
            <span id="monsterHpText" style="font-size:10px; color:#99ff99;"><?php echo $battle['monster_hp']; ?>/<?php echo $battle['monster_max_hp']; ?></span>
        </div>
    </div>

    <div id="battleLog" class="battle-log">
        Requirements loaded. An aggressive combat encounter begins! It is your turn...
    </div>

    <div class="action-bar" id="actionBar">
        <button class="action-btn" onclick="executeAttack()">ATTACK</button>
        <button class="action-btn" style="background:#555;" onclick="document.getElementById('battleBgm').pause(); location.href='town.php'">FLEE</button>
    </div>
</div>

<script>
window.addEventListener('click', () => {
    const bgm = document.getElementById("battleBgm");
    if (bgm.paused) {
        bgm.volume = 0.4;
        bgm.play().catch(error => console.log("Audio play blocked:", error));
    }
}, { once: true });

function executeAttack() {
    document.getElementById("actionBar").style.pointerEvents = "none"; 

    fetch('combat_engine.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const log = document.getElementById("battleLog");
            
            log.innerHTML += `<br>🎲 Rolling damage wheel... You struck ${document.getElementById("monsterName").innerText} dealing <b>${data.player_dmg}</b> damage! (${data.roll_percent}% power)`;
            log.scrollTop = log.scrollHeight; 

            if (data.monster_hp <= 0) {
                document.getElementById("battleBgm").pause();
                log.innerHTML += `<br>💀 <b>Victory!</b> The monster collapses...`;
                
                document.getElementById("monsterHpBar").style.width = "0%";
                document.getElementById("monsterHpText").innerText = "0/Dead";
                
                setTimeout(() => {
                    if (data.leveled_up) {
                        alert(`🏆 VICTORY! Collected ${data.gold_reward}g and +${data.exp_reward} EXP.\n\n✨ LEVEL UP! You reached Level ${data.new_level}! Your attributes increased!`);
                    } else {
                        alert(`🏆 Victory! Collected ${data.gold_reward}g and +${data.exp_reward} EXP.`);
                    }
                    window.location.href = 'town.php';
                }, 1500);

            } else {
                document.getElementById("monsterHpBar").style.width = data.monster_hp_percent + "%";
                document.getElementById("monsterHpText").innerText = data.monster_hp + "/" + data.monster_max_hp;

                setTimeout(() => {
                    log.innerHTML += `<br>💥 ${document.getElementById("monsterName").innerText} retaliates and bites you for <b>${data.enemy_dmg}</b> damage!`;
                    log.scrollTop = log.scrollHeight; 
                    
                    let playerHpPercent = (data.player_current_hp / data.player_max_hp) * 100;
                    document.getElementById("playerHpBar").style.width = playerHpPercent + "%";
                    
                    let hpTextEl = document.getElementById("playerHpText");
                    if(hpTextEl) {
                        hpTextEl.innerText = data.player_current_hp + "/" + data.player_max_hp;
                    }

                    if (data.player_current_hp <= 0) {
                        log.innerHTML += `<br>☠️ <b>Defeat!</b> You have been knocked unconscious...`;
                        log.scrollTop = log.scrollHeight;
                        document.getElementById("battleBgm").pause();

                        setTimeout(() => {
                            alert("💀 You passed out! Heading back to town to recover...");
                            window.location.href = 'town.php';
                        }, 2000);
                        return;
                    }

                    document.getElementById("actionBar").style.pointerEvents = "auto";
                }, 800);
            }
        } else {
            alert("Combat script failure: " + data.message);
            document.getElementById("actionBar").style.pointerEvents = "auto";
        }
    })
    .catch(error => {
        console.error('Combat Error:', error);
        document.getElementById("actionBar").style.pointerEvents = "auto";
    });
}
</script>
</body>
</html>