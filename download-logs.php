<?php
/**
 * OSC Bot Blocker - CSV Log Downloader
 * 
 * Standalone script for downloading logs as CSV
 * Bypasses osClass admin template to prevent HTML output
 * 
 * @package OSCBotBlocker
 * @subpackage Admin
 */

// Load osClass
require_once '../../../oc-load.php';

// Security check - admin only
if (!osc_is_admin_user_logged_in()) {
    die('Access denied. Admin login required.');
}

// Define constants
define('OSCBB_PATH', dirname(__FILE__) . '/');
define('OSCBB_TABLE_LOG', DB_TABLE_PREFIX . 't_oscbb_log');

// Get database connection
$db = DBConnectionClass::newInstance();
$conn = $db->getOsclassDb();
$comm = new DBCommandClass($conn);

// Get ALL logs (no limit)
$query = "SELECT * FROM " . OSCBB_TABLE_LOG . " ORDER BY dt_date DESC";
$result = $comm->query($query);

// Set headers for CSV download
$filename = 'osc-bot-blocker-logs-' . date('Y-m-d-His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write CSV header
fputcsv($output, array(
    'ID',
    'Date/Time',
    'IP Address',
    'User Agent',
    'Type',
    'Reason',
    'Form Type',
    'Email',
    'Blocked'
));

// Write data rows
if ($result && $result->numRows() > 0) {
    $logs = $result->result();
    foreach ($logs as $log) {
        fputcsv($output, array(
            $log['pk_i_id'],
            $log['dt_date'],
            $log['s_ip'],
            $log['s_user_agent'],
            $log['s_type'],
            $log['s_reason'],
            $log['s_form_type'],
            $log['s_email'],
            $log['s_blocked'] ? 'Yes' : 'No'
        ));
    }
}

fclose($output);
exit;
