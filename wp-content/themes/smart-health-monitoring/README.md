# Smart Health Monitoring WordPress Theme

A modern, responsive WordPress theme for healthtech/telemedicine with dashboard features for displaying health metrics (blood pressure, glucose, heart rate, steps/activity).

## Features

### Core Features
- **Modern Dashboard**: Clean, intuitive user interface for health data visualization
- **Health Metrics**: Display blood pressure, glucose, heart rate, and activity data
- **Dark/Light Mode**: User-selectable theme with system preference detection
- **Responsive Design**: Mobile-first approach, optimized for all screen sizes
- **Accessible**: WCAG 2.1 AA compliant with proper ARIA labels and keyboard navigation
- **Performance Optimized**: Lazy loading, deferred scripts, preloaded fonts
- **Gutenberg Ready**: Full support for block editor with custom blocks
- **Chart Integration**: Chart.js integration for data visualization
- **Custom Page Templates**: Dashboard, Reports, and Integrations templates

### 10 New Advanced Health Features

1. **Appointment Scheduler**
   - Schedule appointments with healthcare providers
   - Track appointment type, location, and notes
   - Automatic reminder system
   - View upcoming and past appointments

2. **Medication Reminder System**
   - Track all medications with dosage information
   - Set frequency and reminder times
   - Manage start and end dates
   - Store special instructions

3. **Health Goals Tracker**
   - Set personalized health goals (weight, exercise, steps, etc.)
   - Track progress with visual progress bars
   - Monitor target vs. current values
   - Add notes and track deadlines

4. **Emergency Contact Manager**
   - Store emergency contacts with complete information
   - Multiple phone numbers and email addresses
   - Set primary contact
   - Quick access to emergency information

5. **Health Journal & Daily Notes**
   - Daily health diary entries
   - Track mood, energy levels, and sleep quality
   - Searchable notes with tags
   - Private entries with date tracking

6. **Symptom Checker & Tracker**
   - Log symptoms with severity ratings (1-10)
   - Track body location and duration
   - Record triggers and relieving factors
   - Frequency tracking (constant, intermittent, occasional)

7. **Water Intake Tracker**
   - Track daily water consumption
   - Quick add buttons (250ml, 500ml, 750ml, 1L)
   - Customizable daily goals
   - Visual progress indicators
   - Historical tracking

8. **Sleep Quality Tracker**
   - Record bedtime and wake time
   - Track sleep quality ratings
   - Monitor sleep interruptions
   - Dream recall tracking
   - Mood on wake assessment
   - 7-day average calculations

9. **BMI Calculator & Tracker**
   - Calculate BMI with instant results
   - Track weight and height over time
   - BMI category classification
   - Support for metric and imperial units
   - Historical BMI chart
   - Health category indicators

10. **Health Document Manager**
    - Upload and store health documents (PDF, JPG, PNG)
    - Organize by document type (lab reports, prescriptions, x-rays, etc.)
    - Tag documents for easy searching
    - Secure, private document storage
    - Quick view and download options
    - Delete management

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- SHM Data & Integrations Plugin (companion plugin)

## Installation

1. Upload the `smart-health-monitoring` folder to `/wp-content/themes/`
2. Activate the theme through the 'Themes' menu in WordPress
3. Install and activate the SHM Data & Integrations plugin
4. Create pages using the provided page templates:
   - Dashboard (Template: Dashboard)
   - Reports (Template: Reports)
   - Integrations (Template: Integrations)

## Page Templates

### Dashboard (page-dashboard.php)
User health dashboard with:
- Metric cards for BP, glucose, heart rate, activity
- Health alerts
- Trend charts
- Recent entries log
- Date range selector

### Reports (page-reports.php)
Health reports and data export with:
- Date range filter
- Metric type filter
- Data table
- CSV/PDF export
- Summary statistics

### Integrations (page-integrations.php)
Wearable device integrations with:
- Connection status cards
- Manual sync triggers
- Device management
- Integration help

### Health Features Dashboard (page-features.php)
Comprehensive health management interface with all 10 new features:
- Appointments scheduling and management
- Medication tracking and reminders
- Health goals and progress monitoring
- Emergency contacts management
- Daily health journal entries
- Symptom tracking and logging
- Water intake monitoring
- Sleep quality tracking
- BMI calculator with history
- Health document storage

To create a Health Features page:
1. Create a new page in WordPress
2. Select "Health Features Dashboard" as the page template
3. Publish and access all features from one unified interface

## Customization

### Theme Options

Navigate to **Appearance > Customize** to access:
- **Theme Options**
  - Default Color Scheme (Light, Dark, Auto)
  - Enable/Disable Animations
- **Site Identity**
  - Logo upload
  - Site title and tagline
- **Menus**
  - Primary menu
  - Footer menu
- **Widgets**
  - Dashboard sidebar
  - Footer 1, 2, 3

### CSS Variables

The theme uses CSS custom properties for easy customization. Edit `style.css` to modify:

```css
:root {
    --color-primary: #0ea5e9;
    --color-secondary: #06b6d4;
    --color-success: #10b981;
    --color-warning: #f59e0b;
    --color-danger: #ef4444;
}
```

### Adding Custom Colors (Dark Mode)

```css
[data-theme="dark"] {
    --color-bg: #111827;
    --color-text: #f9fafb;
}
```

## Child Theme Support

To create a child theme:

1. Create a new directory in `/wp-content/themes/`
2. Create `style.css`:

```css
/*
Theme Name: My Custom Health Theme
Template: smart-health-monitoring
*/
```

3. Create `functions.php`:

```php
<?php
function my_child_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(), array( 'parent-style' ) );
}
add_action( 'wp_enqueue_scripts', 'my_child_theme_enqueue_styles' );
```

## Template Hierarchy

The theme follows WordPress template hierarchy:
- `front-page.php` - Front page
- `page-dashboard.php` - Dashboard template
- `page-reports.php` - Reports template
- `page-integrations.php` - Integrations template
- `single.php` - Single post
- `archive.php` - Archive pages
- `search.php` - Search results
- `404.php` - 404 error page
- `header.php` - Site header
- `footer.php` - Site footer
- `template-parts/content.php` - Post content

## JavaScript API

The theme exposes a global `SHMTheme` object:

```javascript
// Access theme functionality
SHMTheme.loadDashboardData('30d');
SHMTheme.fetchMetricData('bp', '7d');
```

Available via `shmTheme` localized script:
```javascript
shmTheme.ajaxUrl   // WordPress AJAX URL
shmTheme.restUrl   // REST API base URL
shmTheme.nonce     // REST API nonce
shmTheme.userId    // Current user ID
shmTheme.isDark    // Dark mode preference
shmTheme.i18n      // Translated strings
```

## Hooks & Filters

### Actions

```php
// After theme setup
do_action( 'shm_theme_setup' );

// Before loading dashboard
do_action( 'shm_before_dashboard' );

// After loading dashboard
do_action( 'shm_after_dashboard' );
```

### Filters

```php
// Modify body classes
add_filter( 'shm_body_classes', function( $classes ) {
    $classes[] = 'my-custom-class';
    return $classes;
} );

// Modify content width
add_filter( 'shm_theme_content_width', function( $width ) {
    return 1400;
} );
```

## Template Functions

### Helper Functions

```php
// Get user avatar
shm_theme_get_user_avatar( $user_id, $size );

// Display user greeting
shm_theme_user_greeting();

// Display dark mode toggle
shm_theme_dark_mode_toggle();

// Display breadcrumbs
shm_theme_breadcrumbs();

// Format date range
shm_theme_format_date_range( $from, $to );

// Display status badge
shm_theme_status_badge( $status, $label );

// Display pagination
shm_theme_pagination();
```

## Performance Optimization

The theme is optimized for performance:
- Lazy loading for images
- Deferred JavaScript loading
- Preloaded fonts
- Minified assets (in production)
- Transient caching for API calls
- Skeleton loading states

## Accessibility

WCAG 2.1 AA compliant features:
- Proper heading hierarchy
- ARIA labels and roles
- Keyboard navigation support
- Focus indicators
- Skip to content link
- High contrast support
- Screen reader friendly

## Browser Support

- Chrome (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Edge (last 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Charts Not Displaying

Ensure Chart.js is loaded:
```php
wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true );
```

### Dark Mode Not Working

Check localStorage is enabled and theme preference is saved:
```javascript
localStorage.getItem('shm-theme');
```

### Plugin Not Found Errors

Install and activate the SHM Data & Integrations plugin.

## License

GPL v2 or later

## Credits

- Chart.js for data visualization
- WordPress Block Editor
- Feather Icons (conceptual reference)

## Support

For support, please contact the development team or submit an issue in the project repository.

## Using the Health Features

### Displaying Features in Your Theme

You can display individual features anywhere in your theme by calling their widget functions:

```php
// Display appointments widget
shm_appointments_widget();

// Display medications widget
shm_medications_widget();

// Display health goals widget
shm_health_goals_widget();

// Display emergency contacts widget
shm_emergency_contacts_widget();

// Display health journal widget
shm_health_journal_widget();

// Display symptom tracker widget
shm_symptom_tracker_widget();

// Display water tracker widget
shm_water_tracker_widget();

// Display sleep tracker widget
shm_sleep_tracker_widget();

// Display BMI calculator widget
shm_bmi_calculator_widget();

// Display document manager widget
shm_document_manager_widget();
```

### AJAX Endpoints

All features use AJAX for data management. Available actions:

**Appointments:**
- `shm_add_appointment` - Add new appointment
- `shm_get_appointments` - Get user appointments

**Medications:**
- `shm_add_medication` - Add new medication
- `shm_get_medications` - Get user medications

**Health Goals:**
- `shm_add_health_goal` - Add new goal
- `shm_update_health_goal` - Update goal progress
- `shm_get_health_goals` - Get user goals

**Emergency Contacts:**
- `shm_add_emergency_contact` - Add new contact
- `shm_get_emergency_contacts` - Get user contacts

**Health Journal:**
- `shm_add_journal_entry` - Add new entry
- `shm_get_journal_entries` - Get user entries

**Symptom Tracker:**
- `shm_add_symptom` - Log new symptom
- `shm_get_symptoms` - Get user symptoms

**Water Tracker:**
- `shm_add_water_intake` - Log water intake
- `shm_get_water_today` - Get today's water intake

**Sleep Tracker:**
- `shm_add_sleep_record` - Add sleep record
- `shm_get_sleep_records` - Get sleep records

**BMI Calculator:**
- `shm_add_bmi_record` - Add BMI record
- `shm_get_bmi_records` - Get BMI records

**Document Manager:**
- `shm_upload_document` - Upload document
- `shm_get_documents` - Get user documents
- `shm_delete_document` - Delete document

### Database Tables

The features create the following database tables:
- `wp_shm_appointments` - Appointment records
- `wp_shm_medications` - Medication tracking
- `wp_shm_health_goals` - Health goals
- `wp_shm_emergency_contacts` - Emergency contacts
- `wp_shm_health_journal` - Journal entries
- `wp_shm_symptoms` - Symptom logs
- `wp_shm_water_intake` - Water intake records
- `wp_shm_sleep_records` - Sleep tracking
- `wp_shm_bmi_records` - BMI history
- `wp_shm_health_documents` - Document metadata

Tables are created automatically on theme activation.

## Changelog

### Version 1.1.0
- Added 10 new advanced health features
- New Health Features Dashboard template
- Enhanced feature management system
- Individual feature widgets
- Comprehensive AJAX API
- Dark mode support for all features

### Version 1.0.0
- Initial release
- Dashboard, Reports, and Integrations templates
- Dark/Light mode
- Gutenberg integration
- WCAG 2.1 AA compliance
- Mobile-responsive design
