<?php
/**
 * Plugin Name: S3 Uploads Loader
 * Description: Autoloads the S3 Uploads plugin via Composer.
 */

if ( getenv( 'ENABLE_S3_UPLOADS' ) === 'true' ) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
