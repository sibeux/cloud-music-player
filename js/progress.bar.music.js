let countMusicNumber = 0;

const playIcon = document.getElementById("play-icon");
let statePlay = "play";
let isPlay = false;

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

let nowPlayingIndex = 1;
function animatedPlayMusic(id, linkGDrive, countMusic) {
	countMusicNumber = countMusic;
	// initiate variable
	const playingMusic = document.getElementsByClassName("play_no");
	const buttonPlay = document.getElementsByClassName("flaticon-play-button");
	const currentPlayMusic = document.getElementsByClassName("playing");

	if (currentPlayMusic.length === 0) {
		nowPlayingIndex = id + 1;
	}

	// button play visible and hidden
	const hiddenButtonPlay = buttonPlay[id];
	const visibleButtonPlay = buttonPlay[nowPlayingIndex - 1];

	// check index of button play
	const buttonPlayingMusic =
		id + 1 <= nowPlayingIndex ? playingMusic[id] : playingMusic[id - 1];

	// Check if there is currently playing music
	if (currentPlayMusic.length > 0) {
		const buttonCurrentPlayingMusic = currentPlayMusic[0];
		buttonCurrentPlayingMusic.classList.remove("playing");
		buttonCurrentPlayingMusic.innerHTML =
			'<span class="play_no">' + nowPlayingIndex + "</span>";
		visibleButtonPlay.setAttribute("style", "visibility: visible;");

		pauseMusic();
		isPlay = false;

		// If the next music to play is not the one currently playing, call letsGOParty
		if (id + 1 !== nowPlayingIndex) {
			letsGOParty();
			isPlay = true;
		}
	} else {
		// If there is no currently playing music, call letsGOParty
		letsGOParty();
		isPlay = true;
	}

	function letsGOParty() {
		// change visibility of play button
		hiddenButtonPlay.setAttribute("style", "visibility: hidden;");

		buttonPlayingMusic.classList.remove("play_no");
		buttonPlayingMusic.innerHTML =
			'<div class="playing"> <span class="playing__bar playing__bar1"> </span> <span class="playing__bar playing__bar2"> </span> <span class="playing__bar playing__bar3"></span> </div>';
		nowPlayingIndex = id + 1;
		nowPlayingMusicProgressBar(id);

		const link = linkGDrive;
		playMusic(link);
	}
}

function nowPlayingMusicProgressBar(id) {
	const titleArray = document.getElementsByClassName("title_music");
	const artistArray = document.getElementsByClassName("artist_music");
	const coverArray = document.getElementsByClassName("cover_music");

	document.getElementById("title").innerHTML = titleArray[id].innerHTML;
	document.getElementById("artist").innerHTML = artistArray[id].innerHTML;
	document.getElementById("cover_now_play").src =
		coverArray[id].getAttribute("src");
	document.getElementById("title_doc").innerHTML =
		titleArray[id].innerHTML + " ● " + artistArray[id].innerHTML;
	document.getElementById("title_icon").innerHTML =
		"<link id='title_icon' rel='shortcut icon' type='image/png' href='" +
		coverArray[id].getAttribute("src") +
		"' />";
}

function playMusic(linkGDrive) {
	var audio = document.getElementById("player_music");
	audio.src = linkGDrive;

	audio.load();
	audio.play();
}

document.getElementById("player_music").addEventListener("ended", () => {
	nextMusic(countMusicNumber);
});

function nextMusic(countMusic) {
	let randomNumber = Math.floor(Math.random() * countMusic)+1;

	// Fetch JSON data from a file
	fetch(
		"https://sibeux.my.id/cloud-music-player/json/music.json"
		// "http://localhost:3000/Main_Program/Web%20Programming/cloud-music-player/json/music.json"
	)
		.then((response) => response.json())
		.then((json) =>
			animatedPlayMusic(
				(json[randomNumber]["id_music"])-1,
				json[randomNumber]["link"],
				countMusic
			)
		);
}

function repeatMusic() {
	var audio = document.getElementById("player_music");
	audio.loop = true;
}

function pauseMusic() {
	var audio = document.getElementById("player_music");
	audio.pause();
}
