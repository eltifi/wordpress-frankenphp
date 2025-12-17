<?php
// WordPress Site URL Configuration - Auto-detect based on request
// This prevents redirect loops when WordPress URL doesn't match the request
$scheme = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") ? "https" : "http";
$host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "localhost";
define("WP_HOME", $scheme . "://" . $host);
define("WP_SITEURL", $scheme . "://" . $host);

define("FS_METHOD", "direct");
set_time_limit(300);

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * This has been slightly modified (to read environment variables) for use in Docker.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// IMPORTANT: this file needs to stay in-sync with https://github.com/WordPress/WordPress/blob/master/wp-config-sample.php
// (it gets parsed by the upstream wizard in https://github.com/WordPress/WordPress/blob/f27cb65e1ef25d11b535695a660e7282b98eb742/wp-admin/setup-config.php#L356-L392)

// a helper function to lookup "env_FILE", "env", then fallback
if (!function_exists('getenv_docker')) {
	// https://github.com/docker-library/wordpress/issues/588 (WP-CLI will load this file 2x)
	function getenv_docker($env, $default)
	{
		if ($fileEnv = getenv($env . '_FILE')) {
			return rtrim(file_get_contents($fileEnv), "\r\n");
		} else if (($val = getenv($env)) !== false) {
			return $val;
		} else {
			return $default;
		}
	}
}

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', getenv_docker('WORDPRESS_DB_NAME', 'wordpress'));

/** Database username */
define('DB_USER', getenv_docker('WORDPRESS_DB_USER', 'example username'));

/** Database password */
define('DB_PASSWORD', getenv_docker('WORDPRESS_DB_PASSWORD', 'example password'));

/**
 * Docker image fallback values above are sourced from the official WordPress installation wizard:
 * https://github.com/WordPress/WordPress/blob/1356f6537220ffdc32b9dad2a6cdbe2d010b7a88/wp-admin/setup-config.php#L224-L238
 * (However, using "example username" and "example password" in your database is strongly discouraged.  Please use strong, random credentials!)
 */

/** Database hostname */
define('DB_HOST', getenv_docker('WORDPRESS_DB_HOST', 'mysql'));

/** Database charset to use in creating database tables. */
define('DB_CHARSET', getenv_docker('WORDPRESS_DB_CHARSET', 'utf8mb4'));

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', getenv_docker('WORDPRESS_DB_COLLATE', ''));

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', getenv_docker('WORDPRESS_AUTH_KEY', ''));
define('SECURE_AUTH_KEY', getenv_docker('WORDPRESS_SECURE_AUTH_KEY', ''));
define('LOGGED_IN_KEY', getenv_docker('WORDPRESS_LOGGED_IN_KEY', ''));
define('NONCE_KEY', getenv_docker('WORDPRESS_NONCE_KEY', ''));
define('AUTH_SALT', getenv_docker('WORDPRESS_AUTH_SALT', ''));
define('SECURE_AUTH_SALT', getenv_docker('WORDPRESS_SECURE_AUTH_SALT', ''));
define('LOGGED_IN_SALT', getenv_docker('WORDPRESS_LOGGED_IN_SALT', ''));
define('NONCE_SALT', getenv_docker('WORDPRESS_NONCE_SALT', ''));
// (See also https://wordpress.stackexchange.com/a/152905/199287)

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = getenv_docker('WORDPRESS_TABLE_PREFIX', 'wp_');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */

// Unified logging configuration (runtime-controlled via WORDPRESS_DEBUG)
$debug_enabled = !!getenv_docker('WORDPRESS_DEBUG', 'false');
define('WP_DEBUG', $debug_enabled);

// WordPress logging configuration
define('WP_DEBUG_LOG', $debug_enabled);
define('WP_DEBUG_DISPLAY', false); // Never show errors on page - log to file instead
define('WP_DISABLE_FATAL_ERROR_HANDLER', false); // Don't interfere with fatal error handling

// PHP error logging configuration (controlled by WORDPRESS_DEBUG)
if ($debug_enabled) {
	error_reporting(E_ALL);
	ini_set('display_errors', '0'); // Never display errors on screen
	ini_set('log_errors', '1');
	ini_set('error_log', '/var/www/html/wp-content/debug.log');
} else {
	error_reporting(0);
	ini_set('display_errors', '0');
	ini_set('log_errors', '0');
}

/* Add any custom values between this line and the "stop editing" line. */

// WordPress optimization & feature configuration
define('WP_AUTO_UPDATE_CORE', getenv_docker('WORDPRESS_AUTO_UPDATE_CORE', 'minor'));
define('WP_AUTO_UPDATE_PLUGINS', filter_var(getenv_docker('WORDPRESS_AUTO_UPDATE_PLUGINS', 'false'), FILTER_VALIDATE_BOOLEAN));
define('WP_AUTO_UPDATE_THEMES', filter_var(getenv_docker('WORDPRESS_AUTO_UPDATE_THEMES', 'false'), FILTER_VALIDATE_BOOLEAN));
define('CONCATENATE_SCRIPTS', filter_var(getenv_docker('WORDPRESS_CONCATENATE_SCRIPTS', 'false'), FILTER_VALIDATE_BOOLEAN));
define('COMPRESS_SCRIPTS', filter_var(getenv_docker('WORDPRESS_COMPRESS_SCRIPTS', 'false'), FILTER_VALIDATE_BOOLEAN));
define('COMPRESS_CSS', filter_var(getenv_docker('WORDPRESS_COMPRESS_CSS', 'false'), FILTER_VALIDATE_BOOLEAN));
define('WP_MEMORY_LIMIT', getenv_docker('WORDPRESS_MEMORY_LIMIT', '1024M'));
define('WP_MAX_MEMORY_LIMIT', getenv_docker('WORDPRESS_MAX_MEMORY_LIMIT', '1536M'));
define('DISALLOW_FILE_EDIT', filter_var(getenv_docker('WORDPRESS_DISALLOW_FILE_EDIT', 'true'), FILTER_VALIDATE_BOOLEAN));
define('EMPTY_TRASH_DAYS', (int) getenv_docker('WORDPRESS_EMPTY_TRASH_DAYS', '30'));
define('AUTO_SAVE_INTERVAL', (int) getenv_docker('WORDPRESS_AUTO_SAVE_INTERVAL', '300'));

// ============================================================================
// SMTP CONFIGURATION
// ============================================================================
define('WP_SMTP_HOST', getenv_docker('WORDPRESS_SMTP_HOST', 'localhost'));
define('WP_SMTP_PORT', getenv_docker('WORDPRESS_SMTP_PORT', '1025'));
define('WP_SMTP_USER', getenv_docker('WORDPRESS_SMTP_USER', ''));
define('WP_SMTP_PASSWORD', getenv_docker('WORDPRESS_SMTP_PASSWORD', ''));
define('WP_SMTP_FROM', getenv_docker('WORDPRESS_SMTP_FROM', 'wordpress@localhost'));
define('WP_SMTP_FROM_NAME', getenv_docker('WORDPRESS_SMTP_FROM_NAME', 'WordPress System'));

// ============================================================================
// GENERAL SETTINGS CONFIGURATION
// ============================================================================
define('WP_TAGLINE', getenv_docker('WORDPRESS_TAGLINE', ''));
define('WP_MEMBERSHIP', filter_var(getenv_docker('WORDPRESS_MEMBERSHIP', 'false'), FILTER_VALIDATE_BOOLEAN));
define('WP_DEFAULT_ROLE', getenv_docker('WORDPRESS_DEFAULT_ROLE', 'subscriber'));
define('WP_LANGUAGE', getenv_docker('WORDPRESS_LANGUAGE', 'en_US'));
define('WP_TIMEZONE', getenv_docker('WORDPRESS_TIMEZONE', 'UTC'));
define('WP_DATE_FORMAT', getenv_docker('WORDPRESS_DATE_FORMAT', 'F j, Y'));
define('WP_TIME_FORMAT', getenv_docker('WORDPRESS_TIME_FORMAT', 'g:i a'));
define('WP_START_OF_WEEK', (int) getenv_docker('WORDPRESS_START_OF_WEEK', '1'));

// Redis Object Cache Configuration - OPTIMIZED FOR FRANKENPHP WORKER MODE
// Connection pooling is handled by FrankenPHP, so we can use persistent connections
// This configuration MUST be defined before object-cache.php loads
define('WP_REDIS_HOST', getenv('WORDPRESS_REDIS_HOST') ?: 'redis');
define('WP_REDIS_PORT', (int) (getenv('WORDPRESS_REDIS_PORT') ?: 6379));
define('WP_REDIS_DATABASE', (int) (getenv('WORDPRESS_REDIS_DATABASE') ?: 0));
// Lower timeouts for worker mode since connections are persistent
define('WP_REDIS_TIMEOUT', (float) (getenv('WORDPRESS_REDIS_TIMEOUT') ?: 0.5));
define('WP_REDIS_READ_TIMEOUT', (float) (getenv('WORDPRESS_REDIS_READ_TIMEOUT') ?: 0.5));
define('WP_REDIS_PASSWORD', getenv('WORDPRESS_REDIS_PASSWORD') ?: '');
define('WP_REDIS_PREFIX', getenv('WORDPRESS_REDIS_PREFIX') ?: 'wp');

// Enable Redis client persistence for connection pooling (worker mode)
// This reduces Redis connection overhead significantly
define('WP_REDIS_CLIENT', 'phpredis');
define('WP_REDIS_PERSISTENT', true);
define('WP_REDIS_CLUSTER', false);
define('WP_REDIS_SERIALIZER', 'igbinary'); // Faster serialization if available

// FrankenPHP Worker Mode Optimizations
// These constants improve performance in worker mode environments
if (function_exists('frankenphp_handle_request')) {
	// Disable certain WordPress features that don't work well in worker mode
	define('DISABLE_WP_CRON', true); // Use external cron instead

	// Prevent issues with persistent connections
	define('WP_CACHE', true);
	// WP_CACHE_KEY_SALT defaults are now handled below to allow env override
}

// If we're behind a proxy server and using HTTPS, we need to alert WordPress of that fact
// see also https://wordpress.org/support/article/administration-over-ssl/#using-a-reverse-proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
	$_SERVER['HTTPS'] = 'on';
}
// (we include this by default because reverse proxying is extremely common in container environments)

// Custom Content Directory
if ($wpContentUrl = getenv_docker('WORDPRESS_CONTENT_URL', '')) {
	define('WP_CONTENT_URL', $wpContentUrl);
}
if ($wpContentDir = getenv_docker('WORDPRESS_CONTENT_DIR', '')) {
	define('WP_CONTENT_DIR', $wpContentDir);
}

// Database query optimization
define('DB_QUERY_TIMEOUT', (int) getenv_docker('WORDPRESS_DB_QUERY_TIMEOUT', '5'));

// SSL Enforcement
define('FORCE_SSL_ADMIN', filter_var(getenv_docker('WORDPRESS_FORCE_SSL_ADMIN', 'true'), FILTER_VALIDATE_BOOLEAN));
define('FORCE_SSL_LOGIN', filter_var(getenv_docker('WORDPRESS_FORCE_SSL_LOGIN', 'true'), FILTER_VALIDATE_BOOLEAN));

// Cache Key Salt (Override or Default)
define('WP_CACHE_KEY_SALT', getenv_docker('WORDPRESS_CACHE_KEY_SALT', hash('sha256', 'frankenphp-worker-' . gethostname())));

if ($configExtra = getenv_docker('WORDPRESS_CONFIG_EXTRA', '')) {
	eval ($configExtra);
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';