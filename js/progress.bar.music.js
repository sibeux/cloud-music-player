let countMusicNumber = 0;

let isPlay = false;
let statePlay = "play";

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
function animatedPlayMusic(index, linkGDrive, countMusic, uid_music) {
	countMusicNumber = countMusic;
	// initiate variable
	const playingMusic = document.getElementsByClassName("play_no");
	const buttonPlay = document.getElementsByClassName("flaticon-play-button");
	const currentPlayMusic = document.getElementsByClassName("playing");

	if (currentPlayMusic.length === 0) {
		nowPlayingIndex = index + 1;
	}

	console.log(uid_music);
	setRecentsMusic(uid_music);

	// button play visible and hidden
	const hiddenButtonPlay = buttonPlay[index];
	const visibleButtonPlay = buttonPlay[nowPlayingIndex - 1];

	// check index of button play
	const buttonPlayingMusic =
		index + 1 <= nowPlayingIndex
			? playingMusic[index]
			: playingMusic[index - 1];

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
		if (index + 1 !== nowPlayingIndex) {
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
		nowPlayingIndex = index + 1;
		nowPlayingMusicProgressBar(index);

		const link = linkGDrive;
		playMusic(link);
	}
}

function nowPlayingMusicProgressBar(id) {
	const titleArray = document.getElementsByClassName("title_music");
	const artistArray = document.getElementsByClassName("artist_music");
	const coverArray = document.getElementsByClassName("cover_music");

	const toCapitalize = (str) =>
		str.replace(
			/(^\w|\s\w)(\S*)/g,
			(_, m1, m2) => m1.toUpperCase() + m2.toLowerCase()
		);

	const title = titleArray[id].innerHTML;
	const artist = artistArray[id].innerHTML;

	document.getElementById("title").innerHTML = titleArray[id].innerHTML;
	document.getElementById("artist").innerHTML = artistArray[id].innerHTML;
	document.getElementById("cover_now_play").src =
		coverArray[id].getAttribute("src");
	document.getElementById("title_doc").innerHTML =
		toCapitalize(title) + " ● " + toCapitalize(artist);
	document.getElementById("title_icon").innerHTML =
		"<link id='title_icon' rel='shortcut icon' type='image/png' href='" +
		coverArray[id].getAttribute("src") +
		"' />";
}

function playMusic(linkGDrive) {
	var audio = document.getElementById("player_music");
	var playIcon = document.getElementById("play-icon");

	statePlay = "pause";
	playIcon.classList.remove("fa-play");
	playIcon.innerHTML = '<i class="fas fa-pause"></i>';
	audio.src = linkGDrive;

	audio.load();
	audio.play();
}

document.addEventListener("DOMContentLoaded", () => {
	document.getElementById("player_music").addEventListener("ended", () => {
		nextMusic(countMusicNumber);
	});
});

document.getElementById("next-music").addEventListener("click", () => {
	nextMusic(countMusicNumber);
});

function nextMusic(countMusic) {
	let randomNumber = Math.floor(Math.random() * countMusic) + 1;

	// Fetch JSON data from a file
	fetch(
		"https://sibeux.my.id/cloud-music-player/json/music.json"
		// "http://localhost:3000/Main_Program/Web%20Programming/cloud-music-player/json/music.json"
	)
		.then((response) => response.json())
		.then((json) =>
			animatedPlayMusic(
				json[randomNumber]["id_music"] - 1,
				json[randomNumber]["link"],
				countMusic,
				json[randomNumber]["uid"]
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

let song = document.getElementById("player_music");
var progressBar = document.querySelector("#range");

// if song play, update progress bar
if (song.play) {
	let progressTrack = 0;

	// set interval of range
	onMouse = false;
	setInterval(() => {
		progressBar.value = song.currentTime;

		const progress = (song.currentTime / song.duration) * 100;
		progressTrack = progress;

		if (onMouse) {
			progressBar.style.background = `linear-gradient(to right, #1fd660 ${progress}%, #3c3a3a ${progress}%)`;
		} else {
			progressBar.style.background = `linear-gradient(to right, #fff ${progress}%, #3c3a3a ${progress}%)`;
		}
	}, 500);

	function green(el) {
		el.style.background =
			"linear-gradient(to right, #1fd660 " +
			progressTrack +
			"%, #3c3a3a " +
			progressTrack +
			"%)";
		onMouse = true;
	}

	function white(el) {
		el.style.background =
			"linear-gradient(to right, #fff " +
			progressTrack +
			"%, #3c3a3a " +
			progressTrack +
			"%)";
		onMouse = false;
	}

	song.onloadedmetadata = function () {
		duration = song.duration;

		progressBar.max = song.duration;

		// Convert seconds to mm:ss format
		var minutes = Math.floor(duration / 60);
		var seconds = Math.floor(duration % 60);

		var formattedDuration =
			minutes.toString().padStart(1, "0") +
			":" +
			seconds.toString().padStart(2, "0");

		document.getElementById("last-minute").innerText = formattedDuration;
	};
}

progressBar.onchange = function () {
	song.currentTime = progressBar.value;
};

// update progress span when song is playing
song.addEventListener("timeupdate", () => {
	const minutes = Math.floor(song.currentTime / 60);
	const seconds = Math.floor(song.currentTime % 60);

	var formattedDuration =
		minutes.toString().padStart(1, "0") +
		":" +
		seconds.toString().padStart(2, "0");

	document.getElementById("first-minute").innerText = formattedDuration;
});

const playIcon = document.getElementById("play-icon");

// Play and Pause
playIcon.addEventListener("click", () => {
	playIcon.classList.remove("fa-play");
	if (statePlay === "play") {
		playIcon.innerHTML = '<i class="fas fa-pause"></i>';
		statePlay = "pause";
		song.play();
	} else {
		playIcon.innerHTML = '<i class="fas fa-play"></i>';
		statePlay = "play";
		song.pause();
	}
});

async function setRecentsMusic(id) {
	const url = `https://sibeux.my.id/cloud-music-player/database/mobile-music-player/api/recents_music?_id=${id}`;
	console.log(id);

	try {
		const response = await fetch(url, {
			method: "POST",
		});
		if (!response.ok) {
			throw new Error("Network response was not ok");
		}
	} catch (e) {
		if (typeof console !== "undefined") {
			console.error("Error set recents music:", e);
		}
	}
}
