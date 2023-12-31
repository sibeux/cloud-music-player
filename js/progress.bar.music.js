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

let nowPlayingIndex = 1; //3
function animatedPlayMusic(id) { //3

	// initiate variable
	const playingMusic = document.getElementsByClassName("play_no");
	console.log(playingMusic)
	const buttonPlay = document.getElementsByClassName("flaticon-play-button");
	const currentPlayMusic = document.getElementsByClassName("playing");

	if (currentPlayMusic.length === 0) {
		nowPlayingIndex = id + 1;
	}

	// button play visible and hidden
	const hiddenButtonPlay = buttonPlay[id];
	const visibleButtonPlay = buttonPlay[nowPlayingIndex - 1];

	const buttonPlayingMusic = playingMusic[id];

	if (currentPlayMusic.length > 0) {
		const buttonCurrentPlayingMusic = currentPlayMusic[0];
		buttonCurrentPlayingMusic.classList.remove("playing");
		buttonCurrentPlayingMusic.innerHTML =
			'<span class="play_no">' + nowPlayingIndex + "</span>";
		visibleButtonPlay.setAttribute("style", "visibility: visible;");

		if (id + 1 !== nowPlayingIndex) {
			letsGOParty();
		}
	} else {
		letsGOParty();
	}

	function letsGOParty() {
		// change visibility of play button
		hiddenButtonPlay.setAttribute("style", "visibility: hidden;");

		buttonPlayingMusic.classList.remove("play_no");
		buttonPlayingMusic.innerHTML =
			'<div class="playing"> <span class="playing__bar playing__bar1"> </span> <span class="playing__bar playing__bar2"> </span> <span class="playing__bar playing__bar3"></span> </div>';
		nowPlayingIndex = id + 1;
		}
}
