// get data time from music file
export function getDataTimeFileMusic(link, id) {
	var audio = document.getElementById("player_music");
	audio.src = link;

	const timeArray = document.getElementsByClassName("time_music");
	var x = document.getElementById("player_music");
	// console.log(x);
	// timeArray[id].innerHTML = x;
}
