<?php

include './connection.php';

function setPin($db, $uid)
{
    if ($stmt = $db->prepare('UPDATE playlist SET pin = true, date_pin = ? WHERE uid = ?')) {
        $stmt->bind_param('is', $uid, date('Y-m-d H:i:s'));
        $stmt->execute();
        $stmt->close();

    } else {
        echo 'Could not prepare statement!';
    }
}

function unPin($db, $uid)
{
    if ($stmt = $db->prepare('UPDATE playlist SET pin = false, date_pin = NULL WHERE uid = ?')) {
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $stmt->close();
    } else {
        echo 'Could not prepare statement!';
    }
}

switch ($_GET['action']) {
    case 'pin':
        setPin($conn, $uid);
        break;
    case 'unpin':
        unPin($conn, $uid);
        break;
    default:
        break;
}

$db->close();