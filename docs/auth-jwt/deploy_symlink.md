Nah, **ini menjelaskan error tadi**.

Script deployment-mu sekarang **tidak memakai hardcoded path**. Dia mengambil:

```bash
ENV_FILE="$SCRIPT_DIR/../.env"
```

lalu:

```bash
source "$ENV_FILE"
```

Jadi `DOC_ROOT` dan `REPO_PUBLIC` memang seharusnya berasal dari `.env`.

Isi `.env` kamu:

```env
DOC_ROOT="/home/sibn9212/public_html/cybeat.sibeux.my.id"
REPO_PUBLIC="/home/sibn9212/repositories/cloud-music-player/public"
```

Itu **sudah benar**.

Masalahnya adalah `.env` kemungkinan masih CRLF dari Windows. Makanya ketika:

```bash
source "$ENV_FILE"
```

dijalankan, Bash membaca `\r` sebagai karakter tambahan dan keluar:

```text
$'\r': command not found
```

### Fix

Di server:

```bash
cd /home/sibn9212/repositories/cloud-music-player
sed -i 's/\r$//' .env
```

Lalu:

```bash
bash utils/deploy_symlinks.sh
```

Kalau mau memastikan `.env` sudah bersih:

```bash
cat -A .env
```

Sebelum diperbaiki biasanya terlihat:

```text
DOC_ROOT="..."\r$
```

Setelah diperbaiki harus menjadi:

```text
DOC_ROOT="..."$
```

### Tapi ada satu hal yang perlu dibenerin di desain

Kalau `DOC_ROOT` dan `REPO_PUBLIC` memang **deployment-specific**, menyimpannya di `.env` masuk akal.

Tapi `.env` kamu juga kemungkinan berisi credential aplikasi. Jadi script deployment yang `source .env` berarti **seluruh isi `.env` dimasukkan sebagai environment variable**:

```bash
set -a
source "$ENV_FILE"
set +a
```

Untuk script symlink ini sebenarnya **tidak perlu load seluruh `.env`**. Kalau cuma butuh dua variable, lebih bersih kalau `.env` deployment dipisahkan atau path-nya dibuat langsung di script.

Namun untuk sekarang, **jangan ubah arsitekturnya dulu**. Fix CRLF-nya dan jalankan lagi.

---

[sibn9212@jembayan cloud-music-player]$ cd /home/sibn9212/repositories/cloud-music-player
sed -i 's/\r$//' .env
[sibn9212@jembayan cloud-music-player]$ bash utils/deploy_symlinks.sh
========================================
Deployment Symlink Check
========================================
Document Root: /home/sibn9212/public_html/cybeat.sibeux.my.id
Repository Public: /home/sibn9212/repositories/cloud-music-player/public
----------------------------------------
Creating symlink for 'alternative.php'...
  -> Success
Creating symlink for 'css'...
  -> Success
Creating symlink for 'fonts'...
  -> Success
Creating symlink for 'images'...
  -> Success
Creating symlink for 'index.php'...
  -> Success
Creating symlink for 'indopride.php'...
  -> Success
Creating symlink for 'js'...
  -> Success
Creating symlink for 'json'...
  -> Success
Creating symlink for 'lazy.php'...
  -> Success
Creating symlink for 'load_more.php'...
  -> Success
Creating symlink for 'old-index.php'...
  -> Success
Creating symlink for 'privacy'...
  -> Success
Creating symlink for 'test-symlink.txt'...
  -> Success
Creating symlink for 'webfonts'...
  -> Success
Creating symlink for 'worldwide.php'...
  -> Success
----------------------------------------
Deployment Summary:
  Created         : 15
  Already correct : 0
  Conflicts       : 0
  Errors          : 0
========================================
Deployment checks completed successfully.
[sibn9212@jembayan cloud-music-player]$

---

Nah, **berhasil sempurna.** 🔥

Script melakukan persis yang kita mau:

```text
Created         : 15
Already correct : 0
Conflicts       : 0
Errors          : 0
```

Sekarang struktur deployment-mu kira-kira:

```text
/home/sibn9212/public_html/cybeat.sibeux.my.id/
├── api -> /home/sibn9212/repositories/cloud-music-player/api
├── index.php -> .../cloud-music-player/public/index.php
├── css -> .../public/css
├── js -> .../public/js
├── images -> .../public/images
├── fonts -> .../public/fonts
├── json -> .../public/json
├── privacy -> .../public/privacy
├── *.php -> .../public/*.php
├── .htaccess                 ← hosting
├── .user.ini                 ← hosting
├── php.ini                   ← hosting
├── .well-known               ← hosting
└── cgi-bin                   ← hosting
```

Dan yang penting, **repository tetap bersih dari sisi exposure**:

```text
cloud-music-player/
├── api/        ← exposed via /api symlink
├── public/     ← exposed via individual symlinks
├── database/   ← NOT exposed
├── storage/    ← NOT exposed
├── vendor/     ← NOT exposed
├── utils/      ← NOT exposed
├── .git/       ← NOT exposed
└── .env        ← NOT exposed
```

### Sekarang tinggal test

Buka:

```text
https://cybeat.sibeux.my.id/
```

Kalau frontend muncul, berarti **deployment layer-nya sudah beres**.

Lalu test API yang sebelumnya sudah kamu pakai:

```text
https://cybeat.sibeux.my.id/api/...
```

Kalau dua-duanya hidup, arsitektur kita sudah jalan:

**Git repository → `public/` → symlink → hosting DocumentRoot**

Dan ke depannya kalau kamu `git pull` lalu ada file baru di `public/`, tinggal:

```bash
bash utils/deploy_symlinks.sh
```

Script akan membuat symlink yang belum ada dan membiarkan yang sudah benar. **Tidak perlu bikin symlink manual satu-satu lagi.**
