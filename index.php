<?php
/**
 * OSC Bot Blocker Anti-Spam Plugin for osClass
 * 
 * Plugin Name: OSC Bot Blocker
 * Plugin URI: https://www.yoursite.com/osc-bot-blocker
 * Description: Advanced anti-spam and bot protection for osClass. Blocks spam in items, contact forms, user registration, and comments without CAPTCHAs.
 * Version: 1.3.0
 * Author: Van Isle Web Solutions
 * Author URI: https://www.vanislebc.com
 * License: GPL2+
 * 
 * Requires osClass: Enterprise 3.10.4 or Ospoint 8.2.1
 * 
 * Copyright (c) 2026 Your Name
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

// Prevent direct access to this file
if (!defined('ABS_PATH')) {
    header('HTTP/1.1 403 Forbidden');
    die('Direct access is not allowed.');
}

/**
 * PLUGIN CONSTANTS - Define all paths and URLs
 */

// Plugin version
if (!defined('OSCBB_VERSION')) {
    define('OSCBB_VERSION', '1.3.0');
}

// Plugin path constants
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

if (!defined('OSCBB_LANG_PATH')) {
    define('OSCBB_LANG_PATH', OSCBB_PATH . 'languages/');
}

// Plugin URL constants
if (!defined('OSCBB_URL')) {
    define('OSCBB_URL', osc_plugin_url(basename(dirname(__FILE__))));
}

if (!defined('OSCBB_JS_URL')) {
    define('OSCBB_JS_URL', OSCBB_URL . 'js/');
}

if (!defined('OSCBB_CSS_URL')) {
    define('OSCBB_CSS_URL', OSCBB_URL . 'css/');
}

// Database table names
if (!defined('OSCBB_TABLE_LOG')) {
    define('OSCBB_TABLE_LOG', DB_TABLE_PREFIX . 't_oscbb_log');
}

if (!defined('OSCBB_TABLE_STATS')) {
    define('OSCBB_TABLE_STATS', DB_TABLE_PREFIX . 't_oscbb_stats');
}

if (!defined('OSCBB_TABLE_BLACKLIST')) {
    define('OSCBB_TABLE_BLACKLIST', DB_TABLE_PREFIX . 't_oscbb_blacklist');
}

// Debug mode (set to false in production)
if (!defined('OSCBB_DEBUG')) {
    define('OSCBB_DEBUG', false);
}

/**
 * PLUGIN INITIALIZATION - Load Required Classes
 * This loads the classes but does NOT register hooks (hooks are registered below)
 */

// Load IPValidator class file
if (file_exists(OSCBB_INCLUDES_PATH . 'IPValidator.class.php')) {
    require_once OSCBB_INCLUDES_PATH . 'IPValidator.class.php';
}

// Load ContentFilter class file
if (file_exists(OSCBB_INCLUDES_PATH . 'ContentFilter.class.php')) {
    require_once OSCBB_INCLUDES_PATH . 'ContentFilter.class.php';
}

// Load main class file
if (file_exists(OSCBB_INCLUDES_PATH . 'OSCBotBlocker.class.php')) {
    require_once OSCBB_INCLUDES_PATH . 'OSCBotBlocker.class.php';
}

/**
 * Get plugin instance (helper function)
 * @return OSCBotBlocker|null
 */
function oscbb_get_instance() {
    if (class_exists('OSCBotBlocker')) {
        return OSCBotBlocker::getInstance();
    }
    return null;
}

/**
 * Hook: Inject form protection into registration form
 */
function oscbb_hook_user_register_form() {
    $instance = oscbb_get_instance();
    if ($instance && $instance->isEnabled()) {
        $instance->injectFormProtection();
    }
}

/**
 * Hook: Inject form protection into login form
 */
function oscbb_hook_user_login_form() {
    $instance = oscbb_get_instance();
    if ($instance && $instance->isEnabled()) {
        $instance->injectFormProtection();
    }
}

/**
 * Hook: Inject form protection into contact forms
 */
function oscbb_hook_contact_form() {
    $instance = oscbb_get_instance();
    if ($instance && $instance->isEnabled()) {
        $instance->injectFormProtection();
    }
}

/**
 * Hook: Inject form protection into admin contact form
 */
function oscbb_hook_admin_contact_form() {
    $instance = oscbb_get_instance();
    if ($instance && $instance->isEnabled()) {
        $instance->injectFormProtection();
    }
}

/**
 * Hook: Inject form protection into item contact form
 */
function oscbb_hook_item_contact_form() {
    $instance = oscbb_get_instance();
    if ($instance && $instance->isEnabled()) {
        $instance->injectFormProtection();
    }
}

/**
 * Hook: Inject global protection (JavaScript) in header
 */
function oscbb_hook_header() {
    $instance = oscbb_get_instance();
    if ($instance && $instance->isEnabled() && !OC_ADMIN) {
        $instance->injectGlobalProtection();
    }
}

/**
 * Hook: Validate item submission
 */
function oscbb_hook_validate_item() {
    $instance = oscbb_get_instance();
    if ($instance && $instance->isEnabled()) {
        $instance->validateItemSubmission();
    }
}

/**
 * Hook: Validate contact form submission
 */
function oscbb_hook_validate_contact() {
    $instance = oscbb_get_instance();
    if ($instance && $instance->isEnabled()) {
        $instance->validateContactForm();
    }
}

/**
 * Hook: Validate registration submission
 */
function oscbb_hook_validate_registration() {
    $instance = oscbb_get_instance();
    if ($instance && $instance->isEnabled()) {
        $instance->validateRegistration();
    }
}

/**
 * Hook: Validate comment submission
 */
function oscbb_hook_validate_comment() {
    $instance = oscbb_get_instance();
    if ($instance && $instance->isEnabled()) {
        $instance->validateComment();
    }
}

/**
 * Hook: Daily cleanup cron job
 */
function oscbb_hook_daily_cleanup() {
    $instance = oscbb_get_instance();
    if ($instance) {
        $instance->dailyCleanup();
    }
}

/**
 * Hook: Admin menu
 */
function oscbb_hook_admin_menu() {
    if (!OC_ADMIN) {
        return;
    }
    
    $instance = oscbb_get_instance();
    if ($instance) {
        $instance->addAdminMenu();
    }
}

/**
 * Hook: Dashboard widget
 */
function oscbb_hook_dashboard_widget() {
    if (!OC_ADMIN) {
        return;
    }
    
    $instance = oscbb_get_instance();
    if ($instance && $instance->isEnabled()) {
        $instance->renderDashboardWidget();
    }
}

/**
 * PLUGIN INSTALLATION FUNCTION
 * Called when plugin is activated/installed
 */
function oscbb_install() {
    // Create database tables
    oscbb_create_tables();
    
    // Migrate existing tables if upgrading
    oscbb_migrate_tables();
    
    // Set default preferences
    oscbb_set_default_preferences();
    
    // Log installation
    if (function_exists('osc_add_flash_ok_message')) {
        osc_add_flash_ok_message(__('OSC Bot Blocker plugin installed successfully!', 'osc_bot_blocker'));
    }
}

/**
 * PLUGIN UNINSTALLATION FUNCTION
 * Called when plugin is deactivated/uninstalled
 */
function oscbb_uninstall() {
    // Check if admin wants to keep data
    $keep_data = osc_get_preference('oscbb_keep_data_on_uninstall', 'osc_bot_blocker');
    
    if ($keep_data != '1') {
        // Drop database tables
        oscbb_drop_tables();
        
        // Remove all preferences
        oscbb_remove_preferences();
    }
    
    // Log uninstallation
    if (function_exists('osc_add_flash_ok_message')) {
        osc_add_flash_ok_message(__('OSC Bot Blocker plugin uninstalled.', 'osc_bot_blocker'));
    }
}

/**
 * CREATE DATABASE TABLES
 */
function oscbb_create_tables() {
    $conn = DBConnectionClass::newInstance();
    $db = $conn->getOsclassDb();
    $comm = new DBCommandClass($db);
    
    // Table 1: Enhanced Log table with analytics fields
    $sql_log = "CREATE TABLE IF NOT EXISTS " . OSCBB_TABLE_LOG . " (
        pk_i_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        dt_date DATETIME NOT NULL,
        s_ip VARCHAR(45) NOT NULL,
        s_user_agent VARCHAR(500),
        s_type ENUM('bot', 'spam', 'honeypot', 'javascript', 'rate_limit', 'content', 'other') NOT NULL DEFAULT 'other',
        s_reason TEXT,
        s_form_type ENUM('item', 'contact', 'register', 'comment', 'other') NOT NULL DEFAULT 'other',
        s_email VARCHAR(255),
        s_blocked TINYINT(1) DEFAULT 1,
        
        -- Enhanced logging fields for analytics (v1.3.0)
        s_content_hash VARCHAR(64),
        s_content_length INT,
        i_url_count INT DEFAULT 0,
        i_keyword_matches INT DEFAULT 0,
        s_matched_keywords TEXT,
        i_submit_time INT,
        i_field_count INT DEFAULT 0,
        s_browser_language VARCHAR(10),
        s_email_domain VARCHAR(255),
        i_content_languages INT DEFAULT 0,
        b_has_links TINYINT(1) DEFAULT 0,
        b_all_caps TINYINT(1) DEFAULT 0,
        i_hour_of_day TINYINT,
        i_day_of_week TINYINT,
        
        PRIMARY KEY (pk_i_id),
        KEY idx_date (dt_date),
        KEY idx_ip (s_ip),
        KEY idx_type (s_type),
        KEY idx_form_type (s_form_type),
        KEY idx_content_hash (s_content_hash),
        KEY idx_email_domain (s_email_domain),
        KEY idx_hour_day (i_hour_of_day, i_day_of_week)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    // Table 2: Statistics table
    $sql_stats = "CREATE TABLE IF NOT EXISTS " . OSCBB_TABLE_STATS . " (
        pk_i_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        dt_date DATE NOT NULL,
        i_total_blocks INT(10) UNSIGNED DEFAULT 0,
        i_bot_blocks INT(10) UNSIGNED DEFAULT 0,
        i_spam_blocks INT(10) UNSIGNED DEFAULT 0,
        i_honeypot_blocks INT(10) UNSIGNED DEFAULT 0,
        i_javascript_blocks INT(10) UNSIGNED DEFAULT 0,
        i_rate_limit_blocks INT(10) UNSIGNED DEFAULT 0,
        i_content_blocks INT(10) UNSIGNED DEFAULT 0,
        PRIMARY KEY (pk_i_id),
        UNIQUE KEY idx_date (dt_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    // Table 3: Blacklist table
    $sql_blacklist = "CREATE TABLE IF NOT EXISTS " . OSCBB_TABLE_BLACKLIST . " (
        pk_i_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        s_type ENUM('ip', 'email', 'domain', 'keyword', 'whitelist_ip', 'whitelist_email', 'blacklist_ip', 'blacklist_email', 'blacklist_keyword') NOT NULL,
        s_value VARCHAR(255) NOT NULL,
        dt_added DATETIME NOT NULL,
        s_reason VARCHAR(500),
        b_active TINYINT(1) DEFAULT 1,
        PRIMARY KEY (pk_i_id),
        UNIQUE KEY idx_value (s_type, s_value),
        KEY idx_active (b_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    // Execute table creation
    try {
        $comm->query($sql_log);
        $comm->query($sql_stats);
        $comm->query($sql_blacklist);
        return true;
    } catch (Exception $e) {
        if (OSCBB_DEBUG) {
            error_log('OSC Bot Blocker: Error creating tables - ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * MIGRATE EXISTING TABLES
 * Adds new columns to existing tables when upgrading
 */
function oscbb_migrate_tables() {
    $conn = DBConnectionClass::newInstance();
    $db = $conn->getOsclassDb();
    $comm = new DBCommandClass($db);
    
    try {
        // Check if enhanced logging columns exist
        $result = $comm->query("SHOW COLUMNS FROM " . OSCBB_TABLE_LOG . " LIKE 's_content_hash'");
        
        if (!$result || $result->numRows() == 0) {
            // Columns don't exist, add them
            $migrations = array(
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN s_content_hash VARCHAR(64) AFTER s_blocked",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN s_content_length INT AFTER s_content_hash",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN i_url_count INT DEFAULT 0 AFTER s_content_length",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN i_keyword_matches INT DEFAULT 0 AFTER i_url_count",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN s_matched_keywords TEXT AFTER i_keyword_matches",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN i_submit_time INT AFTER s_matched_keywords",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN i_field_count INT DEFAULT 0 AFTER i_submit_time",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN s_browser_language VARCHAR(10) AFTER i_field_count",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN s_email_domain VARCHAR(255) AFTER s_browser_language",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN i_content_languages INT DEFAULT 0 AFTER s_email_domain",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN b_has_links TINYINT(1) DEFAULT 0 AFTER i_content_languages",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN b_all_caps TINYINT(1) DEFAULT 0 AFTER b_has_links",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN i_hour_of_day TINYINT AFTER b_all_caps",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD COLUMN i_day_of_week TINYINT AFTER i_hour_of_day",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD KEY idx_content_hash (s_content_hash)",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD KEY idx_email_domain (s_email_domain)",
                "ALTER TABLE " . OSCBB_TABLE_LOG . " ADD KEY idx_hour_day (i_hour_of_day, i_day_of_week)"
            );
            
            foreach ($migrations as $sql) {
                try {
                    $comm->query($sql);
                } catch (Exception $e) {
                    // Column might already exist or other error - log it but continue
                    if (OSCBB_DEBUG) {
                        error_log('OSC Bot Blocker Migration: ' . $e->getMessage());
                    }
                }
            }
            
            if (OSCBB_DEBUG) {
                error_log('OSC Bot Blocker: Enhanced logging columns added successfully');
            }
        }
        
        return true;
    } catch (Exception $e) {
        if (OSCBB_DEBUG) {
            error_log('OSC Bot Blocker: Error during migration - ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * DROP DATABASE TABLES
 */
function oscbb_drop_tables() {
    $conn = DBConnectionClass::newInstance();
    $db = $conn->getOsclassDb();
    $comm = new DBCommandClass($db);
    
    try {
        $comm->query("DROP TABLE IF EXISTS " . OSCBB_TABLE_LOG);
        $comm->query("DROP TABLE IF EXISTS " . OSCBB_TABLE_STATS);
        $comm->query("DROP TABLE IF EXISTS " . OSCBB_TABLE_BLACKLIST);
        return true;
    } catch (Exception $e) {
        if (OSCBB_DEBUG) {
            error_log('OSC Bot Blocker: Error dropping tables - ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * SET DEFAULT PREFERENCES
 */
function oscbb_set_default_preferences() {
    // General settings
    osc_set_preference('oscbb_enabled', '1', 'osc_bot_blocker', 'BOOLEAN');
    osc_set_preference('oscbb_protection_level', 'medium', 'osc_bot_blocker', 'STRING');
    
    // JavaScript protection
    osc_set_preference('oscbb_js_enabled', '1', 'osc_bot_blocker', 'BOOLEAN');
    osc_set_preference('oscbb_min_submit_time', '3', 'osc_bot_blocker', 'INTEGER');
    osc_set_preference('oscbb_max_submit_time', '3600', 'osc_bot_blocker', 'INTEGER');
    osc_set_preference('oscbb_token_expiration', '3600', 'osc_bot_blocker', 'INTEGER');
    
    // Honeypot protection
    osc_set_preference('oscbb_honeypot_enabled', '1', 'osc_bot_blocker', 'BOOLEAN');
    
    // User-Agent validation
    osc_set_preference('oscbb_ua_validation_enabled', '1', 'osc_bot_blocker', 'BOOLEAN');
    
    // Referer validation
    osc_set_preference('oscbb_referer_check_enabled', '1', 'osc_bot_blocker', 'BOOLEAN');
    
    // Cookie testing
    osc_set_preference('oscbb_cookie_test_enabled', '1', 'osc_bot_blocker', 'BOOLEAN');
    
    // Content filtering
    osc_set_preference('oscbb_url_limit', '3', 'osc_bot_blocker', 'INTEGER');
    osc_set_preference('oscbb_keyword_filter_enabled', '1', 'osc_bot_blocker', 'BOOLEAN');
    
    // Email protection
    osc_set_preference('oscbb_block_disposable_emails', '1', 'osc_bot_blocker', 'BOOLEAN');
    osc_set_preference('oscbb_block_free_emails', '0', 'osc_bot_blocker', 'BOOLEAN');
    
    // Rate limiting
    osc_set_preference('oscbb_rate_limit_enabled', '1', 'osc_bot_blocker', 'BOOLEAN');
    osc_set_preference('oscbb_rate_limit_count', '5', 'osc_bot_blocker', 'INTEGER');
    osc_set_preference('oscbb_rate_limit_period', '3600', 'osc_bot_blocker', 'INTEGER');
    
    // Logging
    osc_set_preference('oscbb_logging_enabled', '1', 'osc_bot_blocker', 'BOOLEAN');
    osc_set_preference('oscbb_log_retention_days', '30', 'osc_bot_blocker', 'INTEGER');
    
    // Enhanced logging (v1.3.0)
    osc_set_preference('oscbb_enhanced_logging_enabled', '1', 'osc_bot_blocker', 'BOOLEAN');
    
    // Uninstall options
    osc_set_preference('oscbb_keep_data_on_uninstall', '0', 'osc_bot_blocker', 'BOOLEAN');
}

/**
 * REMOVE ALL PREFERENCES
 */
function oscbb_remove_preferences() {
    $conn = DBConnectionClass::newInstance();
    $db = $conn->getOsclassDb();
    $comm = new DBCommandClass($db);
    
    try {
        $sql = "DELETE FROM " . DB_TABLE_PREFIX . "t_preference WHERE s_section = 'osc_bot_blocker'";
        $comm->query($sql);
        return true;
    } catch (Exception $e) {
        if (OSCBB_DEBUG) {
            error_log('OSC Bot Blocker: Error removing preferences - ' . $e->getMessage());
        }
        return false;
    }
}

function osc_plugin_configure_osc_bot_blocker() {
    osc_redirect_to(osc_admin_render_plugin_url('osc_bot_blocker/admin.php'));
}

/**
 * REGISTER ALL HOOKS DIRECTLY (Like Avatar Plugin Does)
 * osClass Enterprise 3.10.4 requires hooks to be registered directly in index.php
 * NOT inside an init function
 */

// INJECTION HOOKS - Add protection to forms when they're displayed
osc_add_hook('user_register_form', 'oscbb_hook_user_register_form');
osc_add_hook('user_login_form', 'oscbb_hook_user_login_form');
osc_add_hook('contact_form', 'oscbb_hook_contact_form');
osc_add_hook('admin_contact_form', 'oscbb_hook_admin_contact_form');
osc_add_hook('item_contact_form', 'oscbb_hook_item_contact_form');
osc_add_hook('header', 'oscbb_hook_header');

// VALIDATION HOOKS - Validate submissions when forms are posted
osc_add_hook('before_item_post', 'oscbb_hook_validate_item');
osc_add_hook('pre_contact_post', 'oscbb_hook_validate_contact');
osc_add_hook('before_user_register', 'oscbb_hook_validate_registration');
osc_add_hook('pre_item_add_comment_post', 'oscbb_hook_validate_comment');

// ADMIN HOOKS
if (OC_ADMIN) {
    osc_add_hook('admin_menu', 'oscbb_hook_admin_menu');
    osc_add_hook('main_dashboard', 'oscbb_hook_dashboard_widget');           // Modern theme (Enterprise 3.10.4)
    osc_add_hook('admin_dashboard_bottom', 'oscbb_hook_dashboard_widget');   // Omega theme (osClass 8.2.1)
}

// CRON HOOKS
osc_add_hook('cron_hourly', 'oscbb_hook_daily_cleanup');

// PLUGIN LIFECYCLE HOOKS
osc_add_hook(osc_plugin_path(__FILE__) . '_install', 'oscbb_install');
osc_add_hook(osc_plugin_path(__FILE__) . '_uninstall', 'oscbb_uninstall');
osc_add_hook(osc_plugin_path(__FILE__) . '_configure', 'osc_plugin_configure_osc_bot_blocker');

// REGISTER THE PLUGIN (only for install function, NOT for init)
osc_register_plugin(osc_plugin_path(__FILE__), 'oscbb_install');
