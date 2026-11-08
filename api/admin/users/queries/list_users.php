<?php

function listUsers($db)
{
    $result = $db->query(
        'SELECT user_id, name, email, role FROM users ORDER BY user_id DESC'
    );

    return $result->fetch_all(MYSQLI_ASSOC);
}
