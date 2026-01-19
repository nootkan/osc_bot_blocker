<?php
/**
 * OSC Bot Blocker - IP Validator Class
 * 
 * Handles IP address validation, sanitization, and analysis.
 * Detects proxy headers, validates IP formats, and checks for suspicious IPs.
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

class IPValidator {
    
    /**
     * Validate if string is a valid IP address
     * @param string $ip IP address to validate
     * @param bool $allow_private Allow private/reserved IPs
     * @return bool True if valid
     */
    public static function isValid($ip, $allow_private = true) {
        if (empty($ip)) {
            return false;
        }
        
        // Check IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (!$allow_private) {
                // Check if it's a private/reserved IP
                if (self::isPrivateIP($ip)) {
                    return false;
                }
            }
            return true;
        }
        
        // Check IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            if (!$allow_private) {
                // Check if it's a private/reserved IP
                if (self::isPrivateIP($ip)) {
                    return false;
                }
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if IP is private or reserved
     * @param string $ip IP address
     * @return bool True if private/reserved
     */
    public static function isPrivateIP($ip) {
        if (empty($ip)) {
            return false;
        }
        
        // Check using PHP filter (checks for private and reserved ranges)
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Sanitize IP address
     * @param string $ip IP address to sanitize
     * @return string Sanitized IP or empty string if invalid
     */
    public static function sanitize($ip) {
        if (empty($ip)) {
            return '';
        }
        
        // Remove any whitespace
        $ip = trim($ip);
        
        // Remove any non-IP characters
        $ip = preg_replace('/[^0-9a-fA-F:.]/', '', $ip);
        
        // Validate the sanitized IP
        if (self::isValid($ip, true)) {
            return $ip;
        }
        
        return '';
    }
    
    /**
     * Get user's real IP address, checking proxy headers
     * @return string IP address
     */
    public static function getRealIP() {
        $ip = '';
        
        // Check for proxy headers in order of reliability
        $proxy_headers = array(
            'HTTP_CF_CONNECTING_IP',    // CloudFlare
            'HTTP_X_REAL_IP',           // Nginx proxy
            'HTTP_X_FORWARDED_FOR',     // Standard proxy header
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP', // Rackspace LB
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'               // Direct connection (most reliable)
        );
        
        foreach ($proxy_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // If multiple IPs (proxy chain), get the first one
                if (strpos($ip, ',') !== false) {
                    $ip_array = explode(',', $ip);
                    $ip = trim($ip_array[0]);
                }
                
                // Validate the IP
                $sanitized = self::sanitize($ip);
                if (!empty($sanitized)) {
                    return $sanitized;
                }
            }
        }
        
        return '';
    }
    
    /**
     * Check if request is behind a proxy
     * @return bool True if behind proxy
     */
    public static function isBehindProxy() {
        $proxy_headers = array(
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_VIA',
            'HTTP_X_COMING_FROM',
            'HTTP_COMING_FROM'
        );
        
        foreach ($proxy_headers as $header) {
            if (!empty($_SERVER[$header])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get all IPs from proxy chain
     * @return array Array of IPs
     */
    public static function getProxyChain() {
        $chain = array();
        
        // Check X-Forwarded-For header
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ips as $ip) {
                $sanitized = self::sanitize($ip);
                if (!empty($sanitized)) {
                    $chain[] = $sanitized;
                }
            }
        }
        
        // Add direct IP
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $sanitized = self::sanitize($_SERVER['REMOTE_ADDR']);
            if (!empty($sanitized) && !in_array($sanitized, $chain)) {
                $chain[] = $sanitized;
            }
        }
        
        return $chain;
    }
    
    /**
     * Check if IP is in a CIDR range
     * @param string $ip IP address to check
     * @param string $cidr CIDR range (e.g., "192.168.1.0/24")
     * @return bool True if in range
     */
    public static function isInCIDR($ip, $cidr) {
        if (empty($ip) || empty($cidr)) {
            return false;
        }
        
        // Split CIDR into IP and netmask
        list($subnet, $mask) = explode('/', $cidr);
        
        // Convert to long integers
        $ip_long = ip2long($ip);
        $subnet_long = ip2long($subnet);
        $mask_long = -1 << (32 - $mask);
        $subnet_long &= $mask_long;
        
        return ($ip_long & $mask_long) == $subnet_long;
    }
    
    /**
     * Check if IP is in a list of CIDR ranges
     * @param string $ip IP address
     * @param array $ranges Array of CIDR ranges
     * @return bool True if in any range
     */
    public static function isInRanges($ip, $ranges) {
        if (empty($ip) || empty($ranges)) {
            return false;
        }
        
        foreach ($ranges as $range) {
            if (self::isInCIDR($ip, $range)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get IP version (4 or 6)
     * @param string $ip IP address
     * @return int 4, 6, or 0 if invalid
     */
    public static function getVersion($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return 4;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 6;
        }
        return 0;
    }
    
    /**
     * Check if IP matches a pattern with wildcards
     * Supports patterns like: 192.168.*.*, 192.168.1.0-255
     * @param string $ip IP address
     * @param string $pattern IP pattern
     * @return bool True if matches
     */
    public static function matchesPattern($ip, $pattern) {
        if (empty($ip) || empty($pattern)) {
            return false;
        }
        
        // Split both into octets
        $ip_parts = explode('.', $ip);
        $pattern_parts = explode('.', $pattern);
        
        // Must have 4 parts for IPv4
        if (count($ip_parts) !== 4 || count($pattern_parts) !== 4) {
            return false;
        }
        
        // Check each octet
        for ($i = 0; $i < 4; $i++) {
            $ip_octet = (int)$ip_parts[$i];
            $pattern_octet = $pattern_parts[$i];
            
            // Wildcard matches anything
            if ($pattern_octet === '*') {
                continue;
            }
            
            // Check for range (e.g., "0-255")
            if (strpos($pattern_octet, '-') !== false) {
                list($min, $max) = explode('-', $pattern_octet);
                if ($ip_octet < (int)$min || $ip_octet > (int)$max) {
                    return false;
                }
                continue;
            }
            
            // Exact match
            if ($ip_octet !== (int)$pattern_octet) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get suspicious IP patterns (datacenter, proxy, VPN ranges)
     * These are common ranges used by spam bots
     * @return array Array of CIDR ranges
     */
    public static function getSuspiciousRanges() {
        return array(
            // Add known suspicious ranges here
            // This is a starting point - can be expanded
            
            // Example: Known proxy/VPN services
            // '5.8.0.0/16',        // Example proxy range
            // '45.9.0.0/16',       // Example VPN range
            
            // For now, return empty array
            // Admin can add ranges via settings in Phase 3
        );
    }
    
    /**
     * Log IP information for analysis
     * @param string $ip IP address
     * @return array IP information
     */
    public static function analyzeIP($ip) {
        $info = array(
            'ip' => $ip,
            'valid' => self::isValid($ip),
            'private' => self::isPrivateIP($ip),
            'version' => self::getVersion($ip),
            'behind_proxy' => self::isBehindProxy(),
            'proxy_chain' => self::getProxyChain(),
        );
        
        return $info;
    }
}
