<?php
/**
 * OSC Bot Blocker - Main Class
 * 
 * This is the core class that manages all bot blocking and spam prevention features.
 * Uses singleton pattern to ensure only one instance exists.
 * 
 * @package OSCBotBlocker
 * @subpackage Classes
 * @author Your Name
 * @version 1.2.1
 */

// Prevent direct access
if (!defined('ABS_PATH')) {
    header('HTTP/1.1 403 Forbidden');
    die('Direct access is not allowed.');
}

class OSCBotBlocker {
    
    /**
     * Singleton instance
     * @var OSCBotBlocker
     */
    private static $instance = null;
    
    /**
     * Plugin configuration settings
     * @var array
     */
    private $config = array();
    
    /**
     * Database connection
     * @var object
     */
    private $db;
    
    /**
     * Current user's IP address
     * @var string
     */
    private $user_ip;
    
    /**
     * Current user's User-Agent
     * @var string
     */
    private $user_agent;
    
    /**
     * Private constructor (Singleton pattern)
     */
    private function __construct() {
        // Initialize database connection
        $this->db = DBConnectionClass::newInstance();
        
        // Get user's IP and User-Agent
        $this->user_ip = $this->getUserIP();
        $this->user_agent = $this->getUserAgent();
        
        // Load configuration from preferences
        $this->loadConfig();
        
        // Register hooks if plugin is enabled
        if ($this->isEnabled()) {
            $this->registerHooks();
        }
    }
    
    /**
     * Get singleton instance
     * @return OSCBotBlocker
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Prevent cloning of singleton
     */
    private function __clone() {
        // Prevent cloning
    }
    
    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Load configuration from osClass preferences
     */
    private function loadConfig() {
        // General settings
        $this->config['enabled'] = (osc_get_preference('oscbb_enabled', 'osc_bot_blocker') == '1');
        $this->config['protection_level'] = osc_get_preference('oscbb_protection_level', 'osc_bot_blocker');
        
        // JavaScript protection
        $this->config['js_enabled'] = (osc_get_preference('oscbb_js_enabled', 'osc_bot_blocker') == '1');
        $this->config['min_submit_time'] = (int)osc_get_preference('oscbb_min_submit_time', 'osc_bot_blocker');
        $this->config['max_submit_time'] = (int)osc_get_preference('oscbb_max_submit_time', 'osc_bot_blocker');
        $this->config['token_expiration'] = (int)osc_get_preference('oscbb_token_expiration', 'osc_bot_blocker');
        
        // Honeypot protection
        $this->config['honeypot_enabled'] = (osc_get_preference('oscbb_honeypot_enabled', 'osc_bot_blocker') == '1');
        
        // User-Agent validation
        $this->config['ua_validation_enabled'] = (osc_get_preference('oscbb_ua_validation_enabled', 'osc_bot_blocker') == '1');
        
        // Referer validation
        $this->config['referer_check_enabled'] = (osc_get_preference('oscbb_referer_check_enabled', 'osc_bot_blocker') == '1');
        
        // Cookie testing
        $this->config['cookie_test_enabled'] = (osc_get_preference('oscbb_cookie_test_enabled', 'osc_bot_blocker') == '1');
        
        // Content filtering
        $this->config['url_limit'] = (int)osc_get_preference('oscbb_url_limit', 'osc_bot_blocker');
        $this->config['keyword_filter_enabled'] = (osc_get_preference('oscbb_keyword_filter_enabled', 'osc_bot_blocker') == '1');
        
        // Email protection
        $this->config['block_disposable_emails'] = (osc_get_preference('oscbb_block_disposable_emails', 'osc_bot_blocker') == '1');
        $this->config['block_free_emails'] = (osc_get_preference('oscbb_block_free_emails', 'osc_bot_blocker') == '1');
        
        // Rate limiting
        $this->config['rate_limit_enabled'] = (osc_get_preference('oscbb_rate_limit_enabled', 'osc_bot_blocker') == '1');
        $this->config['rate_limit_count'] = (int)osc_get_preference('oscbb_rate_limit_count', 'osc_bot_blocker');
        $this->config['rate_limit_period'] = (int)osc_get_preference('oscbb_rate_limit_period', 'osc_bot_blocker');
        
        // Logging
        $this->config['logging_enabled'] = (osc_get_preference('oscbb_logging_enabled', 'osc_bot_blocker') == '1');
        $this->config['log_retention_days'] = (int)osc_get_preference('oscbb_log_retention_days', 'osc_bot_blocker');
        
        // Set defaults if empty
        if (empty($this->config['protection_level'])) {
            $this->config['protection_level'] = 'medium';
        }
        if (empty($this->config['min_submit_time'])) {
            $this->config['min_submit_time'] = 3;
        }
        if (empty($this->config['max_submit_time'])) {
            $this->config['max_submit_time'] = 3600;
        }
        if (empty($this->config['token_expiration'])) {
            $this->config['token_expiration'] = 3600;
        }
        if (empty($this->config['url_limit'])) {
            $this->config['url_limit'] = 3;
        }
        if (empty($this->config['rate_limit_count'])) {
            $this->config['rate_limit_count'] = 5;
        }
        if (empty($this->config['rate_limit_period'])) {
            $this->config['rate_limit_period'] = 3600;
        }
        if (empty($this->config['log_retention_days'])) {
            $this->config['log_retention_days'] = 30;
        }
    }
    
    /**
     * Register hooks for protection
     * Hooks into osClass form submission points
     */
    private function registerHooks() {
        // Item posting hooks
        osc_add_hook('post_item', array($this, 'injectFormProtection'), 10);
        osc_add_hook('before_item_post', array($this, 'validateItemSubmission'), 5);
        
        // Contact form hooks
        osc_add_hook('pre_item_contact_post', array($this, 'validateContactForm'), 5);
        
        // User registration hooks
        osc_add_hook('before_user_register', array($this, 'validateRegistration'), 5);
        
        // Comment hooks
        osc_add_hook('pre_item_add_comment_post', array($this, 'validateComment'), 5);
        
        // Admin hooks (for settings, will be used in Phase 3)
        if (OC_ADMIN) {
            osc_add_hook('admin_menu', array($this, 'addAdminMenu'), 10);
        }
        
        // Cron hook for cleanup (if available)
        osc_add_hook('cron_daily', array($this, 'dailyCleanup'), 10);
    }
    
    /**
     * Inject JavaScript and honeypot protection into forms
     * Called when forms are displayed
     */
    public function injectFormProtection() {
        $this->debugLog('Form protection injection called');
        
        // Set session timestamp for form load time
        $this->setFormLoadTime();
        
        // Generate and inject session token
        $this->injectSessionToken();
        
        // Inject field name mappings for obfuscation
        $this->injectFieldObfuscation();
        
        // Load JavaScript for bot detection
        if ($this->config['js_enabled']) {
            $this->enqueueJavaScript();
        }
        
        // Inject honeypot fields
        if ($this->config['honeypot_enabled']) {
            $this->injectHoneypotFields();
        }
    }
    
    /**
     * Set form load time in session
     * Used as backup for JavaScript time validation
     */
    private function setFormLoadTime() {
        // Store current timestamp in session
        Session::newInstance()->_set('oscbb_form_load_time', time());
        
        // Also store in cookie as additional backup
        $cookie_name = 'oscbb_load_' . substr(md5(session_id()), 0, 8);
        $cookie_value = time();
        $expiry = time() + 3600; // 1 hour
        
        setcookie($cookie_name, $cookie_value, $expiry, '/', '', true, true); // secure, httponly
    }
    
    /**
     * Generate and inject session token field
     * Creates unique token per form load to prevent replay attacks
     */
    private function injectSessionToken() {
        // Generate unique session token
        $token = $this->generateSessionToken();
        
        // Store token in session with expiration
        $token_data = array(
            'token' => $token,
            'created' => time(),
            'used' => false
        );
        
        Session::newInstance()->_set('oscbb_session_token', $token_data);
        
        // Output hidden field with token
        echo '<input type="hidden" name="oscbb_session_token" value="' . esc_attr($token) . '" />' . "\n";
    }
    
    /**
     * Generate a unique session token
     * @return string Session token
     */
    private function generateSessionToken() {
        // Combine multiple sources of randomness
        $data = array(
            session_id(),
            time(),
            $this->user_ip,
            $this->user_agent,
            uniqid('', true),
            mt_rand()
        );
        
        // Create hash
        $token = hash('sha256', implode('|', $data));
        
        return $token;
    }
    
    /**
     * Inject field name obfuscation mapping
     * Creates hidden field with mapping of obfuscated names to real names
     */
    private function injectFieldObfuscation() {
        // Generate daily rotation hash
        $date_hash = substr(md5(date('Ymd') . OSCBB_VERSION), 0, 8);
        
        // Create field mapping (obfuscated => real)
        $field_map = array(
            'field_' . $date_hash . '_1' => 'email',
            'field_' . $date_hash . '_2' => 'name',
            'field_' . $date_hash . '_3' => 'phone',
            'field_' . $date_hash . '_4' => 'message',
            'field_' . $date_hash . '_5' => 'subject',
        );
        
        // Store in session
        Session::newInstance()->_set('oscbb_field_map', $field_map);
        
        // Output hidden field with encrypted mapping (for JavaScript to use)
        $map_json = json_encode($field_map);
        $map_encoded = base64_encode($map_json);
        
        echo '<input type="hidden" name="oscbb_field_map" value="' . esc_attr($map_encoded) . '" />' . "\n";
        
        $this->debugLog('Field obfuscation injected (hash: ' . $date_hash . ')');
    }
    
    /**
     * Enqueue JavaScript file for bot detection
     */
    private function enqueueJavaScript() {
        // Add JavaScript file to page
        echo '<script type="text/javascript" src="' . OSCBB_JS_URL . 'oscbb.js?v=' . OSCBB_VERSION . '"></script>' . "\n";
    }
    
    /**
     * Inject honeypot fields into forms
     * Creates hidden fields that humans won't fill but bots will
     */
    private function injectHoneypotFields() {
        // Generate unique field names using hash to make them harder to detect
        $hash = substr(md5(OSCBB_VERSION . date('Ymd')), 0, 8);
        $field1_name = 'user_' . $hash;
        $field2_name = 'website_' . $hash;
        $field3_name = 'comment_' . $hash;
        
        // Output honeypot HTML
        // These fields are hidden with CSS and should remain empty
        echo '<div style="position:absolute;left:-5000px;top:-5000px;" aria-hidden="true">' . "\n";
        echo '  <label for="' . $field1_name . '">Leave this field empty</label>' . "\n";
        echo '  <input type="text" name="' . $field1_name . '" id="' . $field1_name . '" value="" tabindex="-1" autocomplete="off" />' . "\n";
        echo '  <label for="' . $field2_name . '">Do not fill this field</label>' . "\n";
        echo '  <input type="text" name="' . $field2_name . '" id="' . $field2_name . '" value="" tabindex="-1" autocomplete="off" />' . "\n";
        echo '  <label for="' . $field3_name . '">Skip this field</label>' . "\n";
        echo '  <textarea name="' . $field3_name . '" id="' . $field3_name . '" tabindex="-1" autocomplete="off"></textarea>' . "\n";
        echo '</div>' . "\n";
        
        // Also add a hidden field with expected empty value
        echo '<input type="hidden" name="oscbb_hp_check" value="" />' . "\n";
    }
    
    /**
     * Validate item submission
     * Called before an item is posted
     * @param array $item Item data
     */
    public function validateItemSubmission($item = array()) {
        $this->debugLog('Item submission validation called');
        
        // Run all validation checks
        $validation_result = $this->runAllValidations('item');
        
        if ($validation_result !== true) {
            // Block the submission
            $this->blockSubmission($validation_result, 'item');
        }
    }
    
    /**
     * Validate contact form submission
     * Called before contact form is processed
     * @param array $item Item data
     */
    public function validateContactForm($item = array()) {
        $this->debugLog('Contact form validation called');
        
        // Run all validation checks
        $validation_result = $this->runAllValidations('contact');
        
        if ($validation_result !== true) {
            // Block the submission
            $this->blockSubmission($validation_result, 'contact');
        }
    }
    
    /**
     * Validate user registration
     * Called before user is registered
     */
    public function validateRegistration() {
        $this->debugLog('Registration validation called');
        
        // Run all validation checks
        $validation_result = $this->runAllValidations('register');
        
        if ($validation_result !== true) {
            // Block the submission
            $this->blockSubmission($validation_result, 'register');
        }
    }
    
    /**
     * Validate comment submission
     * Called before comment is posted
     * @param array $item Item data
     */
    public function validateComment($item = array()) {
        $this->debugLog('Comment validation called');
        
        // Run all validation checks
        $validation_result = $this->runAllValidations('comment');
        
        if ($validation_result !== true) {
            // Block the submission
            $this->blockSubmission($validation_result, 'comment');
        }
    }
    
    /**
     * Run all validation checks
     * @param string $form_type Type of form (item, contact, register, comment)
     * @return mixed True if valid, error message if invalid
     */
    private function runAllValidations($form_type) {
        // Check if user is whitelisted (will implement in Phase 4)
        if ($this->isWhitelisted()) {
            $this->debugLog('User is whitelisted - skipping validation');
            return true;
        }
        
        // Rate limiting check
        if ($this->config['rate_limit_enabled']) {
            $rate_check = $this->validateRateLimit($form_type);
            if ($rate_check !== true) {
                return $rate_check;
            }
        }
        
        // Request method validation (POST only for forms)
        $method_check = $this->validateRequestMethod();
        if ($method_check !== true) {
            return $method_check;
        }
        
        // Session token validation (prevents replay attacks)
        $session_check = $this->validateSessionToken();
        if ($session_check !== true) {
            return $session_check;
        }
        
        // Field obfuscation validation
        $field_check = $this->validateFieldObfuscation();
        if ($field_check !== true) {
            return $field_check;
        }
        
        // Email validation (enhanced)
        $email_check = $this->validateEmail($form_type);
        if ($email_check !== true) {
            return $email_check;
        }
        
        // Duplicate content detection
        $duplicate_check = $this->validateDuplicateContent($form_type);
        if ($duplicate_check !== true) {
            return $duplicate_check;
        }
        
        // Content validation (URLs and patterns)
        $content_check = $this->validateContent($form_type);
        if ($content_check !== true) {
            return $content_check;
        }
        
        // JavaScript validation
        if ($this->config['js_enabled']) {
            $js_check = $this->validateJavaScript();
            if ($js_check !== true) {
                return $js_check;
            }
        }
        
        // Honeypot validation
        if ($this->config['honeypot_enabled']) {
            $honeypot_check = $this->validateHoneypot();
            if ($honeypot_check !== true) {
                return $honeypot_check;
            }
        }
        
        // User-Agent validation
        if ($this->config['ua_validation_enabled']) {
            $ua_check = $this->validateUserAgent();
            if ($ua_check !== true) {
                return $ua_check;
            }
        }
        
        // Referer validation
        if ($this->config['referer_check_enabled']) {
            $referer_check = $this->validateReferer();
            if ($referer_check !== true) {
                return $referer_check;
            }
        }
        
        // Cookie validation
        if ($this->config['cookie_test_enabled']) {
            $cookie_check = $this->validateCookie();
            if ($cookie_check !== true) {
                return $cookie_check;
            }
        }
        
        // All checks passed
        return true;
    }
    
    /**
     * Validate rate limiting
     * Prevents too many submissions from same IP in short time
     * @param string $form_type Type of form
     * @return mixed True if valid, error message if invalid
     */
    private function validateRateLimit($form_type) {
        $ip = $this->user_ip;
        $time_window = 3600; // 1 hour in seconds
        $max_attempts = $this->config['rate_limit_count']; // Default: 5
        
        // Query database for recent submissions from this IP
        $query = sprintf(
            "SELECT COUNT(*) as submission_count FROM %s 
             WHERE s_ip = '%s' 
             AND dt_date > DATE_SUB(NOW(), INTERVAL %d SECOND)
             AND s_blocked = 0",
            OSCBB_TABLE_LOG,
            $this->db->escape($ip),
            $time_window
        );
        
        $result = $this->db->osc_dbFetchResult($query);
        
        if ($result && isset($result['submission_count'])) {
            $count = (int)$result['submission_count'];
            
            if ($count >= $max_attempts) {
                $this->debugLog('Rate limit exceeded: ' . $count . ' submissions in last hour');
                return 'Rate limit exceeded: Too many submissions. Please wait before trying again.';
            }
            
            $this->debugLog('Rate limit check passed: ' . $count . '/' . $max_attempts . ' submissions');
        }
        
        return true;
    }
    
    /**
     * Validate duplicate content
     * Prevents resubmission of identical content
     * @param string $form_type Type of form
     * @return mixed True if valid, error message if invalid
     */
    private function validateDuplicateContent($form_type) {
        // Get content hash
        $content = $this->getSubmittedContent();
        
        if (empty($content)) {
            return true; // No content to check
        }
        
        // Create hash of content
        $content_hash = md5($content);
        
        // Store in session for this user
        $recent_hashes = Session::newInstance()->_get('oscbb_content_hashes');
        
        if (!is_array($recent_hashes)) {
            $recent_hashes = array();
        }
        
        // Check if this exact content was recently submitted
        if (in_array($content_hash, $recent_hashes)) {
            $this->debugLog('Duplicate content detected (hash: ' . $content_hash . ')');
            return 'Duplicate content detected: You have already submitted this content recently.';
        }
        
        // Add to recent hashes (keep last 5)
        $recent_hashes[] = $content_hash;
        if (count($recent_hashes) > 5) {
            array_shift($recent_hashes); // Remove oldest
        }
        
        Session::newInstance()->_set('oscbb_content_hashes', $recent_hashes);
        
        $this->debugLog('Duplicate check passed (hash: ' . $content_hash . ')');
        return true;
    }
    
    /**
     * Get submitted content for duplicate checking
     * @return string Combined content
     */
    private function getSubmittedContent() {
        $content = '';
        
        $fields = array('title', 'description', 'message', 'comment', 'body');
        
        foreach ($fields as $field) {
            $value = Params::getParam($field);
            if (!empty($value)) {
                $content .= trim($value) . ' ';
            }
        }
        
        return trim($content);
    }
    
    /**
     * Validate request method and content-type
     * Forms should use POST method with proper headers
     * @return mixed True if valid, error message if invalid
     */
    private function validateRequestMethod() {
        // Get request method
        $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '';
        
        // Form submissions should be POST
        if ($method !== 'POST') {
            $this->debugLog('Invalid request method: ' . $method);
            return 'Request method validation failed: Only POST method allowed for form submissions';
        }
        
        // Validate Content-Type for POST requests
        $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
        if (!empty($content_type)) {
            // Remove charset if present (e.g., "application/x-www-form-urlencoded; charset=UTF-8")
            $content_type_main = strtok($content_type, ';');
            $content_type_main = trim(strtolower($content_type_main));
            
            // Valid content types for form submissions
            $valid_types = array(
                'application/x-www-form-urlencoded',
                'multipart/form-data',  // For file uploads
                'text/plain'            // Sometimes used by AJAX
            );
            
            // Check if content-type is valid
            $type_valid = false;
            foreach ($valid_types as $valid_type) {
                if (strpos($content_type_main, $valid_type) !== false) {
                    $type_valid = true;
                    break;
                }
            }
            
            if (!$type_valid) {
                $this->debugLog('Invalid content-type: ' . $content_type);
                // Don't block for this - some browsers/proxies may alter it
                // Just log for analysis
            }
        }
        
        // Check for suspicious headers that bots might use
        $suspicious_headers = array(
            'HTTP_X_REQUESTED_WITH',  // Often used by bots to mimic AJAX
        );
        
        foreach ($suspicious_headers as $header) {
            if (isset($_SERVER[$header])) {
                $value = $_SERVER[$header];
                // XMLHttpRequest is legitimate AJAX
                if ($header === 'HTTP_X_REQUESTED_WITH' && strtolower($value) === 'xmlhttprequest') {
                    $this->debugLog('AJAX request detected (legitimate)');
                } else {
                    $this->debugLog('Suspicious header detected: ' . $header . ' = ' . $value);
                }
            }
        }
        
        // Validate HTTP version (HTTP/1.0 is suspicious for modern browsers)
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            $protocol = $_SERVER['SERVER_PROTOCOL'];
            if ($protocol === 'HTTP/1.0') {
                $this->debugLog('Warning: HTTP/1.0 protocol (suspicious)');
                // Don't block - could be legitimate old client or proxy
            }
        }
        
        // Request method validation passed
        $this->debugLog('Request method validation passed (POST)');
        return true;
    }
    
    /**
     * Validate field obfuscation mapping
     * Ensures form was loaded properly with field name mapping
     * @return mixed True if valid, error message if invalid
     */
    private function validateFieldObfuscation() {
        // Get submitted field map
        $submitted_map = Params::getParam('oscbb_field_map');
        
        if (empty($submitted_map)) {
            // Field map missing - form may not have been loaded properly
            $this->debugLog('Field obfuscation map missing');
            // Don't block for this alone - could be legitimate old form
            return true;
        }
        
        // Decode the map
        $map_json = base64_decode($submitted_map);
        $submitted_fields = @json_decode($map_json, true);
        
        if (!is_array($submitted_fields)) {
            $this->debugLog('Field obfuscation map invalid format');
            return 'Field validation failed: Invalid field mapping';
        }
        
        // Get expected map from session
        $expected_map = Session::newInstance()->_get('oscbb_field_map');
        
        if (empty($expected_map) || !is_array($expected_map)) {
            // Session expired or missing
            $this->debugLog('Field obfuscation map not in session (possible timeout)');
            // Allow but log - session may have expired
            return true;
        }
        
        // Verify maps match
        if (serialize($submitted_fields) !== serialize($expected_map)) {
            $this->debugLog('Field obfuscation map mismatch');
            return 'Field validation failed: Field mapping verification failed';
        }
        
        // Validation passed
        $this->debugLog('Field obfuscation validation passed');
        return true;
    }
    
    /**
     * Validate content for URLs and suspicious patterns
     * @param string $form_type Type of form
     * @return mixed True if valid, error message if invalid
     */
    private function validateContent($form_type) {
        // Get content from various possible fields
        $content = '';
        
        // Title field
        $title = Params::getParam('title');
        if (!empty($title)) {
            $content .= ' ' . $title;
        }
        
        // Description field (items, comments)
        $description = Params::getParam('description');
        if (!empty($description)) {
            $content .= ' ' . $description;
        }
        
        // Message field (contact forms)
        $message = Params::getParam('message');
        if (!empty($message)) {
            $content .= ' ' . $message;
        }
        
        // Comment field
        $comment = Params::getParam('comment');
        if (!empty($comment)) {
            $content .= ' ' . $comment;
        }
        
        // Body field (generic)
        $body = Params::getParam('body');
        if (!empty($body)) {
            $content .= ' ' . $body;
        }
        
        // If no content found, skip validation
        if (empty(trim($content))) {
            $this->debugLog('No content found for URL validation');
            return true;
        }
        
        // Analyze content using ContentFilter
        // Parameters: content, max_urls, check_keywords, keyword_sensitivity
        $check_keywords = $this->config['keyword_filter_enabled'];
        $analysis = ContentFilter::analyzeContent(
            $content, 
            $this->config['url_limit'],
            $check_keywords,
            2  // Medium sensitivity (can be made configurable in Phase 3)
        );
        
        if (!$analysis['valid']) {
            $this->debugLog('Content validation failed: ' . $analysis['reason']);
            return 'Content validation failed: ' . $analysis['reason'];
        }
        
        // Log URL count if any found
        if (isset($analysis['url_count']) && $analysis['url_count'] > 0) {
            $this->debugLog('Content contains ' . $analysis['url_count'] . ' URL(s)');
        }
        
        // All content validations passed
        $this->debugLog('Content validation passed');
        return true;
    }
    
    /**
     * Validate email address
     * Enhanced validation with disposable email and pattern checking
     * @param string $form_type Type of form
     * @return mixed True if valid, error message if invalid
     */
    private function validateEmail($form_type) {
        // Load email validation functions
        if (!function_exists('oscbb_validate_email_patterns')) {
            require_once OSCBB_DATA_PATH . 'blacklist-emails.php';
        }
        
        // Get email from form submission
        // Different forms use different field names
        $email = '';
        if (Params::getParam('contactEmail')) {
            $email = Params::getParam('contactEmail');
        } elseif (Params::getParam('s_email')) {
            $email = Params::getParam('s_email');
        } elseif (Params::getParam('email')) {
            $email = Params::getParam('email');
        }
        
        // If no email field, skip validation (some forms may not have email)
        if (empty($email)) {
            $this->debugLog('No email field found in form, skipping email validation');
            return true;
        }
        
        // Validate email patterns
        $pattern_check = oscbb_validate_email_patterns($email);
        if (!$pattern_check['valid']) {
            $this->debugLog('Email pattern validation failed: ' . $pattern_check['reason']);
            return 'Email validation failed: ' . $pattern_check['reason'];
        }
        
        // Check disposable emails
        if ($this->config['block_disposable_emails']) {
            if (oscbb_is_disposable_email($email)) {
                $this->debugLog('Disposable email detected: ' . $email);
                return 'Email validation failed: Disposable email addresses are not allowed';
            }
        }
        
        // Check free emails
        if ($this->config['block_free_emails']) {
            if (oscbb_is_free_email($email)) {
                $this->debugLog('Free email detected: ' . $email);
                return 'Email validation failed: Free email addresses are not allowed';
            }
        }
        
        // All email validations passed
        $this->debugLog('Email validation passed: ' . $email);
        return true;
    }
    
    /**
     * Validate session token
     * Prevents replay attacks and ensures form was loaded properly
     * @return mixed True if valid, error message if invalid
     */
    private function validateSessionToken() {
        // Get submitted token
        $submitted_token = Params::getParam('oscbb_session_token');
        
        if (empty($submitted_token)) {
            return 'Session validation failed: Token missing';
        }
        
        // Get session token data
        $token_data = Session::newInstance()->_get('oscbb_session_token');
        
        if (empty($token_data) || !is_array($token_data)) {
            return 'Session validation failed: No session token found';
        }
        
        // Validate token matches
        if (!isset($token_data['token']) || $token_data['token'] !== $submitted_token) {
            return 'Session validation failed: Token mismatch';
        }
        
        // Check if token has been used (replay attack prevention)
        if (isset($token_data['used']) && $token_data['used'] === true) {
            return 'Session validation failed: Token already used (replay attack)';
        }
        
        // Check token age
        if (isset($token_data['created'])) {
            $token_age = time() - $token_data['created'];
            
            // Token expired (older than max_submit_time)
            if ($token_age > $this->config['max_submit_time']) {
                return 'Session validation failed: Token expired';
            }
        }
        
        // Mark token as used to prevent replay
        $token_data['used'] = true;
        Session::newInstance()->_set('oscbb_session_token', $token_data);
        
        // Token is valid
        $this->debugLog('Session token validation passed');
        return true;
    }
    
    /**
     * Check if user is whitelisted (placeholder for Phase 4)
     * @return bool
     */
    private function isWhitelisted() {
        // Check if logged in user is admin
        if (osc_is_admin_user_logged_in()) {
            return true;
        }
        
        // Will add IP whitelist and email whitelist in Phase 4
        return false;
    }
    
    /**
     * Validate JavaScript token
     * Checks that JavaScript executed and timing is valid
     * @return mixed True if valid, error message if invalid
     */
    private function validateJavaScript() {
        // Check if JavaScript enabled flag exists
        $js_enabled = Params::getParam('oscbb_js_enabled');
        if ($js_enabled !== '1') {
            // JavaScript not enabled - check session timing as fallback
            return $this->validateSessionTiming();
        }
        
        // Get token data from POST
        $token = Params::getParam('oscbb_token');
        $timestamp = Params::getParam('oscbb_timestamp');
        $fingerprint = Params::getParam('oscbb_fingerprint');
        $checks = Params::getParam('oscbb_checks');
        
        // Validate all required fields are present
        if (empty($token) || empty($timestamp) || empty($fingerprint)) {
            // Missing token data - fall back to session timing
            $this->debugLog('JavaScript token missing, checking session timing');
            return $this->validateSessionTiming();
        }
        
        // Validate timestamp is numeric
        if (!is_numeric($timestamp)) {
            return 'JavaScript validation failed: Invalid timestamp format';
        }
        
        // Check token age
        $current_time = time() * 1000; // Convert to milliseconds
        $token_age = ($current_time - $timestamp) / 1000; // Age in seconds
        
        // Check if token is expired (older than max_submit_time)
        if ($token_age > $this->config['max_submit_time']) {
            return 'JavaScript validation failed: Token expired (too old)';
        }
        
        // Check if submission is too fast (bot behavior)
        if ($token_age < $this->config['min_submit_time']) {
            return 'JavaScript validation failed: Submission too fast';
        }
        
        // Validate token format (should be alphanumeric)
        if (!preg_match('/^[a-z0-9]+$/i', $token)) {
            return 'JavaScript validation failed: Invalid token format';
        }
        
        // Validate fingerprint format
        if (!preg_match('/^[a-z0-9]+$/i', $fingerprint)) {
            return 'JavaScript validation failed: Invalid fingerprint format';
        }
        
        // Validate browser checks if present
        if (!empty($checks)) {
            $checks_valid = $this->validateBrowserChecks($checks);
            if ($checks_valid !== true) {
                return $checks_valid;
            }
        }
        
        // All JavaScript validations passed
        $this->debugLog('JavaScript validation passed (submission time: ' . round($token_age, 1) . ' seconds)');
        return true;
    }
    
    /**
     * Validate submission timing using session data (fallback)
     * Used when JavaScript validation is not available
     * @return mixed True if valid, error message if invalid
     */
    private function validateSessionTiming() {
        // Get form load time from session
        $load_time = Session::newInstance()->_get('oscbb_form_load_time');
        
        if (empty($load_time)) {
            // No session data - check cookie backup
            $cookie_name_pattern = 'oscbb_load_';
            $load_time_cookie = null;
            
            foreach ($_COOKIE as $key => $value) {
                if (strpos($key, $cookie_name_pattern) === 0) {
                    $load_time_cookie = $value;
                    break;
                }
            }
            
            if ($load_time_cookie) {
                $load_time = $load_time_cookie;
                $this->debugLog('Using cookie timing data as fallback');
            } else {
                // No timing data available at all
                $this->debugLog('Warning: No timing data available (JavaScript disabled and no session)');
                // This is suspicious but we'll allow it for maximum compatibility
                // Admin can enable strict mode in Phase 3
                return true;
            }
        }
        
        // Calculate submission time
        $current_time = time();
        $submission_time = $current_time - $load_time;
        
        // Validate timing
        if ($submission_time < $this->config['min_submit_time']) {
            return 'Time validation failed: Submission too fast (' . $submission_time . ' seconds)';
        }
        
        if ($submission_time > $this->config['max_submit_time']) {
            return 'Time validation failed: Submission took too long (' . $submission_time . ' seconds)';
        }
        
        // Clean up session data
        Session::newInstance()->_drop('oscbb_form_load_time');
        
        $this->debugLog('Session timing validation passed (submission time: ' . $submission_time . ' seconds)');
        return true;
    }
    
    /**
     * Validate browser capability checks
     * @param string $checks_json JSON string of browser checks
     * @return mixed True if valid, error message if invalid
     */
    private function validateBrowserChecks($checks_json) {
        // Try to decode JSON
        $checks = @json_decode($checks_json, true);
        
        if (!is_array($checks)) {
            return 'JavaScript validation failed: Invalid browser checks format';
        }
        
        // Check for required fields
        $required_fields = array('cookies', 'screen', 'timezone');
        foreach ($required_fields as $field) {
            if (!isset($checks[$field])) {
                return 'JavaScript validation failed: Missing browser check - ' . $field;
            }
        }
        
        // Validate cookies are enabled (bots often don't have cookies)
        if ($checks['cookies'] !== '1') {
            // This is suspicious but not necessarily a bot
            // Log it but don't block
            $this->debugLog('Warning: Cookies appear to be disabled');
        }
        
        // Validate screen resolution format
        if (!preg_match('/^\d+x\d+$/', $checks['screen'])) {
            return 'JavaScript validation failed: Invalid screen resolution format';
        }
        
        // Validate timezone is numeric
        if (!is_numeric($checks['timezone'])) {
            return 'JavaScript validation failed: Invalid timezone format';
        }
        
        // All browser checks are valid
        return true;
    }
    
    /**
     * Validate honeypot field
     * Checks that honeypot fields are empty (only bots fill them)
     * @return mixed True if valid, error message if invalid
     */
    private function validateHoneypot() {
        // Generate the same field names used in injection
        $hash = substr(md5(OSCBB_VERSION . date('Ymd')), 0, 8);
        $field1_name = 'user_' . $hash;
        $field2_name = 'website_' . $hash;
        $field3_name = 'comment_' . $hash;
        
        // Check all honeypot fields
        $field1_value = Params::getParam($field1_name);
        $field2_value = Params::getParam($field2_name);
        $field3_value = Params::getParam($field3_name);
        $hp_check_value = Params::getParam('oscbb_hp_check');
        
        // Any of these fields having content means it's a bot
        if (!empty($field1_value)) {
            return 'Honeypot validation failed: Field 1 filled';
        }
        
        if (!empty($field2_value)) {
            return 'Honeypot validation failed: Field 2 filled';
        }
        
        if (!empty($field3_value)) {
            return 'Honeypot validation failed: Field 3 filled';
        }
        
        if (!empty($hp_check_value)) {
            return 'Honeypot validation failed: Check field filled';
        }
        
        // Check if honeypot fields were even submitted
        // If they're missing entirely, it might be a bot that parsed the form
        // However, this is less reliable, so we'll just log it
        if (!isset($_POST[$field1_name]) && !isset($_POST[$field2_name]) && !isset($_POST[$field3_name])) {
            $this->debugLog('Warning: Honeypot fields not submitted (possible bot)');
            // Don't block based on this alone - could be legitimate
        }
        
        // All honeypot checks passed
        $this->debugLog('Honeypot validation passed');
        return true;
    }
    
    /**
     * Validate User-Agent
     * Checks User-Agent against blacklist and for suspicious patterns
     * @return mixed True if valid, error message if invalid
     */
    private function validateUserAgent() {
        // Load User-Agent blacklist functions
        if (!function_exists('oscbb_check_user_agent')) {
            require_once OSCBB_DATA_PATH . 'blacklist-useragents.php';
        }
        
        // Get current User-Agent
        $user_agent = $this->user_agent;
        
        // Check User-Agent using blacklist
        $check_result = oscbb_check_user_agent($user_agent);
        
        if ($check_result['blocked']) {
            // Log the specific reason
            $this->debugLog('User-Agent blocked: ' . $check_result['reason']);
            return 'User-Agent validation failed: ' . $check_result['reason'];
        }
        
        // Additional check: Verify User-Agent header exists
        if (empty($user_agent)) {
            // Empty User-Agent is very suspicious
            // However, some privacy tools remove it, so we'll be lenient
            $this->debugLog('Warning: Empty User-Agent detected');
            // Don't block, just log
        }
        
        // User-Agent validation passed
        $this->debugLog('User-Agent validation passed');
        return true;
    }
    
    /**
     * Validate Referer header
     * Checks that form submission came from our own site
     * @return mixed True if valid, error message if invalid
     */
    private function validateReferer() {
        // Get HTTP referer
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        
        // If referer is empty, it's suspicious but not always a bot
        // Some browsers/privacy tools strip referers
        if (empty($referer)) {
            // Check if it's a POST request
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // POST without referer is very suspicious for form submissions
                $this->debugLog('Warning: POST request without referer header');
                
                // In strict mode, we'd block this, but for compatibility
                // we'll allow it but log as suspicious
                // Admin can enable strict mode in Phase 3 settings
                return true; // Allow for now
            }
            return true;
        }
        
        // Parse referer URL
        $referer_parts = parse_url($referer);
        if (!$referer_parts || !isset($referer_parts['host'])) {
            return 'Referer validation failed: Invalid referer format';
        }
        
        // Get our site's domain
        $site_url = osc_base_url();
        $site_parts = parse_url($site_url);
        if (!$site_parts || !isset($site_parts['host'])) {
            // Can't validate if we can't determine our own domain
            $this->debugLog('Warning: Could not determine site domain for referer check');
            return true;
        }
        
        $site_host = strtolower($site_parts['host']);
        $referer_host = strtolower($referer_parts['host']);
        
        // Remove www. prefix for comparison
        $site_host_clean = preg_replace('/^www\./', '', $site_host);
        $referer_host_clean = preg_replace('/^www\./', '', $referer_host);
        
        // Check if referer matches our domain
        if ($site_host_clean !== $referer_host_clean) {
            // Referer doesn't match our domain
            $this->debugLog('Referer validation failed: ' . $referer_host . ' does not match ' . $site_host);
            return 'Referer validation failed: Form submitted from external site';
        }
        
        // Additional check: Verify referer uses same protocol (http/https)
        if (isset($site_parts['scheme']) && isset($referer_parts['scheme'])) {
            // We're lenient here - allow http->https or https->http
            // Some sites have mixed content or are transitioning to HTTPS
            $valid_schemes = array('http', 'https');
            if (!in_array($referer_parts['scheme'], $valid_schemes)) {
                return 'Referer validation failed: Invalid protocol';
            }
        }
        
        // Referer validation passed
        $this->debugLog('Referer validation passed: ' . $referer);
        return true;
    }
    
    /**
     * Validate Cookie
     * Checks that browser accepts cookies (test cookie set by JavaScript)
     * @return mixed True if valid, error message if invalid
     */
    private function validateCookie() {
        // Check if test cookie exists (set by JavaScript in oscbb.js)
        $test_cookie = isset($_COOKIE['oscbb_test']) ? $_COOKIE['oscbb_test'] : '';
        
        // Check if cookie exists
        if (empty($test_cookie)) {
            // Cookie not found - could be:
            // 1. Bot that doesn't process cookies
            // 2. User with cookies disabled
            // 3. Privacy tool blocking cookies
            
            $this->debugLog('Warning: Test cookie not found');
            
            // Check if JavaScript reported cookies as enabled
            $js_checks = Params::getParam('oscbb_checks');
            if (!empty($js_checks)) {
                $checks = @json_decode($js_checks, true);
                if (is_array($checks) && isset($checks['cookies'])) {
                    if ($checks['cookies'] === '1') {
                        // JavaScript says cookies enabled but we don't see the cookie
                        // This is very suspicious - possible bot
                        return 'Cookie validation failed: JavaScript reports cookies enabled but test cookie missing';
                    } else {
                        // User legitimately has cookies disabled
                        // We'll allow this but log it
                        $this->debugLog('User has cookies disabled (legitimate)');
                        return true;
                    }
                }
            }
            
            // No JavaScript data available, can't determine if legitimate
            // Allow but log as suspicious
            $this->debugLog('Cannot verify cookie status - allowing');
            return true;
        }
        
        // Cookie exists - validate its format
        // Test cookie should be a timestamp (numeric)
        if (!is_numeric($test_cookie)) {
            return 'Cookie validation failed: Invalid test cookie format';
        }
        
        // Check cookie age - should be recent (within last 24 hours)
        $cookie_age = time() - ($test_cookie / 1000); // Convert from milliseconds
        if ($cookie_age > 86400) { // 24 hours
            $this->debugLog('Warning: Test cookie is very old (' . round($cookie_age/3600, 1) . ' hours)');
            // Old cookie might be legitimate (user left form open)
            // Don't block, just log
        }
        
        if ($cookie_age < 0) {
            // Cookie timestamp is in the future - very suspicious
            return 'Cookie validation failed: Test cookie has future timestamp';
        }
        
        // Cookie validation passed
        $this->debugLog('Cookie validation passed (cookie age: ' . round($cookie_age) . ' seconds)');
        return true;
    }
    
    /**
     * Block submission and redirect with error
     * @param string $reason Reason for blocking
     * @param string $form_type Type of form
     */
    private function blockSubmission($reason, $form_type) {
        // Log the block
        $email = Params::getParam('contactEmail') ? Params::getParam('contactEmail') : Params::getParam('s_email');
        $this->logEvent('bot', $reason, $form_type, $email, true);
        
        // Show error to user
        osc_add_flash_error_message(__('Your submission was blocked. Please try again or contact support if you believe this is an error.', 'osc_bot_blocker'));
        
        // Determine redirect URL
        $redirect_url = '';
        switch ($form_type) {
            case 'item':
                $redirect_url = osc_item_post_url();
                break;
            case 'contact':
                $redirect_url = osc_get_http_referer();
                break;
            case 'register':
                $redirect_url = osc_register_account_url();
                break;
            case 'comment':
                $redirect_url = osc_get_http_referer();
                break;
            default:
                $redirect_url = osc_base_url();
                break;
        }
        
        // Redirect and exit
        if ($redirect_url) {
            osc_redirect_to($redirect_url);
        } else {
            osc_redirect_to(osc_base_url());
        }
        
        exit;
    }
    
    /**
     * Add admin menu item
     */
    public function addAdminMenu() {
        if (!OC_ADMIN) {
            return;
        }
        
        // Load admin class
        if (!class_exists('OSCBBAdmin')) {
            require_once OSCBB_PATH . 'admin/OSCBBAdmin.class.php';
        }
        
        // Get admin instance and add menu
        $admin = OSCBBAdmin::getInstance();
        $admin->addAdminMenu();
    }
    
    /**
     * Daily cleanup cron job
     * Cleans old logs and expired session data
     */
    public function dailyCleanup() {
        $this->cleanOldLogs();
        $this->cleanExpiredSessions();
        $this->debugLog('Daily cleanup completed');
    }
    
    /**
     * Clean expired session data
     * Removes old/expired tokens from session storage
     */
    private function cleanExpiredSessions() {
        // Clean up any expired session tokens
        $token_data = Session::newInstance()->_get('oscbb_session_token');
        
        if (!empty($token_data) && is_array($token_data)) {
            if (isset($token_data['created'])) {
                $token_age = time() - $token_data['created'];
                
                // Remove if older than max_submit_time
                if ($token_age > $this->config['max_submit_time']) {
                    Session::newInstance()->_drop('oscbb_session_token');
                    $this->debugLog('Cleaned expired session token');
                }
            }
        }
        
        // Clean up form load time if expired
        $load_time = Session::newInstance()->_get('oscbb_form_load_time');
        if (!empty($load_time)) {
            $load_age = time() - $load_time;
            
            if ($load_age > $this->config['max_submit_time']) {
                Session::newInstance()->_drop('oscbb_form_load_time');
                $this->debugLog('Cleaned expired form load time');
            }
        }
    }
    
    /**
     * Check if plugin is enabled
     * @return bool
     */
    public function isEnabled() {
        return $this->config['enabled'];
    }
    
    /**
     * Get configuration value
     * @param string $key Configuration key
     * @return mixed Configuration value or null if not found
     */
    public function getConfig($key) {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }
    
    /**
     * Get all configuration
     * @return array
     */
    public function getAllConfig() {
        return $this->config;
    }
    
    /**
     * Get user's IP address
     * Uses IPValidator class for enhanced IP detection
     * @return string
     */
    private function getUserIP() {
        // Use IPValidator to get real IP with proxy detection
        $ip = IPValidator::getRealIP();
        
        // Log if behind proxy (can be suspicious)
        if (IPValidator::isBehindProxy()) {
            $this->debugLog('User is behind proxy. Proxy chain: ' . implode(', ', IPValidator::getProxyChain()));
        }
        
        // Validate the IP
        if (!IPValidator::isValid($ip)) {
            $this->debugLog('Warning: Could not get valid IP address');
            return '';
        }
        
        return $ip;
    }
    
    /**
     * Get user's User-Agent string
     * @return string
     */
    private function getUserAgent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';
    }
    
    /**
     * Sanitize IP address (helper method)
     * @param string $ip IP address to sanitize
     * @return string Sanitized IP address
     */
    private function sanitizeIP($ip) {
        return IPValidator::sanitize($ip);
    }
    
    /**
     * Log a block/event to database
     * @param string $type Type of block (bot, spam, honeypot, javascript, rate_limit, content, other)
     * @param string $reason Reason for block
     * @param string $form_type Form type (item, contact, register, comment, other)
     * @param string $email Email address if available
     * @param bool $blocked Whether the submission was blocked (true) or just logged (false)
     * @return bool Success
     */
    public function logEvent($type = 'other', $reason = '', $form_type = 'other', $email = '', $blocked = true) {
        // Check if logging is enabled
        if (!$this->config['logging_enabled']) {
            return false;
        }
        
        try {
            $conn = $this->db->getOsclassDb();
            $comm = new DBCommandClass($conn);
            
            // Prepare data
            $data = array(
                'dt_date' => date('Y-m-d H:i:s'),
                's_ip' => $this->user_ip,
                's_user_agent' => substr($this->user_agent, 0, 500), // Limit to 500 chars
                's_type' => $type,
                's_reason' => $reason,
                's_form_type' => $form_type,
                's_email' => $email,
                's_blocked' => $blocked ? 1 : 0
            );
            
            // Insert into log table
            $result = $comm->insert(OSCBB_TABLE_LOG, $data);
            
            // Update statistics if blocked
            if ($blocked) {
                $this->updateStatistics($type);
            }
            
            return $result;
            
        } catch (Exception $e) {
            if (OSCBB_DEBUG) {
                error_log('OSC Bot Blocker: Error logging event - ' . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Update daily statistics
     * @param string $type Type of block
     * @return bool Success
     */
    private function updateStatistics($type) {
        try {
            $conn = $this->db->getOsclassDb();
            $comm = new DBCommandClass($conn);
            
            $today = date('Y-m-d');
            
            // Check if today's record exists
            $sql = "SELECT pk_i_id FROM " . OSCBB_TABLE_STATS . " WHERE dt_date = '" . $today . "'";
            $result = $comm->query($sql);
            $row = $result->row();
            
            if ($row) {
                // Update existing record
                $update_field = '';
                switch ($type) {
                    case 'bot':
                        $update_field = 'i_bot_blocks';
                        break;
                    case 'spam':
                        $update_field = 'i_spam_blocks';
                        break;
                    case 'honeypot':
                        $update_field = 'i_honeypot_blocks';
                        break;
                    case 'javascript':
                        $update_field = 'i_javascript_blocks';
                        break;
                    case 'rate_limit':
                        $update_field = 'i_rate_limit_blocks';
                        break;
                    case 'content':
                        $update_field = 'i_content_blocks';
                        break;
                }
                
                $sql = "UPDATE " . OSCBB_TABLE_STATS . " 
                        SET i_total_blocks = i_total_blocks + 1";
                
                if ($update_field != '') {
                    $sql .= ", " . $update_field . " = " . $update_field . " + 1";
                }
                
                $sql .= " WHERE dt_date = '" . $today . "'";
                
                $comm->query($sql);
                
            } else {
                // Create new record
                $data = array(
                    'dt_date' => $today,
                    'i_total_blocks' => 1,
                    'i_bot_blocks' => ($type == 'bot' ? 1 : 0),
                    'i_spam_blocks' => ($type == 'spam' ? 1 : 0),
                    'i_honeypot_blocks' => ($type == 'honeypot' ? 1 : 0),
                    'i_javascript_blocks' => ($type == 'javascript' ? 1 : 0),
                    'i_rate_limit_blocks' => ($type == 'rate_limit' ? 1 : 0),
                    'i_content_blocks' => ($type == 'content' ? 1 : 0)
                );
                
                $comm->insert(OSCBB_TABLE_STATS, $data);
            }
            
            return true;
            
        } catch (Exception $e) {
            if (OSCBB_DEBUG) {
                error_log('OSC Bot Blocker: Error updating statistics - ' . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Clean old log entries based on retention settings
     * @return int Number of deleted records
     */
    public function cleanOldLogs() {
        if ($this->config['log_retention_days'] <= 0) {
            return 0;
        }
        
        try {
            $conn = $this->db->getOsclassDb();
            $comm = new DBCommandClass($conn);
            
            $cutoff_date = date('Y-m-d H:i:s', strtotime('-' . $this->config['log_retention_days'] . ' days'));
            
            $sql = "DELETE FROM " . OSCBB_TABLE_LOG . " WHERE dt_date < '" . $cutoff_date . "'";
            $comm->query($sql);
            
            // Get number of affected rows
            $deleted = $conn->affected_rows();
            
            if (OSCBB_DEBUG && $deleted > 0) {
                error_log('OSC Bot Blocker: Cleaned ' . $deleted . ' old log entries');
            }
            
            return $deleted;
            
        } catch (Exception $e) {
            if (OSCBB_DEBUG) {
                error_log('OSC Bot Blocker: Error cleaning old logs - ' . $e->getMessage());
            }
            return 0;
        }
    }
    
    /**
     * Get current user's IP
     * @return string
     */
    public function getCurrentIP() {
        return $this->user_ip;
    }
    
    /**
     * Get current user's User-Agent
     * @return string
     */
    public function getCurrentUserAgent() {
        return $this->user_agent;
    }
    
    /**
     * Debug log helper
     * @param string $message Message to log
     */
    public function debugLog($message) {
        if (OSCBB_DEBUG) {
            error_log('OSC Bot Blocker: ' . $message);
        }
    }
}
