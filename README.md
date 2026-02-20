# OSC Bot Blocker

**Version:** 1.3.0  
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
- [Automatic Log Cleanup (Cron Setup)](#automatic-log-cleanup-cron-setup)
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

### Phase 1 Features (v1.0.0)

#### Core Bot Detection:
- ✅ **JavaScript Token Validation** - Cryptographic tokens prove browser executed JavaScript
- ✅ **Browser Fingerprinting** - Collects browser characteristics for validation
- ✅ **Honeypot Fields** - Invisible fields that catch automated bots
- ✅ **Session Token Management** - Prevents replay attacks with one-time-use tokens
- ✅ **Time-Based Validation** - Dual-layer timing checks (JavaScript + Session)

#### Advanced Validation:
- ✅ **User-Agent Blacklist** - Blocks 100+ known spam bots and scrapers
- ✅ **User-Agent Whitelist** - Allows legitimate bots (Google, Bing, etc.)
- ✅ **IP Address Validation** - Enhanced IPv4/IPv6 validation with proxy detection
- ✅ **HTTP Referer Checking** - Ensures submissions come from your site
- ✅ **Cookie Testing** - Verifies browser accepts cookies

#### System Features:
- ✅ **Database Logging** - Comprehensive logging of all blocks and attempts
- ✅ **Statistics Tracking** - Daily statistics by block type
- ✅ **Automatic Cleanup** - Removes old logs based on retention settings
- ✅ **Debug Mode** - Detailed logging for troubleshooting
- ✅ **Admin Whitelist** - Logged-in admins bypass all checks

### Phase 2 Features (v1.1.0)

#### Email Protection:
- ✅ **Disposable Email Blocking** - Blocks 200+ temporary email services
- ✅ **Free Email Blocking** - Optional blocking of 35+ free providers (Gmail, Yahoo, etc.)
- ✅ **Email Pattern Validation** - Detects suspicious email patterns
- ✅ **Domain Validation** - IP addresses, short domains, invalid formats

#### Content Filtering:
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

#### Advanced Protection:
- ✅ **Form Field Obfuscation** - Daily rotating field names
- ✅ **Request Method Validation** - POST-only enforcement
- ✅ **Content-Type Validation** - Proper header checking
- ✅ **Rate Limiting** - 5 submissions per hour per IP (configurable)
- ✅ **Duplicate Content Detection** - MD5 hashing prevents resubmissions
- ✅ **Session-Based Tracking** - Tracks last 5 submissions per user

#### Protection Points:
- ✅ Item/Listing Posting
- ✅ Contact Forms
- ✅ User Registration
- ✅ Comment Submissions

### Phase 3 Features (v1.2.0)

#### Complete Admin Interface:
- ✅ **7-Tab Navigation System** - Organized, intuitive interface
- ✅ **Professional Dashboard** - Clean, responsive design
- ✅ **Settings Management** - Full control over all features
- ✅ **Real-Time Statistics** - Live data visualization

#### General Settings Tab:
- ✅ **Plugin On/Off Switch** - Master enable/disable
- ✅ **Protection Level Selector** - Low/Medium/High modes
- ✅ **Logging Controls** - Enable/disable database logging
- ✅ **Log Retention** - Configurable retention period (1-365 days)

#### Protection Settings Tab:
- ✅ **JavaScript Configuration** - Enable/disable with timing controls
- ✅ **Honeypot Controls** - Toggle 4 invisible traps
- ✅ **User-Agent Settings** - 100+ bot blacklist toggle
- ✅ **Referer Checking** - Domain verification on/off
- ✅ **Cookie Testing** - Browser cookie validation
- ✅ **Rate Limiting** - Configurable limits (1-100 per hour)

#### Content Filtering Tab:
- ✅ **URL Limit Control** - Set maximum URLs (0-50)
- ✅ **Keyword Filter Toggle** - Enable/disable 100+ spam keywords
- ✅ **Disposable Email Toggle** - Block 200+ temporary services
- ✅ **Free Email Toggle** - Optional Gmail/Yahoo blocking

#### Statistics Dashboard:
- ✅ **Summary Cards** - Today, 7 days, 30 days, total blocks
- ✅ **Block Types Chart** - Breakdown by protection layer
- ✅ **Top Blocked IPs** - Repeat offenders list
- ✅ **Daily Activity Chart** - Visual 30-day bar chart
- ✅ **Recent Blocks Table** - Last 20 blocked submissions

#### Log Viewer:
- ✅ **Pagination System** - 10 logs per page
- ✅ **Manual Log Cleanup** - Delete logs older than selected number of days
- ✅ **CSV Export** - Download all logs as a CSV file

#### Whitelist Management:
- ✅ **Add IP/Email Whitelist** - Bypass all checks
- ✅ **View All Entries** - Complete whitelist table
- ✅ **Remove Entries** - One-click removal
- ✅ **Format Validation** - Validates IPs and emails
- ✅ **Auto Admin Whitelist** - Logged-in admins auto-whitelisted

#### Blacklist Management:
- ✅ **Custom Blacklist** - Add IP/email/keyword blocks
- ✅ **Enable/Disable Toggle** - Temporarily disable without deleting
- ✅ **Delete Entries** - Permanent removal
- ✅ **Reason Tracking** - Optional notes for each entry
- ✅ **Status Indicators** - Visual active/disabled states

### Phase 3.6 Features (v1.2.3)

#### Enhanced Spam Detection:
- ✅ **Random Character Detection** - Catches gibberish names/subjects (mixed case patterns)
- ✅ **Suspicious Gmail Patterns** - Detects obfuscated Gmail addresses (4+ dots, dot+number combos)
- ✅ **Multiple Space Detection** - Flags excessive spacing (3+ consecutive spaces)
- ✅ **Gibberish Message Detection** - Identifies random character content
- ✅ **Form Field Validation** - Comprehensive name, email, subject, message analysis

### Phase 4 Features (v1.3.0)

#### Cron Setup Tab:
- ✅ **Secret Token Generator** - One-click secure token generation
- ✅ **Cron URL Display** - Ready-to-use URL for your cron job
- ✅ **Step-by-Step Instructions** - Guides you through cron job setup
- ✅ **Token Regeneration** - Regenerate token if security is compromised
- ✅ **Retention Reminder** - Shows current log retention setting

#### Enhanced Entropy Detection:
- ✅ **No-Space Name Rejection** - Blocks single-word names longer than 15 characters with no spaces
- ✅ **Vowel Ratio Analysis** - Flags text with vowel ratio outside the normal 15–70% range
- ✅ **Consonant Cluster Detection** - Catches impossible sequences of 5+ consecutive consonants
- ✅ **Random Uppercase Pattern Detection** - Identifies bot-style mid-word uppercase mixing
- ✅ **Applied to All Key Fields** - Covers name, message, and subject fields universally

---

## 🔧 Requirements

### Server Requirements:
- **PHP:** 7.1 or higher
- **MySQL:** 5.5 or higher
- **osClass:** Enterprise 3.10.4 or osClass 8.2.1+
- **curl** - Required on server for cron job execution

### PHP Extensions Required:
- `json` - For browser checks encoding
- `hash` - For token generation
- `session` - For session management
- `filter` - For IP validation

### Browser Requirements (Users):
- JavaScript enabled (recommended but not required)
- Cookies enabled (recommended but not required)

**Note:** The plugin gracefully degrades when JavaScript or cookies are disabled, using fallback validation methods.

---

## 📥 Installation

### Step 1: Download Plugin
Download the `osc_bot_blocker` plugin package (ZIP file).

### Step 2: Upload Files
Upload the entire `osc_bot_blocker` folder to:
```
/oc-content/plugins/
```

Your structure should look like:
```
/oc-content/plugins/osc_bot_blocker/
├── index.php
├── admin.php
├── cron-cleanup.php
├── includes/
│   ├── OSCBotBlocker.class.php
│   ├── IPValidator.class.php
│   └── ContentFilter.class.php
├── admin/
│   └── OSCBBAdmin.class.php
├── js/
│   └── oscbb.js
└── data/
    ├── blacklist-useragents.php
    ├── blacklist-emails.php
    └── blacklist-keywords.php
```

### Step 3: Activate Plugin
1. Log into your osClass admin panel
2. Go to **Plugins** → **Manage Plugins**
3. Find "OSC Bot Blocker" in the list
4. Click **Install** or **Activate**

### Step 4: Verify Installation
The plugin will automatically:
- ✅ Create 3 database tables (`oc_t_oscbb_log`, `oc_t_oscbb_stats`, `oc_t_oscbb_blacklist`)
- ✅ Set default preferences
- ✅ Show success message

### Step 5: Set Up Automatic Log Cleanup (Cron Job)
See the [Automatic Log Cleanup](#automatic-log-cleanup-cron-setup) section below.

### Step 6: Test Protection
Try posting a test item or submitting a contact form. Protection is now active!

---

## 🔄 Upgrade Instructions

1. **Backup** your current plugin files and database
2. **Deactivate** the old version (do NOT uninstall - keeps your data)
3. **Replace** plugin files with new version
4. **Reactivate** the plugin
5. Visit the plugin settings to see new features

### Important Notes:
- Never delete the plugin if you want to keep logs and statistics
- Deactivation preserves all data
- Uninstallation removes data

---

## 📁 File Structure

```
osc_bot_blocker/
│
├── index.php                           # Main plugin file, initialization
├── admin.php                           # Admin interface entry point
├── cron-cleanup.php                    # Cron job endpoint for automatic log cleanup
│
├── includes/                           # PHP Classes
│   ├── OSCBotBlocker.class.php        # Core plugin class (singleton)
│   ├── IPValidator.class.php          # IP validation & analysis class
│   └── ContentFilter.class.php        # Content analysis & filtering class
│
├── admin/                              # Admin Interface
│   └── OSCBBAdmin.class.php           # Admin controller with 8 tabs
│
├── js/                                 # JavaScript Files
│   └── oscbb.js                       # Client-side bot detection
│
└── data/                               # Data Files
    ├── blacklist-useragents.php       # User-Agent blacklist database
    ├── blacklist-emails.php           # Email blacklist database (disposable domains)
    └── blacklist-keywords.php         # Keyword blacklist database (spam keywords)
```

### Total Files: 12
- **3 Core Files** (index.php, admin.php, cron-cleanup.php)
- **3 Class Files** (OSCBotBlocker, IPValidator, ContentFilter)
- **1 Admin File** (OSCBBAdmin - 8-tab interface)
- **3 Data Files** (User-Agents, Emails, Keywords)
- **1 JavaScript File** (oscbb.js)
- **1 Documentation File** (README.md)

---

## ⚙️ How It Works

### Protection Flow:

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
Plugin validates submission through multiple layers:
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

---

## 🎛️ Using the Admin Interface

### Accessing the Admin Panel:

1. Log into your osClass admin panel
2. Navigate to **Plugins** menu
3. Click **Bot Blocker** in the submenu
4. You'll see 8 tabs at the top

### Tab Overview:

#### 1. General Settings Tab
- Enable/disable the plugin (master switch)
- Set protection level (Low/Medium/High)
- Enable/disable database logging
- Set log retention period (1-365 days)

#### 2. Protection Settings Tab
- Enable/disable JavaScript validation with timing controls
- Enable/disable honeypot fields
- Enable/disable User-Agent blacklist
- Enable/disable referer checking
- Enable/disable cookie testing
- Configure rate limiting (1-100 submissions per hour)

#### 3. Content Filtering Tab
- Set maximum URLs allowed in content (0-50)
- Enable/disable keyword filter (100+ spam keywords)
- Enable/disable disposable email blocking (200+ domains)
- Enable/disable free email provider blocking

#### 4. Statistics Dashboard
- View block counts for today, last 7 days, last 30 days, and all time
- See breakdown of block types (last 30 days)

#### 5. Log Viewer
- View paginated list of all blocked submissions
- Manually delete logs older than a selected number of days (7, 30, 90, 180, 365 days, or all)
- Download all logs as a CSV file

#### 6. Whitelist Management
- Add IP addresses or email addresses to bypass all checks
- View and remove existing whitelist entries
- Admin users are automatically whitelisted when logged in

#### 7. Blacklist Management
- Add custom IP addresses, email addresses, or keywords to block
- Enable/disable entries without deleting
- Add optional reason notes for each entry

#### 8. Cron Setup Tab
- Generate your secret cron token
- View your ready-to-use cron URL
- Step-by-step instructions for setting up automatic log cleanup

### Recommended Settings for New Sites:

```
General:
- Plugin: Enabled
- Protection Level: Medium
- Logging: Enabled
- Log Retention: 30 days

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

---

## 🎛️ Configuration

All settings are manageable through the admin interface.

**Access:** Plugins → Bot Blocker (in osClass admin)

### Available Settings:

Settings are stored in the osClass preferences table under section `osc_bot_blocker`.

#### General Settings:
- `oscbb_enabled` - Plugin enabled/disabled (default: **ON**)
- `oscbb_protection_level` - Protection level: low/medium/high (default: **medium**)
- `oscbb_logging_enabled` - Database logging (default: **ON**)
- `oscbb_log_retention_days` - Days to keep logs (default: **30**)

#### JavaScript Protection:
- `oscbb_js_enabled` - JavaScript validation (default: **ON**)
- `oscbb_min_submit_time` - Minimum seconds before submit (default: **3**)
- `oscbb_max_submit_time` - Maximum seconds before expiry (default: **3600**)

#### Honeypot Protection:
- `oscbb_honeypot_enabled` - Honeypot fields (default: **ON**)

#### User-Agent Validation:
- `oscbb_ua_validation_enabled` - User-Agent checking (default: **ON**)

#### Referer Validation:
- `oscbb_referer_check_enabled` - Referer header checking (default: **ON**)

#### Cookie Testing:
- `oscbb_cookie_test_enabled` - Cookie validation (default: **ON**)

#### Email Protection:
- `oscbb_block_disposable_emails` - Block temporary email services (default: **ON**)
- `oscbb_block_free_emails` - Block free email providers (default: **OFF**)

#### Content Filtering:
- `oscbb_url_limit` - Maximum URLs allowed in content (default: **3**)
- `oscbb_keyword_filter_enabled` - Enable spam keyword filtering (default: **ON**)

#### Rate Limiting:
- `oscbb_rate_limit_enabled` - Enable rate limiting (default: **ON**)
- `oscbb_rate_limit_count` - Max submissions per hour (default: **5**)

#### Cron:
- `oscbb_cron_token` - Secret token for cron endpoint security (generated via admin panel)

---

## 🗄️ Database Tables

The plugin creates 3 database tables (prefix: `oc_t_`):

### 1. oscbb_log
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

### 2. oscbb_stats
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

### 3. oscbb_blacklist
Custom IP/email/keyword blacklist and whitelist (admin managed).

**Columns:**
- `pk_i_id` - Primary key
- `s_type` - Type (blacklist_ip, blacklist_email, blacklist_keyword, whitelist_ip, whitelist_email)
- `s_value` - The blocked/whitelisted value
- `dt_added` - Date added
- `s_reason` - Reason for entry
- `b_active` - Active status

---

## 🛡️ Protection Layers

### Complete Protection System:

#### Phase 1 Layers (1-12):
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

#### Phase 2 Layers (13-22):
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

#### Phase 3.6 Layers (23-27):
23. **Random Character Detection** - Catches gibberish names/subjects
24. **Suspicious Gmail Patterns** - Detects obfuscated Gmail addresses (4+ dots, dot+number combos)
25. **Multiple Space Detection** - Flags excessive spacing (3+ consecutive spaces)
26. **Gibberish Message Detection** - Identifies random character content
27. **Form Field Validation** - Comprehensive name, email, subject, message analysis

---

## ⏰ Automatic Log Cleanup (Cron Setup)

The plugin includes a secure cron endpoint for automatic log cleanup. This allows your logs to be cleaned daily based on your configured retention period without manual intervention.

> **Note:** osClass's built-in cron hooks do not reliably trigger plugin cleanup functions. A server-level cron job is required for automatic log cleanup.

### Step 1: Generate Your Secret Token

1. Go to your **osClass Admin Panel**
2. Navigate to **Plugins → Bot Blocker**
3. Click the **Cron Setup** tab
4. Click **"Generate Token"**
5. Your secret token will be displayed - keep this safe!

The token never changes unless you click "Regenerate Token". Your cron job will continue working indefinitely without any maintenance.

### Step 2: Test Your Cron URL

Your cron URL will be displayed on the Cron Setup tab. It will look like this:

```
https://yourdomain.com/oc-content/plugins/osc_bot_blocker/cron-cleanup.php?token=YOUR_TOKEN_HERE
```

Visit this URL in your browser. You should see:

```
OSC Bot Blocker: Cron cleanup completed successfully at 2026-02-17 03:00:00
```

If you see this message, the cleanup script is working correctly.

### Step 3: Add Cron Job in cPanel

1. Log into **cPanel**
2. Go to **Cron Jobs**
3. Set the following schedule (runs daily at 3:00 AM):
   - **Minute:** 0
   - **Hour:** 3
   - **Day:** *
   - **Month:** *
   - **Weekday:** *
4. In the **Command** field, enter (copy from your Cron Setup tab):

```
curl -s "https://yourdomain.com/oc-content/plugins/osc_bot_blocker/cron-cleanup.php?token=YOUR_TOKEN_HERE" >/dev/null 2>&1
```

5. Click **"Add New Cron Job"**

### How Log Retention Works

The cron job deletes logs based on your retention setting in the **General Settings** tab. For example:
- Set to **7 days** → Logs older than 7 days are deleted daily
- Set to **30 days** → Logs older than 30 days are deleted daily
- Set to **90 days** → Logs older than 90 days are deleted daily

You can also manually delete logs at any time using the **Logs** tab.

### Security Notes

- The token is a 64-character randomly generated string
- Without a valid token, the script returns a 403 Forbidden error
- If you suspect your token has been compromised, click "Regenerate Token" in the Cron Setup tab and update your cron job with the new token

---

## 🔍 Troubleshooting

### Issue: Legitimate users getting blocked

1. Check your logs (Logs tab) to see the exact reason for the block
2. Add the user's IP or email to the Whitelist tab
3. If it happens frequently, consider lowering your Protection Level to "Low"

### Issue: Plugin not blocking spam

1. Verify the plugin is activated (Plugins → Manage Plugins)
2. Check that protection is Enabled in the General Settings tab
3. Enable Debug Mode by editing `index.php` and changing `OSCBB_DEBUG` to `true`
4. Check your server error log for detailed information

### Issue: White screen when accessing plugin settings

This is usually a PHP syntax error. Check your server error log for the specific line and file causing the error.

### Issue: Cron job not deleting logs

1. Test your cron URL manually in a browser - you should see a success message
2. Verify the token in your cron command matches the token shown in the Cron Setup tab
3. Check that `curl` is available on your server
4. Verify the cron job is set up correctly in cPanel

### Issue: Manual log deletion not working

Verify you are clicking "Delete Old Logs" and confirming the dialog. The logs tab will refresh with a success message showing how many entries were deleted.

### Debug Mode:

Enable detailed logging by editing `index.php`:
```php
// Change:
define('OSCBB_DEBUG', false);
// To:
define('OSCBB_DEBUG', true);
```

Debug messages will appear in your server's error log.

---

## 📜 Version History

### Version 1.3.0 (Current)
**Release Date:** February 2026

**Added - Phase 4 (Cron Setup):**
- ✅ New Cron Setup tab in admin panel (8th tab)
- ✅ One-click secret token generation from admin panel
- ✅ Token stored securely in database
- ✅ Ready-to-use cron URL displayed in admin panel
- ✅ Step-by-step cron job setup instructions
- ✅ Token regeneration with confirmation dialog
- ✅ Current retention setting reminder on cron page
- ✅ `cron-cleanup.php` endpoint file for server cron jobs
- ✅ Fixed manual log deletion (parameter name conflict with osClass resolved)
- ✅ Improved form submission handling

**Added - Enhanced Entropy Detection:**
- ✅ Mathematical gibberish detection via `isGibberish()` method in `OSCBotBlocker.class.php`
- ✅ Long single-word name rejection (no spaces + over 15 characters = blocked)
- ✅ Vowel ratio analysis — real words have 15–70% vowels, random strings do not
- ✅ Impossible consonant cluster detection (5+ consecutive consonants)
- ✅ Random mid-word uppercase pattern detection (bot-style camelCase strings)
- ✅ Checks applied universally to name, message, and subject fields
- ✅ Catches all current and future variations without needing keyword updates

**Files Added:**
- `cron-cleanup.php` - Secure cron endpoint for automatic log cleanup

**Bug Fixes:**
- Fixed manual log deletion redirecting to wrong page
- Fixed parameter name conflict with osClass routing (`action` → `oscbb_action`)

### Version 1.2.3
**Release Date:** January 2026

**Added - Phase 3.6 (Enhanced Spam Detection):**
- ✅ Random character detection for names and subjects
- ✅ Suspicious Gmail pattern detection (4+ dots, dot+number combinations)
- ✅ Multiple space detection (3+ consecutive spaces)
- ✅ Gibberish message detection
- ✅ Comprehensive form field validation

### Version 1.2.0
**Release Date:** January 2026

**Added - Phase 3:**
- ✅ Complete admin interface with 7-tab navigation
- ✅ General Settings page (plugin on/off, protection level, logging)
- ✅ Protection Settings page (JS, honeypot, validation controls)
- ✅ Content Filtering page (URLs, keywords, emails)
- ✅ Statistics Dashboard (summary cards, block type breakdown)
- ✅ Log Viewer with pagination (10 logs per page)
- ✅ Manual log cleanup with selectable date range
- ✅ CSV log export
- ✅ Whitelist Management (IP/email)
- ✅ Blacklist Management (IP/email/keyword)
- ✅ Flash messages for user feedback

**Files Added:**
- `admin.php` - Admin interface entry point
- `admin/OSCBBAdmin.class.php` - Admin controller

### Version 1.1.0
**Release Date:** January 2026

**Added - Phase 2:**
- ✅ Enhanced email validation with pattern checking
- ✅ Disposable email blocking (200+ domains)
- ✅ Free email blocking option (35+ providers)
- ✅ URL analysis and counting (max URLs configurable)
- ✅ Obfuscated URL detection
- ✅ Suspicious TLD blocking (.tk, .ml, .ga, etc.)
- ✅ Keyword filtering system (100+ spam keywords)
- ✅ Form field obfuscation with daily rotation
- ✅ Rate limiting (5 submissions per hour per IP)
- ✅ Duplicate content detection (MD5 hashing)

**Files Added:**
- `data/blacklist-emails.php` - Email blacklist database
- `data/blacklist-keywords.php` - Keyword blacklist database
- `includes/ContentFilter.class.php` - Content analysis class

### Version 1.0.0
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
- ✅ HTTP referer validation
- ✅ Cookie testing
- ✅ Database logging system
- ✅ Daily statistics tracking
- ✅ Admin whitelist
- ✅ Debug mode

**Files Included:**
- `index.php` - Main plugin file
- `includes/OSCBotBlocker.class.php` - Core class
- `includes/IPValidator.class.php` - IP validation class
- `js/oscbb.js` - Client-side protection
- `data/blacklist-useragents.php` - User-Agent database

---

## 👨‍💻 Credits

### Development:
**Van Isle Web Solutions**  
Website: https://www.vanislebc.com/  
Email: Contact via website

### Inspired By:
This plugin is based on the security concepts from **WP-SpamShield 1.9.21** for WordPress by Red Sand Media Group (https://www.redsandmarketing.com/), adapted specifically for osClass using native osClass code structure and hooks.

### Thanks To:
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

### Documentation:
- This README file
- Code comments in all files
- Debug mode for troubleshooting

### Community Support:
- osClass Forum: https://forums.osclass.org/

### Commercial Support:
- Contact Van Isle Web Solutions: https://www.vanislebc.com/

---

**Thank you for using OSC Bot Blocker!**

*Keep your osClass site spam-free without annoying CAPTCHAs!* 🛡️

---

**Last Updated:** February 17, 2026  
**Plugin Version:** 1.3.0  
**Protection Layers:** 27 Active  
**osClass Compatibility:** Enterprise 3.10.4+ and osClass 8.2.1+
