# Cloud Music Player - Backend API

RESTful API backend for the Cloud Music Player streaming platform.

## Quick Start

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Start the local server:**
   ```bash
   composer serve
   # or: php -S localhost:8000 router.php
   ```

3. **Explore endpoints:**
   * **Interactive API Reference (Scalar):** [http://localhost:8000/api/docs](http://localhost:8000/api/docs)
   * **Health Check:** [http://localhost:8000/api/health](http://localhost:8000/api/health)

---

## Documentation

Detailed guides and specifications are organized in the [`docs/`](docs/) directory:

| Topic | Description | Link |
| :--- | :--- | :--- |
| **Local Development** | In-depth guide on `php -S`, `router.php`, LAN/mobile testing, and port setup | [docs/local-development.md](docs/local-development.md) |
| **API Specification** | Raw OpenAPI 3.0.3 YAML schema powering the Scalar docs | [api/docs/openapi.yaml](api/docs/openapi.yaml) |
| **JWT Authentication** | Setup guide for JWT tokens and authentication | [docs/auth-jwt/setup_auth_jwt.md](docs/auth-jwt/setup_auth_jwt.md) |
| **Deployment** | Server deployment notes and symlink scripts | [docs/auth-jwt/deploy_symlink.md](docs/auth-jwt/deploy_symlink.md) |
