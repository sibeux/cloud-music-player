export function getDataFromAPISpotify(link, id) {
	const accessToken =
		"BQCv9qt6_QDBOTnJqvyTf082sBpzg-XCT4Ubm7mjGQiSxb2RLd5p_NUAidK5OBhN-LCWZBlgb69hNjR-z-Vkw69nOiTwdCnoqgTruzEU5M-yGsLMB3AmPQRSFvtROcXmdVO-ebAaQd6DEUZaB41CVJNDcSGl2tj5DYoPJV7D9646bOjuDHHjH2uFUSRkK_Rtac8DnGM0NxFLJXZiMRN3zw"; // Replace with your actual Spotify access token

	const match = link.match(/track\/(\w+)/);

	fetch(`https://api.spotify.com/v1/tracks/${match[1]}`, {
		headers: {
			Authorization: `Bearer ${accessToken}`,
		},
	})
		.then((response) => response.json())
		.then((data) => {
			const titleArray = document.getElementsByClassName("title_music");
			const artistArray = document.getElementsByClassName("artist_music");
			const coverArray = document.getElementsByClassName("cover_music");
			const albumArray = document.getElementsByClassName("album_music");
			const timeArray = document.getElementsByClassName("time_music");

			// cut the artist name if it is too long
			let dataArtists = data.artists.map((artist) => artist.name).join(", ");
			dataArtists =
				dataArtists.length > 30
					? dataArtists.substring(0, 30) + "..."
					: dataArtists;

			// cut the album name if it is too long
			let dataAlbum = data.album.name;
			dataAlbum =
				dataAlbum.length > 30 ? dataAlbum.substring(0, 30) + "..." : dataAlbum;

			titleArray[id].innerHTML = data.name;
			artistArray[id].innerHTML = dataArtists;
			coverArray[id].src = data.album.images[0].url;
			albumArray[id].innerHTML = dataAlbum;

			timeArray[id].innerHTML = `${String(
				Math.floor(data.duration_ms / 60000)
			).padStart(2, "0")}:${String(
				Math.floor((data.duration_ms / 1000) % 60)
			).padStart(2, "0")}`;
		})
		.catch((error) => {
			console.error("Error:", error);
		});
}
