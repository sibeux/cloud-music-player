**DELIMITER // itu buat apa?**

`DELIMITER //` itu gunanya untuk **mengganti tanda pengakhir perintah** sementara di MySQL.

Secara *default*, MySQL menggunakan titik koma (`;`) sebagai tanda bahwa sebuah perintah SQL sudah selesai dan siap dijalankan. Tapi, saat kita membuat *Stored Procedure*, masalah muncul karena di dalam prosedur itu sendiri biasanya ada banyak titik koma.

---

### Kenapa Kita Butuh Menggantinya?

Bayangkan kamu sedang menulis kode ini tanpa `DELIMITER //`:

```sql
CREATE PROCEDURE CekData()
BEGIN
  SELECT * FROM TabelA;  -- < MySQL mengira perintah SELESAI di sini!
  SELECT * FROM TabelB;
END;

```

**Masalahnya:** Begitu MySQL bertemu titik koma pertama setelah `TabelA`, dia akan langsung mencoba menjalankan kode tersebut. Padahal, prosedur kamu belum selesai ditulis sampai kata `END`. Hasilnya? **Error.**

---

### Cara Kerjanya

1. **`DELIMITER //`**: Kamu bilang ke MySQL, *"Mulai sekarang, jangan anggap `;` sebagai akhir perintah. Anggap `//` sebagai tanda selesainya."*
2. **Menulis Prosedur**: Kamu bebas memakai `;` di dalam blok `BEGIN ... END` tanpa takut perintahnya terputus di tengah jalan.
3. **`END //`**: Kamu menutup prosedur dengan `//`. Sekarang MySQL tahu bahwa seluruh blok prosedur sudah lengkap dan baru menjalankannya.
4. **`DELIMITER ;`**: Kamu mengembalikan fungsi titik koma (`;`) ke normal agar perintah SQL biasa bisa jalan lagi seperti semula.

---

### Analogi Sederhana

Ibarat kamu sedang menulis surat yang di dalamnya ada banyak tanda titik (.).

* **Normalnya:** Setiap ada titik, pak pos langsung mengambil surat itu dan mengirimnya.
* **Masalah:** Surat kamu belum selesai, tapi sudah diambil pak pos karena ada titik di kalimat pertama.
* **Solusi (`DELIMITER`):** Kamu bilang ke pak pos, *"Jangan ambil surat saya sampai saya kasih tanda **SELESAI** di pojok kertas."* Setelah tanda itu ada, baru pak pos membawanya.