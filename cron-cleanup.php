<?php
/**
 * OSC Bot Blocker - Cron Cleanup Endpoint
 * 
 * This file is called by server cron to run daily log cleanup.
 * Secured with a secret token to prevent unauthorized access.
 * 
 * Usage: Call this file via server cron daily with your secret token:
 * curl -s "https://YOURDOMAIN.com/oc-content/plugins/osc_bot_blocker/cron-cleanup.php?token=YOUR_TOKEN"
 * 
 * @package OSCBotBlocker
 * @version 1.3.0
 */

// Load osClass core
$abs_path = str_replace('\\', '/', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
define('ABS_PATH', $abs_path);
require_once ABS_PATH . 'oc-load.php';

// Security: Verify secret token
$submitted_token = isset($_GET['token']) ? trim($_GET['token']) : '';
$stored_token    = osc_get_preference('oscbb_cron_token', 'osc_bot_blocker');

// Generate a token if one doesn't exist yet
if (empty($stored_token)) {
    $stored_token = bin2hex(random_bytes(32));
    osc_set_preference('oscbb_cron_token', $stored_token, 'osc_bot_blocker', 'STRING');
    error_log('OSC Bot Blocker: Cron token generated - copy it from the database.');
}

// Block access if token doesn't match
if (empty($submitted_token) || $submitted_token !== $stored_token) {
    header('HTTP/1.1 403 Forbidden');
    die('Invalid or missing token.');
}

// Load required plugin classes
require_once dirname(__FILE__) . '/includes/IPValidator.class.php';
require_once dirname(__FILE__) . '/includes/ContentFilter.class.php';
require_once dirname(__FILE__) . '/includes/OSCBotBlocker.class.php';

// Define plugin constants if not already defined
if (!defined('OSCBB_PATH'))          define('OSCBB_PATH',          dirname(__FILE__) . '/');
if (!defined('OSCBB_INCLUDES_PATH')) define('OSCBB_INCLUDES_PATH', OSCBB_PATH . 'includes/');
if (!defined('OSCBB_DATA_PATH'))     define('OSCBB_DATA_PATH',     OSCBB_PATH . 'data/');
if (!defined('OSCBB_VERSION'))       define('OSCBB_VERSION',       '1.3.0');
if (!defined('OSCBB_DEBUG'))         define('OSCBB_DEBUG',         true);
if (!defined('OSCBB_TABLE_LOG'))     define('OSCBB_TABLE_LOG',     DB_TABLE_PREFIX . 't_oscbb_log');
if (!defined('OSCBB_TABLE_STATS'))   define('OSCBB_TABLE_STATS',   DB_TABLE_PREFIX . 't_oscbb_stats');
if (!defined('OSCBB_TABLE_BLACKLIST')) define('OSCBB_TABLE_BLACKLIST', DB_TABLE_PREFIX . 't_oscbb_blacklist');

// Run the cleanup
try {
    $bot_blocker = OSCBotBlocker::getInstance();
    $bot_blocker->dailyCleanup();
    
    $message = 'OSC Bot Blocker: Cron cleanup completed successfully at ' . date('Y-m-d H:i:s');
    echo $message;
    error_log($message);
    
} catch (Exception $e) {
    // Log detailed error for admin debugging
    error_log('OSC Bot Blocker: Cron cleanup error - ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Display generic error message (no sensitive details)
    echo 'OSC Bot Blocker: Cron cleanup failed. Check server error log for details.';
}
