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
            'blacklist' => 'Blacklist'
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
        
        $query = "SELECT 
                    COUNT(CASE WHEN DATE(dt_date) = '$today' THEN 1 END) as today,
                    COUNT(CASE WHEN DATE(dt_date) >= '$week_ago' THEN 1 END) as week,
                    COUNT(CASE WHEN DATE(dt_date) >= '$month_ago' THEN 1 END) as month,
                    COUNT(*) as total
                  FROM " . OSCBB_TABLE_LOG . " 
                  WHERE s_blocked = 1";
        
        $db_result = $db->getOsclassDb()->query($query);
        $result = false;
        if ($db_result) {
            $result = $db_result->fetch(PDO::FETCH_ASSOC);
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
        
        $db_result = $db->getOsclassDb()->query($query);
        $block_types = array();
        if ($db_result) {
            while ($row = $db_result->fetch(PDO::FETCH_ASSOC)) {
                $block_types[] = $row;
            }
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
                <td style="padding:10px; border:1px solid #ddd; text-transform:capitalize;"><?php echo esc_html($type['s_type']); ?></td>
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
        
        // Get recent logs
        $query = "SELECT * FROM " . OSCBB_TABLE_LOG . " 
                  WHERE s_blocked = 1 
                  ORDER BY dt_date DESC 
                  LIMIT 50";
        
        $db_result = $db->getOsclassDb()->query($query);
        $logs = array();
        if ($db_result) {
            while ($row = $db_result->fetch(PDO::FETCH_ASSOC)) {
                $logs[] = $row;
            }
        }
        ?>
        
        <h3>Recent Blocks (Last 50)</h3>
        
        <?php if (!empty($logs)): ?>
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
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td style="padding:8px; border:1px solid #ddd; white-space:nowrap;">
                            <?php echo date('Y-m-d H:i', strtotime($log['dt_date'])); ?>
                        </td>
                        <td style="padding:8px; border:1px solid #ddd;">
                            <code><?php echo esc_html($log['s_ip']); ?></code>
                        </td>
                        <td style="padding:8px; border:1px solid #ddd; text-transform:capitalize;">
                            <?php echo esc_html($log['s_type']); ?>
                        </td>
                        <td style="padding:8px; border:1px solid #ddd; max-width:300px;">
                            <?php echo esc_html(substr($log['s_reason'], 0, 80)); ?>
                        </td>
                        <td style="padding:8px; border:1px solid #ddd; text-transform:capitalize;">
                            <?php echo esc_html($log['s_form_type']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p style="margin:30px 0; padding:20px; background:#e7f7e7; border-left:4px solid #00a32a;">
            <strong>No blocks recorded yet!</strong><br>
            Your site is spam-free! When spam is blocked, it will appear here.
        </p>
        <?php endif; ?>
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
                    $query = "INSERT INTO " . OSCBB_TABLE_BLACKLIST . " (s_type, s_value, dt_added, s_reason, b_active) 
                             VALUES ('" . esc_sql($type) . "', '" . esc_sql($value) . "', NOW(), 'Manual whitelist', 1)";
                    $db->getOsclassDb()->exec($query);
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
                $query = "DELETE FROM " . OSCBB_TABLE_BLACKLIST . " WHERE pk_i_id = " . $id;
                $db->getOsclassDb()->exec($query);
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
        
        $db_result = $db->getOsclassDb()->query($query);
        $whitelist = array();
        if ($db_result) {
            while ($row = $db_result->fetch(PDO::FETCH_ASSOC)) {
                $whitelist[] = $row;
            }
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
                        <code><?php echo esc_html($entry['s_value']); ?></code>
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
                    
                    $query = "INSERT INTO " . OSCBB_TABLE_BLACKLIST . " (s_type, s_value, dt_added, s_reason, b_active) 
                             VALUES ('" . esc_sql($type) . "', '" . esc_sql($value) . "', NOW(), '" . esc_sql($reason) . "', 1)";
                    $db->getOsclassDb()->exec($query);
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
                $query = "DELETE FROM " . OSCBB_TABLE_BLACKLIST . " WHERE pk_i_id = " . $id;
                $db->getOsclassDb()->exec($query);
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
                $query = "UPDATE " . OSCBB_TABLE_BLACKLIST . " SET b_active = 1 - b_active WHERE pk_i_id = " . $id;
                $db->getOsclassDb()->exec($query);
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
        
        $db_result = $db->getOsclassDb()->query($query);
        $blacklist = array();
        if ($db_result) {
            while ($row = $db_result->fetch(PDO::FETCH_ASSOC)) {
                $blacklist[] = $row;
            }
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
                        <?php echo esc_html(str_replace('blacklist_', '', $entry['s_type'])); ?>
                    </td>
                    <td style="padding:10px; border:1px solid #ddd;">
                        <code><?php echo esc_html($entry['s_value']); ?></code>
                    </td>
                    <td style="padding:10px; border:1px solid #ddd;">
                        <?php echo esc_html($entry['s_reason'] ? $entry['s_reason'] : '-'); ?>
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
}
