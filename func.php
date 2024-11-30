<?php

function compressImage($image_url, $new_width = 100, $new_height = 100) {
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

    // Ubah ukuran
    $resized_image = imagescale($image, $new_width, $new_height);

    // Simpan ke variabel (bukan file)
    ob_start();
    switch ($mime_type) {
        case 'image/jpeg':
            // Menambahkan kualitas kompresi JPEG (0-100)
            imagejpeg($resized_image, null, 85); // 85 adalah kualitas JPEG
            break;
        case 'image/png':
            // PNG tidak menggunakan kualitas seperti JPEG
            imagepng($resized_image);
            break;
        case 'image/gif':
            imagegif($resized_image);
            break;
    }

    // Ambil gambar hasil kompresi dari buffer
    $compressed_image_data = ob_get_clean();

    // Bersihkan memori
    imagedestroy($image);
    imagedestroy($resized_image);

    return $compressed_image_data;
}