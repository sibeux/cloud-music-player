
const playIconContainer = document.getElementById("play-icon");
let state = "play";

// Play and Pause
playIconContainer.addEventListener("click", () => {
    if (state === "play") {
        playIconContainer.classList.remove("fa-play");
        playIconContainer.innerHTML = '<i class="fas fa-pause"></i>';
        state = "pause";
    } else {
        playIconContainer.innerHTML = '<i class="fas fa-play"></i>';
        state = "play";
    }
    });