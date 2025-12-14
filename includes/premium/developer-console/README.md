# CampaignPress Developer Console

## Overview

The CampaignPress Developer Console is a comprehensive system management and debugging tool designed exclusively for the platform creator. It provides secure, authenticated access to advanced development tools, system monitoring, database management, API testing, and more.

## Features

### 🔐 Security Features

- **Creator-Only Access**: Authenticated access restricted to the license holder/creator
- **IP Whitelisting**: Optional IP-based access control with CIDR notation support
- **Account Lockout**: Automatic lockout after 5 failed login attempts (30-minute lockout)
- **Activity Logging**: Complete audit trail of all developer console actions
- **Session Management**: Configurable session timeout (300-86400 seconds)
- **Security Levels**: Standard, High, and Maximum security configurations

### 📊 Dashboard

- **System Overview**: WordPress, PHP, Database, and Server information
- **Quick Stats**: Real-time CampaignPress statistics
- **Recent Activity**: Latest developer console actions
- **Performance Metrics**: Memory usage, query performance, cache statistics

### 💚 System Health Monitor

- **WordPress Info**: Version, memory limits, debug mode status
- **Server Info**: PHP version, memory usage, peak memory, extensions
- **Database Info**: Version, size, table count, connection info
- **Performance Metrics**: Query count, page generation time, cache hit ratio
- **Security Checks**: HTTPS status, file permissions, salts configuration
- **License Status**: Current license info and activation status
- **Feature Status**: Enabled/disabled premium features
- **Error Logs**: Recent errors from WordPress debug.log

### 🗄️ Database Management

#### Query Tool
- Execute any SQL query (SELECT, INSERT, UPDATE, DELETE)
- Syntax highlighting and formatting
- Dangerous query detection with confirmation requirement
- Query execution time and affected rows tracking
- Save frequently used queries
- Query history and favorites

#### Table Management
- View all database tables
- Preview table data (up to 100 rows)
- View table structure (columns, indexes, constraints)
- Optimize tables
- Table size and row count statistics

#### CampaignPress Statistics
- CRM contacts and interactions count
- Field operations statistics
- FEC compliance data
- Content statistics (issues, events, endorsements, team, volunteers)

### 🔌 API Tester

- **Endpoint Testing**: Test any CampaignPress REST API endpoint
- **HTTP Methods**: Support for GET, POST, PUT, DELETE
- **Request Builder**: Interactive form for building API requests
- **JSON Body Editor**: Syntax-highlighted JSON editor
- **Response Viewer**: Formatted JSON response display
- **Request Details**: Full request/response logging
- **Execution Time**: Performance tracking for API calls
- **Endpoint Library**: Pre-configured endpoints for quick testing
- **API Statistics**: Success rate, average execution time

Available Endpoints:
- `/contacts` - CRM contact management
- `/interactions` - Interaction tracking
- `/walks` - Canvassing walks
- `/phone-calls` - Phone banking
- `/donors` - FEC donors
- `/contributions` - FEC contributions
- `/analytics/summary` - Analytics data
- `/webhooks` - Webhook management

### 📝 Activity Logs

- **Complete Audit Trail**: Every developer console action is logged
- **Category Filtering**: Filter by auth, database, api, system, user, data, security, settings
- **Detailed Information**:
  - User ID and email
  - Action type and description
  - Timestamp and IP address
  - User agent and request method
  - Execution time and memory usage
  - Success/failure status
  - Error messages

### 📥 Data Export

#### Export Types
- **CRM Contacts**: All contact records
- **Interactions**: All interaction logs
- **FEC Donors**: Donor records
- **FEC Contributions**: Contribution history
- **Settings**: All CampaignPress options
- **Developer Logs**: Developer console activity logs
- **Full Backup**: Complete system backup (all data + settings)

#### Export Formats
- **JSON**: JavaScript Object Notation (recommended)
- **CSV**: Comma-Separated Values
- **XML**: Extensible Markup Language

#### Export Features
- Automatic filename generation with timestamp
- File size and record count display
- Direct browser download
- Export history tracking

### 👥 User Management

- List all WordPress users
- View user roles and capabilities
- User registration dates
- Email addresses
- Quick user lookup

### ⚙️ Settings

#### Security Settings
- **Security Level**: Standard, High, or Maximum
- **Session Timeout**: 300-86400 seconds (5 minutes to 24 hours)
- **IP Whitelist**: Comma-separated list of allowed IPs (supports CIDR)

#### Console Information
- Console version
- Creator user ID and email
- Console status (enabled/disabled)
- Failed login attempts
- Last access timestamp and IP

## Installation

The Developer Console is automatically available when CampaignPress Premium is activated. It requires a "professional" license level.

### Automatic Setup

1. Activate CampaignPress Premium
2. Database tables are created automatically on first access
3. Creator is identified by license email or current admin user
4. Access the console via **Dev Console** menu item in WordPress admin

### Manual Setup

If tables need to be recreated:

```php
// Run in WordPress admin or via WP-CLI
do_action('campaignpress_developer_console_activate');
```

## Database Tables

### `wp_cp_dev_console_settings`
Stores developer console configuration and security settings.

**Columns:**
- `id` - Primary key
- `creator_user_id` - WordPress user ID of creator
- `creator_email` - Email address of creator
- `api_token` - Optional API token
- `api_token_hash` - Hashed API token
- `ip_whitelist` - JSON array of allowed IPs
- `enabled` - Console enabled status
- `two_factor_enabled` - 2FA status (future)
- `session_timeout` - Session timeout in seconds
- `allowed_actions` - JSON array of allowed actions
- `security_level` - standard/high/maximum
- `last_access_at` - Last access timestamp
- `last_access_ip` - Last access IP address
- `failed_login_attempts` - Failed login counter
- `locked_until` - Lockout expiration timestamp

### `wp_cp_dev_console_logs`
Complete audit trail of all developer console activities.

**Columns:**
- `id` - Primary key
- `developer_user_id` - User who performed action
- `developer_email` - User email
- `action_type` - Type of action performed
- `action_category` - Category (auth/database/api/system/user/data/security/settings)
- `action_description` - Human-readable description
- `action_details` - JSON encoded details
- `affected_table` - Database table affected
- `affected_record_id` - Record ID affected
- `sql_query` - SQL query executed (for database actions)
- `result_status` - success/failure/warning/info
- `error_message` - Error message if failed
- `ip_address` - Client IP address
- `user_agent` - Browser user agent
- `request_method` - HTTP method
- `request_uri` - Request URI
- `execution_time` - Action execution time
- `memory_usage` - Memory used
- `created_at` - Timestamp

### `wp_cp_dev_console_queries`
Saved database queries for quick reuse.

**Columns:**
- `id` - Primary key
- `developer_user_id` - User who saved query
- `query_name` - Name/label for query
- `query_description` - Optional description
- `sql_query` - The SQL query
- `query_type` - SELECT/INSERT/UPDATE/DELETE/SHOW/DESCRIBE/CUSTOM
- `is_favorite` - Favorite flag
- `is_dangerous` - Dangerous operation flag
- `execution_count` - Times executed
- `last_executed_at` - Last execution timestamp

## Security Best Practices

### Recommended Settings

1. **Security Level**: Set to "High" or "Maximum"
2. **Session Timeout**: 3600 seconds (1 hour) or less
3. **IP Whitelist**: Add your office/home IP addresses
4. **HTTPS**: Always access via HTTPS
5. **Regular Monitoring**: Review activity logs weekly

### Access Control

The Developer Console uses multiple layers of authentication:

1. **WordPress Authentication**: Must be logged into WordPress
2. **Administrator Capability**: Must have `manage_options` capability
3. **Creator Verification**: Must match creator user ID or email
4. **IP Whitelist**: Optional IP-based restriction
5. **Account Lockout**: Protection against brute force

### What's Logged

Every action in the Developer Console is logged:
- All database queries
- All API endpoint tests
- All data exports
- All settings changes
- All login attempts (success and failure)
- All security events

## Usage Examples

### Execute a Database Query

```sql
SELECT * FROM wp_cp_crm_contacts
WHERE support_level = 'strong_supporter'
ORDER BY engagement_score DESC
LIMIT 100
```

### Test an API Endpoint

**Method**: POST
**Endpoint**: `/contacts`
**Body**:
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone": "555-1234",
  "support_level": "supporter"
}
```

### Export All Contacts

1. Navigate to **Data Export** tab
2. Select **Export Type**: CRM Contacts
3. Select **Format**: JSON
4. Click **Export Data**
5. Click **Download File**

### Add IP to Whitelist

1. Navigate to **Settings** tab
2. In **IP Whitelist** textarea, add IPs (one per line):
```
192.168.1.100
10.0.0.0/8
2001:0db8:85a3::8a2e:0370:7334
```
3. Click **Save Security Settings**

### View Recent Activity

1. Navigate to **Activity Logs** tab
2. Select category filter (e.g., "Database")
3. Click **Refresh**
4. Review logged actions

## API Reference

### AJAX Actions

All AJAX actions require the `cp_dev_console_nonce` nonce.

#### `cp_dev_system_health`
Get complete system health data.

**Request:**
```javascript
{
  action: 'cp_dev_system_health',
  nonce: cpDevConsole.nonce
}
```

**Response:**
```javascript
{
  success: true,
  data: {
    wordpress: {...},
    server: {...},
    database: {...},
    performance: {...},
    security: {...},
    license: {...},
    features: {...},
    errors: {...},
    storage: {...}
  }
}
```

#### `cp_dev_execute_query`
Execute a database query.

**Request:**
```javascript
{
  action: 'cp_dev_execute_query',
  nonce: cpDevConsole.nonce,
  query: 'SELECT * FROM wp_posts LIMIT 10',
  confirmed: false,
  save_query: false,
  query_name: ''
}
```

**Response:**
```javascript
{
  success: true,
  query_type: 'SELECT',
  execution_time: 0.0234,
  affected_rows: 10,
  results: [...],
  row_count: 10,
  columns: ['ID', 'post_title', ...]
}
```

#### `cp_dev_get_logs`
Retrieve activity logs.

**Request:**
```javascript
{
  action: 'cp_dev_get_logs',
  nonce: cpDevConsole.nonce,
  category: 'all',
  limit: 50,
  offset: 0
}
```

**Response:**
```javascript
{
  success: true,
  data: {
    logs: [...],
    total: 150
  }
}
```

#### `cp_dev_test_api`
Test an API endpoint.

**Request:**
```javascript
{
  action: 'cp_dev_test_api',
  nonce: cpDevConsole.nonce,
  method: 'GET',
  endpoint: '/contacts',
  body: {},
  headers: {}
}
```

#### `cp_dev_export_data`
Export data.

**Request:**
```javascript
{
  action: 'cp_dev_export_data',
  nonce: cpDevConsole.nonce,
  export_type: 'contacts',
  format: 'json'
}
```

#### `cp_dev_update_settings`
Update console settings.

**Request:**
```javascript
{
  action: 'cp_dev_update_settings',
  nonce: cpDevConsole.nonce,
  key: 'security_level',
  value: 'high'
}
```

## Troubleshooting

### Cannot Access Developer Console

**Issue**: "Access denied" message
**Solution**:
1. Verify you are logged in as the license holder
2. Check if your email matches the license email
3. Verify you have administrator role
4. Check IP whitelist settings

### Database Tables Don't Exist

**Issue**: Errors about missing tables
**Solution**:
```php
// Run this in WordPress admin or WP-CLI
$db = new CampaignPress_Developer_Console_Database();
$db->create_tables();
```

### Account Locked

**Issue**: "Account is locked" message
**Solution**:
1. Wait 30 minutes for automatic unlock
2. Or manually unlock in database:
```sql
UPDATE wp_cp_dev_console_settings
SET failed_login_attempts = 0, locked_until = NULL
WHERE id = 1
```

### Query Execution Fails

**Issue**: SQL errors when executing queries
**Solution**:
1. Check SQL syntax
2. Verify table names exist
3. Check database permissions
4. Review error message in logs

## File Structure

```
includes/premium/developer-console/
├── README.md                          # This documentation
├── developer-console-init.php         # Initialization file
├── class-developer-console.php        # Main console class
├── class-developer-console-database.php  # Database schema
├── class-system-health.php            # System health monitoring
├── class-database-manager.php         # Database query manager
├── class-api-tester.php               # API testing tools
├── class-data-exporter.php            # Data export utilities
├── admin-page.php                     # Main UI template
└── assets/
    ├── developer-console.css          # Styles
    └── developer-console.js           # JavaScript
```

## Version History

### Version 1.0.0 (2025-01-21)
- Initial release
- Dashboard with system overview
- System health monitoring
- Database query tool
- API endpoint tester
- Activity logging
- Data export (JSON/CSV/XML)
- User management
- Security settings
- Creator-only authentication
- IP whitelisting
- Account lockout protection

## Support

For issues or questions about the Developer Console:

1. Review activity logs for error details
2. Check system health for any warnings
3. Verify all security settings are correct
4. Contact CampaignPress support with:
   - Console version
   - WordPress version
   - PHP version
   - Error messages from logs
   - Steps to reproduce the issue

## License

The Developer Console is part of CampaignPress Premium and is licensed under GPL-2.0+.

Copyright (c) 2025 CampaignPress Team
