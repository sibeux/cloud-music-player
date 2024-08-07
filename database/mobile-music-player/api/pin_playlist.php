<?php

include './connection.php';

function setPin($db, $uid)
{
    $sql = "UPDATE playlist SET pin = true, date_pin = NOW() WHERE uid = '$uid'";

    // Query to retrieve data from MySQL
    $result = $db->query($sql);

    // Check if the query was successful
    if (!$result) {
        die("Query failed: " . $db->error);
    } else {
        echo "Success";
    }
}

function unPin($db, $uid)
{
    $sql = "UPDATE playlist SET pin = 'false', date_pin = NULL WHERE uid = '$uid'";

    // Query to retrieve data from MySQL
    $result = $db->query($sql);

    // Check if the query was successful
    if (!$result) {
        die("Query failed: " . $db->error);
    } else {
        echo "Success";
    }
}

switch ($_GET['action']) {
    case 'pin':
        setPin($conn, $_GET['uid']);
        break;
    case 'unpin':
        unPin($conn, $_GET['uid']);
        break;
    default:
        break;
}

// Close the connection
$conn->close();