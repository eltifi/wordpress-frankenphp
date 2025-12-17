# Dockerized WordPress on FrankenPHP

[![Build](https://github.com/eltifi/wordpress-frankenphp/actions/workflows/ci.yml/badge.svg)](https://github.com/eltifi/wordpress-frankenphp/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A high-performance command-line optimized WordPress environment running on [FrankenPHP](https://frankenphp.dev/), utilizing the power of [Caddy Web Server](https://caddyserver.com/) and [Docker](https://www.docker.com/).

This project provides a fully automated, "zero-touch" WordPress installation that is ready for development or production immediately after booting.

## Features

- **High Performance**: Built on **PHP 8.5** and FrankenPHP, a modern application server for PHP built on top of Caddy.
- **Automated Setup**: WordPress is automatically downloaded, installed, and configured upon the first container start.
- **Dual HTTP/HTTPS**: Out-of-the-box support for both HTTP and HTTPS.
    - Automatic self-signed certificate generation for local development (`localhost`).
    - Production-ready TLS support via Caddy's automatic HTTPS.
- **Optimized**: 
    - Worker mode enabled for FrankenPHP.
    - Brotli (`br`) and Zstandard (`zstd`) compression enabled.
    - Redis object caching configured.
    - **Image Optimization**: On-the-fly image processing and optimization via **imgproxy**.
- **Secure**: Sensitive keys and salts are automatically generated if not provided.

## Security & Hardening

This project implements a multi-layered security approach:

### 1. Header Hardening
Sensitive headers that leak information are stripped or obscured:
*   **`X-Powered-By`**: Removed via custom PHP configuration (`expose_php = Off`).
*   **`Link` (REST API)**: Removed via a custom WordPress MU-plugin to prevent user enumeration.
*   **`Server`**: Minimized to generic "FrankenPHP Caddy".
*   **`X-Pingback` / `X-Redirect-By`**: Removed to reduce footprint.
*   **Additional Headers**: `Strict-Transport-Security` (HSTS), `X-Frame-Options` (SAMEORIGIN), `X-Content-Type-Options` (nosniff), `Referrer-Policy`, and `Permissions-Policy` are enforced.

### 2. Access Control
Critical files and endpoints are blocked at the server level (Caddy):
*   **Sensitive Files**: `.env`, `.htaccess`, `wp-config.php`, `*.sql`, `*.bak`, `*.log`, `*.old`.
*   **Version Control**: `.git` directory and subdirectories.
*   **Legacy API**: `xmlrpc.php` is blocked (403 Forbidden) to prevent brute-force attacks.

### 3. Performance Enhancements
*   **FrankenPHP Worker Mode**: Keeps the application in memory for "blazing fast" performance.
*   **Compression**: Modern **Brotli** (`br`) and **Zstandard** (`zstd`) compression enabled alongside Gzip.
*   **Browser Caching**: Static assets (CSS, JS, Images) are cached for 1 year (`Cache-Control: public, max-age=31536000`).
*   **Redis**: Pre-configured for object caching.

## Attribution & Credits

This project stands on the shoulders of giants. We gratefully acknowledge the following open-source projects:

*   **[WordPress](https://wordpress.org/)**: The world's most popular content management system. Licensed under [GPLv2+](https://wordpress.org/about/license/).
*   **[FrankenPHP](https://frankenphp.dev/)**: The modern PHP app server. Licensed under [MIT](https://github.com/dunglas/frankenphp/blob/main/LICENSE).
*   **[Caddy](https://caddyserver.com/)**: The ultimate server with automatic HTTPS. Licensed under [Apache 2.0](https://github.com/caddyserver/caddy/blob/master/LICENSE).
*   **[PHP](https://www.php.net/)**: A popular general-purpose scripting language that is especially suited to web development. Licensed under [PHP License](https://www.php.net/license/).
*   **[MariaDB](https://mariadb.org/)**: An open source relational database. Licensed under [GPLv2](https://mariadb.org/about/#license).
*   **[WP-CLI](https://wp-cli.org/)**: The command line interface for WordPress. Licensed under [MIT](https://github.com/wp-cli/wp-cli/blob/main/LICENSE).

## Getting Started

### Prerequisites

*   Docker && Docker Compose

### Fast Start

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/eltifi/wordpress-frankenphp.git
    cd wordpress-frankenphp
    ```

2.  **Start the environment**:
    ```bash
    docker compose up -d
    ```

3.  **Access the site**:
    *   Open [https://localhost](https://localhost) (Accept the self-signed certificate warning for local dev).
    *   Or [http://localhost](http://localhost).
    *   Default Credentials (if not changed in `.env`):
        *   User: `admin`
        *   Password: `password`

### Configuration

#### Stopping the Environment

```bash
docker compose down
```

To remove all data:
```bash
docker compose down -v
```

#### Environment Variables

Create a `.env` file in the project root to customize settings. An `.env.example` file is provided as reference.

#### WordPress Settings
| Variable | Description | Default |
|----------|-------------|---------|
| `WORDPRESS_URL` | The URL of the site | `http://localhost` |
| `WORDPRESS_TITLE` | The title of the website | `My Docker WordPress` |
| `WORDPRESS_ADMIN_USER` | Admin username for auto-install | `admin` |
| `WORDPRESS_ADMIN_PASSWORD` | Admin password for auto-install | `password` |
| `WORDPRESS_ADMIN_EMAIL` | Admin email address | `admin@example.com` |
| `WORDPRESS_DB_CHARSET` | Database charset | `utf8mb4` |
| `WORDPRESS_DB_COLLATE` | Database collation | `utf8mb4_unicode_ci` |
| `WORDPRESS_DB_QUERY_TIMEOUT` | Database query timeout in seconds | `5` |
| `WORDPRESS_DB_HOST` | Database host | `mysql` |
| `WORDPRESS_DB_NAME` | Database name | `wordpress_db` |
| `WORDPRESS_DB_USER` | Database user | `wordpress_user` |
| `WORDPRESS_DB_PASSWORD` | Database password | *(randomly generated)* |
| `WORDPRESS_TABLE_PREFIX`| Database table prefix | `wp_` |
| `WORDPRESS_DEBUG` | Enable WP debug mode | `1` (true) |

#### SMTP Configuration
| Variable | Description | Default |
|----------|-------------|---------|
| `WORDPRESS_SMTP_HOST` | SMTP server host | `localhost` |
| `WORDPRESS_SMTP_PORT` | SMTP server port | `1025` |
| `WORDPRESS_SMTP_USER` | SMTP username | *(empty)* |
| `WORDPRESS_SMTP_PASSWORD` | SMTP password | *(empty)* |
| `WORDPRESS_SMTP_FROM` | From email address | `wordpress@localhost` |
| `WORDPRESS_SMTP_FROM_NAME` | From name | `WordPress System` |

#### General Settings
| Variable | Description | Default |
|----------|-------------|---------|
| `WORDPRESS_TAGLINE` | Site tagline | `Just another WordPress site` |
| `WORDPRESS_MEMBERSHIP` | Allow anyone to register (0/1) | `0` (false) |
| `WORDPRESS_DEFAULT_ROLE` | New user default role | `subscriber` |
| `WORDPRESS_LANGUAGE` | Site language code | `en_US` |
| `WORDPRESS_TIMEZONE` | Site timezone | `UTC` |
| `WORDPRESS_DATE_FORMAT` | Date format string | `F j, Y` |
| `WORDPRESS_TIME_FORMAT` | Time format string | `g:i a` |
| `WORDPRESS_START_OF_WEEK` | Start of week (0=Sun, 1=Mon) | `1` |

#### Image Optimization (imgproxy)
| Variable | Description | Default |
|----------|-------------|---------|
| `IMGPROXY_ALLOWED_SOURCES` | Allowed image sources | `http://web,local://` |
| `IMGPROXY_ENFORCE_WEBP` | Force WebP format | `true` |
| `IMGPROXY_ENABLE_WEBP_DETECTION` | Detect browser WebP support | `true` |
| `IMGPROXY_MAX_SRC_RESOLUTION` | Max source image resolution (MP) | `30.0` |
| `IMGPROXY_MEMORY` | Memory limit (MB) | `512` |
| `IMGPROXY_CONCURRENCY` | Processing concurrency | `2` |

### Database Seeding & Content

#### Database Seeding
Place any `.sql` or `.sql.gz` files in the `seed/` directory. These files will be executed automatically by MariaDB when the database container is initialized for the first time. This is perfect for importing a starter database or schema.

#### Custom Content Management
The local `wp-content/` directory is copied directly into the Docker image during the build process.
- **Plugins/Themes**: Place your plugins and themes in `wp-content/plugins` and `wp-content/themes`.
- **MU-Plugins**: The system uses `wp-content/mu-plugins` for critical configurations (like `autoconfig.php`).
- **Build**: Any changes to `wp-content/` require a rebuild (`docker compose build`) to be reflected in the container.

### Advanced Configuration
| Variable | Description | Default |
|----------|-------------|---------|
| `WORDPRESS_CONTENT_URL` | Custom URL for `wp-content` | *(empty)* |
| `WORDPRESS_CONTENT_DIR` | Custom directory path for `wp-content` | `wp-content` |
| `WORDPRESS_FORCE_SSL_ADMIN` | Force SSL for admin | `true` |
| `WORDPRESS_FORCE_SSL_LOGIN` | Force SSL for login | `true` |
| `WORDPRESS_CACHE_KEY_SALT` | Unique salt for object caching | *(auto-generated)* |
| `WORDPRESS_CONFIG_EXTRA` | Additional PHP code for `wp-config.php` | *(empty)* |

#### Security & Salts
*If left empty, these are automatically generated on container start.*
- `WORDPRESS_AUTH_KEY`
- `WORDPRESS_SECURE_AUTH_KEY`
- `WORDPRESS_LOGGED_IN_KEY`
- `WORDPRESS_NONCE_KEY`
- `WORDPRESS_AUTH_SALT`
- `WORDPRESS_SECURE_AUTH_SALT`
- `WORDPRESS_LOGGED_IN_SALT`
- `WORDPRESS_NONCE_SALT`

#### Server & Caddy Configuration
| Variable | Description | Default |
|----------|-------------|---------|
| `SERVER_NAME` | Domain name for Caddy | `localhost` |
| `CADDY_SERVER_TLS` | TLS mode (`internal` for local, or allow empty for ACME) | `internal` |
| `CADDY_SERVER_ROOT` | Web root directory | `/var/www/html` |
| `CADDY_SERVER_OPTIONS` | Additional global Caddy options | *(empty)* |
| `MAX_REQUESTS` | Max requests before worker restart | `100000` |
| `FRANKENPHP_CONFIG` | FrankenPHP specific config keys | *(empty)* |

#### Database Container
| Variable | Description | Default |
|----------|-------------|---------|
| `MYSQL_ROOT_PASSWORD` | Root password for MariaDB | *(empty)* |
| `MYSQL_DATABASE` | Database name to create | `wordpress_db` |
| `MYSQL_USER` | Database user to create | `wordpress_user` |
| `MYSQL_PASSWORD` | Password for the user | *(matches WP_DB_PASSWORD)* |

## Troubleshooting

### Access Issues

**Can't access WordPress?**
- Ensure containers are running: `docker compose ps`
- Check logs: `docker compose logs`
- Verify port 80/443 aren't in use by other services

**Certificate warning in browser?**
- This is normal for local development with self-signed certificates
- Click "Advanced" and proceed (safe for local dev)
- For production, configure `CADDY_SERVER_TLS` with your domain

### Database Issues

**Can't connect to database?**
- Verify MySQL container is running: `docker compose ps mysql`
- Check database credentials in `.env` match `WORDPRESS_DB_*` variables
- Review logs: `docker compose logs mysql`

**Data persists after `docker compose down`?**
- Use `docker compose down -v` to remove volumes (warning: deletes data!)

### Performance

**Site is slow?**
- Enable FrankenPHP worker mode in `.env`: `FRANKENPHP_CONFIG=worker /app/index.php`
- Increase PHP threads: `FRANKENPHP_NUM_THREADS=16`
- Enable Redis caching (already configured)
- Check container resources: `docker stats`

## Support

- 🐛 **Issues**: Report bugs on [GitHub Issues](https://github.com/eltifi/wordpress-frankenphp/issues)
- 💬 **Discussions**: Join us on [GitHub Discussions](https://github.com/eltifi/wordpress-frankenphp/discussions)

## License

The code in this repository is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for full details.

> **Note**: WordPress itself is licensed under the **GPLv2+**.

