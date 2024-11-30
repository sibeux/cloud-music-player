window.onscroll = function () {
    if (document.body.scrollHeight === window.innerHeight + window.scrollY) {
        var page = getCurrentPage(); // Ambil halaman saat ini
        loadMoreMusic(page);
    }
};

function loadMoreMusic(page) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "lazy.php?page=" + page, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            appendMusic(data);
        }
    };
    xhr.send();
}
