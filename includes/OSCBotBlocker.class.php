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
 * @version 1.3.0
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
        
        // NOTE: Hooks are now registered directly in index.php (not here)
        // This is required for osClass Enterprise 3.10.4 compatibility
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
        
        // Content filtering$url_pref = osc_get_preference('oscbb_url_limit', 'osc_bot_blocker');
        $url_pref = osc_get_preference('oscbb_url_limit', 'osc_bot_blocker');
        $this->config['url_limit'] = ($url_pref !== null) ? (int)$url_pref : 3;
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
     * 
     * DEPRECATED: This method is no longer used.
     * Hooks are now registered directly in index.php for osClass Enterprise 3.10.4 compatibility.
     * Kept for reference only.
     */
    private function registerHooksOLD() {
        // INJECTION HOOKS - These inject protection into forms when they're displayed
        
        // User registration form
        osc_add_hook('user_register_form', array($this, 'injectFormProtection'), 10);
        
        // User login form
        osc_add_hook('user_login_form', array($this, 'injectFormProtection'), 10);
        
        // Contact forms (public and admin)
        osc_add_hook('contact_form', array($this, 'injectFormProtection'), 10);
        osc_add_hook('admin_contact_form', array($this, 'injectFormProtection'), 10);
        
        // Item contact form (seller contact)
        osc_add_hook('item_contact_form', array($this, 'injectFormProtection'), 10);
        
        // Item posting form (fallback - inject via header since no form hook exists)
        osc_add_hook('header', array($this, 'injectGlobalProtection'), 1);
        
        // VALIDATION HOOKS - These validate submissions when forms are posted
        
        // Item posting validation
        osc_add_hook('before_item_post', array($this, 'validateItemSubmission'), 5);
        
        // Contact form validation
        osc_add_hook('pre_item_contact_post', array($this, 'validateContactForm'), 5);
        
        // User registration validation
        osc_add_hook('before_user_register', array($this, 'validateRegistration'), 5);
        
        // Comment validation
        osc_add_hook('pre_item_add_comment_post', array($this, 'validateComment'), 5);
        
        // Admin hooks (for settings)
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
        // INJECTION GUARD: Prevent duplicate injection if hook is called multiple times
        static $already_injected = false;
        
        if ($already_injected) {
            return;
        }
        
        try {
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
            
            // Mark as injected to prevent duplicates
            $already_injected = true;
            
        } catch (Exception $e) {
            // Silent fail - don't break the form
            if (OSCBB_DEBUG) {
                error_log('OSCBB Error in injectFormProtection: ' . $e->getMessage());
            }
        } catch (Error $e) {
            // Silent fail - don't break the form
            if (OSCBB_DEBUG) {
                error_log('OSCBB Fatal Error in injectFormProtection: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Inject global protection (JavaScript) in header
     * Called on every page via header hook to ensure JavaScript is loaded
     * This catches forms that don't have specific hooks (like item posting)
     */
    public function injectGlobalProtection() {
        // Only inject on frontend (not admin)
        if (OC_ADMIN) {
            return;
        }
        
        // Load JavaScript for bot detection if enabled
        if ($this->config['js_enabled']) {
            $this->enqueueJavaScript();
        }
        
        $this->debugLog('Global protection JavaScript injected via header hook');
    }
    
    /**
     * Set form load time in session
     * Used as backup for JavaScript time validation
     */
    private function setFormLoadTime() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Store current timestamp in session using native PHP
        $_SESSION['oscbb_form_load_time'] = time();
        
        // Also store in cookie as additional backup
        $cookie_name = 'oscbb_load_' . substr(hash('sha256', session_id()), 0, 8);
        $cookie_value = time();
        $expiry = time() + 3600; // 1 hour
        
        // Set secure flag based on HTTPS usage
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                  (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        
        setcookie($cookie_name, $cookie_value, $expiry, '/', '', $secure, true);
	}
    
    /**
     * Generate and inject session token field
     * Creates unique token per form load to prevent replay attacks
     */
    private function injectSessionToken() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Generate unique session token
        $token = $this->generateSessionToken();
        
        // Store token in session with expiration using native PHP
        $token_data = array(
            'token' => $token,
            'created' => time(),
            'used' => false
        );
        
        $_SESSION['oscbb_session_token'] = $token_data;
        
        // Output hidden field with token
        echo '<input type="hidden" name="oscbb_session_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '" />' . "\n";
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
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Generate daily rotation hash
        $date_hash = substr(hash('sha256', date('Ymd') . OSCBB_VERSION), 0, 8);
        
        // Create field mapping (obfuscated => real)
        $field_map = array(
            'field_' . $date_hash . '_1' => 'email',
            'field_' . $date_hash . '_2' => 'name',
            'field_' . $date_hash . '_3' => 'phone',
            'field_' . $date_hash . '_4' => 'message',
            'field_' . $date_hash . '_5' => 'subject',
        );
        
        // Store in session using native PHP
        $_SESSION['oscbb_field_map'] = $field_map;
        
        // Output hidden field with encrypted mapping (for JavaScript to use)
        $map_json = json_encode($field_map);
        $map_encoded = base64_encode($map_json);
        
        echo '<input type="hidden" name="oscbb_field_map" value="' . htmlspecialchars($map_encoded, ENT_QUOTES, 'UTF-8') . '" />' . "\n";
        
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
        $hash = substr(hash('sha256', OSCBB_VERSION . date('Ymd')), 0, 8);
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
        
        // NEW: Validate form fields for spam patterns
        $field_validation = $this->validateFormFields('contact');
        if ($field_validation !== true) {
            $this->blockSubmission($field_validation, 'contact');
        }
        
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
     * Validate form field patterns for spam
     * @param string $form_type Type of form
     * @return mixed True if valid, error message if invalid
     */
    private function validateFormFields($form_type) {
        // Load ContentFilter if not already loaded
        if (!class_exists('ContentFilter')) {
            require_once OSCBB_INCLUDES_PATH . 'ContentFilter.class.php';
        }
        
        // Gather form fields
        $fields = array();
        
        // Name field (various possible names)
        $name = Params::getParam('yourName');
        if (empty($name)) $name = Params::getParam('s_name');
        if (empty($name)) $name = Params::getParam('name');
        $fields['name'] = $name;
        
        // Email field
        $email = Params::getParam('contactEmail');
        if (empty($email)) $email = Params::getParam('s_email');
        if (empty($email)) $email = Params::getParam('email');
        $fields['email'] = $email;
        
        // Subject field
        $subject = Params::getParam('subject');
        if (empty($subject)) $subject = Params::getParam('s_subject');
        $fields['subject'] = $subject;
        
        // Message field
        $message = Params::getParam('message');
        if (empty($message)) $message = Params::getParam('s_message');
        $fields['message'] = $message;

        // --- Gibberish Detection ---
        // Detects bot-generated random strings regardless of content variation.
        // Catches spam like "TnjBNulLnzQAwdFEMoomDyl" universally.
        if (!empty($fields['name']) && $this->isGibberish($fields['name'], 'name')) {
            $this->debugLog('Gibberish name detected: ' . $fields['name']);
            return 'Submission rejected: Name appears to be computer-generated.';
        }

        if (!empty($fields['message']) && $this->isGibberish($fields['message'], 'message')) {
            $this->debugLog('Gibberish message detected: ' . $fields['message']);
            return 'Submission rejected: Message appears to be computer-generated.';
        }

        if (!empty($fields['subject']) && $this->isGibberish($fields['subject'], 'message')) {
            $this->debugLog('Gibberish subject detected: ' . $fields['subject']);
            return 'Submission rejected: Subject appears to be computer-generated.';
        }

        // Validate fields
        $result = ContentFilter::validateFormFields($fields);
        
        if (!$result['valid']) {
            $this->debugLog('Form field validation failed: ' . $result['reason']);
            return $result['reason'];
        }
        
        $this->debugLog('Form field validation passed');
        return true;
    }
	
	/**
	 * Detect if a string is random/gibberish computer-generated text
	 * Checks vowel ratio, word structure, and character entropy
	 * @param string $text The text to check
	 * @param string $field_type 'name' or 'message' for different thresholds
	 * @return bool True if gibberish detected
	 */
	private function isGibberish($text, $field_type = 'message') {
        if (empty($text)) {
        return false;
    }

    $text = trim($text);
    $length = strlen($text);

    // Very short text — let other validators handle it
    if ($length < 6) {
        return false;
    }

    // --- Check 1: Name fields must contain at least one space ---
    // Real full names have a first and last name separated by a space.
    // "TnjBNulLnzQAwdFEMoomDyl" has no spaces at all.
    if ($field_type === 'name' && $length > 15 && strpos($text, ' ') === false) {
        $this->debugLog('Gibberish detected: name field too long with no spaces');
        return true;
    }

    // --- Check 2: Vowel ratio ---
    // Real English/French words have 25–55% vowels.
    // Random strings are outside this range.
    $vowels = preg_match_all('/[aeiouAEIOU]/', $text, $matches);
    $letters = preg_match_all('/[a-zA-Z]/', $text, $letter_matches);

    if ($letters > 8) {
        $vowel_ratio = $vowels / $letters;
        if ($vowel_ratio < 0.15 || $vowel_ratio > 0.70) {
            $this->debugLog('Gibberish detected: abnormal vowel ratio (' . round($vowel_ratio, 2) . ')');
            return true;
        }
    }

    // --- Check 3: Consecutive consonant clusters ---
    // Real words rarely have more than 3 consonants in a row.
    // "TnjBNulL" has clusters like "TnjBN" which are impossible in real speech.
    if (preg_match('/[bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]{5,}/', $text)) {
        $this->debugLog('Gibberish detected: impossible consonant cluster found');
        return true;
    }

    // --- Check 4: Random uppercase mixing ---
    // Real sentences are either normal case or all-caps.
    // Bots often produce "rAnDoM" case mixing like "TnjBNulLnzQ".
    // Count uppercase letters that are NOT at the start of a word.
    $uppercase_mid_word = preg_match_all('/(?<=[a-z])[A-Z]/', $text, $m);
    $total_letters_for_caps = max(1, $letters);
    $random_caps_ratio = $uppercase_mid_word / $total_letters_for_caps;

    if ($length > 10 && $random_caps_ratio > 0.25) {
        $this->debugLog('Gibberish detected: random mid-word uppercase pattern (' . round($random_caps_ratio, 2) . ')');
        return true;
    }

    return false;
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
        
        try {
            // Get database connection
            $conn = $this->db->getOsclassDb();
            $comm = new DBCommandClass($conn);
            $escaped_ip = $conn->real_escape_string($ip);
            
            // Query database for recent submissions from this IP
            $query = sprintf(
                "SELECT COUNT(*) as submission_count FROM %s 
                 WHERE s_ip = '%s' 
                 AND dt_date > DATE_SUB(NOW(), INTERVAL %d SECOND)
                 AND s_blocked = 0",
                OSCBB_TABLE_LOG,
                $escaped_ip,
                $time_window
            );
            
            $result = $comm->query($query);
            
            if ($result) {
                $row = $result->row();
                if ($row && isset($row['submission_count'])) {
                    $count = (int)$row['submission_count'];
                    
                    if ($count >= $max_attempts) {
                        $this->debugLog('Rate limit exceeded: ' . $count . ' submissions in last hour');
                        return 'Rate limit exceeded: Too many submissions. Please wait before trying again.';
                    }
                    
                    $this->debugLog('Rate limit check passed: ' . $count . '/' . $max_attempts . ' submissions');
                }
            }
        } catch (Exception $e) {
            // If rate limit check fails, allow submission but log error
            $this->debugLog('Rate limit check error: ' . $e->getMessage());
            return true;
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
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get content hash
        $content = $this->getSubmittedContent();
        
        if (empty($content)) {
            return true; // No content to check
        }
        
        // Create hash of content
        $content_hash = hash('sha256', $content);
        
        // Store in session for this user using native PHP
        $recent_hashes = isset($_SESSION['oscbb_content_hashes']) ? $_SESSION['oscbb_content_hashes'] : null;
        
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
        
        $_SESSION['oscbb_content_hashes'] = $recent_hashes;
        
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
        
        // Get expected map from session using native PHP
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $expected_map = isset($_SESSION['oscbb_field_map']) ? $_SESSION['oscbb_field_map'] : null;
        
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
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get submitted token
        $submitted_token = Params::getParam('oscbb_session_token');
        
        if (empty($submitted_token)) {
            return 'Session validation failed: Token missing';
        }
        
        // Get session token data using native PHP
        $token_data = isset($_SESSION['oscbb_session_token']) ? $_SESSION['oscbb_session_token'] : null;
        
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
        $_SESSION['oscbb_session_token'] = $token_data;
        
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
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get form load time from session using native PHP
        $load_time = isset($_SESSION['oscbb_form_load_time']) ? $_SESSION['oscbb_form_load_time'] : null;
        
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
        
        // Clean up session data using native PHP
        unset($_SESSION['oscbb_form_load_time']);
        
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
        $hash = substr(hash('sha256', OSCBB_VERSION . date('Ymd')), 0, 8);
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
     * 
     * Note: Admin interface is accessible via the Configure button on plugins page.
     * This method is kept for future enhancement but does nothing currently.
     */
    public function addAdminMenu() {
        // Admin interface already accessible via Configure button
        // Future: Could add a dedicated menu item here if needed
        return;
    }
    
    /**
     * Daily cleanup cron job
     * Cleans old logs and expired session data
     */
    public function dailyCleanup() {
        $this->debugLog('dailyCleanup() method was called at ' . date('Y-m-d H:i:s'));
        $this->cleanOldLogs();
        $this->cleanExpiredSessions();
        $this->debugLog('Daily cleanup completed');
    }
    
    /**
     * Clean expired session data
     * Removes old/expired tokens from session storage
     */
    private function cleanExpiredSessions() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clean up any expired session tokens using native PHP
        $token_data = isset($_SESSION['oscbb_session_token']) ? $_SESSION['oscbb_session_token'] : null;
        
        if (!empty($token_data) && is_array($token_data)) {
            if (isset($token_data['created'])) {
                $token_age = time() - $token_data['created'];
                
                // Remove if older than max_submit_time
                if ($token_age > $this->config['max_submit_time']) {
                    unset($_SESSION['oscbb_session_token']);
                    $this->debugLog('Cleaned expired session token');
                }
            }
        }
        
        // Clean up form load time if expired using native PHP
        $load_time = isset($_SESSION['oscbb_form_load_time']) ? $_SESSION['oscbb_form_load_time'] : null;
        if (!empty($load_time)) {
            $load_age = time() - $load_time;
            
            if ($load_age > $this->config['max_submit_time']) {
                unset($_SESSION['oscbb_form_load_time']);
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
     * ENHANCED LOGGING METHODS (v1.3.0)
     * These methods extract additional analytics data for improved spam pattern recognition
     */
    
    /**
     * Extract and analyze content for enhanced logging
     * @return array Analytics data
     */
    private function extractEnhancedData() {
        // Check if enhanced logging is enabled
        $enhanced_enabled = osc_get_preference('oscbb_enhanced_logging_enabled', 'osc_bot_blocker');
        if ($enhanced_enabled != '1') {
            return array(); // Return empty if not enabled
        }
        
        $data = array();
        
        // Get content from submission
        $content = $this->getSubmittedContent();
        
        // Content hash (for duplicate detection)
        $data['s_content_hash'] = !empty($content) ? hash('sha256', $content) : null;
        
        // Content length
        $data['s_content_length'] = !empty($content) ? strlen($content) : 0;
        
        // URL count
        if (class_exists('ContentFilter')) {
            $data['i_url_count'] = ContentFilter::countURLs($content);
            $data['b_has_links'] = ($data['i_url_count'] > 0) ? 1 : 0;
        } else {
            $data['i_url_count'] = 0;
            $data['b_has_links'] = 0;
        }
        
        // Keyword matches
        $keyword_data = $this->getKeywordMatches($content);
        $data['i_keyword_matches'] = $keyword_data['count'];
        $data['s_matched_keywords'] = !empty($keyword_data['keywords']) ? json_encode($keyword_data['keywords']) : null;
        
        // Submit time (from session or JavaScript)
        $data['i_submit_time'] = $this->getSubmitTime();
        
        // Field count
        $data['i_field_count'] = $this->countFilledFields();
        
        // Browser language
        $data['s_browser_language'] = $this->getBrowserLanguage();
        
        // Email domain
        $email = $this->getSubmittedEmail();
        $data['s_email_domain'] = $this->extractEmailDomain($email);
        
        // Content languages count
        $data['i_content_languages'] = $this->detectContentLanguages($content);
        
        // All caps check
        if (class_exists('ContentFilter')) {
            $caps_check = ContentFilter::checkAllCaps($content);
            $data['b_all_caps'] = $caps_check['all_caps'] ? 1 : 0;
        } else {
            $data['b_all_caps'] = 0;
        }
        
        // Time patterns
        $data['i_hour_of_day'] = (int)date('G'); // 0-23
        $data['i_day_of_week'] = (int)date('w'); // 0-6 (Sunday=0)
        
        return $data;
    }
    
    /**
     * Get keyword matches from content
     * @param string $content Content to analyze
     * @return array Match data with count and keywords array
     */
    private function getKeywordMatches($content) {
        if (empty($content)) {
            return array('count' => 0, 'keywords' => array());
        }
        
        // Load keyword checking function
        if (!function_exists('oscbb_check_spam_keywords')) {
            require_once OSCBB_DATA_PATH . 'blacklist-keywords.php';
        }
        
        // Check keywords with medium sensitivity
        $result = oscbb_check_spam_keywords($content, 2);
        
        return array(
            'count' => $result['score'],
            'keywords' => array_slice($result['matches'], 0, 10) // Limit to first 10 keywords
        );
    }
    
    /**
     * Calculate submission time from page load
     * @return int Seconds elapsed or null if unavailable
     */
    private function getSubmitTime() {
        // Try to get from session first
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $load_time = isset($_SESSION['oscbb_form_load_time']) ? $_SESSION['oscbb_form_load_time'] : null;
        
        if ($load_time) {
            return time() - $load_time;
        }
        
        // Try to get from JavaScript timestamp
        $js_timestamp = Params::getParam('oscbb_timestamp');
        if ($js_timestamp && is_numeric($js_timestamp)) {
            $load_time_js = $js_timestamp / 1000; // Convert milliseconds to seconds
            return time() - $load_time_js;
        }
        
        return null;
    }
    
    /**
     * Count how many form fields were filled
     * @return int Number of non-empty POST fields
     */
    private function countFilledFields() {
        $count = 0;
        
        // Count non-empty POST fields, excluding our security fields
        $exclude_fields = array(
            'oscbb_token', 'oscbb_timestamp', 'oscbb_fingerprint', 
            'oscbb_checks', 'oscbb_js_enabled', 'oscbb_session_token',
            'oscbb_field_map', 'oscbb_hp_check'
        );
        
        foreach ($_POST as $key => $value) {
            // Skip our security fields
            if (in_array($key, $exclude_fields)) {
                continue;
            }
            
            // Skip honeypot fields
            if (strpos($key, 'user_') === 0 || strpos($key, 'website_') === 0 || strpos($key, 'comment_') === 0) {
                continue;
            }
            
            // Count if not empty
            if (!empty($value) && is_string($value) && trim($value) !== '') {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Get browser language from Accept-Language header
     * @return string Primary language code (e.g., 'en', 'es', 'fr')
     */
    private function getBrowserLanguage() {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }
        
        $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        
        // Parse Accept-Language header (e.g., "en-US,en;q=0.9,es;q=0.8")
        // Get the first language
        $languages = explode(',', $accept_language);
        if (!empty($languages)) {
            $primary = explode(';', $languages[0])[0]; // Remove quality values
            $primary = explode('-', $primary)[0]; // Remove region code
            return strtolower(trim($primary));
        }
        
        return null;
    }
    
    /**
     * Get submitted email from form
     * @return string Email address or empty string
     */
    private function getSubmittedEmail() {
        // Try various email field names
        $email_fields = array('contactEmail', 's_email', 'email');
        
        foreach ($email_fields as $field) {
            $email = Params::getParam($field);
            if (!empty($email)) {
                return $email;
            }
        }
        
        return '';
    }
    
    /**
     * Extract domain from email address
     * @param string $email Email address
     * @return string Domain or null
     */
    private function extractEmailDomain($email) {
        if (empty($email) || strpos($email, '@') === false) {
            return null;
        }
        
        $parts = explode('@', $email);
        return strtolower(trim($parts[1]));
    }
    
    /**
     * Detect number of different language scripts in content
     * @param string $content Content to analyze
     * @return int Number of different scripts (Latin, Cyrillic, CJK, Arabic, etc.)
     */
    private function detectContentLanguages($content) {
        if (empty($content)) {
            return 0;
        }
        
        $script_count = 0;
        
        // Check for different Unicode scripts
        // Latin (English, Spanish, French, etc.)
        if (preg_match('/[a-zA-Z]/', $content)) {
            $script_count++;
        }
        
        // Cyrillic (Russian, Ukrainian, etc.)
        if (preg_match('/[\x{0400}-\x{04FF}]/u', $content)) {
            $script_count++;
        }
        
        // CJK (Chinese, Japanese, Korean)
        if (preg_match('/[\x{4E00}-\x{9FFF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{AC00}-\x{D7AF}]/u', $content)) {
            $script_count++;
        }
        
        // Arabic
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $content)) {
            $script_count++;
        }
        
        // Hebrew
        if (preg_match('/[\x{0590}-\x{05FF}]/u', $content)) {
            $script_count++;
        }
        
        // Thai
        if (preg_match('/[\x{0E00}-\x{0E7F}]/u', $content)) {
            $script_count++;
        }
        
        // Greek
        if (preg_match('/[\x{0370}-\x{03FF}]/u', $content)) {
            $script_count++;
        }
        
        return $script_count;
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
            
            // Prepare basic data
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
            
            // Add enhanced logging data (v1.3.0)
            $enhanced_data = $this->extractEnhancedData();
            if (!empty($enhanced_data)) {
                $data = array_merge($data, $enhanced_data);
            }
            
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
            $escaped_today = $conn->real_escape_string($today);
            
            // Check if today's record exists
            $sql = "SELECT pk_i_id FROM " . OSCBB_TABLE_STATS . " WHERE dt_date = '" . $escaped_today . "'";
            $result = $comm->query($sql);
            
            // Check if query was successful
            if ($result === false) {
                $this->debugLog('Statistics query failed - table may not exist');
                return false;
            }
            
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
                
                $sql .= " WHERE dt_date = '" . $escaped_today . "'";
                
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
            $deleted = $conn->affected_rows;
            
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
	
	/**
     * Render dashboard widget showing recent bot blocks
     * Called via admin_dashboard_top hook
     */
    public function renderDashboardWidget() {
        // Only show on dashboard and if logging is enabled
        if (!$this->config['logging_enabled']) {
            return;
        }
        
        try {
            $db = DBConnectionClass::newInstance();
            $conn = $db->getOsclassDb();
            $comm = new DBCommandClass($conn);
            
            // Get summary statistics
            $today = date('Y-m-d');
            $week_ago = date('Y-m-d', strtotime('-7 days'));
            
            $stats_query = "SELECT 
                                COUNT(CASE WHEN DATE(dt_date) = '$today' THEN 1 END) as today,
                                COUNT(CASE WHEN DATE(dt_date) >= '$week_ago' THEN 1 END) as week,
                                COUNT(*) as total
                            FROM " . OSCBB_TABLE_LOG . " 
                            WHERE s_blocked = 1";
            
            $stats_result = $comm->query($stats_query);
            $stats = array('today' => 0, 'week' => 0, 'total' => 0);
            
            if ($stats_result) {
                $stats_row = $stats_result->row();
                if ($stats_row) {
                    $stats['today'] = (int)$stats_row['today'];
                    $stats['week'] = (int)$stats_row['week'];
                    $stats['total'] = (int)$stats_row['total'];
                }
            }
            
            // Get recent blocks (last 15)
            $logs_query = "SELECT * FROM " . OSCBB_TABLE_LOG . " 
                          WHERE s_blocked = 1 
                          ORDER BY dt_date DESC 
                          LIMIT 15";
            
            $logs_result = $comm->query($logs_query);
            $recent_blocks = array();
            
            if ($logs_result && $logs_result->numRows() > 0) {
                $recent_blocks = $logs_result->result();
            }
            
            // Render widget HTML
            ?>
            <div class="grid-row grid-100">
                <div class="row-wrapper">
                    <div class="widget-box">
                        <div class="widget-box-title">
                            <h3>
                                <span><?php _e('Bot Blocker Activity'); ?></span>
                            </h3>
                        </div>
                        <div class="widget-box-content">
                            
                            <!-- Summary Statistics -->
                            <div class="row st">
                                <?php echo sprintf(__('Blocked in last 24 hours: %s'), '<strong>' . number_format($stats['today']) . '</strong>'); ?>
                            </div>
                            
                            <div class="row st">
                                <?php echo sprintf(__('Blocked in last 7 days: %s'), '<strong>' . number_format($stats['week']) . '</strong>'); ?>
                            </div>
                            
                            <div class="row st">
                                <?php echo sprintf(__('Overall blocked: %s'), '<strong>' . number_format($stats['total']) . '</strong>'); ?>
                            </div>
                            
                            <div class="row"></div>
                            
                            <h4><?php _e('Recently blocked'); ?></h4>
                            
                            <?php if (count($recent_blocks) <= 0) { ?>
                                <div class="empty"><?php _e('No spam blocked yet - your site is clean!'); ?></div>
                            <?php } else { ?>
                                <?php foreach ($recent_blocks as $block) { 
                                    // Determine status class and title based on block type
                                    $block_type = $block['s_type'];
                                    $form_type = $block['s_form_type'];
                                    
                                    switch($block_type) {
                                        case 'bot':
                                            $class = 'spam';
                                            $title = __('Bot Detected');
                                            break;
                                        case 'spam':
                                            $class = 'spam';
                                            $title = __('Spam Content');
                                            break;
                                        case 'honeypot':
                                            $class = 'blocked';
                                            $title = __('Honeypot Trap');
                                            break;
                                        case 'javascript':
                                            $class = 'inactive';
                                            $title = __('JS Validation Failed');
                                            break;
                                        case 'rate_limit':
                                            $class = 'moderation';
                                            $title = __('Rate Limit Exceeded');
                                            break;
                                        case 'content':
                                            $class = 'spam';
                                            $title = __('Content Filter');
                                            break;
                                        default:
                                            $class = 'blocked';
                                            $title = __('Blocked');
                                    }
                                    
                                    // Create display text
                                    $display_text = sprintf(
                                        __('%s attempt from %s'),
                                        ucfirst($form_type),
                                        substr($block['s_ip'], 0, 15)
                                    );
                                ?>
                                    <div class="row">
                                        <span class="date"><?php echo osc_format_date($block['dt_date'], 'd M, H:i'); ?></span>
                                        <i class="fa fa-circle <?php echo $class; ?>" title="<?php echo osc_esc_html($title); ?>"></i>
                                        <span title="<?php echo osc_esc_html($block['s_reason']); ?>">
                                            <?php echo osc_esc_html($display_text); ?>
                                        </span>
                                    </div>
                                <?php } ?>
                                
                                <!-- Link to full logs -->
                                <div class="row" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #e0e0e0;">
                                    <a href="<?php echo osc_admin_render_plugin_url('osc_bot_blocker/admin.php') . '&tab=logs'; ?>" class="btn">
                                        <?php _e('View All Logs'); ?>
                                    </a>
                                    <a href="<?php echo osc_admin_render_plugin_url('osc_bot_blocker/admin.php') . '&tab=statistics'; ?>" class="btn" style="margin-left: 10px;">
                                        <?php _e('View Statistics'); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            
                        </div>
                    </div>
                </div>
            </div>
            <?php
            
        } catch (Exception $e) {
            // Silent fail - don't break dashboard
            if (OSCBB_DEBUG) {
                error_log('OSCBB Dashboard Widget Error: ' . $e->getMessage());
            }
        }
    }
}
