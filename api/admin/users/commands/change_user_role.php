<?php

function changeUserRole($db, $adminUserId, $userId, $newRole)
{
    $allowedRoles = ['free', 'premium', 'admin'];
    if (!in_array($newRole, $allowedRoles, true)) {
        sendAdminJsonError(422, 'invalid_role', 'Role harus free, premium, atau admin.');
    }

    if ($userId < 1) {
        sendAdminJsonError(422, 'invalid_user', 'ID user tidak valid.');
    }

    if ($userId === $adminUserId && $newRole !== 'admin') {
        sendAdminJsonError(409, 'cannot_remove_own_admin', 'Admin tidak bisa mengubah role admin dirinya sendiri.');
    }

    $db->begin_transaction();
    try {
        // Lock admin rows while checking the invariant to prevent removing the last admin.
        $admins = $db->query("SELECT user_id FROM users WHERE role = 'admin' FOR UPDATE");
        $adminIds = $admins->fetch_all(MYSQLI_ASSOC);

        $find = $db->prepare('SELECT user_id, role FROM users WHERE user_id = ? FOR UPDATE');
        $find->bind_param('i', $userId);
        $find->execute();
        $target = $find->get_result()->fetch_assoc();
        $find->close();

        if (!$target) {
            $db->rollback();
            sendAdminJsonError(404, 'user_not_found', 'User tidak ditemukan.');
        }

        if ($target['role'] === 'admin' && $newRole !== 'admin' && count($adminIds) <= 1) {
            $db->rollback();
            sendAdminJsonError(409, 'last_admin', 'Role admin terakhir tidak bisa dihapus.');
        }

        $update = $db->prepare('UPDATE users SET role = ? WHERE user_id = ?');
        $update->bind_param('si', $newRole, $userId);
        $update->execute();
        $update->close();
        $db->commit();

        return ['user_id' => $userId, 'role' => $newRole];
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
}
