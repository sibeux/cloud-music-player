<?php

function compressImage($image_url){
    // Deteksi format berdasarkan mime type
    $image_info = getimagesize($image_url);
    $mime_type = $image_info['mime'];

    // Muat gambar berdasarkan format
    switch ($mime_type) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($image_url);
            break;
        case 'image/png':
            $image = imagecreatefrompng($image_url);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($image_url);
            break;
        default:
            die('Format gambar tidak didukung.');
    }

    // Resolusi baru
    $new_width = 100;
    $new_height = 100;

    // Ubah ukuran
    $resized_image = imagescale($image, $new_width, $new_height);

    // Mulai output buffer
    ob_start();

    // Kirim header yang sesuai
    header("Content-Type: $mime_type");

    // Output gambar yang sudah dikompresi
    switch ($mime_type) {
        case 'image/jpeg':
            imagejpeg($resized_image);
            break;
        case 'image/png':
            imagepng($resized_image);
            break;
        case 'image/gif':
            imagegif($resized_image);
            break;
    }

    // Ambil hasil gambar dalam buffer dan kembalikan
    // return ob_get_clean();
    return $image_url;
}