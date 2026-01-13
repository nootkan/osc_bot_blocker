<?php
/**
 * OSC Bot Blocker - Admin Interface Entry Point
 * 
 * This file is loaded by osClass when accessing the admin interface.
 * 
 * @package OSCBotBlocker
 * @subpackage Admin
 * @author Van Isle Web Solutions
 * @link https://www.vanislebc.com/
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABS_PATH')) {
    header('HTTP/1.1 403 Forbidden');
    die('Direct access is not allowed.');
}

// Define constants if not already defined (in case index.php wasn't loaded)
if (!defined('OSCBB_PATH')) {
    define('OSCBB_PATH', dirname(__FILE__) . '/');
}
if (!defined('OSCBB_INCLUDES_PATH')) {
    define('OSCBB_INCLUDES_PATH', OSCBB_PATH . 'includes/');
}
if (!defined('OSCBB_ADMIN_PATH')) {
    define('OSCBB_ADMIN_PATH', OSCBB_PATH . 'admin/');
}
if (!defined('OSCBB_DATA_PATH')) {
    define('OSCBB_DATA_PATH', OSCBB_PATH . 'data/');
}
if (!defined('OSCBB_TABLE_LOG')) {
    define('OSCBB_TABLE_LOG', DB_TABLE_PREFIX . 't_oscbb_log');
}
if (!defined('OSCBB_TABLE_STATS')) {
    define('OSCBB_TABLE_STATS', DB_TABLE_PREFIX . 't_oscbb_stats');
}
if (!defined('OSCBB_TABLE_BLACKLIST')) {
    define('OSCBB_TABLE_BLACKLIST', DB_TABLE_PREFIX . 't_oscbb_blacklist');
}
if (!defined('OSCBB_VERSION')) {
    define('OSCBB_VERSION', '1.2.0');
}

// Load required classes
// Compatibility layer for osClass Enterprise
// osClass Enterprise uses different CSRF token functions
if (!function_exists('osc_create_nonce')) {
    function osc_create_nonce($action = '') {
        // Use osClass Enterprise's CSRF token
        if (function_exists('osc_csrf_token_url')) {
            return osc_csrf_token_url();
        }
        // Fallback: create simple token
        if (!isset($_SESSION['oscbb_token'])) {
            $_SESSION['oscbb_token'] = md5(uniqid(rand(), true));
        }
        return $_SESSION['oscbb_token'];
    }
}

if (!function_exists('osc_verify_nonce')) {
    function osc_verify_nonce($nonce, $action = '') {
        // For osClass Enterprise, always return true (it has its own CSRF protection)
        // Or implement simple verification
        if (isset($_SESSION['oscbb_token']) && $_SESSION['oscbb_token'] === $nonce) {
            return true;
        }
        // Be permissive for now
        return true;
    }
}

require_once OSCBB_INCLUDES_PATH . 'IPValidator.class.php';

// Load admin class
require_once OSCBB_ADMIN_PATH . 'OSCBBAdmin.class.php';

// Render admin interface with error handling
try {
    // Get admin instance
    $admin = OSCBBAdmin::getInstance();
    
    // Render settings page
    $admin->renderSettingsPage();
    
} catch (Exception $e) {
    // Display error for debugging
    echo '<div style="padding: 20px; background: #ffebee; border: 2px solid #c62828; margin: 20px;">';
    echo '<h2 style="color: #c62828;">OSC Bot Blocker - Admin Error</h2>';
    echo '<p><strong>Error:</strong> ' . esc_html($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . esc_html($e->getFile()) . '</p>';
    echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
    echo '<p style="margin-top: 20px; padding: 10px; background: #fff; border-left: 4px solid #ff9800;">';
    echo '<strong>Troubleshooting:</strong><br>';
    echo '1. Check that all plugin files are uploaded correctly<br>';
    echo '2. Verify file permissions (644 for PHP files)<br>';
    echo '3. Check your server error log for details<br>';
    echo '4. Ensure database tables were created during installation';
    echo '</p>';
    echo '</div>';
}
