<?php
/**
 * OSC Bot Blocker - Disposable Email Blacklist
 * 
 * List of disposable/temporary email service domains.
 * These are commonly used by spammers to create throwaway accounts.
 * 
 * @package OSCBotBlocker
 * @subpackage Data
 * @author Van Isle Web Solutions
 * @link https://www.vanislebc.com/
 * @version 1.2.3
 */

// Prevent direct access
if (!defined('ABS_PATH')) {
    header('HTTP/1.1 403 Forbidden');
    die('Direct access is not allowed.');
}

/**
 * Get list of disposable email domains
 * @return array Array of disposable email domains
 */
function oscbb_get_disposable_email_domains() {
    return array(
        // Common disposable email services
        '10minutemail.com',
        '10minutemail.net',
        '20minutemail.com',
        '2prong.com',
        '30minutemail.com',
        '33mail.com',
        'guerrillamail.com',
        'guerrillamail.net',
        'guerrillamail.org',
        'guerrillamail.biz',
        'guerrillamailblock.com',
        'sharklasers.com',
        'spam4.me',
        'grr.la',
        'guerrillamail.de',
        'mailinator.com',
        'mailinator.net',
        'mailinator2.com',
        'tempmail.com',
        'temp-mail.org',
        'temp-mail.io',
        'throwaway.email',
        'trashmail.com',
        'trashmail.net',
        'tempr.email',
        'fakeinbox.com',
        'getnada.com',
        'getairmail.com',
        'yopmail.com',
        'yopmail.fr',
        'cool.fr.nf',
        'jetable.fr.nf',
        'nospam.ze.tc',
        'nomail.xl.cx',
        'mega.zik.dj',
        'speed.1s.fr',
        'courriel.fr.nf',
        'moncourrier.fr.nf',
        'monemail.fr.nf',
        'monmail.fr.nf',
        'hidemail.de',
        'emailtemporario.com.br',
        'maildrop.cc',
        'mailnesia.com',
        'mailcatch.com',
        'mailtothis.com',
        'mytemp.email',
        'mytrashmail.com',
        'spamgourmet.com',
        'mintemail.com',
        'dispostable.com',
        'disposeamail.com',
        'discard.email',
        'discardmail.com',
        'discardmail.de',
        'spambog.com',
        'spambog.de',
        'spambog.ru',
        'spam.la',
        'spambox.us',
        'spamfree24.org',
        'spamfree24.de',
        'spamfree24.eu',
        'spamfree24.info',
        'spamfree24.net',
        'spamfree24.org',
        'spamoff.de',
        'fakemailgenerator.com',
        'anonbox.net',
        'anonymbox.com',
        'antichef.com',
        'binkmail.com',
        'bobmail.info',
        'bugmenot.com',
        'deadaddress.com',
        'despam.it',
        'despammed.com',
        'dontreg.com',
        'emailias.com',
        'emailwarden.com',
        'filzmail.com',
        'haltospam.com',
        'incognitomail.org',
        'klzlk.com',
        'mailexpire.com',
        'mailforspam.com',
        'mailfreeonline.com',
        'mailimate.com',
        'mailmetrash.com',
        'mailmoat.com',
        'mailnull.com',
        'mailsac.com',
        'mailshell.com',
        'mailzilla.com',
        'mt2009.com',
        'nobulk.com',
        'noclickemail.com',
        'nogmailspam.info',
        'notsharingmy.info',
        'nowmymail.com',
        'pookmail.com',
        'proxymail.eu',
        'putthisinyourspamdatabase.com',
        'rcpt.at',
        'recode.me',
        'recursor.net',
        'rtrtr.com',
        'safetymail.info',
        'selfdestructingmail.com',
        'sendspamhere.com',
        'shiftmail.com',
        'skeefmail.com',
        'slopsbox.com',
        'smellfear.com',
        'sneakemail.com',
        'sogetthis.com',
        'soodonims.com',
        'spam.su',
        'spamavert.com',
        'spambox.info',
        'spamcero.com',
        'spamcon.org',
        'spamcorptastic.com',
        'spamday.com',
        'spamex.com',
        'spamfree.eu',
        'spamherelots.com',
        'spamhereplease.com',
        'spamhole.com',
        'spamify.com',
        'spaminator.de',
        'spamkill.info',
        'spaml.com',
        'spaml.de',
        'spammotel.com',
        'spamobox.com',
        'spamspot.com',
        'spamthis.co.uk',
        'spamthisplease.com',
        'speed.1s.fr',
        'supergreatmail.com',
        'supermailer.jp',
        'tempemail.co.za',
        'tempemail.com',
        'tempemail.net',
        'tempinbox.co.uk',
        'tempinbox.com',
        'tempmail.eu',
        'tempmaildemo.com',
        'tempmailer.com',
        'tempmailer.de',
        'tempomail.fr',
        'temporarily.de',
        'temporarioemail.com.br',
        'temporaryemail.net',
        'temporaryforwarding.com',
        'temporaryinbox.com',
        'temporarymailaddress.com',
        'thanksnospam.info',
        'thankyou2010.com',
        'thisisnotmyrealemail.com',
        'throwawayemailaddress.com',
        'tilien.com',
        'tmailinator.com',
        'tradermail.info',
        'trash-amil.com',
        'trash-mail.at',
        'trash-mail.com',
        'trash-mail.de',
        'trash2009.com',
        'trashemail.de',
        'trashmail.at',
        'trashmail.me',
        'trashmail.ws',
        'trashymail.com',
        'trialmail.de',
        'twinmail.de',
        'uggsrock.com',
        'whatpayne.com',
        'whyspam.me',
        'willselfdestruct.com',
        'winemaven.info',
        'wronghead.com',
        'wuzupmail.net',
        'xagloo.com',
        'xemaps.com',
        'xents.com',
        'xmaily.com',
        'yuurok.com',
        'zehnminuten.de',
        'zippymail.info',
    );
}

/**
 * Get list of free email domains
 * These are legitimate but often abused by spammers
 * @return array Array of free email domains
 */
function oscbb_get_free_email_domains() {
    return array(
        // Major free email providers
        'gmail.com',
        'googlemail.com',
        'yahoo.com',
        'yahoo.co.uk',
        'yahoo.fr',
        'yahoo.de',
        'yahoo.es',
        'yahoo.it',
        'hotmail.com',
        'hotmail.co.uk',
        'hotmail.fr',
        'hotmail.de',
        'hotmail.es',
        'hotmail.it',
        'outlook.com',
        'live.com',
        'msn.com',
        'aol.com',
        'mail.com',
        'inbox.com',
        'icloud.com',
        'me.com',
        'mac.com',
        'protonmail.com',
        'protonmail.ch',
        'yandex.com',
        'yandex.ru',
        'mail.ru',
        'gmx.com',
        'gmx.de',
        'gmx.net',
        'web.de',
        'zoho.com',
        'tutanota.com',
        'fastmail.com',
    );
}

/**
 * Check if email is disposable
 * @param string $email Email address
 * @return bool True if disposable
 */
function oscbb_is_disposable_email($email) {
    if (empty($email)) {
        return false;
    }
    
    // Extract domain from email
    if (strpos($email, '@') === false) {
        return false;
    }
    
    list($local, $domain) = explode('@', $email);
    $domain = strtolower(trim($domain));
    
    // Check against disposable list
    $disposable_domains = oscbb_get_disposable_email_domains();
    
    return in_array($domain, $disposable_domains);
}

/**
 * Check if email is from free provider
 * @param string $email Email address
 * @return bool True if free provider
 */
function oscbb_is_free_email($email) {
    if (empty($email)) {
        return false;
    }
    
    // Extract domain from email
    if (strpos($email, '@') === false) {
        return false;
    }
    
    list($local, $domain) = explode('@', $email);
    $domain = strtolower(trim($domain));
    
    // Check against free provider list
    $free_domains = oscbb_get_free_email_domains();
    
    return in_array($domain, $free_domains);
}

/**
 * Validate email patterns for suspicious content
 * @param string $email Email address
 * @return array Result array with 'valid' (bool) and 'reason' (string)
 */
function oscbb_validate_email_patterns($email) {
    $result = array(
        'valid' => true,
        'reason' => ''
    );
    
    if (empty($email)) {
        $result['valid'] = false;
        $result['reason'] = 'Email is empty';
        return $result;
    }
    
    // Use osClass email validation first
    if (!osc_validate_email($email)) {
        $result['valid'] = false;
        $result['reason'] = 'Invalid email format';
        return $result;
    }
    
    // Extract parts
    list($local, $domain) = explode('@', $email);
    
    // Check for suspicious patterns in local part
    
    // Too many numbers (common in generated spam emails)
    $number_count = preg_match_all('/[0-9]/', $local);
    if (strlen($local) > 0 && ($number_count / strlen($local)) > 0.7) {
        $result['valid'] = false;
        $result['reason'] = 'Email contains excessive numbers';
        return $result;
    }
    
    // Suspicious character patterns
    if (preg_match('/[+]{2,}/', $local)) {
        $result['valid'] = false;
        $result['reason'] = 'Email contains suspicious character pattern';
        return $result;
    }
    
    // Very long local part (often spam)
    if (strlen($local) > 64) {
        $result['valid'] = false;
        $result['reason'] = 'Email local part too long';
        return $result;
    }
    
    // Check domain part
    
    // IP address as domain (suspicious)
    if (preg_match('/^\[?[0-9\.]+\]?$/', $domain)) {
        $result['valid'] = false;
        $result['reason'] = 'Email uses IP address as domain';
        return $result;
    }
    
    // Very short domain (suspicious)
    if (strlen($domain) < 4) {
        $result['valid'] = false;
        $result['reason'] = 'Email domain too short';
        return $result;
    }
    
    // All checks passed
    return $result;
}

/* End of file blacklist-emails.php */
/* Location: /oc-content/plugins/osc_bot_blocker/data/blacklist-emails.php */
