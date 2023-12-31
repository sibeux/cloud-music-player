export function getDataFromAPISpotify(link) {
	const accessToken =
		"BQBIYVLGfedt8vhg47TLom2KKBVo2SCqHQdM6t3pF-sZrCrrr-ZaoSDpHCzP4n7HvQ-2L5yyS5ZBgJ0Qiq_UDfcezFgRu56q3NuyTx7zrK34R7QjWPdc3CNZ4EhH-ZDyUTSOSUiRvnXviaRQahMwiWCI3YHi4gSob0VBM5CXo1HOVOJFoWFAg4V_B9AYP1KkxXj_3JJkEDejtAQ0QOzOfQ"; // Replace with your actual Spotify access token

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
            dataAlbum = dataAlbum.length > 30 ? dataAlbum.substring(0, 30) + "..." : dataAlbum;

			titleArray[0].innerHTML = data.name;
			artistArray[0].innerHTML = dataArtists;
			coverArray[0].src = data.album.images[0].url;
            albumArray[0].innerHTML = dataAlbum;

            timeArray[0].innerHTML = `${Math.floor(
							data.duration_ms / 60000
						)}:${Math.floor((data.duration_ms / 1000) % 60)}`;

		})
		.catch((error) => {
			console.error("Error:", error);
		});
}
