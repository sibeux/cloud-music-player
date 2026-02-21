<?php
/**
 * Menambahkan foreign key constraint pada tabel `recent_musics`.
 * 
 * Constraint ini memastikan integritas data antara tabel `recent_musics` dan `users`.
 * Jika seorang pengguna dihapus, semua riwayat musik mereka di `recent_musics` 
 * akan otomatis dihapus (CASCADE).
 *
 * @table recent_musics
 * @constraint fk_recent_musics_user
 * @on_delete CASCADE
 * @on_update RESTRICT
 * 
 * ALTER TABLE recent_musics
 * ADD CONSTRAINT fk_recent_musics_user -- Nama ini harus unik, jangan cuma 'fk_user'
 * FOREIGN KEY (user_id) REFERENCES users(user_id)
 * ON DELETE CASCADE ON UPDATE RESTRICT;
 */