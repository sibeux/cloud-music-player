// get data time from music file
export function getDataTimeFileMusic(link, id) {
	const timeArray = document.getElementsByClassName("time_music");
	const audioContext = new AudioContext();

	// Load the audio file
	fetch(link)
		.then((response) => response.arrayBuffer())
		.then((buffer) => audioContext.decodeAudioData(buffer))
		.then((audioBuffer) => {
			// Get the duration of the audio file
			const duration = audioBuffer.duration;
			console.log(`The audio file duration is ${duration} seconds`);
			timeArray[id].innerHTML = duration;
		}); 
}
