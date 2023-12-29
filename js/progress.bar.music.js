const playIcon = document.getElementById("play-icon");
let statePlay = "play";

const favoriteIcon = document.getElementById("favorite-icon");
let stateFavorite = "favorite";

// Play and Pause
playIcon.addEventListener("click", () => {
	if (statePlay === "play") {
		playIcon.classList.remove("fa-play");
		playIcon.innerHTML = '<i class="fas fa-pause"></i>';
		statePlay = "pause";
	} else {
		playIcon.innerHTML = '<i class="fas fa-play"></i>';
		statePlay = "play";
	}
});

// Favorite
favoriteIcon.addEventListener("click", () => {
	if (stateFavorite === "favorite") {
        favoriteIcon.classList.remove("fa-heart");
        favoriteIcon.innerHTML =
					'<i class="far fa-heart" style="color: #fff;"></i>';
        stateFavorite = "unfavorite";
    } else {
        favoriteIcon.innerHTML = '<i class="fas fa-heart"></i>';
        stateFavorite = "favorite";
    }
});
