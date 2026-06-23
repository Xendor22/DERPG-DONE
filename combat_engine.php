<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized entry.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "rpg");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

$char_stmt = $conn->prepare("SELECT c.*, i.item_stat FROM characters c LEFT JOIN inventory i ON c.user_id = i.user_id AND i.item_type = 'Weapon' AND i.is_equipped = 1 WHERE c.user_id = ?");
$char_stmt->bind_param("i", $user_id);
$char_stmt->execute();
$char = $char_stmt->get_result()->fetch_assoc();
$char_stmt->close();

if (!$char) {
    echo json_encode(['success' => false, 'message' => 'Character not found.']);
    exit();
}

$weapon_atk = 0;
if (!empty($char['item_stat'])) {
    preg_match('/\d+/', $char['item_stat'], $matches);
    $weapon_atk = isset($matches[0]) ? intval($matches[0]) : 0;
}
$base_damage = 5 + ($char['level'] * 2) + $weapon_atk; 

$btl_stmt = $conn->prepare("SELECT * FROM active_battles WHERE user_id = ?");
$btl_stmt->bind_param("i", $user_id);
$btl_stmt->execute();
$battle = $btl_stmt->get_result()->fetch_assoc();
$btl_stmt->close();

if (!$battle) {
    echo json_encode(['success' => false, 'message' => 'No active battle record found.']);
    exit();
}

$random_multiplier = mt_rand(50, 100); 
$player_raw_damage = $base_damage * ($random_multiplier / 100);
$player_final_damage = intval(floor($player_raw_damage)); 
if ($player_final_damage < 1) $player_final_damage = 1; 

$new_monster_hp = max(0, $battle['monster_hp'] - $player_final_damage);

// Dynamic Enemy Damage Scaling based on active monster profiles
switch($battle['monster_name']) {
    case "Green Slime": $enemy_retaliation_damage = mt_rand(2, 6); break;
    case "Alpha Wolf": $enemy_retaliation_damage = mt_rand(6, 12); break;
    case "Goblin Scout": $enemy_retaliation_damage = mt_rand(10, 18); break;
    case "Orc Marauder": $enemy_retaliation_damage = mt_rand(18, 30); break;
    case "Stone Giant": $enemy_retaliation_damage = mt_rand(35, 55); break;
    default: $enemy_retaliation_damage = mt_rand(5, 10); break;
}

$max_hp = isset($battle['monster_max_hp']) ? intval($battle['monster_max_hp']) : 60;
if ($max_hp <= 0) $max_hp = 60; 
$hp_percent = ($new_monster_hp / $max_hp) * 100;

$conn->autocommit(FALSE);
try {
    if ($new_monster_hp <= 0) {
        $quest_exp_reward = $battle['reward_exp'];
        $quest_gold_reward = $battle['reward_gold'];
        
        $new_exp = $char['exp'] + $quest_exp_reward;
        $new_gold = $char['gold'] + $quest_gold_reward;
        $current_level = $char['level'];
        
        $new_max_hp = $char['max_hp'];
        $new_max_mana = $char['max_mana'];
        $new_max_stamina = $char['max_stamina'];
        $new_max_exp = $char['max_exp']; 
        $leveled_up = false;

        while (true) {
            $xp_needed = 100 * pow(2, $current_level - 1);
            if ($new_exp >= $xp_needed) {
                $new_exp -= $xp_needed;
                $current_level++;
                $new_max_hp += 20;
                $new_max_mana += 30;
                $new_max_stamina += 50;
                $new_max_exp = 100 * pow(2, $current_level - 1); 
                $leveled_up = true;
            } else {
                break;
            }
        }

        if ($leveled_up) {
            $reward_stmt = $conn->prepare("UPDATE characters SET level = ?, exp = ?, max_exp = ?, gold = ?, max_hp = ?, hp = ?, max_mana = ?, mana = ?, max_stamina = ?, stamina = ? WHERE user_id = ?");
            $reward_stmt->bind_param("iiiiiiiiiii", $current_level, $new_exp, $new_max_exp, $new_gold, $new_max_hp, $new_max_hp, $new_max_mana, $new_max_mana, $new_max_stamina, $new_max_stamina, $user_id);
        } else {
            $reward_stmt = $conn->prepare("UPDATE characters SET gold = gold + ?, exp = exp + ? WHERE user_id = ?");
            $reward_stmt->bind_param("iii", $quest_gold_reward, $quest_exp_reward, $user_id);
        }
        $reward_stmt->execute();
        $reward_stmt->close();

        $del_stmt = $conn->prepare("DELETE FROM active_battles WHERE user_id = ?");
        $del_stmt->bind_param("i", $user_id);
        $del_stmt->execute();
        $del_stmt->close();
        
        $conn->commit();
        echo json_encode([
            'success' => true,
            'player_dmg' => $player_final_damage,
            'roll_percent' => $random_multiplier,
            'monster_hp' => 0,
            'gold_reward' => $quest_gold_reward,
            'exp_reward' => $quest_exp_reward,
            'leveled_up' => $leveled_up,
            'new_level' => $current_level
        ]);
    } else {
        $up_stmt = $conn->prepare("UPDATE active_battles SET monster_hp = ? WHERE user_id = ?");
        $up_stmt->bind_param("ii", $new_monster_hp, $user_id);
        $up_stmt->execute();
        $up_stmt->close();

        $player_hurt_stmt = $conn->prepare("UPDATE characters SET hp = GREATEST(0, hp - ?) WHERE user_id = ?");
        $player_hurt_stmt->bind_param("ii", $enemy_retaliation_damage, $user_id);
        $player_hurt_stmt->execute();
        $player_hurt_stmt->close();

        $check_stmt = $conn->prepare("SELECT hp, max_hp FROM characters WHERE user_id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $current_player = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        $conn->commit();
        echo json_encode([
            'success' => true,
            'player_dmg' => $player_final_damage,
            'roll_percent' => $random_multiplier,
            'monster_hp' => $new_monster_hp,
            'monster_max_hp' => $max_hp,
            'monster_hp_percent' => $hp_percent,
            'enemy_dmg' => $enemy_retaliation_damage,
            'player_current_hp' => $current_player['hp'],
            'player_max_hp' => $current_player['max_hp']
        ]);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>