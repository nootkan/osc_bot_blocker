/**
 * OSC Bot Blocker - JavaScript Bot Detection
 * 
 * Generates authentication tokens to prove a real browser is submitting forms.
 * Includes timestamp validation and basic browser fingerprinting.
 * 
 * @package OSCBotBlocker
 * @subpackage JavaScript
 * @author Van Isle Web Solutions
 * @link https://www.vanislebc.com/
 * @version 1.2.1
 */

(function() {
    'use strict';
    
    /**
     * Generate a simple hash from a string
     * @param {string} str String to hash
     * @return {string} Hash value
     */
    function simpleHash(str) {
        var hash = 0;
        if (str.length === 0) return hash.toString();
        
        for (var i = 0; i < str.length; i++) {
            var char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32bit integer
        }
        
        return Math.abs(hash).toString(36);
    }
    
    /**
     * Generate browser fingerprint
     * Collects basic browser information to create a unique identifier
     * @return {string} Fingerprint string
     */
    function generateFingerprint() {
        var fingerprint = [];
        
        // User Agent
        fingerprint.push(navigator.userAgent || 'unknown');
        
        // Language
        fingerprint.push(navigator.language || navigator.userLanguage || 'unknown');
        
        // Screen resolution
        fingerprint.push(screen.width + 'x' + screen.height);
        
        // Color depth
        fingerprint.push(screen.colorDepth || 'unknown');
        
        // Timezone offset
        fingerprint.push(new Date().getTimezoneOffset());
        
        // Platform
        fingerprint.push(navigator.platform || 'unknown');
        
        // Plugins count (if available)
        if (navigator.plugins) {
            fingerprint.push(navigator.plugins.length);
        }
        
        // Cookies enabled
        fingerprint.push(navigator.cookieEnabled ? '1' : '0');
        
        // Java enabled (if available)
        if (typeof navigator.javaEnabled === 'function') {
            fingerprint.push(navigator.javaEnabled() ? '1' : '0');
        }
        
        // Do Not Track
        fingerprint.push(navigator.doNotTrack || window.doNotTrack || navigator.msDoNotTrack || 'unknown');
        
        return fingerprint.join('|');
    }
    
    /**
     * Generate authentication token
     * @return {object} Token data
     */
    function generateToken() {
        var timestamp = new Date().getTime();
        var fingerprint = generateFingerprint();
        var random = Math.random().toString(36).substring(2, 15);
        
        // Create token from timestamp + fingerprint + random
        var tokenString = timestamp + '|' + fingerprint + '|' + random;
        var token = simpleHash(tokenString);
        
        return {
            token: token,
            timestamp: timestamp,
            fingerprint: simpleHash(fingerprint)
        };
    }
    
    /**
     * Collect browser capability checks
     * @return {object} Browser capabilities
     */
    function getBrowserChecks() {
        var checks = {
            cookies: navigator.cookieEnabled ? '1' : '0',
            screen: screen.width + 'x' + screen.height,
            timezone: new Date().getTimezoneOffset().toString()
        };
        
        // Check for Java
        if (typeof navigator.javaEnabled === 'function') {
            checks.java = navigator.javaEnabled() ? '1' : '0';
        }
        
        // Check for localStorage
        try {
            checks.localStorage = typeof(Storage) !== 'undefined' ? '1' : '0';
        } catch(e) {
            checks.localStorage = '0';
        }
        
        // Check for sessionStorage
        try {
            checks.sessionStorage = typeof(sessionStorage) !== 'undefined' ? '1' : '0';
        } catch(e) {
            checks.sessionStorage = '0';
        }
        
        return checks;
    }
    
    /**
     * Create hidden input field
     * @param {string} name Field name
     * @param {string} value Field value
     * @return {HTMLElement} Input element
     */
    function createHiddenField(name, value) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    }
    
    /**
     * Inject token fields into a form
     * @param {HTMLFormElement} form Form element
     */
    function injectTokenFields(form) {
        // Check if already injected
        if (form.querySelector('input[name="oscbb_token"]')) {
            return;
        }
        
        // Generate token data
        var tokenData = generateToken();
        var browserChecks = getBrowserChecks();
        
        // Create and append hidden fields
        form.appendChild(createHiddenField('oscbb_token', tokenData.token));
        form.appendChild(createHiddenField('oscbb_timestamp', tokenData.timestamp));
        form.appendChild(createHiddenField('oscbb_fingerprint', tokenData.fingerprint));
        form.appendChild(createHiddenField('oscbb_checks', JSON.stringify(browserChecks)));
        
        // Add a flag to indicate JavaScript is enabled
        form.appendChild(createHiddenField('oscbb_js_enabled', '1'));
    }
    
    /**
     * Process all forms on the page
     */
    function processForms() {
        var forms = document.getElementsByTagName('form');
        
        for (var i = 0; i < forms.length; i++) {
            var form = forms[i];
            
            // Skip forms that shouldn't be protected
            // (e.g., search forms, login forms)
            if (shouldProtectForm(form)) {
                injectTokenFields(form);
            }
        }
    }
    
    /**
     * Determine if a form should be protected
     * @param {HTMLFormElement} form Form element
     * @return {boolean} True if should be protected
     */
    function shouldProtectForm(form) {
        var formId = form.id || '';
        var formClass = form.className || '';
        var formAction = form.action || '';
        
        // Skip search forms
        if (formId.indexOf('search') !== -1 || formClass.indexOf('search') !== -1) {
            return false;
        }
        
        // Skip login forms
        if (formId.indexOf('login') !== -1 || formClass.indexOf('login') !== -1) {
            return false;
        }
        
        // Skip very simple forms (likely search or filters)
        var inputs = form.querySelectorAll('input, textarea, select');
        if (inputs.length < 2) {
            return false;
        }
        
        // Protect forms that are likely submission forms
        // Look for email fields, comment fields, item posting fields, etc.
        var hasEmail = form.querySelector('input[type="email"], input[name*="email"]');
        var hasTextarea = form.querySelector('textarea');
        var hasTitle = form.querySelector('input[name*="title"]');
        var hasComment = form.querySelector('textarea[name*="comment"]');
        
        if (hasEmail || hasTextarea || hasTitle || hasComment) {
            return true;
        }
        
        // Check for common osClass form actions
        if (formAction.indexOf('item') !== -1 || 
            formAction.indexOf('contact') !== -1 || 
            formAction.indexOf('register') !== -1 || 
            formAction.indexOf('comment') !== -1) {
            return true;
        }
        
        // Default to not protecting if unsure
        return false;
    }
    
    /**
     * Set a test cookie
     * Used to verify browser accepts cookies
     */
    function setTestCookie() {
        var cookieName = 'oscbb_test';
        var cookieValue = new Date().getTime().toString();
        var expiryDate = new Date();
        expiryDate.setTime(expiryDate.getTime() + (24 * 60 * 60 * 1000)); // 24 hours
        
        document.cookie = cookieName + '=' + cookieValue + '; expires=' + expiryDate.toUTCString() + '; path=/; Secure; SameSite=Lax';
    }
    
    /**
     * Initialize bot detection
     * Called when DOM is ready
     */
    function init() {
        // Set test cookie
        setTestCookie();
        
        // Process forms immediately
        processForms();
        
        // Watch for dynamically added forms
        // (in case forms are loaded via AJAX)
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                        for (var i = 0; i < mutation.addedNodes.length; i++) {
                            var node = mutation.addedNodes[i];
                            if (node.tagName === 'FORM') {
                                if (shouldProtectForm(node)) {
                                    injectTokenFields(node);
                                }
                            } else if (node.querySelectorAll) {
                                var forms = node.querySelectorAll('form');
                                for (var j = 0; j < forms.length; j++) {
                                    if (shouldProtectForm(forms[j])) {
                                        injectTokenFields(forms[j]);
                                    }
                                }
                            }
                        }
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM is already ready
        init();
    }
    
})();
