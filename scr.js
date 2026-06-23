let activeShopItem = null;
let selectedSlotElement = null;

function opsop(){
    closeAllMenus();
    document.getElementById("shopMenu").classList.remove("hidden");
}

function opmith() {
    closeAllMenus();
    document.getElementById("smithMenu").classList.remove("hidden");
}

function opild() {
    closeAllMenus();
    document.getElementById("guildMenu").classList.remove("hidden");
}

function closop(){
    document.getElementById("shopMenu").classList.add("hidden");
}

function clomith() {
    document.getElementById("smithMenu").classList.add("hidden");
}

function cloild() {
    document.getElementById("guildMenu").classList.add("hidden");
}

function closeItem() {
    document.getElementById("itemDetail").classList.add("hidden");
}

function closeAllMenus() {
    document.getElementById("shopMenu").classList.add("hidden");
    document.getElementById("smithMenu").classList.add("hidden");
    document.getElementById("guildMenu").classList.add("hidden");
}

document.querySelectorAll(".item").forEach(item => {
    item.addEventListener("click", () => {
        const name = item.dataset.name;
        if (!name) return;

        activeShopItem = {
            name: name,
            price: parseInt(item.dataset.price),
            type: item.dataset.type,
            stat: item.dataset.stat,
            img: item.getAttribute('src') 
        };

        document.getElementById("itemName").innerText = activeShopItem.name;
        document.getElementById("itemImg").src = activeShopItem.img;
        document.getElementById("itemPrice").innerText = activeShopItem.price + " Gold";

        if (item.dataset.desc) {
            document.getElementById("itemDesc").innerText = item.dataset.desc;
        } else {
            document.getElementById("itemDesc").innerText =
                "DMG: " + (item.dataset.damage ?? '0') + 
                " | SPD: " + (item.dataset.speed ?? '0') + 
                " | DUR: " + (item.dataset.durability ?? '0') + 
                " | TYP: " + activeShopItem.type;
        }

        document.getElementById("itemDetail").classList.remove("hidden");
    });
});

function buyItem() {
    if (!activeShopItem) return;

    fetch('buy_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            name: activeShopItem.name,
            price: activeShopItem.price,
            type: activeShopItem.type,
            stat: activeShopItem.stat,
            img: activeShopItem.img
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Purchase successful! " + activeShopItem.name + " added to backpack.");
            location.reload(); 
        } else {
            alert("Transaction failed: " + data.message);
        }
    })
    .catch(error => console.error('Error processing checkout:', error));
}

function toggleBackpack() {
    const bpMenu = document.getElementById('backpackMenu');
    bpMenu.classList.toggle('hidden');

    if (bpMenu.classList.contains('hidden')) {
        document.getElementById('invItemDetail').classList.add('hidden');
        if (selectedSlotElement) selectedSlotElement.classList.remove('selected-slot');
    }
}

function selectInvItem(slotElement) {
    if (slotElement.classList.contains('empty')) return;

    selectedSlotElement = slotElement;
    
    const detailBox = document.getElementById('invItemDetail');
    const nameText = document.getElementById('invItemName');
    const typeText = document.getElementById('invItemType');
    const statText = document.getElementById('invItemStat');
    const actionBtn = document.getElementById('invActionBtn');

    const name = slotElement.getAttribute('data-name');
    const type = slotElement.getAttribute('data-type');
    const stat = slotElement.getAttribute('data-stat');
    const isEquipped = slotElement.getAttribute('data-equipped') === 'true';

    nameText.innerText = name;
    typeText.innerText = type.toUpperCase();
    statText.innerText = stat;

    // Dynamically adjust operational buttons across classes
    if (type === "Weapon") {
        actionBtn.innerText = isEquipped ? "UNEQUIP" : "EQUIP";
        actionBtn.style.background = isEquipped ? "#d40000" : "#19d400";
        actionBtn.style.color = isEquipped ? "#fff" : "#000";
    } else if (type === "Potion") {
        actionBtn.innerText = "DRINK";
        actionBtn.style.background = "#00bcd4";
        actionBtn.style.color = "#fff";
    } else {
        actionBtn.innerText = "USE";
        actionBtn.style.background = "#00bcd4";
        actionBtn.style.color = "#fff";
    }

    detailBox.classList.remove('hidden');
}

function selectInvItem(slotElement) {
    if (slotElement.classList.contains('empty')) return;

    selectedSlotElement = slotElement;
    
    const detailBox = document.getElementById('invItemDetail');
    const nameText = document.getElementById('invItemName');
    const typeText = document.getElementById('invItemType');
    const statText = document.getElementById('invItemStat');
    
    // FIX: Changed from 'invActionBtn' to 'equipActionBtn' to match your town.php layout
    const actionBtn = document.getElementById('equipActionBtn'); 

    const name = slotElement.getAttribute('data-name');
    const type = slotElement.getAttribute('data-type');
    const stat = slotElement.getAttribute('data-stat');
    const isEquipped = slotElement.getAttribute('data-equipped') === 'true';

    nameText.innerText = name;
    typeText.innerText = type.toUpperCase();
    statText.innerText = stat;

    // Dynamically adjust operational buttons across item classes
    if (type === "Weapon") {
        actionBtn.innerText = isEquipped ? "UNEQUIP" : "EQUIP";
        actionBtn.disabled = false;
        actionBtn.style.opacity = "1";
        actionBtn.style.background = isEquipped ? "#d40000" : "#19d400";
        actionBtn.style.borderColor = isEquipped ? "#7a0000" : "#118c00";
        actionBtn.style.color = isEquipped ? "#fff" : "#000";
    } else if (type === "Potion") {
        actionBtn.innerText = "DRINK";
        actionBtn.disabled = false;
        actionBtn.style.opacity = "1";
        actionBtn.style.background = "#2196F3";
        actionBtn.style.borderColor = "#0b7dda";
        actionBtn.style.color = "#fff";
    } else {
        actionBtn.innerText = "USE";
        actionBtn.disabled = false;
        actionBtn.style.opacity = "1";
        actionBtn.style.background = "#00bcd4";
        actionBtn.style.borderColor = "#008ba3";
        actionBtn.style.color = "#fff";
    }

    detailBox.classList.remove('hidden');
}

function drinkPotion(itemId) {
    fetch('use_potion.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ id: itemId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload(); 
        } else {
            alert("Error consuming potion: " + data.message);
        }
    })
    .catch(error => console.error('Error handling consumption request:', error));
}
// Replace this function inside your scr.js file
function acceptQuest(questName, rewardGold, rewardExp) {
    // Passes structural settings parameters directly through URL parameters initialization setup
    window.location.href = `battle.php?quest=${encodeURIComponent(questName)}&gold=${rewardGold}&exp=${rewardExp}`;
}