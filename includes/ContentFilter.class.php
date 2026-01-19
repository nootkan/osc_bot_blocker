<?php
/**
 * OSC Bot Blocker - Content Filter Class
 * 
 * Analyzes submitted content for spam indicators including:
 * - URL counting and validation
 * - Keyword filtering
 * - Suspicious patterns
 * 
 * @package OSCBotBlocker
 * @subpackage Classes
 * @author Van Isle Web Solutions
 * @link https://www.vanislebc.com/
 * @version 1.2.3
 */

// Prevent direct access
if (!defined('ABS_PATH')) {
    header('HTTP/1.1 403 Forbidden');
    die('Direct access is not allowed.');
}

class ContentFilter {
    
    /**
     * Count URLs in content
     * @param string $content Content to analyze
     * @return int Number of URLs found
     */
    public static function countURLs($content) {
        if (empty($content)) {
            return 0;
        }
        
        $url_count = 0;
        
        // Pattern 1: Standard URLs with protocol
        $pattern1 = '/https?:\/\/[^\s]+/i';
        preg_match_all($pattern1, $content, $matches1);
        $url_count += count($matches1[0]);
        
        // Pattern 2: URLs without protocol (www.)
        $pattern2 = '/\bwww\.[a-z0-9\-]+\.[a-z]{2,}/i';
        preg_match_all($pattern2, $content, $matches2);
        $url_count += count($matches2[0]);
        
        // Pattern 3: Domain-like patterns (domain.com)
        $pattern3 = '/\b[a-z0-9\-]+\.(com|org|net|info|biz|io|co|me|tv|cc|uk|de|fr|es|it|ru|cn|jp)\b/i';
        preg_match_all($pattern3, $content, $matches3);
        // Only count if not already counted by previous patterns
        foreach ($matches3[0] as $match) {
            if (!preg_match('/https?:\/\//i', $match) && !preg_match('/^www\./i', $match)) {
                $url_count++;
            }
        }
        
        return $url_count;
    }
    
    /**
     * Extract all URLs from content
     * @param string $content Content to analyze
     * @return array Array of URLs found
     */
    public static function extractURLs($content) {
        if (empty($content)) {
            return array();
        }
        
        $urls = array();
        
        // Extract HTTP/HTTPS URLs
        preg_match_all('/https?:\/\/[^\s]+/i', $content, $matches);
        $urls = array_merge($urls, $matches[0]);
        
        // Extract www. URLs
        preg_match_all('/\bwww\.[a-z0-9\-]+\.[a-z]{2,}[^\s]*/i', $content, $matches);
        $urls = array_merge($urls, $matches[0]);
        
        // Remove duplicates
        $urls = array_unique($urls);
        
        return $urls;
    }
    
    /**
     * Check for obfuscated URLs
     * Detects URLs disguised as IP addresses, hex encoding, etc.
     * @param string $content Content to analyze
     * @return array Result array with 'found' (bool) and 'type' (string)
     */
    public static function detectObfuscatedURLs($content) {
        $result = array(
            'found' => false,
            'type' => ''
        );
        
        if (empty($content)) {
            return $result;
        }
        
        // Check for IP addresses used as URLs (often malicious)
        if (preg_match('/https?:\/\/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i', $content)) {
            $result['found'] = true;
            $result['type'] = 'IP address URL';
            return $result;
        }
        
        // Check for hex-encoded URLs
        if (preg_match('/%[0-9a-f]{2}/i', $content) && preg_match('/https?.*%[0-9a-f]{2}/i', $content)) {
            $result['found'] = true;
            $result['type'] = 'Hex-encoded URL';
            return $result;
        }
        
        // Check for URL shorteners (often used to hide destination)
        $shorteners = array('bit.ly', 'tinyurl.com', 'goo.gl', 't.co', 'ow.ly', 'is.gd', 'buff.ly', 'adf.ly');
        foreach ($shorteners as $shortener) {
            if (stripos($content, $shortener) !== false) {
                $result['found'] = true;
                $result['type'] = 'URL shortener';
                return $result;
            }
        }
        
        // Check for URLs with @ symbol (phishing technique)
        if (preg_match('/https?:\/\/[^\/]*@/i', $content)) {
            $result['found'] = true;
            $result['type'] = 'URL with @ symbol (phishing)';
            return $result;
        }
        
        // Check for excessive subdomains (suspicious)
        if (preg_match('/https?:\/\/([a-z0-9\-]+\.){4,}/i', $content)) {
            $result['found'] = true;
            $result['type'] = 'Excessive subdomains';
            return $result;
        }
        
        return $result;
    }
    
    /**
     * Validate URLs in content
     * @param string $content Content to analyze
     * @param int $max_urls Maximum allowed URLs
     * @return array Result array with 'valid' (bool), 'reason' (string), 'url_count' (int)
     */
    public static function validateURLs($content, $max_urls = 3) {
        $result = array(
            'valid' => true,
            'reason' => '',
            'url_count' => 0
        );
        
        if (empty($content)) {
            return $result;
        }
        
        // Count URLs
        $url_count = self::countURLs($content);
        $result['url_count'] = $url_count;
        
        // Check if exceeds limit
        if ($url_count > $max_urls) {
            $result['valid'] = false;
            $result['reason'] = 'Too many URLs (' . $url_count . ' found, maximum ' . $max_urls . ' allowed)';
            return $result;
        }
        
        // Check for obfuscated URLs
        $obfuscation = self::detectObfuscatedURLs($content);
        if ($obfuscation['found']) {
            $result['valid'] = false;
            $result['reason'] = 'Suspicious URL detected: ' . $obfuscation['type'];
            return $result;
        }
        
        // Validate each URL format
        $urls = self::extractURLs($content);
        foreach ($urls as $url) {
            // Clean URL
            $url = trim($url);
            
            // Skip very short matches (false positives)
            if (strlen($url) < 4) {
                continue;
            }
            
            // Check for suspicious TLDs
            $suspicious_tlds = array('.tk', '.ml', '.ga', '.cf', '.gq', '.pw', '.top', '.xyz');
            foreach ($suspicious_tlds as $tld) {
                if (stripos($url, $tld) !== false) {
                    $result['valid'] = false;
                    $result['reason'] = 'Suspicious TLD detected: ' . $tld;
                    return $result;
                }
            }
            
            // Check for very long URLs (often spam)
            if (strlen($url) > 200) {
                $result['valid'] = false;
                $result['reason'] = 'URL too long (possible spam)';
                return $result;
            }
        }
        
        return $result;
    }
    
    /**
     * Check for excessive special characters (spam indicator)
     * @param string $content Content to analyze
     * @return array Result array with 'excessive' (bool) and 'percentage' (float)
     */
    public static function checkSpecialCharacters($content) {
        $result = array(
            'excessive' => false,
            'percentage' => 0
        );
        
        if (empty($content)) {
            return $result;
        }
        
        $length = strlen($content);
        if ($length == 0) {
            return $result;
        }
        
        // Count special characters (non-alphanumeric, non-whitespace)
        $special_count = preg_match_all('/[^a-z0-9\s]/i', $content);
        
        $percentage = ($special_count / $length) * 100;
        $result['percentage'] = round($percentage, 2);
        
        // If more than 30% special characters, it's suspicious
        if ($percentage > 30) {
            $result['excessive'] = true;
        }
        
        return $result;
    }
    
    /**
     * Check for repeated characters/words (spam indicator)
     * @param string $content Content to analyze
     * @return array Result array with 'suspicious' (bool) and 'pattern' (string)
     */
    public static function checkRepetition($content) {
        $result = array(
            'suspicious' => false,
            'pattern' => ''
        );
        
        if (empty($content)) {
            return $result;
        }
        
        // Check for character repetition (e.g., "aaaaaaa", "!!!!!!")
        if (preg_match('/(.)\1{10,}/', $content, $matches)) {
            $result['suspicious'] = true;
            $result['pattern'] = 'Excessive character repetition: "' . substr($matches[0], 0, 15) . '..."';
            return $result;
        }
        
        // Check for word repetition (e.g., "buy buy buy buy")
        if (preg_match('/\b(\w+)\s+\1\s+\1\s+\1/i', $content, $matches)) {
            $result['suspicious'] = true;
            $result['pattern'] = 'Excessive word repetition: "' . $matches[1] . '"';
            return $result;
        }
        
        return $result;
    }
    
    /**
     * Check for all-caps content (spam indicator)
     * @param string $content Content to analyze
     * @return array Result array with 'all_caps' (bool) and 'percentage' (float)
     */
    public static function checkAllCaps($content) {
        $result = array(
            'all_caps' => false,
            'percentage' => 0
        );
        
        if (empty($content)) {
            return $result;
        }
        
        // Remove non-alphabetic characters
        $alpha_only = preg_replace('/[^a-z]/i', '', $content);
        
        if (strlen($alpha_only) == 0) {
            return $result;
        }
        
        // Count uppercase letters
        $upper_count = preg_match_all('/[A-Z]/', $content);
        
        $percentage = ($upper_count / strlen($alpha_only)) * 100;
        $result['percentage'] = round($percentage, 2);
        
        // If more than 70% uppercase and content is substantial, flag it
        if ($percentage > 70 && strlen($alpha_only) > 20) {
            $result['all_caps'] = true;
        }
        
        return $result;
    }
    
    /**
     * Check content for invalid character encoding
     * @param string $content Content to check
     * @return array Result array with 'valid' (bool) and 'reason' (string)
     */
    public static function checkCharacterEncoding($content) {
        $result = array(
            'valid' => true,
            'reason' => ''
        );
        
        if (empty($content)) {
            return $result;
        }
        
        // Check if content is valid UTF-8
        if (!mb_check_encoding($content, 'UTF-8')) {
            $result['valid'] = false;
            $result['reason'] = 'Invalid character encoding (not UTF-8)';
            return $result;
        }
        
        // Check for null bytes (security risk)
        if (strpos($content, "\0") !== false) {
            $result['valid'] = false;
            $result['reason'] = 'Content contains null bytes';
            return $result;
        }
        
        // Check for suspicious control characters
        // Allow common ones: tab (09), newline (0A), carriage return (0D)
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $content)) {
            $result['valid'] = false;
            $result['reason'] = 'Content contains suspicious control characters';
            return $result;
        }
        
        // Check for right-to-left override (used in phishing)
        if (preg_match('/\x{202E}/u', $content)) {
            $result['valid'] = false;
            $result['reason'] = 'Content contains right-to-left override character';
            return $result;
        }
        
        // Check for excessive non-printable characters
        $printable_count = preg_match_all('/[[:print:]\s]/u', $content);
        $total_length = mb_strlen($content, 'UTF-8');
        
        if ($total_length > 0) {
            $printable_ratio = $printable_count / $total_length;
            
            if ($printable_ratio < 0.95) {
                $result['valid'] = false;
                $result['reason'] = 'Content contains excessive non-printable characters';
                return $result;
            }
        }
        
        return $result;
    }
    
    /**
     * Comprehensive content analysis
     * @param string $content Content to analyze
     * @param int $max_urls Maximum allowed URLs
     * @param bool $check_keywords Whether to check for spam keywords
     * @param int $keyword_sensitivity Keyword sensitivity (1=low, 2=medium, 3=high)
     * @return array Result array with 'valid' (bool) and 'reason' (string)
     */
    public static function analyzeContent($content, $max_urls = 3, $check_keywords = true, $keyword_sensitivity = 2) {
        $result = array(
            'valid' => true,
            'reason' => ''
        );
        
        if (empty($content)) {
            return $result;
        }
        
        // Character encoding validation
        $encoding_check = self::checkCharacterEncoding($content);
        if (!$encoding_check['valid']) {
            return $encoding_check;
        }
        
        // URL validation
        $url_check = self::validateURLs($content, $max_urls);
        if (!$url_check['valid']) {
            return $url_check;
        }
        
        // Keyword filtering
        if ($check_keywords) {
            $keyword_check = self::checkKeywords($content, $keyword_sensitivity);
            if (!$keyword_check['valid']) {
                return $keyword_check;
            }
        }
        
        // Check special characters
        $special_check = self::checkSpecialCharacters($content);
        if ($special_check['excessive']) {
            $result['valid'] = false;
            $result['reason'] = 'Content contains excessive special characters (' . $special_check['percentage'] . '%)';
            return $result;
        }
        
        // Check repetition
        $repetition_check = self::checkRepetition($content);
        if ($repetition_check['suspicious']) {
            $result['valid'] = false;
            $result['reason'] = 'Content contains suspicious repetition: ' . $repetition_check['pattern'];
            return $result;
        }
        
        // Check all-caps
        $caps_check = self::checkAllCaps($content);
        if ($caps_check['all_caps']) {
            $result['valid'] = false;
            $result['reason'] = 'Content is mostly uppercase (' . $caps_check['percentage'] . '%)';
            return $result;
        }
        
        // All checks passed
        return $result;
    }
	
	/**
     * Validate form submission fields for spam patterns
     * Checks name, email, subject, and message for suspicious patterns
     * 
     * @param array $fields Array with keys: name, email, subject, message
     * @return array ['valid' => bool, 'reason' => string]
     */
    public static function validateFormFields($fields) {
        $name = isset($fields['name']) ? trim($fields['name']) : '';
        $email = isset($fields['email']) ? trim($fields['email']) : '';
        $subject = isset($fields['subject']) ? trim($fields['subject']) : '';
        $message = isset($fields['message']) ? trim($fields['message']) : '';
        
        // Check 1: Random character detection in name
        if (!empty($name)) {
            if (self::isRandomCharacters($name)) {
                return array(
                    'valid' => false,
                    'reason' => 'Spam detected: Random character pattern in name'
                );
            }
        }
        
        // Check 2: Random character detection in subject
        if (!empty($subject)) {
            if (self::isRandomCharacters($subject)) {
                return array(
                    'valid' => false,
                    'reason' => 'Spam detected: Random character pattern in subject'
                );
            }
        }
        
        // Check 3: Suspicious Gmail patterns
        if (!empty($email) && stripos($email, '@gmail.com') !== false) {
            if (self::isSuspiciousGmailPattern($email)) {
                return array(
                    'valid' => false,
                    'reason' => 'Spam detected: Suspicious Gmail address pattern'
                );
            }
        }
        
        // Check 4: Multiple consecutive spaces (typo indicator)
        if (!empty($subject) || !empty($message)) {
            $text_to_check = $subject . ' ' . $message;
            if (preg_match('/\s{3,}/', $text_to_check)) {
                return array(
                    'valid' => false,
                    'reason' => 'Spam detected: Multiple consecutive spaces in content'
                );
            }
        }
        
        // Check 5: Message is only gibberish
        if (!empty($message)) {
            if (self::isGibberish($message)) {
                return array(
                    'valid' => false,
                    'reason' => 'Spam detected: Message contains only random characters'
                );
            }
        }
        
        // All checks passed
        return array('valid' => true, 'reason' => '');
    }
    
    /**
     * Detect random character patterns (mixed case gibberish)
     * Example: "XXEScFiorLkuFsZwrIGtb"
     * 
     * @param string $text Text to analyze
     * @return bool True if appears to be random characters
     */
    private static function isRandomCharacters($text) {
        $text = trim($text);
        
        // Must be at least 10 characters to analyze
        if (strlen($text) < 10) {
            return false;
        }
        
        // Count uppercase and lowercase letters
        $uppercase = 0;
        $lowercase = 0;
        $length = strlen($text);
        
        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];
            if (ctype_upper($char)) {
                $uppercase++;
            } elseif (ctype_lower($char)) {
                $lowercase++;
            }
        }
        
        // If we have both uppercase and lowercase
        if ($uppercase > 0 && $lowercase > 0) {
            $total_letters = $uppercase + $lowercase;
            
            // Random pattern: alternating case (more than 40% case changes)
            $case_changes = 0;
            for ($i = 1; $i < $length; $i++) {
                if (ctype_alpha($text[$i]) && ctype_alpha($text[$i-1])) {
                    if ((ctype_upper($text[$i]) && ctype_lower($text[$i-1])) ||
                        (ctype_lower($text[$i]) && ctype_upper($text[$i-1]))) {
                        $case_changes++;
                    }
                }
            }
            
            // If more than 40% of letters have case changes = random
            if ($total_letters > 0 && ($case_changes / $total_letters) > 0.4) {
                return true;
            }
        }
        
        // Check for lack of vowels (gibberish often has few vowels)
        $vowel_count = preg_match_all('/[aeiouAEIOU]/', $text);
        $consonant_count = preg_match_all('/[bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]/', $text);
        
        // If less than 20% vowels in a word with 10+ letters = suspicious
        if ($consonant_count > 0) {
            $vowel_ratio = $vowel_count / ($vowel_count + $consonant_count);
            if ($vowel_ratio < 0.2 && strlen($text) >= 10) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect suspicious Gmail address patterns
     * Examples:
     * - t.eka.l.udag6.41@gmail.com (excessive dots + numbers)
     * - dinanikolskaya99@gmail.com (ends in multiple numbers)
     * 
     * @param string $email Email address
     * @return bool True if suspicious pattern detected
     */
    private static function isSuspiciousGmailPattern($email) {
        // Extract username part (before @)
        $parts = explode('@', strtolower($email));
        if (count($parts) != 2) {
            return false;
        }
        
        $username = $parts[0];
        
        // Pattern 1: More than 3 dots in username (t.eka.l.udag6.41)
        // This is VERY unusual and likely obfuscation
        $dot_count = substr_count($username, '.');
        if ($dot_count > 3) {
            return true;
        }
        
        // Pattern 2: REMOVED - numbers at end are common and legitimate
        // Many users have pmsmith601@gmail.com style addresses
        
        // Pattern 3: Combination of dots AND numbers at end (udag6.41)
        // This specific pattern is suspicious
        if (preg_match('/\.\d+\.\d+$/', $username)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Detect if entire message is gibberish
     * Example: "pRYOONQQvytDyHcAoUFXNNVt"
     * 
     * @param string $text Message text
     * @return bool True if appears to be gibberish
     */
    private static function isGibberish($text) {
        $text = trim($text);
        
        // Skip if too short
        if (strlen($text) < 15) {
            return false;
        }
        
        // Remove spaces and check if it's all one word of random chars
        $no_spaces = str_replace(' ', '', $text);
        
        // If message is one long "word" with mixed case
        if ($no_spaces == $text && strlen($text) > 20) {
            if (self::isRandomCharacters($text)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check content for spam keywords
     * @param string $content Content to check
     * @param int $sensitivity Sensitivity level (1=low, 2=medium, 3=high)
     * @return array Result array with 'valid' (bool) and 'reason' (string)
     */
    public static function checkKeywords($content, $sensitivity = 2) {
        $result = array(
            'valid' => true,
            'reason' => ''
        );
        
        if (empty($content)) {
            return $result;
        }
        
        // Load keyword functions
        if (!function_exists('oscbb_analyze_keywords')) {
            require_once OSCBB_DATA_PATH . 'blacklist-keywords.php';
        }
        
        // Analyze keywords
        $keyword_analysis = oscbb_analyze_keywords($content, $sensitivity);
        
        if ($keyword_analysis['spam']) {
            $result['valid'] = false;
            $result['reason'] = $keyword_analysis['reason'];
        }
        
        return $result;
    }
}
