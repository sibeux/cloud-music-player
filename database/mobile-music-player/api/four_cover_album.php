<?php

include './connection.php';

$sql = "";

function getFourCovercategory($type)
{
    global $sql;

    $sql = "SELECT 
    p.uid AS playlist_uid,
    SUBSTRING_INDEX(GROUP_CONCAT(cover ORDER BY title ASC SEPARATOR ','), ',', 1) AS cover_1,
    SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(cover ORDER BY title ASC SEPARATOR ','), ',', 2), ',', -1) AS cover_2,
    SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(cover ORDER BY title ASC SEPARATOR ','), ',', 3), ',', -1) AS cover_3,
    SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(cover ORDER BY title ASC SEPARATOR ','), ',', 4), ',', -1) AS cover_4,
    (CASE WHEN SUBSTRING_INDEX(GROUP_CONCAT(cover ORDER BY title ASC SEPARATOR ','), ',', 1) IS NOT NULL THEN 1 ELSE 0 END +
    CASE WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(cover ORDER BY title ASC SEPARATOR ','), ',', 2), ',', -1) IS NOT NULL THEN 1 ELSE 0 END +
    CASE WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(cover ORDER BY title ASC SEPARATOR ','), ',', 3), ',', -1) IS NOT NULL THEN 1 ELSE 0 END +
    CASE WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(cover ORDER BY title ASC SEPARATOR ','), ',', 4), ',', -1) IS NOT NULL THEN 1 ELSE 0 END) 
    AS total_non_null_cover
FROM 
    playlist p
LEFT JOIN 
    (SELECT min(title) as title, cover, category
FROM music
GROUP by cover
ORDER BY title asc
LIMIT 4
    ) AS m ON p.uid = m.category
WHERE p.image IS NULL and p.type = 'category'
GROUP BY 
    p.uid
ORDER BY 
    p.uid ASC;";
}

switch ($_GET['method']) {
    case 'four_cover_category':
        getFourCovercategory($_GET['type']);
        break;
    default:
        break;
}

$result = $db->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $db->error);
}

// Create an array to store the data
$data = array();

// Check if there is any data
if ($result->num_rows > 0) {
    // Loop through each row of data
    while ($row = $result->fetch_assoc()) {
        // Clean up the data to handle special characters
        array_walk_recursive($row, function (&$item) {
            if (is_string($item)) {
                $item = htmlentities($item, ENT_QUOTES, 'UTF-8');
            }
        });

        // Add each row to the data array
        $data[] = $row;
    }
}

// Convert the data array to JSON format
$json_data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Check if JSON conversion was successful
if ($json_data === false) {
    die("JSON encoding failed");
}

// Output the JSON data
echo $json_data;

// Close the dbection
$db->close();