<?php
/**
 * OSC Bot Blocker - User-Agent Blacklist
 * 
 * List of known spam bot and malicious User-Agent strings.
 * This file contains patterns to identify and block known bad bots.
 * 
 * @package OSCBotBlocker
 * @subpackage Data
 * @author Van Isle Web Solutions
 * @link https://www.vanislebc.com/
 * @version 1.2.1
 */

// Prevent direct access
if (!defined('ABS_PATH')) {
    header('HTTP/1.1 403 Forbidden');
    die('Direct access is not allowed.');
}

/**
 * Get blacklisted User-Agent patterns
 * @return array Array of User-Agent patterns (case-insensitive)
 */
function oscbb_get_blacklisted_user_agents() {
    return array(
        // Common spam bots
        'sitebot',
        'spambot',
        'spam bot',
        'spammer',
        'harvester',
        'email collector',
        'email extractor',
        'emailcollector',
        'emailextractor',
        'emailsiphon',
        'emailwolf',
        'extractorpro',
        'copyrightcheck',
        
        // Scraping tools
        'httrack',
        'teleport',
        'webcopier',
        'webcopy',
        'offline explorer',
        'webripper',
        'webzip',
        'webmirror',
        'wget',
        'curl',
        'libwww',
        'python-requests',
        'python-urllib',
        
        // Known malicious bots
        'masscan',
        'nmap',
        'nikto',
        'sqlmap',
        'acunetix',
        'webinspect',
        'brutus',
        'hydra',
        'havij',
        
        // Fake bots (impersonating real ones)
        'googlebot/1.',  // Old Googlebot versions (suspicious)
        'msnbot/0.',     // Old MSNBot (suspicious)
        
        // SEO tools (often abused)
        'semrush',
        'ahrefs',
        'majestic',
        'mj12bot',
        'rogerbot',
        'exabot',
        'dotbot',
        'gigabot',
        
        // Content theft bots
        'psbot',
        'asterias',
        'blackwidow',
        'blowfish',
        'bullseye',
        'bunnyslippers',
        'cegbfeieh',
        'cheesebot',
        'cherrypicker',
        'chinaclaw',
        'copyrightcheck',
        'cosmos',
        'crescent',
        'disco',
        
        // Auto-posting tools
        'xrumer',
        'senuke',
        'bookmarkdemon',
        'autoseosubmitter',
        'submitwolf',
        
        // Generic suspicious patterns
        'bot@',
        'spider@',
        'crawler@',
        '.ru)',          // Russian bots often end with .ru)
        'Mozilla/1.',    // Very old Mozilla (suspicious)
        'Mozilla/2.',    // Very old Mozilla (suspicious)
        'Mozilla/3.',    // Very old Mozilla (suspicious)
        
        // Empty or very short user agents (highly suspicious)
        // These are checked separately in validation
    );
}

/**
 * Get list of legitimate bots that should NOT be blocked
 * @return array Array of legitimate bot patterns
 */
function oscbb_get_legitimate_bots() {
    return array(
        // Search engine bots (legitimate)
        'googlebot',
        'google-site-verification',
        'bingbot',
        'msnbot',
        'yahoo! slurp',
        'duckduckbot',
        'baiduspider',
        'yandexbot',
        'sogou',
        
        // Social media crawlers
        'facebookexternalhit',
        'facebot',
        'twitterbot',
        'linkedinbot',
        'pinterest',
        'whatsapp',
        'telegrambot',
        'slackbot',
        
        // Monitoring/uptime services
        'pingdom',
        'uptimerobot',
        'statuscake',
        'newrelic',
        'datadog',
        
        // Feed readers
        'feedly',
        'feedburner',
        'bloglines',
        
        // W3C validators
        'w3c_validator',
        'w3c_css_validator',
        'w3c-checklink',
        
        // Other legitimate crawlers
        'applebot',
        'amazonbot',
        'slurp',
    );
}

/**
 * Check if User-Agent is suspicious
 * @param string $user_agent User-Agent string
 * @return array Result array with 'blocked' (bool) and 'reason' (string)
 */
function oscbb_check_user_agent($user_agent) {
    $result = array(
        'blocked' => false,
        'reason' => ''
    );
    
    // Normalize to lowercase for comparison
    $ua_lower = strtolower(trim($user_agent));
    
    // Check if empty or too short (highly suspicious)
    if (empty($ua_lower) || strlen($ua_lower) < 10) {
        $result['blocked'] = true;
        $result['reason'] = 'User-Agent empty or too short';
        return $result;
    }
    
    // First check if it's a legitimate bot (whitelist)
    $legitimate_bots = oscbb_get_legitimate_bots();
    foreach ($legitimate_bots as $pattern) {
        if (stripos($ua_lower, $pattern) !== false) {
            // It's a legitimate bot - don't block
            return $result;
        }
    }
    
    // Now check against blacklist
    $blacklist = oscbb_get_blacklisted_user_agents();
    foreach ($blacklist as $pattern) {
        if (stripos($ua_lower, $pattern) !== false) {
            $result['blocked'] = true;
            $result['reason'] = 'User-Agent matches blacklist pattern: ' . $pattern;
            return $result;
        }
    }
    
    // Check for suspicious patterns
    
    // Too many numbers (often bot signatures)
    $number_count = preg_match_all('/[0-9]/', $ua_lower);
    $total_length = strlen($ua_lower);
    if ($total_length > 0 && ($number_count / $total_length) > 0.5) {
        $result['blocked'] = true;
        $result['reason'] = 'User-Agent contains excessive numbers';
        return $result;
    }
    
    // Contains SQL injection attempts
    if (preg_match('/(union|select|insert|update|delete|drop|create|alter)/i', $user_agent)) {
        $result['blocked'] = true;
        $result['reason'] = 'User-Agent contains SQL injection attempt';
        return $result;
    }
    
    // Contains script tags or JavaScript
    if (preg_match('/<script|javascript:|onerror=/i', $user_agent)) {
        $result['blocked'] = true;
        $result['reason'] = 'User-Agent contains XSS attempt';
        return $result;
    }
    
    // Passed all checks
    return $result;
}

/* End of file blacklist-useragents.php */
/* Location: /oc-content/plugins/osc_bot_blocker/data/blacklist-useragents.php */
