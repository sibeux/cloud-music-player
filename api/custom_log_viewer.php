<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Viewer</title>
    <style>
        body {
            background-color: #f4f4f9;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .log-container {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .log-entry {
            padding: 8px 12px;
            margin-bottom: 6px;
            border-radius: 5px;
            color: white;
            white-space: pre-wrap; /* Agar spasi dan format tetap terjaga */
            word-wrap: break-word; /* Agar teks panjang tidak merusak layout */
        }
        /* Style untuk setiap jenis log */
        .log-error {
            background-color: #e74c3c; /* Merah */
        }
        .log-success {
            background-color: #2ecc71; /* Hijau */
        }
        .log-info {
            background-color: #34495e; /* Abu-abu gelap/hitam */
        }
        .log-warning {
            background-color: #f39c12; /* Oranye */
        }
        .log-default {
            background-color: #95a5a6; /* Abu-abu netral */
            color: #2c3e50;
        }
    </style>
</head>
<body>

    <h1>Log Viewer - custom.log</h1>

    <div class="log-container">
        <?php
        $logFile = 'custom.log';

        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lines = array_reverse($lines); // Tampilkan dari yang terbaru

            foreach ($lines as $line) {
                $css_class = 'log-default'; // Kelas default jika tidak ada keyword

                // Cek kata kunci (case-insensitive)
                if (stripos($line, '[ERROR]') !== false) {
                    $css_class = 'log-error';
                } elseif (stripos($line, '[SUCCESS]') !== false) {
                    $css_class = 'log-success';
                } elseif (stripos($line, '[INFO]') !== false) {
                    $css_class = 'log-info';
                } elseif (stripos($line, '[WARNING]') !== false) {
                    $css_class = 'log-warning';
                }

                // Tampilkan baris log dengan div dan kelas yang sesuai
                // htmlspecialchars() penting untuk keamanan dan mencegah teks log merusak HTML
                echo sprintf(
                    '<div class="log-entry %s">%s</div>',
                    $css_class,
                    htmlspecialchars($line)
                );
            }
        } else {
            echo '<div class="log-entry log-error">Error: File log tidak ditemukan di ' . htmlspecialchars($logFile) . '</div>';
        }
        ?>
    </div>

</body>
</html>