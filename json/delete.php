<?php
// generate function
function deleteJSON(){
    // include "./json/delete.php";

    // delete all data in json file
    // Path to the JSON file
    $jsonFilePath = './music.json';

    // Open the JSON file for writing
    $file = fopen($jsonFilePath, 'w');

    // Truncate the file (erase its contents)
    ftruncate($file, 0);

    // Close the file
    fclose($file);
};

// echo 'All data deleted from the JSON file.';