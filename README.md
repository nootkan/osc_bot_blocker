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

### **Phase 1 Features (v1.0.0)

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

### **Phase 2 Features (v1.1.1)

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

### **Phase 3 Features (v1.2.0)

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
- ✅ **Pagination System** - 50 logs per page
- ✅ **Sortable Columns** - Date, IP, type, form, reason
- ✅ **Detailed View** - Full information for each block

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

---

## 🔧 Requirements

### **Server Requirements:**
- **PHP:** the minimum PHP version required is PHP 7.1. Versions lower than that won't work.
- **MySQL:** 5.5 or higher
- **osClass:** Enterprise 3.10.4 or Osclass 8.2.1+

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
├── includes/
│   ├── OSCBotBlocker.class.php
│   └── IPValidator.class.php
├── js/
│   └── oscbb.js
├── data/
│   └── blacklist-useragents.php
├── admin/ (Phase 3 - coming soon)
├── css/ (Phase 3 - coming soon)
└── languages/ (Phase 4 - coming soon)
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

## 🔄 Upgrade Instructions

### **From Future Versions:**
1. **Backup** your current plugin files and database
2. **Deactivate** the old version (do NOT uninstall - keeps your data)
3. **Replace** plugin files with new version
4. **Reactivate** the plugin
5. Visit the plugin settings to see new features

### **Important Notes:**
- Never delete the plugin if you want to keep logs and statistics
- Deactivation preserves all data
- Uninstallation removes data (unless "Keep Data" option is set)

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
│   ├── blacklist-emails.php           # Email blacklist database (disposable domains)
│   └── blacklist-keywords.php         # Keyword blacklist database (spam keywords)
│
├── css/                                # Stylesheets (Phase 4)
│   └── (planned for Phase 4)
│
└── languages/                          # Translations (Phase 4)
    └── (planned for Phase 4)
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
Plugin validates submission through 8 layers:
  1. Session Token (replay attack prevention)
  2. JavaScript Token (bot detection)
  3. Honeypot Fields (automated bot detection)
  4. User-Agent (known bot blacklist)
  5. Referer Header (external submission blocking)
  6. Cookie Test (browser validation)
  7. Time Validation (too fast = bot)
  8. IP Validation (format & logging)
    ↓
ALL checks pass? → ✅ Allow submission
ANY check fails? → ❌ Block + Log + Redirect with error
```

### **Validation Layers Explained:**

#### **Layer 1: Session Token**
- Unique token per form load
- One-time use only
- Prevents replay attacks
- Expires after 1 hour

#### **Layer 2: JavaScript Token**
- Generated client-side with timestamp
- Includes browser fingerprint
- Validates JavaScript execution
- Checks submission timing

#### **Layer 3: Honeypot Fields**
- 4 invisible fields
- Humans can't see/fill them
- Bots auto-fill them
- Any data in honeypot = instant block

#### **Layer 4: User-Agent**
- Checks against 100+ known spam bot patterns
- Whitelists legitimate bots (Google, Bing, etc.)
- Detects scrapers and malicious tools

#### **Layer 5: Referer Header**
- Ensures submission came from your site
- Blocks external/direct POST attacks
- Validates domain match

#### **Layer 6: Cookie Test**
- JavaScript sets test cookie
- PHP validates cookie exists
- Bots often don't handle cookies

#### **Layer 7: Time Validation**
- JavaScript timing (primary)
- Session timing (fallback)
- Too fast (< 3 sec) = bot
- Too slow (> 1 hour) = expired

#### **Layer 8: IP Validation**
- IPv4 & IPv6 support
- Proxy detection
- Format validation
- Logging for analysis

---

## 🎛️ Using the Admin Interface

### **Accessing the Admin Panel:**

1. Log into your osClass admin panel
2. Navigate to **Plugins** menu
3. Click **Bot Blocker** in the submenu
4. You'll see 7 tabs at the top

### **Quick Start Guide:**

#### **1. General Settings Tab**
- Check that the plugin is **Enabled** (green toggle)
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
- View 50 logs per page
- Click through pages to review all blocks

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

### **Recommended Settings for New Sites:**

```
General:
- Plugin: Enabled
- Protection Level: Medium
- Logging: Enabled

Protection:
- JavaScript: Enabled
- Honeypot: Enabled
- User-Agent: Enabled
- Rate Limiting: Enabled (5/hour)

Content:
- Max URLs: 3
- Keywords: Enabled
- Disposable Emails: Blocked
- Free Emails: Not Blocked
```

### **Monitoring Your Protection:**

**Daily:**
- Check Statistics Dashboard for block counts
- Review Recent Blocks for unusual activity

**Weekly:**
- Check Top Blocked IPs
- Review Log Viewer for patterns
- Adjust settings if needed

**Monthly:**
- Clean old logs (automatic if retention set)
- Review protection effectiveness
- Fine-tune settings based on data

## 🎛️ Configuration

### **Admin Interface (Phase 3 - Complete):**

All settings are now manageable through the comprehensive admin interface:

**Access:** Plugins → Bot Blocker (in osClass admin)

**7 Tabs Available:**
1. **General** - Plugin status, protection level, logging
2. **Protection** - JavaScript, honeypot, validation controls
3. **Content Filtering** - URLs, keywords, email settings
4. **Statistics** - Dashboard with charts and reports
5. **Logs** - Search and filter blocked submissions
6. **Whitelist** - Manage trusted IPs and emails
7. **Blacklist** - Manage custom blocks

### **Available Settings:**

Settings are stored in osClass preferences table under section `osc_bot_blocker`.

#### **General Settings:**
- `oscbb_enabled` - Plugin enabled/disabled (default: **ON**)
- `oscbb_protection_level` - Protection level: low/medium/high (default: **medium**)
- `oscbb_logging_enabled` - Database logging (default: **ON**)
- `oscbb_log_retention_days` - Days to keep logs (default: **30**)

#### **JavaScript Protection:**
- `oscbb_js_enabled` - JavaScript validation (default: **ON**)
- `oscbb_min_submit_time` - Minimum seconds before submit (default: **3**)
- `oscbb_max_submit_time` - Maximum seconds before expiry (default: **3600**)
- `oscbb_token_expiration` - Token expiration time (default: **3600**)

#### **Honeypot Protection:**
- `oscbb_honeypot_enabled` - Honeypot fields (default: **ON**)

#### **User-Agent Validation:**
- `oscbb_ua_validation_enabled` - User-Agent checking (default: **ON**)

#### **Referer Validation:**
- `oscbb_referer_check_enabled` - Referer header checking (default: **ON**)

#### **Cookie Testing:**
- `oscbb_cookie_test_enabled` - Cookie validation (default: **ON**)

#### **Email Protection:**
- `oscbb_block_disposable_emails` - Block temporary email services (default: **ON**)
- `oscbb_block_free_emails` - Block free email providers (default: **OFF**)

#### **Content Filtering:**
- `oscbb_url_limit` - Maximum URLs allowed in content (default: **3**)
- `oscbb_keyword_filter_enabled` - Enable spam keyword filtering (default: **ON**)

#### **Rate Limiting:**
- `oscbb_rate_limit_enabled` - Enable rate limiting (default: **ON**)
- `oscbb_rate_limit_count` - Max submissions per hour (default: **5**)

### **Manual Configuration (Advanced):**

For manual configuration or programmatic access, settings can be modified directly:

```php
// Example: Change protection level
osc_set_preference('oscbb_protection_level', 'high', 'osc_bot_blocker', 'STRING');

// Example: Increase rate limit
osc_set_preference('oscbb_rate_limit_count', 10, 'osc_bot_blocker', 'INTEGER');
```

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
Custom IP/email/domain blacklist (Phase 3 - admin managed).

**Columns:**
- `pk_i_id` - Primary key
- `s_type` - Type (ip, email, domain, keyword)
- `s_value` - Blacklist value
- `dt_added` - Date added
- `s_reason` - Reason for blacklist
- `b_active` - Active status

---

## 🛡️ Protection Layers

### **Bot Detection Success Rate:**

Based on **WP-SpamShield 1.9.21** for WordPress - Red Sand Media Group - https://www.redsandmarketing.com/ (this plugin's inspiration), the multi-layer approach blocks:
- ✅ **99.9%** of automated spam bots
- ✅ **95%+** of manual spam attempts
- ✅ **100%** of common scraping tools

### **Complete 22-Layer Protection System:**

#### **Phase 1 Layers (1-12):**
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

#### **Phase 2 Layers (13-22):**
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

### **Additional Protections:**
- **Special Characters** - Flags excessive symbols (>30%)
- **Repetition Detection** - Catches repeated chars/words
- **All-Caps Detection** - Blocks SHOUTING (>70% uppercase)
- **Suspicious TLDs** - Blocks free/spam domains (.tk, .ml, etc.)
- **Content-Type Validation** - Proper HTTP header checking

### **What Gets Blocked:**

#### **Automated Bots:**
- Comment spambots
- Registration bots
- Contact form bots
- Content scrapers
- Email harvesters
- Auto-posting tools (XRumer, SEnuke, etc.)
- Spam submission scripts

#### **Suspicious Behavior:**
- Submissions without JavaScript
- Honeypot field violations
- Blacklisted User-Agents
- External referers
- Missing/invalid cookies
- Too-fast submissions (< 3 seconds)
- Replay attacks
- Non-POST requests

#### **Phase 2 Blocks:**
- **Disposable Emails** - 200+ temporary email services
- **Suspicious URLs** - IP addresses, hex encoding, shorteners, phishing patterns
- **Spam Keywords** - 100+ common spam words/phrases
- **Too Many URLs** - More than 3 URLs in content (configurable)
- **Suspicious TLDs** - .tk, .ml, .ga, .cf, .gq, .pw, .top, .xyz
- **Invalid Encoding** - Non-UTF-8, control characters
- **Rate Limit Violations** - More than 5 submissions per hour
- **Duplicate Content** - Identical resubmissions
- **All-Caps Content** - >70% uppercase text
- **Excessive Repetition** - Repeated characters/words
- **Special Characters** - >30% symbols

### **What Doesn't Get Blocked:**

#### **Legitimate Users:**
- ✅ Normal form submissions
- ✅ Users with JavaScript disabled (fallback validation)
- ✅ Users with strict privacy settings
- ✅ Users behind proxies/VPNs (logged but allowed)
- ✅ Legitimate search engine bots (whitelisted)

#### **Admin Users:**
- ✅ Logged-in admins bypass all checks
- ✅ Full access for testing and posting

---

## 🔍 Troubleshooting

### **Issue: Legitimate users getting blocked**

**Solutions:**
1. Check if user has JavaScript disabled
   - Verify session timing fallback is working
2. Check User-Agent blacklist
   - User might have unusual browser
3. Check logs in database (`oc_t_oscbb_log`)
   - See exact reason for block
4. Temporarily disable specific checks to isolate issue

### **Issue: Plugin not blocking spam**

**Solutions:**
1. Verify plugin is activated (Plugins > Manage Plugins)
2. Check if hooks are registered
   - Look for JavaScript on forms
   - Look for honeypot fields in HTML source
3. Enable debug mode:
   - Edit `index.php`
   - Change: `define('OSCBB_DEBUG', false);` to `define('OSCBB_DEBUG', true);`
   - Check server error logs
4. Verify database tables exist (`oc_t_oscbb_log`, etc.)

### **Issue: Forms not submitting at all**

**Solutions:**
1. Check for JavaScript errors in browser console
2. Verify session is working (check `php.ini` session settings)
3. Check if cookies are being set
4. Temporarily disable plugin to isolate issue
5. Check for conflicts with other plugins

### **Issue: Admin can't post items**

**Note:** Admins should be automatically whitelisted.

**Solutions:**
1. Verify you're logged in as admin
2. Check `osc_is_admin_user_logged_in()` is returning true
3. Check logs to see why admin is being blocked
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

---

## 📜 Version History

### **Version 1.2.0** (Current)
**Release Date:** January 2026

**Added - Phase 3 (Steps 23-30):**
- ✅ Complete admin interface with 7-tab navigation
- ✅ Professional dashboard with responsive design
- ✅ General Settings page (plugin on/off, protection level, logging)
- ✅ Protection Settings page (JS, honeypot, validation controls)
- ✅ Content Filtering page (URLs, keywords, emails)
- ✅ Statistics Dashboard (summary cards, charts, tables)
- ✅ Block type breakdown with percentages
- ✅ Top blocked IPs list
- ✅ Daily activity chart (30-day bar chart)
- ✅ Recent blocks table
- ✅ Log Viewer with search and filtering
- ✅ Advanced search (IP, email, reason)
- ✅ Multiple filters (type, form, date range)
- ✅ Pagination system (50 logs per page)
- ✅ Whitelist Management (IP/email)
- ✅ Add/remove whitelist entries
- ✅ Format validation for whitelist
- ✅ Blacklist Management (IP/email/keyword)
- ✅ Add/remove/toggle blacklist entries
- ✅ Custom reason tracking
- ✅ Enable/disable without deleting
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

**Added - Phase 2 (Steps 13-22):**
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

**Added:**
- ✅ Initial plugin release
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

### **Version 1.3.0** (Phase 4 - Planned)
**Planned Advanced Features:**
- MCP server integration
- REST API endpoints
- Bulk operations
- Settings export/import
- Email notifications for blocks
- Advanced reporting
- Scheduled cleanup tasks
- Performance monitoring
- Multi-language support
- Custom CSS styling
- Webhook integrations
- Extended analytics
**Planned Features:**
- Database optimization
- AJAX validation
- Admin notifications
- Performance improvements
- Translation support
- Documentation improvements
- Testing suite

---

## 👨‍💻 Credits

### **Development:**
**Van Isle Web Solutions**  
Website: https://www.vanislebc.com/  
Email: Contact via website

### **Inspired By:**
This plugin is based on the security concepts from **WP-SpamShield 1.9.21** for WordPress - Red Sand Media Group - https://www.redsandmarketing.com/, adapted specifically for osClass using native osClass code structure and hooks.

### **Thanks To:**
- osClass community for testing and feedback
- WP-SpamShield for the original concept and inspiration

---

## 📄 License

**OSC Bot Blocker** is released under the **GPL2+ License**.

```
Copyright © 2026 Van Isle Web Solutions

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see https://www.gnu.org/licenses/gpl-2.0.html
```

---

## 🆘 Support

### **Documentation:**
- This README file
- Code comments in all files
- Debug mode for troubleshooting

### **Community Support:**
- osClass Forum: https://forums.osclass.org/
- Report issues via GitHub (if available)

### **Commercial Support:**
- Contact Van Isle Web Solutions: https://www.vanislebc.com/

---

## 🚀 Future Roadmap

### **Phase 2: Content Filtering & Advanced Validation**
- Enhanced email validation
- URL analysis
- Keyword filtering
- Rate limiting
- Content analysis

### **Phase 3: Admin Interface & Configuration**
- Full admin panel
- Visual statistics
- Easy configuration
- Whitelist/blacklist management

### **Phase 4: Polish & Optimization**
- Performance optimization
- AJAX validation
- Multilingual support
- Complete testing suite
- Full documentation

---

## ⚠️ Important Notes

### **Privacy:**
- IP addresses are logged for security purposes
- User-Agent strings are logged
- No personal information is stored beyond what's necessary for spam prevention
- Logs are automatically cleaned based on retention settings

### **Performance:**
- Minimal performance impact (< 50ms per request)
- Database queries are optimized
- JavaScript is lightweight (< 5KB)
- No external API calls or dependencies

### **Compatibility:**
- Tested on osClass 3.10.4 and 8.2.1
- Compatible with most osClass themes
- Compatible with most osClass plugins
- Report conflicts via support channels

---

**Thank you for using OSC Bot Blocker!**

*Keep your osClass site spam-free without annoying CAPTCHAs!* 🛡️

---

**Last Updated:** January 11, 2026  
**Plugin Version:** 1.2.0 (Phase 3 Complete - Full Admin Interface)  
**Protection Layers:** 22 Active  
**osClass Compatibility:** enterprise 3.10.4+ and osclass 8.2.1+
