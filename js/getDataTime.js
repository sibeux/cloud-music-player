// get data time from music file
export function getDataTimeFileMusic(link, id) {
	// Create an audio element
	var audio = new Audio(
		link
	);

	// When the metadata has loaded, the duration should be available
	audio.onloadedmetadata = function () {
		// Get the duration in seconds
		var duration = audio.duration;
		console.log("Duration:", duration);
	};
}
