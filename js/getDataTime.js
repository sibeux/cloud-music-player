// get data time from music file
export function getDataTimeFileMusic(link, id) {
	const timeArray = document.getElementsByClassName("time_music");
	var x = timeArray[id].duration;
	console.log(x);

	timeArray[id].innerHTML = x;
}
