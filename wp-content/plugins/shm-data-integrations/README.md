# SHM Data & Integrations Plugin

WordPress plugin for managing health data, wearable integrations (OAuth 2.0), REST API, roles/permissions, and reporting for Smart Health Monitoring.

## Features

- **Custom Database Tables**: Stores blood pressure, glucose, activity data, alerts, and audit logs
- **Custom Roles**: Patient, Clinician, and Health Admin roles with specific capabilities
- **Wearable Integrations**: OAuth 2.0 integration with Fitbit and Garmin (extensible)
- **REST API**: Comprehensive endpoints for metrics, alerts, and data management
- **Admin Dashboard**: Full-featured admin interface with charts and data visualization
- **Gutenberg Blocks**: Custom blocks for displaying health metrics
- **Shortcodes**: Flexible shortcodes for embedding health data anywhere
- **Automated Sync**: Cron job for automatic wearable data synchronization
- **Data Export**: CSV and PDF export functionality
- **WP-CLI Commands**: Command-line tools for data seeding, syncing, and exporting

## Installation

1. Upload the `shm-data-integrations` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure API credentials in Settings > SHM Settings
4. Create users with appropriate roles (Patient, Clinician, or Health Admin)

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher

## Database Tables

The plugin creates the following custom tables:

- `wp_shm_bp` - Blood pressure readings
- `wp_shm_glucose` - Blood glucose readings
- `wp_shm_activity` - Daily activity data (steps, calories, heart rate)
- `wp_shm_alerts` - Health alerts and notifications
- `wp_shm_audit_logs` - Audit trail for data changes
- `wp_shm_connections` - Wearable device connections

## REST API Endpoints

### Metrics

- `GET /wp-json/shm/v1/metrics/summary` - Get summary statistics
  - Parameters: `range` (7d, 30d), `metrics` (bp,glucose,activity)
- `GET /wp-json/shm/v1/metrics/series` - Get time-series data
  - Parameters: `metric` (bp, glucose, activity), `from`, `to`
- `POST /wp-json/shm/v1/metrics/bp` - Create blood pressure record
- `POST /wp-json/shm/v1/metrics/glucose` - Create glucose record
- `POST /wp-json/shm/v1/metrics/activity` - Create activity record

### Alerts

- `GET /wp-json/shm/v1/alerts` - Get alerts
  - Parameters: `resolved` (true/false)
- `POST /wp-json/shm/v1/alerts/{id}/resolve` - Resolve an alert

## Roles & Capabilities

### Patient (shm_patient)
- `shm_view_own_data` - View their own health data
- `shm_manage_own_data` - Manage their own health data
- `shm_connect_wearables` - Connect wearable devices

### Clinician (shm_clinician)
- All patient capabilities plus:
- `shm_view_patient_data` - View assigned patients' data
- `shm_create_notes` - Create clinical notes
- `shm_manage_alerts` - Manage health alerts
- `shm_view_reports` - View health reports

### Health Admin (shm_admin)
- All clinician capabilities plus:
- `shm_manage_all_data` - Manage all health data
- `shm_manage_settings` - Configure plugin settings
- `shm_manage_integrations` - Manage wearable integrations
- `shm_export_data` - Export health data
- `shm_view_audit_logs` - View audit logs

## Shortcodes

### [shm_metric]
Display a single health metric card.

```
[shm_metric metric="bp" range="7d"]
[shm_metric metric="glucose" range="30d"]
[shm_metric metric="activity" range="7d"]
```

### [shm_chart]
Display a trend chart.

```
[shm_chart metric="bp" type="line" range="30d"]
[shm_chart metric="glucose" type="line" range="7d"]
```

### [shm_alerts]
Display active health alerts.

```
[shm_alerts]
```

### [shm_integrations]
Display wearable integration status.

```
[shm_integrations]
```

## Gutenberg Blocks

- **shm/metric-card** - Display health metric with status indicator
- **shm/weekly-trend** - Display weekly/monthly trend chart
- **shm/alerts-list** - Display active alerts
- **shm/integration-status** - Display connected devices

## WP-CLI Commands

### Seed Demo Data
```bash
wp shm seed --user=1 --days=30
```

### Sync Wearables
```bash
# Sync all connections
wp shm sync

# Sync specific user and connector
wp shm sync --user=1 --connector=fitbit
```

### Export Data
```bash
wp shm export --user=1 --range=30d --format=csv
```

## Wearable Integrations

### Fitbit Setup

1. Create a Fitbit app at https://dev.fitbit.com/
2. Set OAuth 2.0 Application Type to "Server"
3. Set callback URL to: `https://yoursite.com/wp-admin/admin-ajax.php?action=shm_fitbit_callback`
4. Add Client ID and Client Secret in plugin settings
5. Users can connect via Integrations page

### Garmin Setup

Garmin integration requires Garmin Health API access. Contact Garmin for API credentials.

### Creating Custom Connectors

To add a new wearable connector:

1. Create a class extending `SHM_Connector_Base`
2. Implement required methods: `get_auth_url()`, `handle_callback()`, `sync()`, `refresh_token()`
3. Register the connector using the `shm_register_connectors` filter

Example:

```php
class SHM_Connector_Apple extends SHM_Connector_Base {
    protected $name = 'apple';
    protected $label = 'Apple Health';
    
    // Implement required methods...
}

add_filter('shm_register_connectors', function($connectors) {
    $connectors['apple'] = new SHM_Connector_Apple();
    return $connectors;
});
```

## Filters & Actions

### Filters

- `shm_metric_thresholds` - Modify health metric thresholds
- `shm_register_connectors` - Register custom wearable connectors

Example:
```php
add_filter('shm_metric_thresholds', function($thresholds) {
    $thresholds['bp_systolic_high'] = 150;
    return $thresholds;
});
```

### Actions

- `shm_init` - Fires after plugin initialization
- `shm_bp_inserted` - Fires after blood pressure record is created
- `shm_glucose_inserted` - Fires after glucose record is created
- `shm_activity_inserted` - Fires after activity record is created
- `shm_after_sync` - Fires after wearable sync completes

Example:
```php
add_action('shm_after_sync', function($user_id, $connector, $counts) {
    error_log("Synced {$counts['activity']} records for user {$user_id}");
}, 10, 3);
```

## Security

- All tokens are encrypted using AES-256-CBC
- Nonce verification on all write operations
- Prepared statements for all database queries
- Capability checks on all operations
- Audit logging for data changes
- Input validation and sanitization

## Disclaimer

**This plugin is NOT a medical device and should not be used for medical diagnosis or treatment.**

All health data displayed is for informational purposes only. Always consult with qualified healthcare professionals for medical advice, diagnosis, and treatment.

## Support

For support, please contact the development team or submit an issue in the project repository.

## License

GPL v2 or later
