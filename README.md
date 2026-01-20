# OSC Bot Blocker

**Version:** 1.2.3  
**Author:** Van Isle Web Solutions  
**Website:** https://www.vanislebc.com/  
**Requires:** osClass enterprise 3.10.4 or osClass 8.2.1+  
**License:** GPL3

---

## 📋 Table of Contents

- [Description](#description)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Upgrade Instructions](#upgrade-instructions)
- [File Structure](#file-structure)
- [How It Works](#how-it-works)
- [Configuration](#configuration)
- [Database Tables](#database-tables)
- [Protection Layers](#protection-layers)
- [Dashboard Widget](#dashboard-widget)
- [Using the Admin Interface](#using-the-admin-interface)
- [Troubleshooting](#troubleshooting)
- [Version History](#version-history)
- [Credits](#credits)
- [License](#license)

---

## 📖 Description

**OSC Bot Blocker** is an advanced anti-spam and bot protection plugin for osClass. It provides enterprise-grade protection against spam submissions in items, contact forms, user registrations, and comments **without requiring CAPTCHAs or challenge questions**.

The plugin uses multiple layers of validation to detect and block automated bots while remaining completely invisible and hassle-free for legitimate human users.

---

## ✨ Features

### **Phase 1 Features (v1.0.0)**

#### **Core Bot Detection:**
- ✅ **JavaScript Token Validation** - Cryptographic tokens prove browser executed JavaScript
- ✅ **Browser Fingerprinting** - Collects browser characteristics for validation
- ✅ **Honeypot Fields** - Invisible fields that catch automated bots
- ✅ **Session Token Management** - Prevents replay attacks with one-time-use tokens
- ✅ **Time-Based Validation** - Dual-layer timing checks (JavaScript + Session)

#### **Advanced Validation:**
- ✅ **User-Agent Blacklist** - Blocks 100+ known spam bots and scrapers
- ✅ **User-Agent Whitelist** - Allows legitimate bots (Google, Bing, etc.)
- ✅ **IP Address Validation** - Enhanced IPv4/IPv6 validation with proxy detection
- ✅ **HTTP Referer Checking** - Ensures submissions come from your site
- ✅ **Cookie Testing** - Verifies browser accepts cookies

#### **System Features:**
- ✅ **Database Logging** - Comprehensive logging of all blocks and attempts
- ✅ **Statistics Tracking** - Daily statistics by block type
- ✅ **Automatic Cleanup** - Removes old logs based on retention settings
- ✅ **Debug Mode** - Detailed logging for troubleshooting
- ✅ **Admin Whitelist** - Logged-in admins bypass all checks

### **Phase 2 Features (v1.1.1)**

#### **Email Protection:**
- ✅ **Disposable Email Blocking** - Blocks 200+ temporary email services
- ✅ **Free Email Blocking** - Optional blocking of 35+ free providers (Gmail, Yahoo, etc.)
- ✅ **Email Pattern Validation** - Detects suspicious email patterns
- ✅ **Domain Validation** - IP addresses, short domains, invalid formats

#### **Content Filtering:**
- ✅ **URL Analysis** - Counts and validates URLs in content
- ✅ **URL Limit Enforcement** - Configurable maximum URLs (default: 3)
- ✅ **Obfuscated URL Detection** - IP URLs, hex encoding, shorteners, phishing patterns
- ✅ **Suspicious TLD Blocking** - Blocks free/spam TLDs (.tk, .ml, .ga, etc.)
- ✅ **Keyword Filtering** - 100+ spam keywords with sensitivity levels
- ✅ **Keyword Combinations** - Detects suspicious word pairs
- ✅ **Special Character Analysis** - Flags excessive symbols
- ✅ **Repetition Detection** - Catches repeated characters/words
- ✅ **All-Caps Detection** - Blocks SHOUTING spam
- ✅ **Character Encoding Validation** - UTF-8 verification, control character detection

#### **Advanced Protection:**
- ✅ **Form Field Obfuscation** - Daily rotating field names
- ✅ **Request Method Validation** - POST-only enforcement
- ✅ **Content-Type Validation** - Proper header checking
- ✅ **Rate Limiting** - 5 submissions per hour per IP (configurable)
- ✅ **Duplicate Content Detection** - MD5 hashing prevents resubmissions
- ✅ **Session-Based Tracking** - Tracks last 5 submissions per user

#### **Protection Points:**
- ✅ Item/Listing Posting
- ✅ Contact Forms
- ✅ User Registration
- ✅ Comment Submissions

### **Phase 3 Features (v1.2.0)**

#### **Complete Admin Interface:**
- ✅ **7-Tab Navigation System** - Organized, intuitive interface
- ✅ **Professional Dashboard** - Clean, responsive design
- ✅ **Settings Management** - Full control over all features
- ✅ **Real-Time Statistics** - Live data visualization

#### **General Settings Tab:**
- ✅ **Plugin On/Off Switch** - Master enable/disable
- ✅ **Protection Level Selector** - Low/Medium/High modes
- ✅ **Logging Controls** - Enable/disable database logging
- ✅ **Log Retention** - Configurable retention period (1-365 days)
- ✅ **Debug Mode Status** - Shows current debug state
- ✅ **Plugin Information** - Version, layers, protected forms

#### **Protection Settings Tab:**
- ✅ **JavaScript Configuration** - Enable/disable with timing controls
- ✅ **Honeypot Controls** - Toggle 4 invisible traps
- ✅ **User-Agent Settings** - 100+ bot blacklist toggle
- ✅ **Referer Checking** - Domain verification on/off
- ✅ **Cookie Testing** - Browser cookie validation
- ✅ **Rate Limiting** - Configurable limits (1-100 per hour)
- ✅ **Active Layers Display** - Shows currently enabled protections

#### **Content Filtering Tab:**
- ✅ **URL Limit Control** - Set maximum URLs (0-50)
- ✅ **Keyword Filter Toggle** - Enable/disable 100+ spam keywords
- ✅ **Disposable Email Toggle** - Block 200+ temporary services
- ✅ **Free Email Toggle** - Optional Gmail/Yahoo blocking

#### **Statistics Dashboard:**
- ✅ **Summary Cards** - Today, 7 days, 30 days, total blocks
- ✅ **Block Types Chart** - Breakdown by protection layer
- ✅ **Top Blocked IPs** - Repeat offenders list
- ✅ **Daily Activity Chart** - Visual 30-day bar chart
- ✅ **Recent Blocks Table** - Last 20 blocked submissions

#### **Log Viewer:**
- ✅ **Advanced Search** - Search by IP, email, or reason
- ✅ **Multiple Filters** - Type, form, date range
- ✅ **Pagination System** - 10 logs per page
- ✅ **Sortable Columns** - Date, IP, type, form, reason
- ✅ **Detailed View** - Full information for each block
- ✅ **CSV Export** - Download all logs

#### **Whitelist Management:**
- ✅ **Add IP/Email Whitelist** - Bypass all checks
- ✅ **View All Entries** - Complete whitelist table
- ✅ **Remove Entries** - One-click removal
- ✅ **Format Validation** - Validates IPs and emails
- ✅ **Auto Admin Whitelist** - Logged-in admins auto-whitelisted

#### **Blacklist Management:**
- ✅ **Custom Blacklist** - Add IP/email/keyword blocks
- ✅ **Enable/Disable Toggle** - Temporarily disable without deleting
- ✅ **Delete Entries** - Permanent removal
- ✅ **Reason Tracking** - Optional notes for each entry
- ✅ **Status Indicators** - Visual active/disabled states

### **Phase 3.5 Features (v1.2.1)**

#### **Dashboard Widget:**
- ✅ **Recent Activity Display** - Shows last 6 bot blocks
- ✅ **Summary Statistics** - 24h, 7 days, total blocks
- ✅ **Colored Status Indicators** - Visual block type identification
- ✅ **Quick Access Links** - Direct links to Logs and Statistics tabs
- ✅ **Cross-Theme Compatibility** - Works in both Omega (8.2.1) and Modern (3.10.4) admin themes
- ✅ **Smart Placement** - Appears at bottom of dashboard above footer

### **Phase 3.6 Features (v1.2.2 & v1.2.3 - Current)**

#### **Enhanced Spam Detection:**
- ✅ **Random Character Detection** - Catches gibberish names/subjects (e.g., "XXEScFiorLkuFsZwrIGtb")
- ✅ **Suspicious Gmail Patterns** - Detects obfuscated addresses with excessive dots (4+ dots)
- ✅ **Multiple Space Detection** - Flags excessive spacing in content (3+ consecutive spaces)
- ✅ **Gibberish Message Detection** - Identifies random character messages
- ✅ **Form Field Validation** - Comprehensive analysis of name, email, subject, and message fields
- ✅ **Vowel Ratio Analysis** - Detects words with abnormally low vowel count
- ✅ **Case Change Detection** - Identifies alternating uppercase/lowercase patterns

#### **Security Enhancements:**
- ✅ **Secure Error Handling** - No sensitive information exposure in browser output
- ✅ **Cryptographically Secure Tokens** - Uses random_bytes() for CSRF protection (PHP 7+)
- ✅ **Secure Cookie Attributes** - Automatic HTTPS detection for secure flag
- ✅ **Server-Side Error Logging** - All errors logged securely to server error log only
- ✅ **Debug Mode Protection** - Sensitive info never displayed in browser, even in debug mode
- ✅ **SHA-256 Fallback** - Uses SHA-256 instead of MD5 for legacy PHP support

#### **Bug Fixes:**
- ✅ **Database Query Fix** - Corrected affected_rows property access
- ✅ **Error Suppression** - Proper error handling prevents fatal errors during cron jobs
- ✅ **Snyk Compliance** - All security vulnerabilities resolved

---

## 🔧 Requirements

### **Server Requirements:**
- **PHP:** 7.1 or higher (PHP 7.4+ recommended for best security)
- **MySQL:** 5.5 or higher
- **osClass:** Enterprise 3.10.4 or osClass 8.2.1+

### **PHP Extensions Required:**
- `json` - For browser checks encoding
- `hash` - For token generation
- `session` - For session management
- `filter` - For IP validation

### **Browser Requirements (Users):**
- JavaScript enabled (recommended but not required)
- Cookies enabled (recommended but not required)

**Note:** The plugin gracefully degrades when JavaScript or cookies are disabled, using fallback validation methods.

---

## 📥 Installation

### **Step 1: Download Plugin**
Download the `osc_bot_blocker` plugin package (ZIP file).

### **Step 2: Upload Files**
Upload the entire `osc_bot_blocker` folder to:
```
/oc-content/plugins/
```

Your structure should look like:
```
/oc-content/plugins/osc_bot_blocker/
├── index.php
├── admin.php
├── includes/
│   ├── OSCBotBlocker.class.php
│   ├── IPValidator.class.php
│   └── ContentFilter.class.php
├── admin/
│   └── OSCBBAdmin.class.php
├── js/
│   └── oscbb.js
├── data/
│   ├── blacklist-useragents.php
│   ├── blacklist-emails.php
│   └── blacklist-keywords.php
└── README.md
```

### **Step 3: Activate Plugin**
1. Log into your osClass admin panel
2. Go to **Plugins** → **Manage Plugins**
3. Find "OSC Bot Blocker" in the list
4. Click **Install** or **Activate**

### **Step 4: Verify Installation**
The plugin will automatically:
- ✅ Create 3 database tables (`oc_t_oscbb_log`, `oc_t_oscbb_stats`, `oc_t_oscbb_blacklist`)
- ✅ Set default preferences
- ✅ Show success message

### **Step 5: Test Protection**
Try posting a test item or submitting a contact form. Protection is now active!

---

## 📄 Upgrade Instructions

### **From v1.2.0/v1.2.1/v1.2.2 to v1.2.3:**
1. **Backup** your current plugin files and database
2. **Deactivate** the plugin (do NOT uninstall - keeps your data)
3. **Replace** plugin files with new version
4. **Reactivate** the plugin
5. Clear browser cache (Ctrl+Shift+Delete)
6. Visit admin dashboard to see updated widget
7. Test contact form submissions

### **Important Notes:**
- Deactivation preserves all logs, statistics, and settings
- Uninstallation removes data (unless "Keep Data" option is set)
- New features activate automatically upon reactivation
- Dashboard widget works in both Omega and Modern admin themes

---

## 📁 File Structure

```
osc_bot_blocker/
│
├── index.php                           # Main plugin file, initialization
├── admin.php                           # Admin interface entry point
│
├── includes/                           # PHP Classes
│   ├── OSCBotBlocker.class.php        # Core plugin class (singleton)
│   ├── IPValidator.class.php          # IP validation & analysis class
│   └── ContentFilter.class.php        # Content analysis & filtering class
│
├── admin/                              # Admin Interface
│   └── OSCBBAdmin.class.php           # Admin controller with 7 tabs
│
├── js/                                 # JavaScript Files
│   └── oscbb.js                       # Client-side bot detection
│
├── data/                               # Data Files
│   ├── blacklist-useragents.php       # User-Agent blacklist database
│   ├── blacklist-emails.php           # Email blacklist database
│   └── blacklist-keywords.php         # Keyword blacklist database
│
└── README.md                           # This file
```

### **Total Files: 11**
- **3 Core Files** (index.php, admin.php, README.md)
- **3 Class Files** (OSCBotBlocker, IPValidator, ContentFilter)
- **1 Admin File** (OSCBBAdmin - 7-tab interface)
- **3 Data Files** (User-Agents, Emails, Keywords)
- **1 JavaScript File** (oscbb.js)

---

## ⚙️ How It Works

### **Protection Flow:**

```
User loads form (item post, contact, register, comment)
    ↓
Plugin injects protection:
  - JavaScript token generation
  - Hidden honeypot fields
  - Session token
  - Form load timestamp
    ↓
User fills form and submits
    ↓
Plugin validates submission through 27 layers:
  1. Form Field Validation (name, email, subject, message)
  2. Session Token (replay attack prevention)
  3. JavaScript Token (bot detection)
  4. Honeypot Fields (automated bot detection)
  5. User-Agent (known bot blacklist)
  6. Referer Header (external submission blocking)
  7. Cookie Test (browser validation)
  8. Time Validation (too fast = bot)
  9-27. Additional validation layers...
    ↓
ALL checks pass? → ✅ Allow submission
ANY check fails? → ❌ Block + Log + Redirect with error
```

### **Multi-Layer Defense Philosophy:**

The plugin uses a **defense-in-depth strategy**:

1. **Layer 1: Bot Blocker** (Form-level) - Blocks 99.9% of automated bots
2. **Layer 2: SpamAssassin** (Email-level) - Catches email-based spam patterns
3. **Layer 3: Manual Review** - Admin reviews anything that slips through

This multi-layer approach ensures maximum spam protection while minimizing false positives.

---

## 🎛️ Configuration

### **Admin Interface:**

All settings are manageable through the comprehensive admin interface:

**Access:** Plugins → Bot Blocker (in osClass admin)

**7 Tabs Available:**
1. **General** - Plugin status, protection level, logging
2. **Protection** - JavaScript, honeypot, validation controls
3. **Content Filtering** - URLs, keywords, email settings
4. **Statistics** - Dashboard with charts and reports
5. **Logs** - Search and filter blocked submissions
6. **Whitelist** - Manage trusted IPs and emails
7. **Blacklist** - Manage custom blocks

### **Recommended Settings for New Sites:**

```
General:
- Plugin: Enabled
- Protection Level: Medium
- Logging: Enabled
- Log Retention: 30 days

Protection:
- JavaScript: Enabled
- Min Submit Time: 3 seconds
- Honeypot: Enabled
- User-Agent: Enabled
- Rate Limiting: 5 per hour

Content Filtering:
- Max URLs: 3
- Keyword Filter: Enabled
- Disposable Emails: Blocked
- Free Emails: Not Blocked (important!)
```

---

## 🗄️ Database Tables

The plugin creates 3 database tables (prefix: `oc_t_`):

### **1. oscbb_log**
Logs all blocked submissions and events.

**Columns:**
- `pk_i_id` - Primary key
- `dt_date` - Date/time of event
- `s_ip` - User's IP address
- `s_user_agent` - Browser User-Agent
- `s_type` - Block type (bot, spam, honeypot, javascript, rate_limit, content, other)
- `s_reason` - Detailed reason for block
- `s_form_type` - Form type (item, contact, register, comment, other)
- `s_email` - Email address (if available)
- `s_blocked` - Whether blocked (1) or just logged (0)

### **2. oscbb_stats**
Daily statistics summary.

**Columns:**
- `pk_i_id` - Primary key
- `dt_date` - Date (unique)
- `i_total_blocks` - Total blocks for the day
- `i_bot_blocks` - Bot-specific blocks
- `i_spam_blocks` - Spam blocks
- `i_honeypot_blocks` - Honeypot catches
- `i_javascript_blocks` - JavaScript validation failures
- `i_rate_limit_blocks` - Rate limit blocks
- `i_content_blocks` - Content filter blocks

### **3. oscbb_blacklist**
Custom IP/email/domain/keyword blacklist.

**Columns:**
- `pk_i_id` - Primary key
- `s_type` - Type (ip, email, domain, keyword)
- `s_value` - Blacklist value
- `dt_added` - Date added
- `s_reason` - Reason for blacklist
- `b_active` - Active status

---

## 🛡️ Protection Layers

### **Complete 27-Layer Protection System:**

#### **Phase 1 Layers (1-12) - Core Bot Detection:**
1. **Session Token** - Unique one-time tokens prevent replay attacks
2. **JavaScript Token** - Cryptographic tokens with timestamps
3. **Browser Fingerprint** - Device/browser characteristic validation
4. **Honeypot Fields** - 4 invisible fields catch automated bots
5. **User-Agent Blacklist** - Blocks 100+ known spam bots
6. **User-Agent Whitelist** - Allows legitimate bots (Google, Bing)
7. **IP Validation** - IPv4/IPv6 format validation & proxy detection
8. **HTTP Referer** - Ensures submission from your domain
9. **Cookie Testing** - Verifies browser cookie support
10. **Time Validation** - Dual-layer (JavaScript + Session) timing checks
11. **Request Method** - POST-only enforcement
12. **Admin Whitelist** - Admins bypass all checks

#### **Phase 2 Layers (13-22) - Content Filtering:**
13. **Email Validation** - Pattern checking & format validation
14. **Disposable Emails** - Blocks 200+ temporary email services
15. **URL Analysis** - Counts URLs, max limit enforcement (default: 3)
16. **URL Obfuscation** - Detects IP URLs, hex encoding, shorteners, phishing
17. **Keyword Filtering** - 100+ spam keywords with sensitivity levels
18. **Keyword Combinations** - Detects suspicious word pairs
19. **Field Obfuscation** - Daily rotating field names
20. **Character Encoding** - UTF-8 validation, control character detection
21. **Rate Limiting** - 5 submissions per hour per IP
22. **Duplicate Detection** - MD5 hashing prevents resubmissions

#### **Phase 3.6 Layers (23-27) - Enhanced Spam Detection:**
23. **Random Character Detection** - Catches gibberish names/subjects (mixed case patterns)
24. **Suspicious Gmail Patterns** - Detects obfuscated Gmail addresses (4+ dots, dot+number combos)
25. **Multiple Space Detection** - Flags excessive spacing (3+ consecutive spaces)
26. **Gibberish Message Detection** - Identifies random character content
27. **Form Field Validation** - Comprehensive name, email, subject, message analysis

### **Additional Protections:**
- **Special Characters** - Flags excessive symbols (>30%)
- **Repetition Detection** - Catches repeated chars/words
- **All-Caps Detection** - Blocks SHOUTING (>70% uppercase)
- **Suspicious TLDs** - Blocks free/spam domains (.tk, .ml, etc.)
- **Content-Type Validation** - Proper HTTP header checking
- **Vowel Ratio Analysis** - Detects abnormally low vowel counts (<20%)
- **Case Change Detection** - Identifies alternating case patterns (>40%)

### **What Gets Blocked:**

#### **Automated Bots:**
- Comment spambots
- Registration bots
- Contact form bots
- Content scrapers
- Email harvesters
- Auto-posting tools (XRumer, SEnuke, etc.)

#### **Human Spammers (New in v1.2.2/v1.2.3):**
- Random character names (e.g., "XXEScFiorLkuFsZwrIGtb")
- Obfuscated Gmail addresses (e.g., "t.eka.l.udag6.41@gmail.com")
- Gibberish messages (e.g., "pRYOONQQvytDyHcAoUFXNNVt")
- Multiple space spam (e.g., "wrote about   the price")

### **What Doesn't Get Blocked:**

#### **Legitimate Users:**
- ✅ Normal form submissions
- ✅ Users with JavaScript disabled (fallback validation)
- ✅ Users with strict privacy settings
- ✅ Users behind proxies/VPNs (logged but allowed)
- ✅ Legitimate search engine bots (whitelisted)
- ✅ Gmail users with normal addresses (numbers at end are OK)

#### **Admin Users:**
- ✅ Logged-in admins bypass all checks
- ✅ Full access for testing and posting

---

## 📊 Dashboard Widget

### **Features:**
The dashboard widget provides at-a-glance spam protection monitoring directly on your admin dashboard.

#### **Summary Statistics:**
- **24 Hours:** Blocks in last 24 hours
- **7 Days:** Blocks in last 7 days
- **Total:** All-time blocks

#### **Recent Activity:**
- Shows last 6 blocked spam attempts
- Displays date/time of each block
- Shows form type (item, contact, register, comment)
- Truncated IP address for privacy
- Block reason on hover

#### **Block Type Indicators:**
- 🔴 **Red (Spam)** - Bot/Spam detected, content filter
- 🔵 **Blue (Inactive)** - JavaScript validation failed
- 🟡 **Yellow (Moderation)** - Rate limit exceeded
- ⚫ **Black (Blocked)** - Honeypot triggered

#### **Quick Actions:**
- **View All Logs** - Direct link to full log viewer
- **View Statistics** - Direct link to statistics dashboard

#### **Compatibility:**
- ✅ Works in **Omega** admin theme (osClass 8.2.1)
- ✅ Works in **Modern** admin theme (Enterprise 3.10.4)
- ✅ Automatically detects theme and uses appropriate hooks
- ✅ Appears at bottom of dashboard above footer

---

## 🎛️ Using the Admin Interface

### **Quick Start Guide:**

#### **1. General Settings Tab**
- Check that plugin is **Enabled** (green toggle)
- Set **Protection Level** to "Medium" (recommended)
- Enable **Logging** to track blocks
- Set **Log Retention** to 30 days
- Review **Protection Statistics** summary

#### **2. Protection Settings Tab**
- Enable **JavaScript Validation** (recommended)
- Set **Min Submit Time** to 3 seconds
- Enable **Honeypot Fields** (highly effective)
- Enable **User-Agent Blacklist**
- Enable **Rate Limiting** (5 per hour recommended)
- Review **Active Protection Layers** list

#### **3. Content Filtering Tab**
- Set **Max URLs** to 3 (or 0 for no URLs)
- Enable **Keyword Filter** (100+ spam keywords)
- Enable **Disposable Email Blocking**
- Keep **Free Email Blocking** OFF (unless needed)

#### **4. Statistics Dashboard**
- View real-time block counts
- Check **Block Types** to see what's catching spam
- Review **Top Blocked IPs** for repeat offenders
- Monitor **Daily Activity Chart**
- Check **Recent Blocks** for latest activity

#### **5. Log Viewer**
- Search by IP, email, or reason
- Filter by block type or form type
- Set date range to narrow results
- View 10 logs per page
- Download CSV export for analysis

#### **6. Whitelist Management**
- Add your own IP address (so you're never blocked)
- Add trusted user emails
- Remove entries when no longer needed
- Admin users are automatically whitelisted

#### **7. Blacklist Management**
- Add custom IPs to block
- Add spam email addresses
- Add custom spam keywords
- Enable/disable entries without deleting
- Add notes explaining why you blocked something

### **Monitoring Your Protection:**

**Daily:**
- Check Dashboard Widget for block counts
- Review Recent Blocks for unusual activity

**Weekly:**
- Check Statistics Dashboard
- Review Log Viewer for patterns
- Adjust settings if needed

**Monthly:**
- Clean old logs (automatic if retention set)
- Review protection effectiveness
- Fine-tune settings based on data

---

## 🔧 Troubleshooting

### **Issue: Legitimate users getting blocked**

**Solutions:**
1. Check Logs tab to see exact reason
2. Add user to Whitelist (IP or email)
3. Adjust protection level to "Low"
4. Review specific check that failed
5. If Gmail user blocked, verify they don't have 4+ dots in address

### **Issue: Spam still getting through**

**Solutions:**
1. Enable more protection layers
2. Lower URL limit (3 → 1)
3. Add custom keywords to blacklist
4. Add spam email patterns to blacklist
5. Review logs for patterns
6. Remember: SpamAssassin catches email-level spam as second layer

### **Issue: Dashboard widget not showing**

**Solutions:**
1. Verify plugin is enabled
2. Clear browser cache (Ctrl+Shift+Delete)
3. Check that logging is enabled in General Settings
4. Verify you're using Omega or Modern admin theme
5. Check for JavaScript errors in browser console

### **Issue: Forms not submitting at all**

**Solutions:**
1. Check for JavaScript errors in browser console
2. Verify session is working
3. Check if cookies are being set
4. Temporarily disable plugin to isolate issue
5. Check for conflicts with other plugins

### **Issue: Admin can't post items**

**Note:** Admins should be automatically whitelisted.

**Solutions:**
1. Verify you're logged in as admin
2. Check Logs to see why admin is being blocked
3. Add your IP to whitelist manually
4. Contact support with log details

### **Debug Mode:**

Enable detailed logging:
```php
// In index.php, change:
define('OSCBB_DEBUG', false);
// To:
define('OSCBB_DEBUG', true);
```

Debug messages will appear in your server's error log (usually `/error_log` or `/logs/error_log`).

**Important:** Debug mode does NOT expose sensitive information in browser - all debug output goes to server error log only.

---

## 📜 Version History

### **Version 1.2.3** (Current)
**Release Date:** January 19, 2026

**Added - Enhanced Spam Detection:**
- ✅ Random character detection in names/subjects (catches gibberish like "XXEScFiorLkuFsZwrIGtb")
- ✅ Suspicious Gmail pattern detection (4+ dots or dot+number combos)
- ✅ Multiple consecutive space detection (3+ spaces)
- ✅ Gibberish message detection (random character content)
- ✅ Comprehensive form field validation system
- ✅ Vowel ratio analysis (detects words with <20% vowels)
- ✅ Case change detection (>40% alternating case = suspicious)

**Added - Security Enhancements:**
- ✅ Secure error handling (no browser exposure, even in debug mode)
- ✅ Cryptographically secure CSRF tokens (random_bytes for PHP 7+)
- ✅ SHA-256 fallback for older PHP versions (instead of MD5)
- ✅ Automatic secure cookie flag for HTTPS sites
- ✅ Server-side only error logging (all errors to server log)
- ✅ Protected debug mode (details never shown in browser)
- ✅ Snyk security compliance (all vulnerabilities resolved)

**Fixed - Bug Fixes:**
- ✅ Database query fix (affected_rows property access)
- ✅ Fatal error prevention during cron jobs
- ✅ Proper error suppression in cleanup tasks
- ✅ Syntax error fixes in validation methods

**Files Modified:**
- `admin.php` - Secure error handling
- `includes/OSCBotBlocker.class.php` - Field validation, secure cookies, bug fixes
- `includes/ContentFilter.class.php` - New spam detection methods

**Protection Layers:** 22 → 27 (5 new layers)

**Database:**
- No schema changes
- Enhanced logging for new block types

### **Version 1.2.2**
**Release Date:** January 19, 2026

**Initial implementation of enhanced spam detection and security fixes** (combined with v1.2.3)

### **Version 1.2.1**
**Release Date:** January 18, 2026

**Added - Dashboard Widget:**
- ✅ Shows last 6 bot blocks
- ✅ Summary statistics (24h, 7d, total)
- ✅ Colored status indicators for block types
- ✅ Quick links to Logs/Statistics tabs
- ✅ Cross-theme compatibility (Omega & Modern)
- ✅ Smart bottom placement above footer

**Files Modified:**
- `includes/OSCBotBlocker.class.php` - Added renderDashboardWidget() method
- `index.php` - Registered dashboard hooks for both themes

**Hooks Added:**
- `main_dashboard` (Modern theme - Enterprise 3.10.4)
- `admin_dashboard_bottom` (Omega theme - osClass 8.2.1)

### **Version 1.2.0**
**Release Date:** January 11, 2026

**Added - Phase 3 (Complete Admin Interface):**
- ✅ 7-tab navigation system
- ✅ Professional dashboard design
- ✅ Complete settings management
- ✅ Real-time statistics with charts
- ✅ Log viewer with pagination (10 per page)
- ✅ CSV log export
- ✅ Whitelist/Blacklist management
- ✅ Flash messages for user feedback
- ✅ CSRF protection on all forms

**Files Added:**
- `admin.php` - Admin interface entry point
- `admin/OSCBBAdmin.class.php` - Admin controller (7 tabs)

**Database:**
- Uses existing `oc_t_oscbb_blacklist` for whitelist/blacklist management

**Admin Interface:**
- Clean, professional design
- Intuitive navigation
- Real-time statistics
- Full control over all features

### **Version 1.1.0**
**Release Date:** January 2026

**Added - Phase 2 (Content Filtering):**
- ✅ Enhanced email validation with pattern checking
- ✅ Disposable email blocking (200+ domains)
- ✅ Free email blocking option (35+ providers)
- ✅ URL analysis and counting (max URLs configurable)
- ✅ Obfuscated URL detection (IP addresses, hex encoding, shorteners)
- ✅ Suspicious TLD blocking (.tk, .ml, .ga, etc.)
- ✅ Keyword filtering system (100+ spam keywords)
- ✅ Keyword combination detection
- ✅ Sensitivity levels (Low/Medium/High)
- ✅ Form field obfuscation with daily rotation
- ✅ Request method validation (POST-only)
- ✅ Content-Type header validation
- ✅ Character encoding validation (UTF-8)
- ✅ Control character detection
- ✅ Rate limiting (5 submissions per hour per IP)
- ✅ Duplicate content detection (MD5 hashing)
- ✅ Contact form protection enhancement
- ✅ Comment protection enhancement

**Files Added:**
- `data/blacklist-emails.php` - Email blacklist database
- `data/blacklist-keywords.php` - Keyword blacklist database
- `includes/ContentFilter.class.php` - Content analysis class

**Database:**
- Enhanced logging with more block types
- Rate limiting queries optimized

**Configuration:**
- `oscbb_block_disposable_emails` - Block temporary emails (default: ON)
- `oscbb_block_free_emails` - Block free email providers (default: OFF)
- `oscbb_url_limit` - Maximum URLs allowed (default: 3)
- `oscbb_keyword_filter_enabled` - Enable keyword filtering (default: ON)
- `oscbb_rate_limit_enabled` - Enable rate limiting (default: ON)
- `oscbb_rate_limit_count` - Max submissions per hour (default: 5)

### **Version 1.0.0**
**Release Date:** January 2026

**Added - Initial Release:**
- ✅ JavaScript token-based bot detection
- ✅ Browser fingerprinting
- ✅ Honeypot field protection (4 fields)
- ✅ Session token management with replay attack prevention
- ✅ Time-based validation (dual-layer: JavaScript + Session)
- ✅ User-Agent blacklist (100+ patterns)
- ✅ User-Agent whitelist (30+ legitimate bots)
- ✅ IP validation class with IPv4/IPv6 support
- ✅ Proxy detection and logging
- ✅ HTTP referer validation
- ✅ Cookie testing
- ✅ Database logging system
- ✅ Daily statistics tracking
- ✅ Automatic log cleanup
- ✅ Admin whitelist
- ✅ Debug mode

**Protected Forms:**
- ✅ Item/Listing posting
- ✅ Contact forms
- ✅ User registration
- ✅ Comment submissions

**Database:**
- ✅ 3 tables created (log, stats, blacklist)
- ✅ Automatic cleanup of old logs

**Files Included:**
- `index.php` - Main plugin file
- `includes/OSCBotBlocker.class.php` - Core class
- `includes/IPValidator.class.php` - IP validation class
- `js/oscbb.js` - Client-side protection
- `data/blacklist-useragents.php` - User-Agent database

---

## 💻 Credits

### **Development:**
**Van Isle Web Solutions**  
Website: https://www.vanislebc.com/  
Email: Contact via website

### **Inspired By:**
This plugin is based on the security concepts from **WP-SpamShield 1.9.21** for WordPress - Red Sand Media Group - https://www.redsandmarketing.com/, adapted specifically for osClass using native osClass code structure and hooks.

### **Thanks To:**
- osClass community for testing and feedback
- Security researchers for vulnerability reports (Snyk)
- Users providing real-world spam examples
- WP-SpamShield for the original concept and inspiration

---

## 📄 License

**OSC Bot Blocker** is released under the **GPL3 License**.

```
Copyright © 2026 Van Isle Web Solutions

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see https://www.gnu.org/licenses/gpl-3.0.html
```

---

## 🆘 Support

### **Documentation:**
- This README file
- Code comments in all files
- Debug mode for troubleshooting (server log only)

### **Community Support:**
- osClass Forum: https://forums.osclass.org/

### **Commercial Support:**
- Contact Van Isle Web Solutions: https://www.vanislebc.com/

---

## ⚠️ Important Notes

### **Privacy:**
- IP addresses are logged for security purposes
- User-Agent strings are logged
- No personal information stored beyond security requirements
- Logs automatically cleaned based on retention settings
- All sensitive data stored in database only (never in browser)

### **Performance:**
- Minimal performance impact (< 50ms per request)
- Database queries optimized
- JavaScript lightweight (< 5KB)
- No external API calls or dependencies

### **Compatibility:**
- Tested on osClass 3.10.4 and 8.2.1
- Compatible with most osClass themes
- Compatible with most osClass plugins
- Report conflicts via support channels

### **Security:**
- All Snyk vulnerabilities resolved
- Cryptographically secure token generation
- Secure cookie attributes (HTTPS auto-detection)
- No sensitive information exposed in browser
- Server-side error logging only

---

**Thank you for using OSC Bot Blocker!**

*Keep your osClass site spam-free without annoying CAPTCHAs!* 🛡️

---

**Last Updated:** January 19, 2026  
**Plugin Version:** 1.2.3  
**Protection Layers:** 27 Active  
**osClass Compatibility:** Enterprise 3.10.4+ and osClass 8.2.1+  
**Security Status:** Snyk Compliant ✅
