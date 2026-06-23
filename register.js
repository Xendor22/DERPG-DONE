function selectClass(selected) {
    // If the user clicks the sprite or span, climb back up to the parent .class-card element
    const card = selected.closest('.class-card');
    
    document.querySelectorAll(".class-card").forEach(c => c.classList.remove("selected"));
    card.classList.add("selected");

    document.getElementById("classInput").value = card.dataset.value;
}

function selectGender(selected) {
    // Climb up to the parent .gender-card element if needed
    const card = selected.closest('.gender-card');

    document.querySelectorAll(".gender-card").forEach(c => c.classList.remove("selected"));
    card.classList.add("selected");

    const genderValue = card.dataset.value;
    document.getElementById("genderInput").value = genderValue;

    // Sprite switching logic remains exactly the same...
    const warrior = document.getElementById("warriorSprite");
    const ranger = document.getElementById("rangerSprite");
    const wizard = document.getElementById("wizardSprite");

    if (genderValue === "female") {
        if(warrior) warrior.className = "pixel-sprite pixel-warrior-female";
        if(ranger)  ranger.className  = "pixel-sprite pixel-ranger-female";
        if(wizard)  wizard.className  = "pixel-sprite pixel-wizard-female";
    } else {
        if(warrior) warrior.className = "pixel-sprite pixel-warrior-male";
        if(ranger)  ranger.className  = "pixel-sprite pixel-ranger-male";
        if(wizard)  wizard.className  = "pixel-sprite pixel-wizard-male";
    }
}