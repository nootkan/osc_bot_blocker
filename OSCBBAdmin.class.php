<?php
/**
 * OSC Bot Blocker - Admin Settings Controller (osClass Enterprise Compatible)
 * 
 * Radio button version - JavaScript redirects
 * 
 * @package OSCBotBlocker
 * @subpackage Admin
 * @author Van Isle Web Solutions
 * @version 1.2.3
 */

if (!defined('ABS_PATH')) {
    header('HTTP/1.1 403 Forbidden');
    die('Direct access is not allowed.');
}

class OSCBBAdmin {
    
    private static $instance;
    
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Singleton constructor
    }
    
    /**
     * Main settings page renderer
     */
    public function renderSettingsPage() {
        // =====================================================================
        // PROCESS FORM SUBMISSIONS FIRST (before any output)
        // =====================================================================
        
		// Handle token generation BEFORE rendering anything
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Params::getParam('oscbb_action') === 'generate_cron_token') {
            $new_token = bin2hex(random_bytes(32));
            osc_set_preference('oscbb_cron_token', $new_token, 'osc_bot_blocker', 'STRING');
            osc_add_flash_ok_message('New cron token generated successfully!');
            $redirect_url = osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osc_bot_blocker/admin.php&tab=cron';
            header('Location: ' . $redirect_url);
            exit();
        }
		
        // Handle cleanup action BEFORE rendering anything
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Params::getParam('oscbb_action') === 'cleanup_logs') {
            // Prevent any output
            ob_start();
            
            $days = (int)Params::getParam('cleanup_days');
            $deleted = $this->cleanupLogs($days);
            
            // Clear buffer
            ob_end_clean();
            
            // Set flash message
            if ($deleted !== false) {
                osc_add_flash_ok_message('Successfully deleted ' . number_format($deleted) . ' log entries!');
            } else {
                osc_add_flash_error_message('Failed to delete logs. Please check your database connection.');
            }
            
            // Redirect to logs tab
            $redirect_url = osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osc_bot_blocker/admin.php&tab=logs';
            header('Location: ' . $redirect_url);
            exit();
        }
        
        // =====================================================================
        // NOW RENDER THE PAGE (after processing is done)
        // =====================================================================
        
        // Get current tab
        $tab = Params::getParam('tab');
        if (empty($tab)) {
            $tab = 'general';
        }
        
        if (function_exists('osc_show_flash_message')) {
            osc_show_flash_message();
        }
        
        echo '<div class="container">';
        echo '<h2>OSC Bot Blocker Settings</h2>';
        
        // Render tabs
        $this->renderTabs($tab);
        
        // Render tab content
        switch ($tab) {
            case 'general':
                $this->renderGeneralSettings();
                break;
            case 'protection':
                $this->renderProtectionSettings();
                break;
            case 'content':
                $this->renderContentSettings();
                break;
            case 'statistics':
                $this->renderStatistics();
                break;
            case 'logs':
                $this->renderLogs();
                break;
            case 'whitelist':
                $this->renderWhitelist();
                break;
            case 'blacklist':
                $this->renderBlacklist();
                break;
            case 'cron':
                $this->renderCronSettings();
                break;
        }
        
        echo '</div>';
    }
    
    /**
     * Render tab navigation
     */
    private function renderTabs($current) {
        $base_url = osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osc_bot_blocker/admin.php';
        $tabs = array(
            'general' => 'General',
            'protection' => 'Protection',
            'content' => 'Content Filtering',
            'statistics' => 'Statistics',
            'logs' => 'Logs',
            'whitelist' => 'Whitelist',
            'blacklist' => 'Blacklist',
            'cron' => 'Cron Setup'
        );
        
        echo '<p style="margin: 20px 0; border-bottom: 2px solid #ddd; padding-bottom: 10px;">';
        foreach ($tabs as $key => $label) {
            $active = ($key === $current) ? ' style="font-weight:bold; color:#0073aa;"' : '';
            echo '<a href="' . $base_url . '&tab=' . $key . '"' . $active . '>' . $label . '</a> &nbsp; | &nbsp; ';
        }
        echo '</p>';
    }
    
    /**
     * GENERAL SETTINGS TAB
     */
    private function renderGeneralSettings() {
        // Handle POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Params::getParam('oscbb_tab') === 'general') {
            $this->saveGeneralSettings();
            osc_add_flash_ok_message('General settings saved successfully.');
            $url = osc_admin_render_plugin_url('osc_bot_blocker/admin.php') . '&tab=general';
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
        
        // Get settings
        $enabled = osc_get_preference('oscbb_enabled', 'osc_bot_blocker');
        $level = osc_get_preference('oscbb_protection_level', 'osc_bot_blocker');
        $logging = osc_get_preference('oscbb_logging_enabled', 'osc_bot_blocker');
        $retention = osc_get_preference('oscbb_log_retention_days', 'osc_bot_blocker');
        
        if ($enabled === null) $enabled = 1;
        if ($level === null) $level = 'medium';
        if ($logging === null) $logging = 1;
        if ($retention === null) $retention = 30;
        ?>
        
        <form method="post">
            <input type="hidden" name="oscbb_tab" value="general">
            
            <h3>Plugin Status</h3>
            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="oscbb_enabled" value="1" <?php echo $enabled ? 'checked' : ''; ?>>
                Enable OSC Bot Blocker Protection
            </label>
            <p style="margin-left:25px; color:#666; font-size:13px;">
                Master switch to enable/disable all bot protection features.
            </p>
            
            <h3 style="margin-top:25px;">Protection Level</h3>
            <div style="margin:10px 0;">
                <label style="display:block; margin:8px 0;">
                    <input type="radio" name="oscbb_protection_level" value="low" <?php echo ($level === 'low') ? 'checked' : ''; ?>>
                    Low - Basic protection
                </label>
                <label style="display:block; margin:8px 0;">
                    <input type="radio" name="oscbb_protection_level" value="medium" <?php echo ($level === 'medium') ? 'checked' : ''; ?>>
                    Medium - Balanced (Recommended)
                </label>
                <label style="display:block; margin:8px 0;">
                    <input type="radio" name="oscbb_protection_level" value="high" <?php echo ($level === 'high') ? 'checked' : ''; ?>>
                    High - Strict blocking
                </label>
            </div>
            <p style="margin-left:0; color:#666; font-size:13px;">
                <strong>Low:</strong> Minimal blocking, fewer false positives.<br>
                <strong>Medium:</strong> Balanced approach (recommended).<br>
                <strong>High:</strong> Strict blocking, may have false positives.
            </p>
            
            <h3 style="margin-top:25px;">Logging Settings</h3>
            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="oscbb_logging_enabled" value="1" <?php echo $logging ? 'checked' : ''; ?>>
                Enable database logging
            </label>
            <p style="margin-left:25px; color:#666; font-size:13px;">
                Records all blocked submissions to database for analysis.
            </p>
            
            <label style="display:block; margin:15px 0 5px 0;">
                Log Retention Period:
                <input type="number" name="oscbb_log_retention_days" value="<?php echo (int)$retention; ?>" min="1" max="365" style="width:80px;">
                days
            </label>
            <p style="margin-left:0; color:#666; font-size:13px;">
                How many days to keep logs (1-365). Older logs are automatically deleted.
            </p>
            
            <p style="margin-top:25px;">
                <button type="submit" class="btn btn-submit">Save General Settings</button>
            </p>
        </form>
        <?php
    }
    
    private function saveGeneralSettings() {
        $enabled = isset($_POST['oscbb_enabled']) ? 1 : 0;
        osc_set_preference('oscbb_enabled', $enabled, 'osc_bot_blocker', 'BOOLEAN');
        
        $level = Params::getParam('oscbb_protection_level');
        if (in_array($level, array('low', 'medium', 'high'))) {
            osc_set_preference('oscbb_protection_level', $level, 'osc_bot_blocker', 'STRING');
        }
        
        $logging = isset($_POST['oscbb_logging_enabled']) ? 1 : 0;
        osc_set_preference('oscbb_logging_enabled', $logging, 'osc_bot_blocker', 'BOOLEAN');
        
        $retention = (int)Params::getParam('oscbb_log_retention_days');
        if ($retention < 1) $retention = 1;
        if ($retention > 365) $retention = 365;
        osc_set_preference('oscbb_log_retention_days', $retention, 'osc_bot_blocker', 'INTEGER');
    }
    
    /**
     * PROTECTION SETTINGS TAB
     */
    private function renderProtectionSettings() {
        // Handle POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Params::getParam('oscbb_tab') === 'protection') {
            $this->saveProtectionSettings();
            osc_add_flash_ok_message('Protection settings saved successfully.');
            $url = osc_admin_render_plugin_url('osc_bot_blocker/admin.php') . '&tab=protection';
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
        
        // Get settings
        $js_enabled = osc_get_preference('oscbb_js_enabled', 'osc_bot_blocker');
        $min_time = osc_get_preference('oscbb_min_submit_time', 'osc_bot_blocker');
        $max_time = osc_get_preference('oscbb_max_submit_time', 'osc_bot_blocker');
        $honeypot = osc_get_preference('oscbb_honeypot_enabled', 'osc_bot_blocker');
        $ua_check = osc_get_preference('oscbb_ua_validation_enabled', 'osc_bot_blocker');
        $referer = osc_get_preference('oscbb_referer_check_enabled', 'osc_bot_blocker');
        $cookie = osc_get_preference('oscbb_cookie_test_enabled', 'osc_bot_blocker');
        $rate_limit = osc_get_preference('oscbb_rate_limit_enabled', 'osc_bot_blocker');
        $rate_count = osc_get_preference('oscbb_rate_limit_count', 'osc_bot_blocker');
        
        // Defaults
        if ($js_enabled === null) $js_enabled = 1;
        if ($min_time === null) $min_time = 3;
        if ($max_time === null) $max_time = 3600;
        if ($honeypot === null) $honeypot = 1;
        if ($ua_check === null) $ua_check = 1;
        if ($referer === null) $referer = 1;
        if ($cookie === null) $cookie = 1;
        if ($rate_limit === null) $rate_limit = 1;
        if ($rate_count === null) $rate_count = 5;
        ?>
        
        <form method="post">
            <input type="hidden" name="oscbb_tab" value="protection">
            
            <h3>JavaScript Protection</h3>
            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="oscbb_js_enabled" value="1" <?php echo $js_enabled ? 'checked' : ''; ?>>
                Enable JavaScript token validation
            </label>
            <p style="margin-left:25px; color:#666; font-size:13px;">
                Requires JavaScript to generate security tokens. Recommended!
            </p>
            
            <label style="display:block; margin:15px 0 5px 0;">
                Minimum submit time:
                <input type="number" name="oscbb_min_submit_time" value="<?php echo (int)$min_time; ?>" min="0" max="60" style="width:60px;">
                seconds
            </label>
            <p style="margin-left:0; color:#666; font-size:13px;">
                Users must wait at least this many seconds before submitting (0-60). Default: 3
            </p>
            
            <label style="display:block; margin:15px 0 5px 0;">
                Maximum submit time:
                <input type="number" name="oscbb_max_submit_time" value="<?php echo (int)$max_time; ?>" min="60" max="86400" style="width:80px;">
                seconds
            </label>
            <p style="margin-left:0; color:#666; font-size:13px;">
                Token expires after this many seconds (60-86400). Default: 3600 (1 hour)
            </p>
            
            <h3 style="margin-top:25px;">Honeypot Protection</h3>
            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="oscbb_honeypot_enabled" value="1" <?php echo $honeypot ? 'checked' : ''; ?>>
                Enable honeypot invisible fields
            </label>
            <p style="margin-left:25px; color:#666; font-size:13px;">
                Adds invisible fields that bots fill in but humans don't see. Very effective!
            </p>
            
            <h3 style="margin-top:25px;">User-Agent Validation</h3>
            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="oscbb_ua_validation_enabled" value="1" <?php echo $ua_check ? 'checked' : ''; ?>>
                Enable User-Agent blacklist checking (100+ bot patterns)
            </label>
            <p style="margin-left:25px; color:#666; font-size:13px;">
                Blocks known bots based on their User-Agent string.
            </p>
            
            <h3 style="margin-top:25px;">HTTP Referer & Cookie Validation</h3>
            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="oscbb_referer_check_enabled" value="1" <?php echo $referer ? 'checked' : ''; ?>>
                Enable HTTP referer checking
            </label>
            <p style="margin-left:25px; color:#666; font-size:13px;">
                Verifies submissions come from your website domain.
            </p>
            
            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="oscbb_cookie_test_enabled" value="1" <?php echo $cookie ? 'checked' : ''; ?>>
                Enable cookie testing
            </label>
            <p style="margin-left:25px; color:#666; font-size:13px;">
                Requires browsers to support cookies.
            </p>
            
            <h3 style="margin-top:25px;">Rate Limiting</h3>
            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="oscbb_rate_limit_enabled" value="1" <?php echo $rate_limit ? 'checked' : ''; ?>>
                Enable rate limiting
            </label>
            <p style="margin-left:25px; color:#666; font-size:13px;">
                Limits how many submissions each IP can make per hour.
            </p>
            
            <label style="display:block; margin:15px 0 5px 0;">
                Maximum submissions per hour:
                <input type="number" name="oscbb_rate_limit_count" value="<?php echo (int)$rate_count; ?>" min="1" max="100" style="width:60px;">
            </label>
            <p style="margin-left:0; color:#666; font-size:13px;">
                How many submissions allowed per IP per hour (1-100). Default: 5
            </p>
            
            <p style="margin-top:25px;">
                <button type="submit" class="btn btn-submit">Save Protection Settings</button>
            </p>
        </form>
        <?php
    }
    
    private function saveProtectionSettings() {
        // JavaScript
        $js_enabled = isset($_POST['oscbb_js_enabled']) ? 1 : 0;
        osc_set_preference('oscbb_js_enabled', $js_enabled, 'osc_bot_blocker', 'BOOLEAN');
        
        $min_time = (int)Params::getParam('oscbb_min_submit_time');
        if ($min_time < 0) $min_time = 0;
        if ($min_time > 60) $min_time = 60;
        osc_set_preference('oscbb_min_submit_time', $min_time, 'osc_bot_blocker', 'INTEGER');
        
        $max_time = (int)Params::getParam('oscbb_max_submit_time');
        if ($max_time < 60) $max_time = 60;
        if ($max_time > 86400) $max_time = 86400;
        osc_set_preference('oscbb_max_submit_time', $max_time, 'osc_bot_blocker', 'INTEGER');
        
        // Honeypot
        $honeypot = isset($_POST['oscbb_honeypot_enabled']) ? 1 : 0;
        osc_set_preference('oscbb_honeypot_enabled', $honeypot, 'osc_bot_blocker', 'BOOLEAN');
        
        // User-Agent
        $ua_check = isset($_POST['oscbb_ua_validation_enabled']) ? 1 : 0;
        osc_set_preference('oscbb_ua_validation_enabled', $ua_check, 'osc_bot_blocker', 'BOOLEAN');
        
        // Referer
        $referer = isset($_POST['oscbb_referer_check_enabled']) ? 1 : 0;
        osc_set_preference('oscbb_referer_check_enabled', $referer, 'osc_bot_blocker', 'BOOLEAN');
        
        // Cookie
        $cookie = isset($_POST['oscbb_cookie_test_enabled']) ? 1 : 0;
        osc_set_preference('oscbb_cookie_test_enabled', $cookie, 'osc_bot_blocker', 'BOOLEAN');
        
        // Rate limiting
        $rate_limit = isset($_POST['oscbb_rate_limit_enabled']) ? 1 : 0;
        osc_set_preference('oscbb_rate_limit_enabled', $rate_limit, 'osc_bot_blocker', 'BOOLEAN');
        
        $rate_count = (int)Params::getParam('oscbb_rate_limit_count');
        if ($rate_count < 1) $rate_count = 1;
        if ($rate_count > 100) $rate_count = 100;
        osc_set_preference('oscbb_rate_limit_count', $rate_count, 'osc_bot_blocker', 'INTEGER');
    }
    
    /**
     * CONTENT FILTERING TAB
     */
    private function renderContentSettings() {
        // Handle POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Params::getParam('oscbb_tab') === 'content') {
            $this->saveContentSettings();
            osc_add_flash_ok_message('Content filtering settings saved successfully.');
            $url = osc_admin_render_plugin_url('osc_bot_blocker/admin.php') . '&tab=content';
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
        
        // Get settings
        $url_limit = osc_get_preference('oscbb_url_limit', 'osc_bot_blocker');
        $keywords = osc_get_preference('oscbb_keyword_filter_enabled', 'osc_bot_blocker');
        $disposable = osc_get_preference('oscbb_block_disposable_emails', 'osc_bot_blocker');
        $free_email = osc_get_preference('oscbb_block_free_emails', 'osc_bot_blocker');
        
        if ($url_limit === null) $url_limit = 3;
        if ($keywords === null) $keywords = 1;
        if ($disposable === null) $disposable = 1;
        if ($free_email === null) $free_email = 0;
        ?>
        
        <form method="post">
            <input type="hidden" name="oscbb_tab" value="content">
            
            <h3>URL Filtering</h3>
            <label style="display:block; margin:15px 0 5px 0;">
                Maximum URLs allowed:
                <input type="number" name="oscbb_url_limit" value="<?php echo (int)$url_limit; ?>" min="0" max="50" style="width:60px;">
            </label>
            <p style="margin-left:0; color:#666; font-size:13px;">
                Maximum URLs allowed in content (title, description, message). <br>
                Recommended: 3. Set to 0 for no URLs, 50 for unlimited.
            </p>
            
            <h3 style="margin-top:25px;">Keyword Filtering</h3>
            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="oscbb_keyword_filter_enabled" value="1" <?php echo $keywords ? 'checked' : ''; ?>>
                Enable spam keyword filtering (100+ keywords)
            </label>
            <p style="margin-left:25px; color:#666; font-size:13px;">
                Blocks common spam keywords (viagra, casino, cheap pills, etc.). Recommended!
            </p>
            
            <h3 style="margin-top:25px;">Email Validation</h3>
            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="oscbb_block_disposable_emails" value="1" <?php echo $disposable ? 'checked' : ''; ?>>
                Block disposable/temporary email services (200+ domains)
            </label>
            <p style="margin-left:25px; color:#666; font-size:13px;">
                Blocks 10minutemail, guerrillamail, mailinator, temp-mail, yopmail, etc. Recommended!
            </p>
            
            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="oscbb_block_free_emails" value="1" <?php echo $free_email ? 'checked' : ''; ?>>
                Block free email providers (Gmail, Yahoo, Hotmail, etc.)
            </label>
            <p style="margin-left:25px; color:#d63638; font-size:13px;">
                ⚠️ NOT RECOMMENDED! Most legitimate users use free email services.
            </p>
            
            <p style="margin-top:25px;">
                <button type="submit" class="btn btn-submit">Save Content Settings</button>
            </p>
        </form>
        <?php
    }
    
    private function saveContentSettings() {
        $url_limit = (int)Params::getParam('oscbb_url_limit');
        if ($url_limit < 0) $url_limit = 0;
        if ($url_limit > 50) $url_limit = 50;
        osc_set_preference('oscbb_url_limit', $url_limit, 'osc_bot_blocker', 'INTEGER');
        
        $keywords = isset($_POST['oscbb_keyword_filter_enabled']) ? 1 : 0;
        osc_set_preference('oscbb_keyword_filter_enabled', $keywords, 'osc_bot_blocker', 'BOOLEAN');
        
        $disposable = isset($_POST['oscbb_block_disposable_emails']) ? 1 : 0;
        osc_set_preference('oscbb_block_disposable_emails', $disposable, 'osc_bot_blocker', 'BOOLEAN');
        
        $free_email = isset($_POST['oscbb_block_free_emails']) ? 1 : 0;
        osc_set_preference('oscbb_block_free_emails', $free_email, 'osc_bot_blocker', 'BOOLEAN');
    }
    
    /**
     * STATISTICS TAB
     */
    private function renderStatistics() {
        $db = DBConnectionClass::newInstance();
        
        // Get block counts
        $today = date('Y-m-d');
        $week_ago = date('Y-m-d', strtotime('-7 days'));
        $month_ago = date('Y-m-d', strtotime('-30 days'));
        
        $conn = $db->getOsclassDb();
        $comm = new DBCommandClass($conn);
        
        $query = "SELECT 
                    COUNT(CASE WHEN DATE(dt_date) = '$today' THEN 1 END) as today,
                    COUNT(CASE WHEN DATE(dt_date) >= '$week_ago' THEN 1 END) as week,
                    COUNT(CASE WHEN DATE(dt_date) >= '$month_ago' THEN 1 END) as month,
                    COUNT(*) as total
                  FROM " . OSCBB_TABLE_LOG . " 
                  WHERE s_blocked = 1";
        
        $db_result = $comm->query($query);
        $result = false;
        if ($db_result) {
            $result = $db_result->row();
        }
        
        $blocks_today = ($result && isset($result['today'])) ? (int)$result['today'] : 0;
        $blocks_week = ($result && isset($result['week'])) ? (int)$result['week'] : 0;
        $blocks_month = ($result && isset($result['month'])) ? (int)$result['month'] : 0;
        $blocks_total = ($result && isset($result['total'])) ? (int)$result['total'] : 0;
        
        // Get top block types
        $query = "SELECT s_type, COUNT(*) as count 
                  FROM " . OSCBB_TABLE_LOG . " 
                  WHERE s_blocked = 1 AND DATE(dt_date) >= '$month_ago'
                  GROUP BY s_type 
                  ORDER BY count DESC LIMIT 5";
        
        $db_result = $comm->query($query);
        $block_types = array();
        if ($db_result && $db_result->numRows() > 0) {
            $block_types = $db_result->result();
        }
        ?>
        
        <h3>Block Statistics</h3>
        
        <table style="width:100%; max-width:600px; margin:20px 0; border-collapse:collapse;">
            <tr style="background:#f5f5f5;">
                <th style="padding:12px; text-align:left; border:1px solid #ddd;">Period</th>
                <th style="padding:12px; text-align:right; border:1px solid #ddd;">Blocks</th>
            </tr>
            <tr>
                <td style="padding:10px; border:1px solid #ddd;">Today</td>
                <td style="padding:10px; text-align:right; border:1px solid #ddd; font-weight:bold;"><?php echo number_format($blocks_today); ?></td>
            </tr>
            <tr>
                <td style="padding:10px; border:1px solid #ddd;">Last 7 Days</td>
                <td style="padding:10px; text-align:right; border:1px solid #ddd; font-weight:bold;"><?php echo number_format($blocks_week); ?></td>
            </tr>
            <tr>
                <td style="padding:10px; border:1px solid #ddd;">Last 30 Days</td>
                <td style="padding:10px; text-align:right; border:1px solid #ddd; font-weight:bold;"><?php echo number_format($blocks_month); ?></td>
            </tr>
            <tr style="background:#f5f5f5;">
                <td style="padding:10px; border:1px solid #ddd;"><strong>Total All Time</strong></td>
                <td style="padding:10px; text-align:right; border:1px solid #ddd; font-weight:bold;"><?php echo number_format($blocks_total); ?></td>
            </tr>
        </table>
        
        <?php if (!empty($block_types)): ?>
        <h3 style="margin-top:30px;">Top Block Types (Last 30 Days)</h3>
        <table style="width:100%; max-width:600px; margin:20px 0; border-collapse:collapse;">
            <tr style="background:#f5f5f5;">
                <th style="padding:12px; text-align:left; border:1px solid #ddd;">Type</th>
                <th style="padding:12px; text-align:right; border:1px solid #ddd;">Count</th>
            </tr>
            <?php foreach ($block_types as $type): ?>
            <tr>
                <td style="padding:10px; border:1px solid #ddd; text-transform:capitalize;"><?php echo htmlspecialchars($type['s_type']); ?></td>
                <td style="padding:10px; text-align:right; border:1px solid #ddd; font-weight:bold;"><?php echo number_format($type['count']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
        
        <?php if ($blocks_total === 0): ?>
        <p style="margin:30px 0; padding:20px; background:#e7f7e7; border-left:4px solid #00a32a;">
            <strong>No blocks recorded yet!</strong><br>
            Your site is spam-free! 🎉 The protection is active and ready to block spam when it arrives.
        </p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * LOGS TAB
     */
    private function renderLogs() {
        $db = DBConnectionClass::newInstance();
        $conn = $db->getOsclassDb();
        $comm = new DBCommandClass($conn);
        
        // Pagination settings
        $logs_per_page = 10;
        $current_page = max(1, (int)Params::getParam('log_page'));
        $offset = ($current_page - 1) * $logs_per_page;
        
        // Get total log count
        $count_query = "SELECT COUNT(*) as total FROM " . OSCBB_TABLE_LOG;
        $count_result = $comm->query($count_query);
        $total_logs = 0;
        if ($count_result) {
            $count_row = $count_result->row();
            $total_logs = $count_row ? (int)$count_row['total'] : 0;
        }
        
        // Calculate pagination
        $total_pages = max(1, ceil($total_logs / $logs_per_page));
        $current_page = min($current_page, $total_pages); // Don't exceed max pages
        $start_item = ($current_page - 1) * $logs_per_page + 1;
        $end_item = min($current_page * $logs_per_page, $total_logs);
        
        // Get logs for current page
        $query = "SELECT * FROM " . OSCBB_TABLE_LOG . " 
                  WHERE s_blocked = 1 
                  ORDER BY dt_date DESC 
                  LIMIT " . $logs_per_page . " OFFSET " . $offset;
        
        $result = $comm->query($query);
        $logs = array();
        if ($result && $result->numRows() > 0) {
            $logs = $result->result();
        }
        
        // Base URL for pagination
        $base_url = osc_admin_render_plugin_url('osc_bot_blocker/admin.php') . '&tab=logs';
        ?>
        
        <!-- Cleanup Form -->
        <div style="background:#fff9e6; border-left:4px solid #f0ad4e; padding:15px; margin-bottom:20px;">
            <h4 style="margin:0 0 10px 0;">🧹 Manual Log Cleanup</h4>
            <p style="margin:0 0 15px 0;">
                <strong>Total logs in database:</strong> <?php echo number_format($total_logs); ?> entries<br>
                <small>Clean up old logs to keep your database lean. This action cannot be undone!</small>
            </p>
            
            <form method="POST" id="cleanup_form" style="display:flex; gap:10px; align-items:flex-end;">
                <input type="hidden" name="oscbb_action" value="cleanup_logs">
                
                <div>
                    <label for="cleanup_days" style="display:block; margin-bottom:5px; font-weight:bold;">Delete logs older than:</label>
                    <select name="cleanup_days" id="cleanup_days" style="padding:8px; border:1px solid #ddd; border-radius:3px;">
                        <option value="7">7 days</option>
                        <option value="30">30 days</option>
                        <option value="90">90 days</option>
                        <option value="180">180 days</option>
                        <option value="365">1 year</option>
                        <option value="0">All logs (WARNING!)</option>
                    </select>
                </div>
                
                <button type="button" onclick="if(confirm('Are you sure you want to delete these logs? This action cannot be undone!')) { document.getElementById('cleanup_form').submit(); }" style="padding:8px 16px; background:#f0ad4e; color:#fff; border:none; border-radius:3px; cursor:pointer; font-weight:bold;">
                  🗑️ Delete Old Logs
                </button>
            </form>
        </div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h3 style="margin:0 0 5px 0;">Block Log</h3>
                <p style="margin:0; color:#666; font-size:13px;">
                    Showing <?php echo number_format($start_item); ?>-<?php echo number_format($end_item); ?> of <?php echo number_format($total_logs); ?> logs
                </p>
            </div>
            <a href="<?php echo osc_base_url() . 'oc-content/plugins/osc_bot_blocker/download-logs.php'; ?>" 
               class="btn btn-primary" 
               style="padding:8px 16px; background:#0073aa; color:#fff; text-decoration:none; border-radius:3px; display:inline-block;">
                📥 Download All Logs (CSV)
            </a>
        </div>
        
        <?php if ($total_logs > 0): ?>
        
        <!-- Top Pagination -->
        <?php $this->renderPagination($current_page, $total_pages, $base_url); ?>
        
        <div style="overflow-x:auto;">
            <table style="width:100%; margin:20px 0; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#f5f5f5;">
                        <th style="padding:10px; text-align:left; border:1px solid #ddd;">Date/Time</th>
                        <th style="padding:10px; text-align:left; border:1px solid #ddd;">IP Address</th>
                        <th style="padding:10px; text-align:left; border:1px solid #ddd;">Type</th>
                        <th style="padding:10px; text-align:left; border:1px solid #ddd;">Reason</th>
                        <th style="padding:10px; text-align:left; border:1px solid #ddd;">Form</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td style="padding:8px; border:1px solid #ddd; white-space:nowrap;">
                                <?php echo date('Y-m-d H:i', strtotime($log['dt_date'])); ?>
                            </td>
                            <td style="padding:8px; border:1px solid #ddd;">
                                <code><?php echo htmlspecialchars($log['s_ip']); ?></code>
                            </td>
                            <td style="padding:8px; border:1px solid #ddd; text-transform:capitalize;">
                                <?php echo htmlspecialchars($log['s_type']); ?>
                            </td>
                            <td style="padding:8px; border:1px solid #ddd; max-width:300px;">
                                <?php echo htmlspecialchars(substr($log['s_reason'], 0, 80)); ?>
                            </td>
                            <td style="padding:8px; border:1px solid #ddd; text-transform:capitalize;">
                                <?php echo htmlspecialchars($log['s_form_type']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding:20px; text-align:center; border:1px solid #ddd;">
                                No logs found for this page.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Bottom Pagination -->
        <?php $this->renderPagination($current_page, $total_pages, $base_url); ?>
        
        <?php else: ?>
        <p style="margin:30px 0; padding:20px; background:#e7f7e7; border-left:4px solid #00a32a;">
            <strong>No blocks recorded yet!</strong><br>
            Your site is spam-free! When spam is blocked, it will appear here.
        </p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Download all logs as CSV file
     */
    public function downloadLogsCSV() {
        $db = DBConnectionClass::newInstance();
        $conn = $db->getOsclassDb();
        $comm = new DBCommandClass($conn);
        
        // Get ALL logs (no limit)
        $query = "SELECT * FROM " . OSCBB_TABLE_LOG . " 
                  ORDER BY dt_date DESC";
        
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
    }
    
    /**
     * Clean up old logs from database
     * 
     * @param int $days Number of days to keep (0 = delete all)
     * @return int|false Number of deleted rows, or false on error
     */
    private function cleanupLogs($days) {
        $db = DBConnectionClass::newInstance();
        $conn = $db->getOsclassDb();
        $comm = new DBCommandClass($conn);
        
        if ($days === 0) {
            // Delete ALL logs
            $query = "DELETE FROM " . OSCBB_TABLE_LOG;
        } else {
            // Delete logs older than X days
            $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            $query = "DELETE FROM " . OSCBB_TABLE_LOG . " WHERE dt_date < '" . $conn->real_escape_string($cutoff_date) . "'";
        }
        
        // Execute deletion
        $result = $comm->query($query);
        
        if ($result) {
            // Get number of affected rows
            $affected = $conn->affected_rows;
            return $affected;
        }
        
        return false;
    }
    
    /**
     * Render pagination controls
     * 
     * @param int $current_page Current page number
     * @param int $total_pages Total number of pages
     * @param string $base_url Base URL for pagination links
     */
    private function renderPagination($current_page, $total_pages, $base_url) {
        if ($total_pages <= 1) {
            return; // No pagination needed
        }
        ?>
        <div style="display:flex; justify-content:center; align-items:center; gap:5px; padding:15px 0;">
            
            <!-- Previous Button -->
            <?php if ($current_page > 1): ?>
                <a href="<?php echo $base_url . '&log_page=' . ($current_page - 1); ?>" 
                   style="padding:8px 12px; background:#f0f0f0; border:1px solid #ddd; border-radius:3px; text-decoration:none; color:#333;">
                    ← Previous
                </a>
            <?php else: ?>
                <span style="padding:8px 12px; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:3px; color:#999;">
                    ← Previous
                </span>
            <?php endif; ?>
            
            <!-- Page Numbers -->
            <?php
            // Smart pagination - show first, last, current, and nearby pages
            $range = 2; // Show 2 pages on each side of current
            
            for ($i = 1; $i <= $total_pages; $i++) {
                // Always show first page, last page, current page, and nearby pages
                if ($i == 1 || $i == $total_pages || ($i >= $current_page - $range && $i <= $current_page + $range)) {
                    if ($i == $current_page) {
                        // Current page - highlighted
                        echo '<span style="padding:8px 12px; background:#0073aa; border:1px solid #0073aa; border-radius:3px; color:#fff; font-weight:bold;">' . $i . '</span>';
                    } else {
                        // Other pages - clickable
                        echo '<a href="' . $base_url . '&log_page=' . $i . '" style="padding:8px 12px; background:#f0f0f0; border:1px solid #ddd; border-radius:3px; text-decoration:none; color:#333;">' . $i . '</a>';
                    }
                } elseif ($i == $current_page - $range - 1 || $i == $current_page + $range + 1) {
                    // Show ellipsis for gaps
                    echo '<span style="padding:8px 12px; color:#999;">...</span>';
                }
            }
            ?>
            
            <!-- Next Button -->
            <?php if ($current_page < $total_pages): ?>
                <a href="<?php echo $base_url . '&log_page=' . ($current_page + 1); ?>" 
                   style="padding:8px 12px; background:#f0f0f0; border:1px solid #ddd; border-radius:3px; text-decoration:none; color:#333;">
                    Next →
                </a>
            <?php else: ?>
                <span style="padding:8px 12px; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:3px; color:#999;">
                    Next →
                </span>
            <?php endif; ?>
            
        </div>
        <?php
    }
    
    /**
     * WHITELIST TAB
     */
    private function renderWhitelist() {
        $db = DBConnectionClass::newInstance();
        
        // Handle add
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Params::getParam('action') === 'add') {
            $type = Params::getParam('whitelist_type');
            $value = trim(Params::getParam('whitelist_value'));
            
            if (!empty($value) && in_array($type, array('whitelist_ip', 'whitelist_email'))) {
                $valid = false;
                if ($type === 'whitelist_ip' && IPValidator::isValid($value)) {
                    $valid = true;
                } elseif ($type === 'whitelist_email' && osc_validate_email($value)) {
                    $valid = true;
                }
                
                if ($valid) {
                    $conn = $db->getOsclassDb();
                    $comm = new DBCommandClass($conn);
                    $escaped_type = $conn->real_escape_string($type);
                    $escaped_value = $conn->real_escape_string($value);
                    
                    $query = "INSERT INTO " . OSCBB_TABLE_BLACKLIST . " (s_type, s_value, dt_added, s_reason, b_active) 
                             VALUES ('" . $escaped_type . "', '" . $escaped_value . "', NOW(), 'Manual whitelist', 1)";
                    $comm->query($query);
                    osc_add_flash_ok_message('Entry added to whitelist!');
                } else {
                    osc_add_flash_error_message('Invalid format.');
                }
            }
            $url = osc_admin_render_plugin_url('osc_bot_blocker/admin.php') . '&tab=whitelist';
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
        
        // Handle delete
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Params::getParam('action') === 'delete') {
            $id = (int)Params::getParam('entry_id');
            if ($id > 0) {
                $conn = $db->getOsclassDb();
                $comm = new DBCommandClass($conn);
                $query = "DELETE FROM " . OSCBB_TABLE_BLACKLIST . " WHERE pk_i_id = " . $id;
                $comm->query($query);
                osc_add_flash_ok_message('Entry removed from whitelist!');
            }
            $url = osc_admin_render_plugin_url('osc_bot_blocker/admin.php') . '&tab=whitelist';
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
        
        // Get whitelist
        $query = "SELECT * FROM " . OSCBB_TABLE_BLACKLIST . " 
                  WHERE s_type IN ('whitelist_ip', 'whitelist_email') 
                  AND b_active = 1 
                  ORDER BY dt_added DESC";
        
        $conn = $db->getOsclassDb();
        $comm = new DBCommandClass($conn);
        $db_result = $comm->query($query);
        $whitelist = array();
        if ($db_result && $db_result->numRows() > 0) {
            $whitelist = $db_result->result();
        }
        ?>
        
        <div style="background:#d7f0ff; border-left:4px solid #0073aa; padding:15px; margin:20px 0;">
            <p style="margin:0;"><strong>Whitelist Information:</strong></p>
            <p style="margin:5px 0 0 0;">
                IP addresses and email addresses on the whitelist will bypass ALL protection checks.
                Admin users are automatically whitelisted when logged in.
            </p>
        </div>
        
        <h3>Add to Whitelist</h3>
        <form method="post" style="margin:20px 0;">
            <input type="hidden" name="action" value="add">
            <div style="margin:10px 0;">
                <label style="display:inline-block; margin-right:20px;">
                    <input type="radio" name="whitelist_type" value="whitelist_ip" checked>
                    IP Address
                </label>
                <label style="display:inline-block;">
                    <input type="radio" name="whitelist_type" value="whitelist_email">
                    Email Address
                </label>
            </div>
            <input type="text" name="whitelist_value" placeholder="e.g., 192.168.1.1 or user@example.com" style="width:300px; margin-right:10px;" required>
            <button type="submit" class="btn btn-submit">Add to Whitelist</button>
        </form>
        
        <h3 style="margin-top:30px;">Current Whitelist</h3>
        <?php if (!empty($whitelist)): ?>
        <table style="width:100%; margin:20px 0; border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:10px; text-align:left; border:1px solid #ddd;">Type</th>
                    <th style="padding:10px; text-align:left; border:1px solid #ddd;">Value</th>
                    <th style="padding:10px; text-align:left; border:1px solid #ddd;">Date Added</th>
                    <th style="padding:10px; text-align:left; border:1px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($whitelist as $entry): ?>
                <tr>
                    <td style="padding:10px; border:1px solid #ddd; text-transform:capitalize;">
                        <?php echo $entry['s_type'] === 'whitelist_ip' ? 'IP Address' : 'Email'; ?>
                    </td>
                    <td style="padding:10px; border:1px solid #ddd;">
                        <code><?php echo htmlspecialchars($entry['s_value']); ?></code>
                    </td>
                    <td style="padding:10px; border:1px solid #ddd;">
                        <?php echo date('Y-m-d H:i', strtotime($entry['dt_added'])); ?>
                    </td>
                    <td style="padding:10px; border:1px solid #ddd;">
                        <form method="post" style="display:inline;" onsubmit="return confirm('Remove this entry?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="entry_id" value="<?php echo $entry['pk_i_id']; ?>">
                            <button type="submit" class="btn btn-mini">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color:#666;">No whitelist entries. Admin users are automatically whitelisted when logged in.</p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * BLACKLIST TAB
     */
    private function renderBlacklist() {
        $db = DBConnectionClass::newInstance();
        
        // Handle add
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Params::getParam('action') === 'add') {
            $type = Params::getParam('blacklist_type');
            $value = trim(Params::getParam('blacklist_value'));
            $reason = trim(Params::getParam('blacklist_reason'));
            
            if (!empty($value) && in_array($type, array('blacklist_ip', 'blacklist_email', 'blacklist_keyword'))) {
                $valid = false;
                if ($type === 'blacklist_ip' && IPValidator::isValid($value)) {
                    $valid = true;
                } elseif ($type === 'blacklist_email' && osc_validate_email($value)) {
                    $valid = true;
                } elseif ($type === 'blacklist_keyword') {
                    $valid = true;
                }
                
                if ($valid) {
                    if (empty($reason)) $reason = 'Custom blacklist';
                    
                    $conn = $db->getOsclassDb();
                    $comm = new DBCommandClass($conn);
                    $escaped_type = $conn->real_escape_string($type);
                    $escaped_value = $conn->real_escape_string($value);
                    $escaped_reason = $conn->real_escape_string($reason);
                    
                    $query = "INSERT INTO " . OSCBB_TABLE_BLACKLIST . " (s_type, s_value, dt_added, s_reason, b_active) 
                             VALUES ('" . $escaped_type . "', '" . $escaped_value . "', NOW(), '" . $escaped_reason . "', 1)";
                    $comm->query($query);
                    osc_add_flash_ok_message('Entry added to blacklist!');
                } else {
                    osc_add_flash_error_message('Invalid format.');
                }
            }
            $url = osc_admin_render_plugin_url('osc_bot_blocker/admin.php') . '&tab=blacklist';
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
        
        // Handle delete
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Params::getParam('action') === 'delete') {
            $id = (int)Params::getParam('entry_id');
            if ($id > 0) {
                $conn = $db->getOsclassDb();
                $comm = new DBCommandClass($conn);
                $query = "DELETE FROM " . OSCBB_TABLE_BLACKLIST . " WHERE pk_i_id = " . $id;
                $comm->query($query);
                osc_add_flash_ok_message('Entry deleted!');
            }
            $url = osc_admin_render_plugin_url('osc_bot_blocker/admin.php') . '&tab=blacklist';
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
        
        // Handle toggle
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Params::getParam('action') === 'toggle') {
            $id = (int)Params::getParam('entry_id');
            if ($id > 0) {
                $conn = $db->getOsclassDb();
                $comm = new DBCommandClass($conn);
                $query = "UPDATE " . OSCBB_TABLE_BLACKLIST . " SET b_active = 1 - b_active WHERE pk_i_id = " . $id;
                $comm->query($query);
                osc_add_flash_ok_message('Entry status updated!');
            }
            $url = osc_admin_render_plugin_url('osc_bot_blocker/admin.php') . '&tab=blacklist';
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
        
        // Get blacklist
        $query = "SELECT * FROM " . OSCBB_TABLE_BLACKLIST . " 
                  WHERE s_type IN ('blacklist_ip', 'blacklist_email', 'blacklist_keyword') 
                  ORDER BY b_active DESC, dt_added DESC";
        
        $conn = $db->getOsclassDb();
        $comm = new DBCommandClass($conn);
        $db_result = $comm->query($query);
        $blacklist = array();
        if ($db_result && $db_result->numRows() > 0) {
            $blacklist = $db_result->result();
        }
        ?>
        
        <div style="background:#ffe8e8; border-left:4px solid #d63638; padding:15px; margin:20px 0;">
            <p style="margin:0;"><strong>Custom Blacklist Information:</strong></p>
            <p style="margin:5px 0 0 0;">
                Add custom IP addresses, email addresses, or keywords to block. These are in addition to the built-in blacklists (100+ bots, 200+ disposable emails, 100+ spam keywords).
            </p>
        </div>
        
        <h3>Add to Blacklist</h3>
        <form method="post" style="margin:20px 0;">
            <input type="hidden" name="action" value="add">
            <div style="margin:10px 0;">
                <label style="display:inline-block; margin-right:20px;">
                    <input type="radio" name="blacklist_type" value="blacklist_ip" checked>
                    IP Address
                </label>
                <label style="display:inline-block; margin-right:20px;">
                    <input type="radio" name="blacklist_type" value="blacklist_email">
                    Email Address
                </label>
                <label style="display:inline-block;">
                    <input type="radio" name="blacklist_type" value="blacklist_keyword">
                    Keyword
                </label>
            </div>
            <input type="text" name="blacklist_value" placeholder="e.g., 192.168.1.1, spam@example.com, or badword" style="width:250px; margin-right:10px;" required>
            <input type="text" name="blacklist_reason" placeholder="Reason (optional)" style="width:200px; margin-right:10px;">
            <button type="submit" class="btn btn-submit">Add to Blacklist</button>
        </form>
        
        <h3 style="margin-top:30px;">Custom Blacklist Entries</h3>
        <?php if (!empty($blacklist)): ?>
        <table style="width:100%; margin:20px 0; border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:10px; text-align:left; border:1px solid #ddd;">Status</th>
                    <th style="padding:10px; text-align:left; border:1px solid #ddd;">Type</th>
                    <th style="padding:10px; text-align:left; border:1px solid #ddd;">Value</th>
                    <th style="padding:10px; text-align:left; border:1px solid #ddd;">Reason</th>
                    <th style="padding:10px; text-align:left; border:1px solid #ddd;">Date Added</th>
                    <th style="padding:10px; text-align:left; border:1px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blacklist as $entry): ?>
                <tr<?php echo !$entry['b_active'] ? ' style="opacity:0.5;"' : ''; ?>>
                    <td style="padding:10px; border:1px solid #ddd;">
                        <?php if ($entry['b_active']): ?>
                            <span style="color:#00a32a; font-weight:bold;">● Active</span>
                        <?php else: ?>
                            <span style="color:#999;">○ Disabled</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px; border:1px solid #ddd; text-transform:capitalize;">
                        <?php echo htmlspecialchars(str_replace('blacklist_', '', $entry['s_type'])); ?>
                    </td>
                    <td style="padding:10px; border:1px solid #ddd;">
                        <code><?php echo htmlspecialchars($entry['s_value']); ?></code>
                    </td>
                    <td style="padding:10px; border:1px solid #ddd;">
                        <?php echo htmlspecialchars($entry['s_reason'] ? $entry['s_reason'] : '-'); ?>
                    </td>
                    <td style="padding:10px; border:1px solid #ddd;">
                        <?php echo date('Y-m-d H:i', strtotime($entry['dt_added'])); ?>
                    </td>
                    <td style="padding:10px; border:1px solid #ddd; white-space:nowrap;">
                        <form method="post" style="display:inline; margin-right:5px;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="entry_id" value="<?php echo $entry['pk_i_id']; ?>">
                            <button type="submit" class="btn btn-mini">
                                <?php echo $entry['b_active'] ? 'Disable' : 'Enable'; ?>
                            </button>
                        </form>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this entry?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="entry_id" value="<?php echo $entry['pk_i_id']; ?>">
                            <button type="submit" class="btn btn-mini">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color:#666;">No custom blacklist entries. The built-in blacklists are still active (100+ bots, 200+ disposable emails, 100+ spam keywords).</p>
        <?php endif; ?>
        <?php
    }

    /**
     * CRON SETUP TAB
     */
    private function renderCronSettings() {
        $token     = osc_get_preference('oscbb_cron_token', 'osc_bot_blocker');
        $site_url  = osc_base_url();
        $cron_url  = rtrim($site_url, '/') . '/oc-content/plugins/osc_bot_blocker/cron-cleanup.php?token=' . ($token ? $token : 'NOT_GENERATED_YET');
        $retention = osc_get_preference('oscbb_log_retention_days', 'osc_bot_blocker');
        if (!$retention) $retention = 30;
        $general_url = osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=osc_bot_blocker/admin.php&tab=general';
        ?>

        <h3>Automatic Log Cleanup - Cron Setup</h3>
        <p style="color:#666; margin-bottom:20px;">
            Set up a server cron job to automatically clean your logs daily based on your retention setting.
        </p>

        <div style="background:#f9f9f9; border:1px solid #ddd; padding:20px; margin-bottom:20px; border-radius:4px;">
            <h4 style="margin:0 0 10px 0;">Step 1: Generate Your Secret Token</h4>
            <p style="margin:0 0 15px 0; color:#666;">
                A secret token secures your cron endpoint so only authorized requests can trigger cleanup.
            </p>

            <?php if ($token): ?>
                <p><strong>Your current token:</strong></p>
                <code style="display:block; padding:10px; background:#fff; border:1px solid #ddd; word-break:break-all; margin-bottom:15px; font-size:13px;">
                    <?php echo htmlspecialchars($token); ?>
                </code>
                <form method="POST" id="token_form">
                    <input type="hidden" name="oscbb_action" value="generate_cron_token">
                    <button type="button" onclick="if(confirm('Generate a new token? Your existing cron job will stop working until you update it with the new token.')) { document.getElementById(\'token_form\').submit(); }" style="padding:8px 16px; background:#dc3232; color:#fff; border:none; border-radius:3px; cursor:pointer;">
                        Regenerate Token
                    </button>
                </form>
            <?php else: ?>
                <p style="color:#dc3232;"><strong>No token generated yet.</strong></p>
                <form method="POST" id="token_form">
                    <input type="hidden" name="oscbb_action" value="generate_cron_token">
                    <button type="submit" style="padding:8px 16px; background:#0073aa; color:#fff; border:none; border-radius:3px; cursor:pointer;">
                        Generate Token
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if ($token): ?>

        <div style="background:#f9f9f9; border:1px solid #ddd; padding:20px; margin-bottom:20px; border-radius:4px;">
            <h4 style="margin:0 0 10px 0;">Step 2: Test Your Cron URL</h4>
            <p style="margin:0 0 10px 0; color:#666;">
                Visit this URL in your browser to verify the cleanup script works correctly:
            </p>
            <code style="display:block; padding:10px; background:#fff; border:1px solid #ddd; word-break:break-all; margin-bottom:15px; font-size:13px;">
                <?php echo htmlspecialchars($cron_url); ?>
            </code>
            <p style="color:#666; font-size:13px;">
                You should see: <strong>"OSC Bot Blocker: Cron cleanup completed successfully at [date/time]"</strong>
            </p>
        </div>

        <div style="background:#f9f9f9; border:1px solid #ddd; padding:20px; margin-bottom:20px; border-radius:4px;">
            <h4 style="margin:0 0 10px 0;">Step 3: Add Cron Job to Your Server</h4>
            <p style="margin:0 0 10px 0; color:#666;">
                In your hosting control panel (cPanel), go to <strong>Cron Jobs</strong> and add this command to run once daily (recommended: 3:00 AM):
            </p>
            <code style="display:block; padding:10px; background:#fff; border:1px solid #ddd; word-break:break-all; margin-bottom:15px; font-size:13px;">
                curl -s "<?php echo htmlspecialchars($cron_url); ?>" >/dev/null 2>&1
            </code>
            <p style="color:#666; font-size:13px; margin-bottom:5px;"><strong>Recommended schedule (once daily at 3:00 AM):</strong></p>
            <code style="display:block; padding:10px; background:#fff; border:1px solid #ddd; font-size:13px;">
                0 3 * * *
            </code>
        </div>

        <div style="background:#e7f3ff; border-left:4px solid #0073aa; padding:15px; border-radius:4px;">
            <p style="margin:0;">
                <strong>Note:</strong> The cron job will delete logs based on your retention setting
                (currently <strong><?php echo (int)$retention; ?> days</strong>).
                Change this in the <a href="<?php echo htmlspecialchars($general_url); ?>">General Settings</a> tab.
            </p>
        </div>

        <?php else: ?>

        <div style="background:#fff3cd; border-left:4px solid #ffc107; padding:15px; border-radius:4px;">
            <p style="margin:0;"><strong>Please generate your token first (Step 1) before proceeding.</strong></p>
        </div>

        <?php endif; ?>

        <?php
    }
}
