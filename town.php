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

// Fetching exp and max_exp from characters table
$stmt = $conn->prepare("SELECT gender, class, level, gold, hp, max_hp, mana, max_mana, stamina, max_stamina, exp, max_exp FROM characters WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$char_result = $stmt->get_result();

if ($char_result->num_rows === 1) {
    $char = $char_result->fetch_assoc();
} else {
    die("Character profile not found. Please re-register.");
}
$stmt->close();

$inventory_items = [];
$inv_stmt = $conn->prepare("SELECT id, item_name, item_type, item_stat, item_img, is_equipped FROM inventory WHERE user_id = ?");
$inv_stmt->bind_param("i", $user_id);
$inv_stmt->execute();
$inv_result = $inv_stmt->get_result();

while ($row = $inv_result->fetch_assoc()) {
    $inventory_items[] = $row;
}
$inv_stmt->close();

$sprite_class = "pixel-" . strtolower($char['class']) . "-" . strtolower($char['gender']);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Town</title>
    <link rel="stylesheet" href="st1.css">
</head>
<body>

    <div class="backpack-icon-box" onclick="toggleBackpack()">
        <img src="img/backpack.png" alt="Backpack" class="backpack-pixel-icon">
        <span class="backpack-label">ITEMS</span>
    </div>

    <div id="backpackMenu" class="menu hidden">
        <div class="menu-content backpack-bg">
            <span class="close-btn" onclick="toggleBackpack()">X</span>
            <h2>INVENTORY</h2>
            <hr style="border-color: #3a2c1a;">
            
            <div class="inventory-grid">
                <?php 
                $total_slots = 8;
                for ($i = 0; $i < $total_slots; $i++): 
                    if (isset($inventory_items[$i])): 
                        $item = $inventory_items[$i];
                        $is_eq = $item['is_equipped'] == 1;
                ?>
                    <div class="inv-slot <?php echo $is_eq ? 'equipped' : ''; ?>" onclick="selectInvItem(this)" 
                         data-id="<?php echo $item['id']; ?>" 
                         data-name="<?php echo htmlspecialchars($item['item_name']); ?>" 
                         data-type="<?php echo $item['item_type']; ?>" 
                         data-stat="<?php echo htmlspecialchars($item['item_stat']); ?>" 
                         data-equipped="<?php echo $is_eq ? 'true' : 'false'; ?>" 
                         data-img="<?php echo $item['item_img']; ?>">
                        <img src="<?php echo $item['item_img']; ?>" class="inv-item-img">
                        <?php if ($is_eq): ?><span class="equip-tag">E</span><?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="inv-slot empty"></div>
                <?php endif; ?>
                <?php endfor; ?>
            </div>

            <div id="invItemDetail" class="inv-detail-box hidden">
                <h3 id="invItemName">Select an Item</h3>
                <p id="invItemType" style="font-size: 8px; color: #555;"></p>
                <p id="invItemStat"></p>
                <button id="equipActionBtn" class="equip-btn" onclick="equipSelectedItem()">EQUIP</button>
            </div>
        </div>
    </div>

    <div class="hud-container">
        <div class="hud-avatar-box">
            <div class="pixel-sprite <?php echo $sprite_class; ?>"></div>
        </div>
        
        <div class="hud-stats-box">
            <div class="hud-row hero-meta">
                <span class="hero-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <span class="hero-level">LV.<?php echo $char['level']; ?></span>
                <span class="hero-atk" style="color: #ff4757; font-size: 10px;">ATK: <?php echo 5 + ($char['level'] * 2); ?></span>
            </div>

            <div class="hud-row">
                <span class="stat-lbl" style="color: #a55eea;">EXP</span>
                <div class="bar-container" style="border: 2px solid #8854d0;">
                    <div class="bar" style="background-color: #a55eea; width: <?php echo ($char['max_exp'] > 0) ? ($char['exp'] / $char['max_exp']) * 100 : 0; ?>%;"></div>
                    <span class="bar-text"><?php echo $char['exp']; ?>/<?php echo $char['max_exp']; ?></span>
                </div>
            </div>
            
            <div class="hud-row">
                <span class="stat-lbl">HP</span>
                <div class="bar-container border-red">
                    <div class="bar bg-red" style="width: <?php echo ($char['max_hp'] > 0) ? ($char['hp'] / $char['max_hp']) * 100 : 0; ?>%;"></div>
                    <span class="bar-text"><?php echo $char['hp']; ?>/<?php echo $char['max_hp']; ?></span>
                </div>
            </div>

            <div class="hud-row">
                <span class="stat-lbl">MP</span>
                <div class="bar-container border-blue">
                    <div class="bar bg-blue" style="width: <?php echo ($char['max_mana'] > 0) ? ($char['mana'] / $char['max_mana']) * 100 : 0; ?>%;"></div>
                    <span class="bar-text"><?php echo $char['mana']; ?>/<?php echo $char['max_mana']; ?></span>
                </div>
            </div>

            <div class="hud-row">
                <span class="stat-lbl">SP</span>
                <div class="bar-container border-yellow">
                    <div class="bar bg-yellow" style="width: <?php echo ($char['max_stamina'] > 0) ? ($char['stamina'] / $char['max_stamina']) * 100 : 0; ?>%;"></div>
                    <span class="bar-text"><?php echo $char['stamina']; ?>/<?php echo $char['max_stamina']; ?></span>
                </div>
            </div>
            
            <div class="hud-row gold-row">
                <span>GOLD: <span style="color: #ffd700;"><?php echo $char['gold']; ?>g</span></span>
            </div>
        </div> 
    </div> 

    <audio id="bg-music" autoplay loop>
        <source src="music/homesac.mp3" type="audio/mpeg">
    </audio>

    <div class="map-container">
        <div class="map-wrapper">
            <img src="img/path.png" alt="Town Map" class="map">
            
            <button type="button" class="path path-left" onclick="opsop()">
                <img src="img/tent2.png" class="tent">
                <span class="label">SHOP</span>
            </button>

            <button class="path path-right" onclick="opmith()">
                <img src="img/bs.png" class="smith">
                <span class="label">BLACKSMITH</span>
            </button>

            <button class="path path-top" onclick="opild()">
                <img src="img/guild.png" class="smith">
                <span class="label">GUILD HALL</span>
            </button>
        </div>
    </div>

    <div id="shopMenu" class="menu hidden">
        <div class="shop-bg">
            <span class="close-btn" onclick="closop()">X</span>
            
            <img src="img/potion/remal1.png" class="item red-small" data-name="Small Health Potion" data-price="25" data-type="Potion" data-stat="Heals 50 HP" data-desc="A basic potion that heals 50 HP">
            <img src="img/potion/rebig1.png" class="item red-big" data-name="Big Health Potion" data-price="50" data-type="Potion" data-stat="Heals 100 HP" data-desc="An intermediate potion that heals 100 HP">
            
            <img src="img/potion/blual1.png" class="item blue-small" data-name="Small Mana Potion" data-price="50" data-type="Potion" data-stat="Restores 100 Mana" data-desc="A basic potion that can restore 100 Mana">
            <img src="img/potion/bluig1.png" class="item blue-big" data-name="Big Mana Potion" data-price="100" data-type="Potion" data-stat="Restores 200 Mana" data-desc="An intermediate potion that can restore 200 Mana">

            <img src="img/potion/yemal1.png" class="item yellow-small" data-name="Small Stamina Potion" data-price="40" data-type="Potion" data-stat="Restores 50 Stamina" data-desc="A sour potion that restores 50 Stamina">
            <img src="img/potion/yebig1.png" class="item yellow-big" data-name="Big Stamina Potion" data-price="75" data-type="Potion" data-stat="Restores 100 Stamina" data-desc="A highly concentrated fluid that restores 100 Stamina">
        </div>
    </div>

    <div id="smithMenu" class="menu hidden">
        <div class="shop-bg smith-bg">
            <span class="close-btn" onclick="clomith()">X</span>

            <div class="smith-showcase">
                <img src="img/weapon/irsor.png" class="item irsor" data-price="120" data-name="Iron Sword" data-type="Weapon" data-stat="+10 ATK" data-damage="10" data-speed="1" data-durability="200">
                <img src="img/weapon/bobow.png" class="item bobow" data-price="150" data-name="Bone Bow" data-type="Weapon" data-stat="+15 ATK" data-damage="15" data-speed="0.5" data-durability="50">
                <img src="img/weapon/crystaf.png" class="item crystaf" data-price="300" data-name="Crystal Staff" data-type="Weapon" data-stat="+35 MATK" data-damage="35" data-speed="0.5" data-durability="100">
                <img src="img/weapon/chicken.png" class="item chicken" data-price="999999" data-name="Rubber Chicken" data-type="Weapon" data-stat="+10000 ATK" data-damage="10000" data-speed="0.5" data-durability="100">
                <img src="img/weapon/roow.png" class="item roow" data-price="1000" data-name="Bow Of Roots" data-type="Weapon" data-stat="+60 ATK" data-damage="60" data-speed="0.5" data-durability="100">
                <img src="img/weapon/satana.png" class="item satana" data-price="7500" data-name="The Sakura Katana" data-type="Weapon" data-stat="+90 MATK" data-damage="90" data-speed="0.5" data-durability="100">
                <img src="img/weapon/eyef.png" class="item eyef" data-price="15000" data-name="The Staff Of All Seing Eye" data-type="Weapon" data-stat="+150 ATK" data-damage="150" data-speed="0.5" data-durability="100">
                <img src="img/weapon/hearaf.png" class="item hearaf" data-price="10000" data-name="Heart of Darkness" data-type="Weapon" data-stat="+100 MATK" data-damage="100" data-speed="0.5" data-durability="100">
                <img src="img/weapon/spow.png" class="item spow" data-price="25000" data-name="RGB Bow" data-type="Weapon" data-stat="+205 MATK" data-damage="205" data-speed="0.5" data-durability="100">
            </div>
            
        </div>
    </div>

    <div id="guildMenu" class="menu hidden">
        <div class="guild-board-bg">
            <span class="close-btn" onclick="cloild()">X</span>
            
            <div class="quest-board-container">
                <h2 class="board-title">QUEST BOUNTIES</h2>
                
                <div class="quest-list">
                    <div class="quest-card">
                        <div class="quest-details">
                            <h3>Slime Infestation</h3>
                            <p>Clear out the pesky slimes invading the town borders.</p>
                            <span class="reward">Reward: <span class="gold-text">50g</span> | +30 EXP</span>
                        </div>
                        <button class="quest-btn" onclick="acceptQuest('Slime Infestation', 50, 30)">ACCEPT</button>
                    </div>

                    <div class="quest-card">
                        <div class="quest-details">
                            <h3>Wolf Pack Menace</h3>
                            <p>Hunt down the alpha wolf leading attacks on local livestock.</p>
                            <span class="reward">Reward: <span class="gold-text">90g</span> | +55 EXP</span>
                        </div>
                        <button class="quest-btn" onclick="acceptQuest('Wolf Pack Menace', 90, 55)">ACCEPT</button>
                    </div>

                    <div class="quest-card">
                        <div class="quest-details">
                            <h3>Goblin Ambush</h3>
                            <p>Defeat the goblin scout party roaming the eastern trail.</p>
                            <span class="reward">Reward: <span class="gold-text">120g</span> | +80 EXP</span>
                        </div>
                        <button class="quest-btn" onclick="acceptQuest('Goblin Ambush', 120, 80)">ACCEPT</button>
                    </div>

                    <div class="quest-card">
                        <div class="quest-details">
                            <h3>Orc Raider Camp</h3>
                            <p>Dismantle the rogue orc encampment threatening the trade caravans.</p>
                            <span class="reward">Reward: <span class="gold-text">250g</span> | +150 EXP</span>
                        </div>
                        <button class="quest-btn" onclick="acceptQuest('Orc Raider Camp', 250, 150)">ACCEPT</button>
                    </div>

                    <div class="quest-card">
                        <div class="quest-details">
                            <h3>The Giant's Awakening</h3>
                            <p>Slay the rampaging stone giant that woke up in the northern hills.</p>
                            <span class="reward">Reward: <span class="gold-text">600g</span> | +400 EXP</span>
                        </div>
                        <button class="quest-btn" onclick="acceptQuest('The Giant\'s Awakening', 600, 400)">ACCEPT</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="itemDetail" class="menu hidden">
        <div class="menu-content">
            <span class="close-btn" onclick="closeItem()">X</span>
            <img id="itemImg" class="detail-img">
            <h2 id="itemName"></h2>
            <p id="itemDesc"></p>
            <p id="itemPrice"></p>
            <button class="buy-btn" onclick="buyItem()">BUY</button>
        </div>
    </div> 
    
    <button class="town-logout-btn" onclick="location.href='logout.php'">LOGOUT</button>

    <script src="scr.js"></script>
    <script>
    let currentlySelectedItemId = null;
    let currentlySelectedItemType = null;

    function selectInvItem(element) {
        document.querySelectorAll('.inv-slot').forEach(slot => slot.classList.remove('selected'));
        element.classList.add('selected');

        currentlySelectedItemId = element.getAttribute('data-id');
        currentlySelectedItemType = element.getAttribute('data-type');
        let name = element.getAttribute('data-name');
        let stat = element.getAttribute('data-stat');
        let isEquipped = element.getAttribute('data-equipped') === 'true';

        document.getElementById("invItemName").innerText = name;
        document.getElementById("invItemType").innerText = currentlySelectedItemType;
        document.getElementById("invItemStat").innerText = stat;

        let actionBtn = document.getElementById("equipActionBtn");
        
        if (currentlySelectedItemType === 'Potion') {
            actionBtn.innerText = "DRINK";
            actionBtn.disabled = false;
            actionBtn.style.opacity = "1";
            actionBtn.style.background = "#2196F3"; 
            actionBtn.style.borderColor = "#0b7dda";
        } else {
            actionBtn.style.background = "#4caf50"; 
            actionBtn.style.borderColor = "#1e5a22";
            if (isEquipped) {
                actionBtn.innerText = "EQUIPPED";
                actionBtn.disabled = true;
                actionBtn.style.opacity = "0.5";
            } else {
                actionBtn.innerText = "EQUIP";
                actionBtn.disabled = false;
                actionBtn.style.opacity = "1";
            }
        }

        document.getElementById("invItemDetail").classList.remove("hidden");
    }

    function equipSelectedItem() {
        if (!currentlySelectedItemId) return;

        let targetScript = (currentlySelectedItemType === 'Potion') ? 'use_potion.php' : 'equip_item.php';
        let formData = new FormData();
        formData.append('item_id', currentlySelectedItemId);

        fetch(targetScript, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert("Error processing action: " + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function acceptQuest(questName, goldReward, expReward) {
        window.location.href = `battle.php?quest=${encodeURIComponent(questName)}&gold=${goldReward}&exp=${expReward}`;
    }
    </script>
</body>
</html>