<?php
// Header CORS (Wajib agar Frontend bisa akses)
require_once __DIR__ . '/cors.php';

// Load Composer & Class
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/auth/bearer_auth.php';