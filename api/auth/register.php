<?php
require __DIR__ . '/../../vendor/autoload.php';
// require_once __DIR__ . '/../../database/mobile-music-player/api/connection.php';
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/auth_jwt.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$secretKey = $_ENV['JWT_AUTH_SECRET'] ?? null;

if (!$secretKey) {
    die("Error: Secret key belum disetting di .env");
}

// Header JSON: Agar frontend/Android tahu ini data JSON, bukan teks biasa.
header('Content-Type: application/json');

// Pindahkan Session Start ke paling atas
// Hal ini mencegah error "Headers already sent" jika file ini di-include di tempat lain.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getEmailCheck($db)
{
    // KONFIGURASI RATE LIMIT
    $max_requests = 5;
    $time_window = 60;

    if (!isset($_SESSION['last_request_time'])) {
        $_SESSION['last_request_time'] = time();
        $_SESSION['request_count'] = 0;
    }

    $time_elapsed = time() - $_SESSION['last_request_time'];

    if ($time_elapsed > $time_window) {
        $_SESSION['last_request_time'] = time();
        $_SESSION['request_count'] = 0;
    }

    if ($_SESSION['request_count'] >= $max_requests) {
        http_response_code(429);
        echo json_encode([
            "error" => "Terlalu banyak percobaan. Silakan coba lagi dalam 1 menit."
        ]);
        return;
    }

    $_SESSION['request_count']++;
    // AKHIR RATE LIMIT

    // Validasi input
    if (!isset($_POST['email'])) {
        echo json_encode(["error" => "Email kosong"]);
        return;
    }

    // CATATAN: Pastikan nama tabel 'users' dan kolom 'email' sesuai database
    if ($stmt = $db->prepare('SELECT users.email FROM users WHERE users.email = ?')) {
        $stmt->bind_param('s', $_POST['email']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email ditemukan (Terpakai)
            echo json_encode(["email_exists" => "true"]);
        } else {
            // Email tidak ditemukan (Bisa dipakai)
            echo json_encode(["email_exists" => "false"]);
        }
        $stmt->close();
    } else {
        // Log error untuk admin, jangan tampilkan detail error SQL ke user
        error_log("DB Prep Error: " . $db->error);
        echo json_encode(["error" => "System error"]);
    }
}

function createUser($db, $secretKey)
{
    // Validasi Input Dasar
    if (empty($_POST['email']) || empty($_POST['name']) || empty($_POST['password'])) {
        echo json_encode([
            "status" => "failed",
            "message" => "Data tidak lengkap (Email, Nama, atau Password kosong)"
        ]);
        return;
    }

    // Siapkan Data
    $email = $_POST['email'];
    $name = $_POST['name'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';

    // Prepare Statement (Kolom NULL/Auto Increment dihapus agar lebih bersih)
    $query = 'INSERT INTO `users` (`email`, `name`, `password`) VALUES (?, ?, ?)';

    if ($stmt = $db->prepare($query)) {

        $stmt->bind_param('sss', $email, $name, $password);

        // Cek apakah eksekusi berhasil atau gagal (misal: email duplikat)
        try {
            if ($stmt->execute()) {
                // Ambil ID user yang baru saja digenerate oleh database
                $userId = $stmt->insert_id;

                // Generate token secara otomatis
                $token = generateToken($userId, $email, $name, $role, $secretKey);

                // Return response
                echo json_encode(["status" => "success", "message" => "User berhasil ditambahkan", "token" => $token,]);
            } else {
                // Tangani jika execute return false (jarang terjadi jika try-catch aktif, tapi untuk jaga-jaga)
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            // Cek kode error untuk duplikat entry (biasanya 1062 di MySQL)
            if ($db->errno === 1062) {
                echo json_encode([
                    "status" => "failed",
                    "message" => "Email sudah terdaftar"
                ]);
            } else {
                echo json_encode([
                    "status" => "failed",
                    "message" => "Gagal menyimpan data: " . $e->getMessage()
                ]);
            }
        }

        $stmt->close();

    } else {
        // Gagal prepare
        echo json_encode([
            "status" => "failed",
            "message" => "Terjadi kesalahan sistem (Prepare Statement Failed)"
        ]);
    }
}

// Cek keberadaan 'method' sebelum switch
// Tanpa isset, jika user akses langsung tanpa param method, akan muncul warning "Undefined index"
if (isset($_POST['method'])) {
    switch ($_POST['method']) {
        case 'email_check':
            getEmailCheck($db);
            break;
        case 'create_user':
            createUser($db, $secretKey);
            break;
        default:
            echo json_encode(["error" => "Invalid method"]);
            break;
    }
} else {
    echo json_encode(["error" => "Method not provided"]);
}

$db->close();