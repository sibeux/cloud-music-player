const playIcon = document.getElementById("play-icon");
let statePlay = "play";

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

function changeFavoriteButton(id) {
	const favoriteIcon = document.getElementsByClassName("fa-heart");

	const button = favoriteIcon[id];

	// get style color of favorite icon
	const favoriteIconStyle = window.getComputedStyle(button);
	// get color of favorite icon
	const favoriteIconColor = favoriteIconStyle.getPropertyValue("color");
	let stateFavorite = "favorite";
	if (favoriteIconColor === "rgb(255, 255, 255)") {
		stateFavorite = "unfavorite";
	}

	// Favorite and Unfavorite function
	button.classList.remove("fa-heart");
	if (stateFavorite === "favorite") {
		button.innerHTML = '<i class="far fa-heart" style="color: #fff;"></i>';
		stateFavorite = "unfavorite";
	} else {
		button.innerHTML = '<i class="fas fa-heart" style="color: #1fd660"></i>';
		stateFavorite = "favorite";
	}
}
