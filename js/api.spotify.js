async function refreshAccessToken(refreshToken) {
	const clientId = "a13366c52a89450f938ad5964e4976fc";
	const clientSecret = "5259fd47c2a64c6ab2c16138fbcaa8d8";
	const tokenEndpoint = "https://accounts.spotify.com/api/token";

	const basicAuth = btoa(`${clientId}:${clientSecret}`);

	const requestBody = new URLSearchParams();
	requestBody.append("grant_type", "refresh_token");
	requestBody.append("refresh_token", refreshToken);

	const response = await fetch(tokenEndpoint, {
		method: "POST",
		headers: {
			Authorization: `Basic ${basicAuth}`,
			"Content-Type": "application/x-www-form-urlencoded",
		},
		body: requestBody,
	});

	if (!response.ok) {
		throw new Error("Failed to refresh access token");
	}

	const responseData = await response.json();
	return responseData.access_token;
}

export function getDataFromAPISpotify(link, id) {
	// Example usage:
	const refreshToken =
		"AQBI38smNBN4Xka0G9wn0LnUq6QqASg_rS3_ZufpWe7i0MJrqrPknQre21m22sUMLMDktkLrKOJQZYF3gMzWpBxqsIUd7MS5fGuKJ4cYP9qcVvJnxvx31RcRB4mXYzSoz9Y";

	const match = link.match(/track\/(\w+)/);

	refreshAccessToken(refreshToken)
		.then((accessToken) => {
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
					let dataArtists = data.artists
						.map((artist) => artist.name)
						.join(", ");
					dataArtists =
						dataArtists.length > 30
							? dataArtists.substring(0, 30) + "..."
							: dataArtists;

					// cut the album name if it is too long
					let dataAlbum = data.album.name;
					dataAlbum =
						dataAlbum.length > 30
							? dataAlbum.substring(0, 30) + "..."
							: dataAlbum;

					// cut the title name if it is too long
					let dataTitle = data.name;
					dataTitle =
						dataTitle.length > 30
							? dataTitle.substring(0, 30) + "..."
							: dataTitle;

					titleArray[id].innerHTML = dataTitle;
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
		})
		.catch((error) => {
			console.error("Error refreshing access token:", error);
		});
}