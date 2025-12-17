<?php
/**
 * Plugin Name: Security Hardening
 * Description: Removes sensitive headers and endpoints exposure.
 */

// Remove REST API Link header from HTTP responses
remove_action('template_redirect', 'rest_output_link_header', 11, 0);

// Remove Shortlink header
remove_action('template_redirect', 'wp_shortlink_header', 11, 0);

// Remove Generator meta tag (just in case HTML is parsed)
remove_action('wp_head', 'wp_generator');
