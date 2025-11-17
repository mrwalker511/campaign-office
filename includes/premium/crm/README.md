# CampaignPress Political CRM System

A comprehensive voter relationship management system designed specifically for political campaigns.

## Overview

The CampaignPress CRM provides a complete solution for managing voter contacts, tracking interactions, organizing volunteers, and analyzing campaign effectiveness. Built for performance with 50K+ contact capacity and optimized database indexing.

## System Components

### 1. Database Schema (`class-crm-database.php`)
- **11 Database Tables** with optimized indexing
- Tables:
  - `contacts` - Primary voter/contact database
  - `interactions` - Interaction history tracking
  - `tags` - Contact categorization tags
  - `contact_tags` - Contact-tag relationships
  - `custom_fields` - Flexible field definitions
  - `custom_field_values` - Custom field data
  - `households` - Household grouping
  - `duplicate_groups` - Duplicate detection tracking
  - `engagement_scores` - Historical engagement metrics
  - `segments` - Dynamic and static segments
  - `segment_contacts` - Segment membership

### 2. Contact Management (`class-crm-contacts.php`)
- **Full CRUD operations** for contacts
- **Advanced search and filtering** across 20+ fields
- **Bulk operations** (update, delete, tag)
- **Duplicate detection** and merging
- **Household grouping** by address
- **Engagement scoring algorithm** with components:
  - Recency score (last contact date)
  - Frequency score (interaction count)
  - Quality score (positive interactions)
  - Response rate (answered vs. total attempts)
- **Pagination** for large datasets
- **Multi-criteria filtering**:
  - Location (state, city, zip, districts)
  - Demographics (age, gender, party)
  - Engagement levels
  - Tags and segments
  - Contact availability (email, phone)
  - Date ranges

### 3. Interaction Tracking (`class-crm-interactions.php`)
- **Track all contact types**:
  - Phone calls
  - Text messages
  - Door knocks
  - Emails
  - Events
  - Donations
  - Volunteer signups
  - Petition signatures
  - Survey responses
  - Social media
- **Interaction results**:
  - Support levels (strong support to strong against)
  - Contact outcomes (contacted, no answer, wrong number)
  - Commitments (volunteer, donate, vote)
  - Special flags (needs ride, needs absentee ballot)
- **Statistics and analytics**:
  - Interaction type breakdown
  - Results analysis
  - Daily/weekly reports
  - Response rates
  - User performance metrics

### 4. Segmentation & Tagging (`class-crm-segments.php`)
- **Dynamic segments** - Auto-updating based on criteria
- **Static segments** - Manual contact grouping
- **Smart tagging system**:
  - System tags (predefined)
  - Custom tags
  - Color coding
  - Tag categories (support level, role, status)
- **Bulk tagging operations**
- **Segment recalculation** with scheduling
- **Default tags included**:
  - Support levels (Strong Support, Support, Undecided)
  - Roles (Volunteer, Donor, VIP)
  - Status (Contacted, Needs Follow-up)

### 5. Import/Export (`class-crm-import-export.php`)
- **Supported voter file formats**:
  - L2 Political
  - TargetSmart
  - NGP VAN
  - Generic CSV
- **Smart field mapping**:
  - Auto-detection of file format
  - Suggested field mappings
  - Custom field mapping
- **Import features**:
  - Batch processing (configurable batch size)
  - Duplicate detection during import
  - Update existing contacts option
  - Auto-tagging of imports
  - Segment creation from imports
  - Progress callbacks
  - Error reporting
- **Export features**:
  - Export by segment, tag, or filters
  - Custom field selection
  - Multiple format support
  - Automatic file cleanup (7-day retention)

### 6. System Initialization (`crm-init.php`)
- **Singleton pattern** for system access
- **WordPress integration**:
  - Activation/deactivation hooks
  - Scheduled tasks (cron jobs)
  - AJAX endpoints
  - REST API routes
- **Automated maintenance**:
  - Daily database optimization
  - Twice-daily engagement score recalculation
  - Dynamic segment updates
  - Export file cleanup
- **REST API endpoints**:
  - GET/POST /crm/contacts
  - GET/PUT /crm/contacts/{id}
  - POST /crm/interactions
  - GET /crm/segments
  - GET /crm/tags
  - GET /crm/statistics

## Key Features Implemented

### Performance Optimizations
- Indexed database columns on frequently queried fields
- Prepared statements for all database operations
- Batch processing for large imports/exports
- Pagination support throughout
- Optimized queries with proper JOINs

### Security
- WordPress nonce verification
- Capability checks on all operations
- Prepared SQL statements (prevent SQL injection)
- Input sanitization and validation
- CSRF protection on AJAX calls
- Permission callbacks on REST API

### Data Integrity
- Duplicate email prevention (UNIQUE constraint)
- Foreign key relationships
- Data validation before insert/update
- Error handling and logging
- Transaction support for critical operations

### Engagement Scoring Algorithm
```
Final Score = (Recency × 30%) + (Frequency × 30%) + (Quality × 25%) + (Response Rate × 15%)

Where:
- Recency: 100 - (days_since_contact × 2)
- Frequency: min(100, interaction_count × 10)
- Quality: (positive_interactions / total_interactions) × 100
- Response Rate: (answered_attempts / total_attempts) × 100
```

### Household Grouping
- Automatic grouping by matching address
- Household statistics (size, registered voters)
- Family relationship tracking
- Canvassing optimization

### Duplicate Detection
- Email-based matching
- Name + address matching
- Confidence scoring
- Manual merge capability
- Preserve interaction history on merge

## Database Schema Details

### Contacts Table
- **50+ fields** including:
  - Personal info (name, DOB, age, gender)
  - Contact info (email, phone, mobile)
  - Address (full address with geocoding)
  - Political data (party, registration status, voter ID)
  - District info (congressional, state house/senate, precinct)
  - Scores (engagement, turnout, partisan)
  - Flags (volunteer, donor, supporter, DNC)
  - Metadata (source, notes, timestamps)

### Interactions Table
- Contact reference
- Interaction type and date
- Duration and outcome
- Results and support level
- Commitment flags (volunteer, donate, vote)
- Issue priorities
- GPS coordinates
- User and campaign tracking
- JSON metadata field

### Performance Indexes
- Primary keys on all tables
- Composite indexes for common queries
- Geo-spatial indexes for location queries
- Full-text search ready (can be added)

## Code Statistics

- **Total Lines of Code**: 5,226
- **Files**: 6
- **Classes**: 5 main classes + 1 initialization
- **Database Tables**: 11
- **REST API Endpoints**: 8+
- **Default Tags**: 8 system tags

## File Breakdown

1. **class-crm-database.php** (669 lines)
   - Database table creation and schema management
   - Migration and version control
   - Table optimization utilities

2. **class-crm-contacts.php** (1,084 lines)
   - Contact CRUD operations
   - Search and filtering
   - Bulk operations
   - Engagement scoring
   - Duplicate management
   - Household assignment

3. **class-crm-interactions.php** (841 lines)
   - Interaction logging
   - History tracking
   - Statistics and reporting
   - Contact statistics updates
   - Bulk logging

4. **class-crm-segments.php** (1,004 lines)
   - Segment creation and management
   - Dynamic segment calculation
   - Tag management
   - Bulk tagging operations
   - Contact-tag relationships

5. **class-crm-import-export.php** (855 lines)
   - CSV import with format detection
   - Field mapping (auto and manual)
   - Batch processing
   - CSV export with filtering
   - File validation
   - Preview functionality

6. **crm-init.php** (773 lines)
   - System initialization
   - WordPress integration
   - REST API registration
   - AJAX handlers
   - Scheduled tasks
   - Default data creation

## WordPress Coding Standards Compliance

- Proper PHPDoc comments on all functions
- WordPress naming conventions
- Sanitization and validation
- Nonce verification
- Capability checks
- Action and filter hooks
- Translation ready
- Error logging

## Usage Examples

### Initialize CRM
```php
$crm = cp_crm();
```

### Create a Contact
```php
$contact_id = $crm->contacts->create_contact( array(
    'first_name' => 'John',
    'last_name'  => 'Doe',
    'email'      => 'john@example.com',
    'phone'      => '555-1234',
    'city'       => 'Springfield',
    'state'      => 'IL',
    'party_affiliation' => 'Democratic',
) );
```

### Log an Interaction
```php
$interaction_id = $crm->interactions->log_interaction( array(
    'contact_id'       => 123,
    'interaction_type' => 'phone_call',
    'result'           => 'support',
    'support_level'    => 5,
    'will_volunteer'   => true,
    'notes'            => 'Very enthusiastic supporter',
) );
```

### Create a Segment
```php
$segment_id = $crm->segments->create_segment( array(
    'name'         => 'High Propensity Voters',
    'segment_type' => 'dynamic',
    'criteria'     => array(
        'state'          => 'IL',
        'min_engagement' => 70,
        'is_likely_supporter' => 1,
    ),
) );
```

### Import Voter File
```php
$results = $crm->import_export->import_csv( '/path/to/voter-file.csv', array(
    'format'          => 'l2',
    'update_existing' => true,
    'tag_imported'    => true,
    'create_segment'  => true,
    'segment_name'    => 'Q4 2024 Import',
) );
```

### Export Contacts
```php
$file_path = $crm->import_export->export_csv( array(
    'segment_id' => 5,
    'format'     => 'generic',
    'fields'     => array(
        'First Name' => 'first_name',
        'Email'      => 'email',
        'Phone'      => 'phone',
    ),
) );
```

## Scheduled Tasks

### Daily Maintenance (once daily)
- Optimize database tables
- Clean up old export files (7+ days)
- Recalculate dynamic segments

### Engagement Score Recalculation (twice daily)
- Recalculate all contact engagement scores
- Update engagement score history
- Maintain score algorithm versioning

## API Access

All CRM functionality is accessible via:
- Direct PHP class methods
- WordPress REST API (`/wp-json/campaignpress/v1/crm/*`)
- AJAX endpoints (nonce-protected)

## Extensibility

The system is designed for extensibility:
- Action hooks on all major operations
- Filter hooks for customization
- Custom field support
- Pluggable engagement algorithm
- Custom interaction types
- Custom tags and segments

## Future Enhancement Opportunities

1. **Mobile App Integration**
   - Native iOS/Android apps using REST API
   - Offline sync capability

2. **Advanced Analytics**
   - Predictive modeling
   - Turnout prediction
   - Support probability scoring

3. **Communication Tools**
   - Built-in email campaigns
   - SMS messaging
   - Automated follow-ups

4. **Map Integration**
   - Canvassing route optimization
   - Territory management
   - Heat maps

5. **Integration Plugins**
   - ActBlue donation sync
   - NationBuilder integration
   - VAN sync

## Support

For questions or issues, refer to the inline documentation in each class file. All functions include comprehensive PHPDoc comments with parameter descriptions, return types, and usage examples.
