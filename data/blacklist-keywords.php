<?php
/**
 * OSC Bot Blocker - Keyword Blacklist
 * 
 * List of spam keywords and phrases commonly used in spam content.
 * These are checked in titles, descriptions, and message content.
 * 
 * @package OSCBotBlocker
 * @subpackage Data
 * @author Van Isle Web Solutions
 * @link https://www.vanislebc.com/
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABS_PATH')) {
    header('HTTP/1.1 403 Forbidden');
    die('Direct access is not allowed.');
}

/**
 * Get spam keyword blacklist
 * @return array Array of spam keywords/phrases
 */
function oscbb_get_spam_keywords() {
    return array(
        // Pharmaceutical spam
        'viagra',
        'cialis',
        'levitra',
        'xanax',
        'valium',
        'phentermine',
        'tramadol',
        'ambien',
        'adipex',
        'soma',
        'cheap pills',
        'online pharmacy',
        'buy pills',
        'prescription drugs',
        'no prescription',
        
        // Casino/Gambling spam
        'online casino',
        'poker online',
        'casino bonus',
        'slot machine',
        'blackjack',
        'roulette',
        'casino games',
        'free casino',
        'win money',
        'jackpot',
        
        // Financial/Money spam
        'make money fast',
        'earn money online',
        'work from home',
        'get rich quick',
        'easy money',
        'free money',
        'cash advance',
        'payday loan',
        'quick loan',
        'bad credit ok',
        'debt consolidation',
        'credit repair',
        'binary options',
        'forex trading',
        'cryptocurrency investment',
        
        // Adult content
        'xxx',
        'porn',
        'adult dating',
        'sex chat',
        'webcam girls',
        'live girls',
        'hot girls',
        'meet singles',
        
        // SEO spam
        'seo services',
        'link building',
        'backlinks',
        'increase traffic',
        'pagerank',
        'search engine optimization',
        'buy backlinks',
        'cheap seo',
        
        // Fake products
        'replica watches',
        'replica handbags',
        'fake rolex',
        'designer replica',
        'louis vuitton replica',
        'gucci replica',
        'coach outlet',
        
        // Weight loss spam
        'weight loss',
        'lose weight fast',
        'diet pills',
        'fat burner',
        'garcinia cambogia',
        'green coffee',
        'acai berry',
        
        // Tech support scams
        'tech support',
        'computer repair',
        'virus removal',
        'windows support',
        'call now',
        'toll free',
        
        // Generic spam phrases
        'click here',
        'buy now',
        'order now',
        'limited time',
        'act now',
        'hurry up',
        'dont miss',
        'special offer',
        'exclusive deal',
        'risk free',
        'money back guarantee',
        'free trial',
        'no obligation',
        'satisfaction guaranteed',
        'lowest price',
        'best price',
        'cheap',
        'discount',
        'save money',
        'amazing deal',
        
        // MLM/Pyramid schemes
        'mlm',
        'multi level marketing',
        'pyramid scheme',
        'network marketing',
        'home based business',
        'be your own boss',
        'financial freedom',
        
        // Suspicious actions
        'unsubscribe',
        'remove email',
        'opt out',
        'stop receiving',
        
        // Common spam words
        'congratulations',
        'winner',
        'selected',
        'claim your prize',
        'you won',
        'free gift',
        'gift card',
        'award',
    );
}

/**
 * Get exact match phrases (must match exactly, not partial)
 * @return array Array of exact match phrases
 */
function oscbb_get_exact_match_phrases() {
    return array(
        'buy now',
        'click here',
        'act now',
        'order now',
        'call now',
        'limited time',
        'free trial',
    );
}

/**
 * Check content for spam keywords
 * @param string $content Content to check
 * @param int $sensitivity Sensitivity level (1=low, 2=medium, 3=high)
 * @return array Result array with 'spam' (bool), 'matches' (array), 'score' (int)
 */
function oscbb_check_spam_keywords($content, $sensitivity = 2) {
    $result = array(
        'spam' => false,
        'matches' => array(),
        'score' => 0
    );
    
    if (empty($content)) {
        return $result;
    }
    
    $content_lower = strtolower($content);
    $keywords = oscbb_get_spam_keywords();
    $exact_phrases = oscbb_get_exact_match_phrases();
    
    // Check for keywords
    foreach ($keywords as $keyword) {
        if (stripos($content_lower, $keyword) !== false) {
            $result['matches'][] = $keyword;
            $result['score'] += 1;
        }
    }
    
    // Check for exact match phrases (weight them higher)
    foreach ($exact_phrases as $phrase) {
        // Use word boundaries for exact matching
        if (preg_match('/\b' . preg_quote($phrase, '/') . '\b/i', $content)) {
            $result['matches'][] = $phrase . ' (exact)';
            $result['score'] += 3;
        }
    }
    
    // Determine if spam based on sensitivity
    $threshold = 0;
    switch ($sensitivity) {
        case 1: // Low - allow more keywords
            $threshold = 5;
            break;
        case 2: // Medium - balanced
            $threshold = 3;
            break;
        case 3: // High - strict
            $threshold = 1;
            break;
        default:
            $threshold = 3;
    }
    
    if ($result['score'] >= $threshold) {
        $result['spam'] = true;
    }
    
    return $result;
}

/**
 * Check for suspicious keyword combinations
 * @param string $content Content to check
 * @return array Result array with 'suspicious' (bool) and 'combination' (string)
 */
function oscbb_check_keyword_combinations($content) {
    $result = array(
        'suspicious' => false,
        'combination' => ''
    );
    
    if (empty($content)) {
        return $result;
    }
    
    $content_lower = strtolower($content);
    
    // Suspicious combinations
    $combinations = array(
        array('free', 'money'),
        array('click', 'here'),
        array('buy', 'now'),
        array('limited', 'time'),
        array('act', 'now'),
        array('earn', 'money'),
        array('work', 'home'),
        array('make', 'money'),
        array('online', 'casino'),
        array('cheap', 'pills'),
    );
    
    foreach ($combinations as $combo) {
        $word1_found = stripos($content_lower, $combo[0]) !== false;
        $word2_found = stripos($content_lower, $combo[1]) !== false;
        
        if ($word1_found && $word2_found) {
            $result['suspicious'] = true;
            $result['combination'] = $combo[0] . ' + ' . $combo[1];
            return $result;
        }
    }
    
    return $result;
}

/**
 * Comprehensive spam keyword analysis
 * @param string $content Content to analyze
 * @param int $sensitivity Sensitivity level (1-3)
 * @return array Result array with 'spam' (bool) and 'reason' (string)
 */
function oscbb_analyze_keywords($content, $sensitivity = 2) {
    $result = array(
        'spam' => false,
        'reason' => ''
    );
    
    if (empty($content)) {
        return $result;
    }
    
    // Check keywords
    $keyword_check = oscbb_check_spam_keywords($content, $sensitivity);
    if ($keyword_check['spam']) {
        $result['spam'] = true;
        $result['reason'] = 'Spam keywords detected (' . $keyword_check['score'] . ' points): ' . 
                           implode(', ', array_slice($keyword_check['matches'], 0, 3));
        if (count($keyword_check['matches']) > 3) {
            $result['reason'] .= ' and ' . (count($keyword_check['matches']) - 3) . ' more';
        }
        return $result;
    }
    
    // Check combinations
    $combo_check = oscbb_check_keyword_combinations($content);
    if ($combo_check['suspicious']) {
        $result['spam'] = true;
        $result['reason'] = 'Suspicious keyword combination: ' . $combo_check['combination'];
        return $result;
    }
    
    return $result;
}

/* End of file blacklist-keywords.php */
/* Location: /oc-content/plugins/osc_bot_blocker/data/blacklist-keywords.php */
