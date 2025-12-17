<?php
/**
 * Plugin Name: Auto-Config & SMTP
 * Description: Automatically configures SMTP and General Settings from environment variables/constants.
 * Version: 1.0.0
 * Author: Antigravity
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. SMTP Configuration
add_action('phpmailer_init', function ($phpmailer) {
    if (defined('WP_SMTP_HOST') && WP_SMTP_HOST) {
        $phpmailer->isSMTP();
        $phpmailer->Host       = WP_SMTP_HOST;
        $phpmailer->Port       = defined('WP_SMTP_PORT') ? (int) WP_SMTP_PORT : 1025;
        
        if (defined('WP_SMTP_USER') && WP_SMTP_USER) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = WP_SMTP_USER;
            $phpmailer->Password = defined('WP_SMTP_PASSWORD') ? WP_SMTP_PASSWORD : '';
        } else {
            $phpmailer->SMTPAuth = false;
        }

        // Auto-detect encryption
        if ($phpmailer->Port === 465) {
            $phpmailer->SMTPSecure = 'ssl';
        } elseif ($phpmailer->Port === 587) {
            $phpmailer->SMTPSecure = 'tls';
        } else {
            $phpmailer->SMTPSecure = ''; // None for 1025/25 usually
        }

        // Force From address if configured
        if (defined('WP_SMTP_FROM') && WP_SMTP_FROM) {
            $phpmailer->From     = WP_SMTP_FROM;
            $phpmailer->FromName = defined('WP_SMTP_FROM_NAME') && WP_SMTP_FROM_NAME ? WP_SMTP_FROM_NAME : 'WordPress';
        }
    }
});

// 2. Email From Address Fix (Override default wordpress@)
add_filter('wp_mail_from', function ($original_email) {
    if (defined('WP_SMTP_FROM') && WP_SMTP_FROM) {
        return WP_SMTP_FROM;
    }
    return $original_email;
});

add_filter('wp_mail_from_name', function ($original_name) {
    if (defined('WP_SMTP_FROM_NAME') && WP_SMTP_FROM_NAME) {
        return WP_SMTP_FROM_NAME;
    }
    return $original_name;
});

// 3. General Settings Enforcement
add_action('init', function () {
    // Only run on admin or if needed needed, but settings should be consistent.
    // We check value before update to avoid database writes on every request.
    
    // Tagline
    if (defined('WP_TAGLINE') && WP_TAGLINE) {
        if (get_option('blogdescription') !== WP_TAGLINE) {
            update_option('blogdescription', WP_TAGLINE);
        }
    }

    // Membership (0 or 1)
    if (defined('WP_MEMBERSHIP')) {
        $should_register = WP_MEMBERSHIP ? 1 : 0;
        if ((int) get_option('users_can_register') !== $should_register) {
            update_option('users_can_register', $should_register);
        }
    }

    // Default Role
    if (defined('WP_DEFAULT_ROLE') && WP_DEFAULT_ROLE) {
        if (get_option('default_role') !== WP_DEFAULT_ROLE) {
            update_option('default_role', WP_DEFAULT_ROLE);
        }
    }

    // Language
    if (defined('WP_LANGUAGE') && WP_LANGUAGE) {
        // WPLANG is empty string for en_US usually, but let's check.
        // If WP_LANGUAGE is en_US, we might want to set WPLANG to '' if that's the WP way,
        // or just set it.
        $lang = (WP_LANGUAGE === 'en_US') ? '' : WP_LANGUAGE;
        if (get_option('WPLANG') !== $lang) {
            update_option('WPLANG', $lang);
        }
    }

    // Timezone
    if (defined('WP_TIMEZONE') && WP_TIMEZONE) {
        if (get_option('timezone_string') !== WP_TIMEZONE) {
            update_option('timezone_string', WP_TIMEZONE);
        }
    }

    // Date Format
    if (defined('WP_DATE_FORMAT') && WP_DATE_FORMAT) {
        if (get_option('date_format') !== WP_DATE_FORMAT) {
            update_option('date_format', WP_DATE_FORMAT);
        }
    }

    // Time Format
    if (defined('WP_TIME_FORMAT') && WP_TIME_FORMAT) {
        if (get_option('time_format') !== WP_TIME_FORMAT) {
            update_option('time_format', WP_TIME_FORMAT);
        }
    }

    // Start of Week
    if (defined('WP_START_OF_WEEK') && WP_START_OF_WEEK !== '') {
        if ((int) get_option('start_of_week') !== (int) WP_START_OF_WEEK) {
            update_option('start_of_week', (int) WP_START_OF_WEEK);
        }
    }
});
