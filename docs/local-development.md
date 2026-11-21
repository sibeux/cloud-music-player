# Local Development Guide (PHP Built-in Server)

This guide explains how to run and test the **Cloud Music Player** backend locally using PHP's built-in web server and the custom `router.php` routing script.

---

## 1. What is `php -S`?

PHP has included a **built-in web server** since version 5.4. It is designed specifically for **local development and testing**. 

With this feature, you **do not need** to install or run heavy web servers like Apache, Nginx, XAMPP, or Laragon just to run and test backend APIs locally.

Basic syntax:
```bash
php -S <host>:<port> [router_script]
```

---

## 2. Why is `router.php` Needed?

In production web servers like Apache, routing and URL rewrites are handled by `.htaccess` (mod_rewrite) so that clean endpoints like `/api/health` automatically execute `api/health.php`.

However, the PHP built-in web server (`php -S`) does not process `.htaccess` files. That is why **`router.php`** acts as a lightweight local router:

1. **Serve Static Files Directly:** If an incoming request points to an existing file on disk (such as `.css`, `.js`, `.yaml`, `.png`, or `.svg`), the script returns `false`, allowing the built-in server to serve it directly.
2. **Auto-Extension `.php`:** If you call `/api/health`, the router locates and executes `api/health.php`.
3. **Directory Index Support:** If you navigate to `/api/docs` or `/api/docs/`, the router detects the directory and automatically serves `api/docs/index.php`.
4. **Custom Fallbacks:** Safely forwards dedicated routes (such as `/api/logs` or `/public/index.php`).

---

## 3. Prerequisites

Before starting the server, ensure:

1. **PHP is installed (CLI):**
   ```bash
   php -v
   ```
2. **Required PHP extensions are enabled** in your `php.ini` (e.g., `mysqli`, `curl`, `mbstring`, `openssl`).
3. **Composer dependencies are installed:**
   ```bash
   composer install
   ```
4. **Environment Configuration (`.env`):**
   Ensure your `.env` file exists at the project root with the appropriate MySQL credentials:
   ```env
   USERNAME=root
   PASSWORD=
   CYBEAT_DATABASE=sibk1922_cloud_music
   JWT_AUTH_SECRET=your_jwt_secret_here
   ```

---

## 4. Running the Server

Open your terminal (PowerShell, Command Prompt, or Git Bash) in the project root directory:

### Primary Command
```bash
php -S localhost:8000 router.php
```

Or using the Composer shortcut:
```bash
composer serve
```

When started, your terminal will display:
```text
PHP 8.x.x Development Server (http://localhost:8000) started
```

> [!NOTE]
> Keep the terminal window running while developing. The terminal serves as a **live access log**, outputting real-time HTTP requests, response status codes, and timestamps.

### Stopping the Server
Press:
```
Ctrl + C
```

---

## 5. Useful Options & Tips

### A. Changing the Port (If 8000 is Already in Use)
If you encounter `Address already in use`, simply specify an alternate port:
```bash
php -S localhost:8080 router.php
```

### B. Accessing from Mobile Devices / Android Emulators (LAN / WiFi)
If you are testing from a physical mobile device or an Android emulator on the same network:
```bash
php -S 0.0.0.0:8000 router.php
```
Then access the API from your mobile app using your computer's local IP (e.g., `http://192.168.1.5:8000/api/health`).

---

## 6. Testing Endpoints

Once the server is running on `http://localhost:8000`, you can access:

| Endpoint | Description |
| :--- | :--- |
| `http://localhost:8000/api/health` | Backend health check |
| `http://localhost:8000/api/docs` | Interactive **Scalar API Reference Documentation** |
| `http://localhost:8000/api/docs/openapi.yaml` | Raw OpenAPI 3.0.3 specification file |
| `http://localhost:8000/api/music/artist/all` | Fetch artist catalog |

---

## 7. Development vs Production Considerations

* **`php -S` is strictly for Local Development:**
  The built-in web server runs single-threaded and processes requests sequentially. It is ideal for rapid development and testing on your local machine.
* **For Production (VPS / Web Hosting):**
  Use production-grade web servers like **Apache** (with mod_rewrite / `.htaccess`) or **Nginx** (with PHP-FPM) to handle concurrent traffic securely and reliably.
