# Smart Health Monitoring - 10 New Features Guide

## Overview

This document provides a comprehensive guide to the 10 new health features added to the Smart Health Monitoring WordPress theme. All features are fully functional, AJAX-powered, and include database persistence.

## Features Summary

| # | Feature | Database Table | Key Functions |
|---|---------|---------------|---------------|
| 1 | Appointment Scheduler | `wp_shm_appointments` | Schedule and track medical appointments |
| 2 | Medication Reminder System | `wp_shm_medications` | Track medications with dosage and reminders |
| 3 | Health Goals Tracker | `wp_shm_health_goals` | Set and monitor health goals with progress |
| 4 | Emergency Contact Manager | `wp_shm_emergency_contacts` | Store emergency contact information |
| 5 | Health Journal | `wp_shm_health_journal` | Daily health diary with mood tracking |
| 6 | Symptom Tracker | `wp_shm_symptoms` | Log symptoms with severity ratings |
| 7 | Water Intake Tracker | `wp_shm_water_intake` | Track daily water consumption |
| 8 | Sleep Quality Tracker | `wp_shm_sleep_records` | Monitor sleep patterns and quality |
| 9 | BMI Calculator & Tracker | `wp_shm_bmi_records` | Calculate and track BMI history |
| 10 | Health Document Manager | `wp_shm_health_documents` | Upload and manage medical documents |

## Installation & Setup

### Automatic Setup

All features are automatically enabled when the theme is activated:

1. Navigate to **Appearance > Themes**
2. Activate "Smart Health Monitoring" theme
3. Database tables are created automatically
4. Features are immediately available

### Creating the Features Page

1. Go to **Pages > Add New**
2. Enter page title: "Health Features" or "My Health"
3. Select template: "Health Features Dashboard"
4. Publish the page
5. All 10 features will be displayed on one page

## Feature Details

### 1. Appointment Scheduler

**Purpose:** Schedule and manage medical appointments

**Fields:**
- Doctor Name
- Appointment Date & Time
- Appointment Type (General Checkup, Follow-up, Lab Test, Consultation)
- Location
- Notes
- Status (scheduled, completed, cancelled)

**AJAX Endpoints:**
- `shm_add_appointment` - Create new appointment
- `shm_get_appointments` - Retrieve user appointments

**Display Function:**
```php
shm_appointments_widget();
```

**Use Cases:**
- Track upcoming doctor visits
- Store appointment history
- Set appointment reminders
- Manage multiple healthcare providers

---

### 2. Medication Reminder System

**Purpose:** Track all medications and set reminder times

**Fields:**
- Medication Name
- Dosage (e.g., 500mg)
- Frequency (Once daily, Twice daily, Three times daily, As needed)
- Start Date
- End Date
- Reminder Times (comma-separated, e.g., "08:00, 20:00")
- Instructions
- Active Status

**AJAX Endpoints:**
- `shm_add_medication` - Add new medication
- `shm_get_medications` - Get active medications

**Display Function:**
```php
shm_medications_widget();
```

**Use Cases:**
- Manage current prescriptions
- Track medication schedules
- Store dosage instructions
- Monitor treatment duration

---

### 3. Health Goals Tracker

**Purpose:** Set and track personal health goals

**Fields:**
- Goal Type (Weight, Exercise, Steps, Blood Pressure, Glucose, Other)
- Goal Title
- Target Value
- Current Value
- Unit (kg, steps, mmHg, etc.)
- Start Date
- Target Date
- Notes
- Status (in_progress, completed, abandoned)

**AJAX Endpoints:**
- `shm_add_health_goal` - Create new goal
- `shm_update_health_goal` - Update progress
- `shm_get_health_goals` - Get user goals

**Display Function:**
```php
shm_health_goals_widget();
```

**Features:**
- Visual progress bars
- Percentage completion
- Multiple goal types
- Progress tracking over time

---

### 4. Emergency Contact Manager

**Purpose:** Store emergency contact information for quick access

**Fields:**
- Contact Name
- Relationship (Spouse, Parent, Sibling, etc.)
- Primary Phone
- Secondary Phone
- Email Address
- Physical Address
- Primary Contact Flag
- Notes

**AJAX Endpoints:**
- `shm_add_emergency_contact` - Add new contact
- `shm_get_emergency_contacts` - Get all contacts

**Display Function:**
```php
shm_emergency_contacts_widget();
```

**Features:**
- Designate primary contact
- Multiple phone numbers
- Full contact details
- Quick access in emergencies

---

### 5. Health Journal & Daily Notes

**Purpose:** Maintain a daily health diary

**Fields:**
- Entry Date
- Mood (Excellent, Good, Neutral, Low, Poor)
- Energy Level (1-10 scale)
- Sleep Quality (1-10 scale)
- Notes (free text)
- Tags (comma-separated)
- Privacy Flag

**AJAX Endpoints:**
- `shm_add_journal_entry` - Add new entry
- `shm_get_journal_entries` - Get recent entries (last 30)

**Display Function:**
```php
shm_health_journal_widget();
```

**Features:**
- Track emotional well-being
- Monitor energy patterns
- Searchable with tags
- Private entries
- Historical tracking

---

### 6. Symptom Checker & Tracker

**Purpose:** Log and monitor symptoms

**Fields:**
- Symptom Name
- Severity (1-10 scale)
- Body Part/Location
- Onset Date
- Duration (< 1 hour, 1-6 hours, 6-24 hours, 1-3 days, > 3 days)
- Frequency (Constant, Intermittent, Occasional)
- Description
- Triggers
- Relieving Factors

**AJAX Endpoints:**
- `shm_add_symptom` - Log new symptom
- `shm_get_symptoms` - Get symptom history

**Display Function:**
```php
shm_symptom_tracker_widget();
```

**Features:**
- Severity rating system
- Track symptom patterns
- Identify triggers
- Monitor symptom changes

---

### 7. Water Intake Tracker

**Purpose:** Track daily hydration

**Fields:**
- Intake Date
- Amount (ml)
- Intake Time
- Daily Goal (default: 2000ml)
- Notes

**AJAX Endpoints:**
- `shm_add_water_intake` - Log water intake
- `shm_get_water_today` - Get today's total

**Display Function:**
```php
shm_water_tracker_widget();
```

**Features:**
- Quick add buttons (250ml, 500ml, 750ml, 1L)
- Real-time progress bar
- Daily goal tracking
- Percentage completion
- Custom amounts

---

### 8. Sleep Quality Tracker

**Purpose:** Monitor sleep patterns and quality

**Fields:**
- Sleep Date
- Bedtime
- Wake Time
- Total Hours
- Quality Rating (1-10)
- Number of Interruptions
- Dream Recall (Yes/No)
- Mood on Wake (Refreshed, Good, Tired, Groggy, Exhausted)
- Notes

**AJAX Endpoints:**
- `shm_add_sleep_record` - Add sleep record
- `shm_get_sleep_records` - Get sleep history

**Display Function:**
```php
shm_sleep_tracker_widget();
```

**Features:**
- 7-day average calculation
- Sleep quality trends
- Interruption tracking
- Mood correlation
- Historical data

---

### 9. BMI Calculator & Tracker

**Purpose:** Calculate and track Body Mass Index over time

**Fields:**
- Record Date
- Weight (kg)
- Height (cm)
- BMI (auto-calculated)
- BMI Category (Underweight, Normal, Overweight, Obese)
- Unit System (Metric/Imperial)
- Notes

**AJAX Endpoints:**
- `shm_add_bmi_record` - Save BMI record
- `shm_get_bmi_records` - Get BMI history

**Display Function:**
```php
shm_bmi_calculator_widget();
```

**Features:**
- Instant BMI calculation
- Historical tracking
- BMI category classification
- Weight trend analysis
- Metric/Imperial support

**BMI Categories:**
- < 18.5: Underweight
- 18.5 - 24.9: Normal weight
- 25 - 29.9: Overweight
- ≥ 30: Obese

---

### 10. Health Document Manager

**Purpose:** Upload and organize medical documents

**Fields:**
- Document Name
- Document Type (Lab Report, Prescription, X-Ray, Vaccination, Insurance, Discharge Summary, Other)
- Document Date
- File Upload (PDF, JPG, PNG - Max 5MB)
- Description
- Tags
- Privacy Flag

**AJAX Endpoints:**
- `shm_upload_document` - Upload new document
- `shm_get_documents` - Get user documents
- `shm_delete_document` - Delete document

**Display Function:**
```php
shm_document_manager_widget();
```

**Features:**
- Secure file uploads
- Document categorization
- Tag-based organization
- Quick view/download
- Delete functionality
- Private storage

---

## Usage Examples

### Display All Features on One Page

Create a page with template "Health Features Dashboard" (page-features.php) - all features will be displayed automatically.

### Display Individual Features

Add features to any template or page:

```php
<?php
// In your template file
if ( is_user_logged_in() ) {
    ?>
    <div class="health-features">
        <h2>My Health Tools</h2>
        
        <?php shm_water_tracker_widget(); ?>
        <?php shm_sleep_tracker_widget(); ?>
        <?php shm_bmi_calculator_widget(); ?>
    </div>
    <?php
}
?>
```

### Custom Widget Sidebar

Register a custom sidebar and add features:

```php
// In functions.php
register_sidebar( array(
    'name' => 'Health Tools',
    'id' => 'health-tools-sidebar'
) );

// In sidebar-health.php
<?php
if ( is_active_sidebar( 'health-tools-sidebar' ) ) {
    dynamic_sidebar( 'health-tools-sidebar' );
} else {
    shm_appointments_widget();
    shm_medications_widget();
}
?>
```

## Styling & Customization

### CSS Classes

All features use consistent CSS classes:
- `.shm-feature-card` - Feature container
- `.shm-btn` - Button elements
- `.shm-modal` - Modal dialogs
- `.shm-form-group` - Form groups
- `.shm-progress-bar` - Progress indicators

### Color Variables

Customize feature colors in CSS:

```css
:root {
    --color-primary: #0ea5e9;
    --color-success: #10b981;
    --color-warning: #f59e0b;
    --color-danger: #ef4444;
}
```

### Dark Mode Support

All features include dark mode styles:

```css
body.dark-mode .shm-feature-card {
    background: #1e293b;
    color: #e2e8f0;
}
```

## Security Features

All features implement:
- **Nonce Verification:** AJAX requests verified with WordPress nonces
- **User Authentication:** Login required for all operations
- **Data Sanitization:** All inputs sanitized before database storage
- **SQL Injection Prevention:** Prepared statements used throughout
- **Output Escaping:** All output properly escaped
- **File Upload Security:** Type and size restrictions on uploads

## Developer Notes

### Database Schema

All tables use consistent patterns:
- Primary key: `id` (mediumint AUTO_INCREMENT)
- User reference: `user_id` (bigint)
- Timestamp: `created_at` (datetime DEFAULT CURRENT_TIMESTAMP)
- Indexes on `user_id` for performance

### AJAX Pattern

All features follow the same AJAX pattern:

```javascript
jQuery.ajax({
    url: ajaxurl,
    type: 'POST',
    data: {
        action: 'shm_feature_action',
        nonce: nonce,
        // ... other data
    },
    success: function(response) {
        if (response.success) {
            // Handle success
        }
    }
});
```

### Adding New Features

To add additional features, follow this pattern:

1. Create `/inc/feature-name.php`
2. Implement init function with database table creation
3. Add AJAX handlers for add/get/update/delete
4. Create widget display function
5. Include in `functions.php`

## Troubleshooting

### Database Tables Not Created

Run this in WordPress:
```php
global $wpdb;
// Check if table exists
$table_name = $wpdb->prefix . 'shm_appointments';
if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    // Table doesn't exist - reactivate theme
}
```

### AJAX Not Working

1. Check browser console for errors
2. Verify nonce is being passed correctly
3. Ensure user is logged in
4. Check WordPress AJAX URL is correct

### Features Not Displaying

1. Verify user is logged in
2. Check theme is activated
3. Ensure jQuery is loaded
4. Verify template is using correct functions

## Best Practices

1. **Regular Backups:** Backup database regularly as health data is stored
2. **HTTPS Required:** Use HTTPS for all health data transmission
3. **Privacy Compliance:** Ensure compliance with health data regulations
4. **Data Export:** Provide users ability to export their data
5. **Data Retention:** Define and implement data retention policies

## Support & Contributing

For issues or feature requests related to these health features, please:
1. Check this documentation first
2. Review the source code in `/inc/feature-*.php`
3. Check WordPress error logs
4. Submit detailed bug reports

## Version History

**Version 1.1.0** (Current)
- Added 10 new health features
- Complete AJAX API
- Database persistence
- Dark mode support
- Mobile responsive design

## License

GPL v2 or later - Same as WordPress

---

**Note:** These features are for informational and tracking purposes only. They are NOT medical devices and should not be used for medical diagnosis or treatment. Always consult healthcare professionals for medical advice.
