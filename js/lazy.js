let currentPage = 1;
const limit = 10;  // Musik per halaman

window.onscroll = function () {
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight) {
        currentPage++;
        loadMoreMusic(currentPage);
    }
};

function loadMoreMusic(page) {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'lazy.php?page=' + page, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            let data = JSON.parse(`${xhr.responseText}`);
            appendMusic(data);
        }
    };
    xhr.send();
}

function appendMusic(music) {
    const musicList = document.querySelector('.album_inner_list_padding');  // Ambil elemen dengan class 'album_inner_list_padding'

    music.forEach(function (item) {
        const musicItem = document.createElement('ul');
        musicItem.classList.add('album_inner_list_padding');
        musicItem.innerHTML = `
            <li style="cursor: pointer;">
                <a><span class="play_no">${item.number_music}</span>
                    <span class="play_hover" onclick="animatedPlayMusic(${item.number_music}, '${item.link_drive}', '${item.count_music}', '${item.id_music}', '${item.music_data}')">
                        <i class="flaticon-play-button"></i>
                    </span></a>
            </li>
            <li class="song_title_width">
                <div class="top_song_artist_wrapper">
                    <img src="${item.cover}" alt="img" class="cover_music">
                    <div class="top_song_artist_contnt">
                        <h1><a class="title_music">${item.title}</a></h1>
                        <p class="various_artist_text"><a class="artist_music">${item.artist}</a></p>
                    </div>
                </div>
            </li>
            <li class="song_title_width"><a class="album_music">${item.album}</a></li>
            <li class="text-center"><a class="time_music">${item.time}</a></li>
            <li class="text-center favorite-text-center">
                ${item.favorite ? `<i class="fas fa-heart" style="color: #1fd660;"></i>` : `<i class="far fa-heart" style="color: #fff;"></i>`}
            </li>
        `;
        musicList.appendChild(musicItem);
    });
}
