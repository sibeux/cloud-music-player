let currentPage = 1;

// window.addEventListener('scroll', function () {
//     const scrollHeight = document.documentElement.scrollHeight;
//     const scrollPosition = window.innerHeight + window.scrollY;
//     if (scrollHeight - scrollPosition <= 20) { // jika dekat dengan bawah
//         currentPage++;
//         loadMoreMusic(currentPage);
//     }
// });

function loadMoreMusic(page) {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'lazy.php?page=' + page, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            // let data = JSON.parse(`${xhr.responseText}`);
            let data = xhr.responseText;
            appendMusic(data);
        }
    };
    xhr.send();
}

function appendMusic(music) {
    $(".album_list_wrapper_shop").append(music);
}
