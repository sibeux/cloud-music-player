const playIcon = document.getElementById("play-icon");
let statePlay = "play";

const favoriteIcon = document.getElementById("favorite-icon");
// get style color of favorite icon
const favoriteIconStyle = window.getComputedStyle(favoriteIcon);
// get color of favorite icon
const favoriteIconColor = favoriteIconStyle.getPropertyValue("color");
let stateFavorite = "favorite";
if (favoriteIconColor === "rgb(255, 255, 255)") {
	stateFavorite = "unfavorite";
}

// Play and Pause
playIcon.addEventListener("click", () => {
	playIcon.classList.remove("fa-play");
	if (statePlay === "play") {
		playIcon.innerHTML = '<i class="fas fa-pause"></i>';
		statePlay = "pause";
	} else {
		playIcon.innerHTML = '<i class="fas fa-play"></i>';
		statePlay = "play";
	}
});

// Favorite
favoriteIcon.addEventListener("click", () => {
	favoriteIcon.classList.remove("fa-heart");
	if (stateFavorite === "favorite") {
		favoriteIcon.innerHTML =
			'<i class="far fa-heart" style="color: #fff;"></i>';
		stateFavorite = "unfavorite";
	} else {
		favoriteIcon.innerHTML =
			'<i class="fas fa-heart" style="color: #1fd660"></i>';
		stateFavorite = "favorite";
	}
});
