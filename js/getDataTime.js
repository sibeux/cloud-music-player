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

		// Convert seconds to mm:ss format
		var minutes = Math.floor(duration / 60);
		var seconds = Math.floor(duration % 60);
		var formattedDuration =
			minutes.toString().padStart(2, "0") +
			":" +
			seconds.toString().padStart(2, "0");

		console.log("Duration:", formattedDuration);
	};
}
