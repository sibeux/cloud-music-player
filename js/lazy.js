let currentPage = 1;
const limit = 10;  // Musik per halaman

window.addEventListener('scroll', function () {
    const scrollHeight = document.documentElement.scrollHeight;
    const scrollPosition = window.innerHeight + window.scrollY;
    if (scrollHeight - scrollPosition <= 20) { // jika dekat dengan bawah
        currentPage++;
        loadMoreMusic(currentPage);
    }
});

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

    // $.ajax({
    //     type: "GET",
    //     url: "lazy.php",
    //     data: {
    //         'page': page,
    //     },
    //     success: function (data) {
    //         $(".album_list_wrapper album_list_wrapper_shop").append(data);
    //     }
    // });
}

function appendMusic(music) {
    var element = document.getElementsByClassName("album_list_wrapper album_list_wrapper_shop");
    console.log(element);
    element.innerHTML = music;
    $(".album_list_wrapper album_list_wrapper_shop").append(music);
}
