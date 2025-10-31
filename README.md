# Smart Health Monitoring (SHM) - WordPress Platform

A comprehensive WordPress platform for health monitoring and telemedicine, consisting of a custom theme and plugin for managing health data, wearable integrations, and patient dashboards.

## 🏥 Overview

This project provides a complete solution for health data management with:

- **Modern Dashboard Interface**: Clean, intuitive UI for viewing health metrics
- **Health Data Tracking**: Blood pressure, glucose, heart rate, and activity monitoring
- **Wearable Integration**: OAuth 2.0 integration with Fitbit and Garmin
- **REST API**: Comprehensive API for mobile and third-party integrations
- **Role-Based Access**: Patient, Clinician, and Admin roles with specific permissions
- **Data Export**: CSV and PDF export capabilities
- **Real-time Alerts**: Automated health threshold monitoring
- **WCAG 2.1 AA Compliant**: Fully accessible interface

## 📦 Components

### 1. WordPress Plugin: SHM Data & Integrations
Location: `/wp-content/plugins/shm-data-integrations/`

**Features:**
- Custom database tables for health metrics
- Wearable device OAuth 2.0 connectors
- REST API endpoints
- Custom roles and capabilities
- Admin dashboard with charts
- WP-CLI commands
- Gutenberg blocks and shortcodes
- Audit logging

**Documentation:** See [Plugin README](wp-content/plugins/shm-data-integrations/README.md)

### 2. WordPress Theme: Smart Health Monitoring
Location: `/wp-content/themes/smart-health-monitoring/`

**Features:**
- Dashboard, Reports, and Integrations templates
- Dark/Light mode with system preference detection
- Mobile-first responsive design
- Chart.js integration for data visualization
- Custom Gutenberg blocks
- Performance optimized (lazy loading, deferred scripts)
- Accessibility focused (WCAG 2.1 AA)

**Documentation:** See [Theme README](wp-content/themes/smart-health-monitoring/README.md)

## 🚀 Quick Start

### Prerequisites

- WordPress 6.0+
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx web server

### Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd smart-health-monitoring
   ```

2. **Set up WordPress:**
   - Create a database for WordPress
   - Configure `wp-config.php` with your database credentials
   - Complete WordPress installation

3. **Activate the plugin:**
   - Navigate to **Plugins > Installed Plugins**
   - Activate "SHM Data & Integrations"

4. **Activate the theme:**
   - Navigate to **Appearance > Themes**
   - Activate "Smart Health Monitoring"

5. **Create required pages:**
   ```
   Dashboard (using Dashboard template)
   Reports (using Reports template)
   Integrations (using Integrations template)
   ```

6. **Configure plugin settings:**
   - Navigate to **Health Data > Settings**
   - Add Fitbit/Garmin API credentials (optional)

### Initial Setup with WP-CLI

```bash
# Seed demo data (30 days for user ID 1)
wp shm seed --user=1 --days=30

# Test sync functionality
wp shm sync --user=1 --connector=fitbit

# Export data
wp shm export --user=1 --range=30d --format=csv
```

## 🎯 Key Features

### For Patients
- View health metrics dashboard
- Track blood pressure, glucose, heart rate, and activity
- Receive automated health alerts
- Connect wearable devices
- Export personal health data
- Access health education content

**10 New Advanced Health Features:**
1. **Appointment Scheduler** - Schedule and manage healthcare appointments
2. **Medication Reminder System** - Track medications and set reminders
3. **Health Goals Tracker** - Set and monitor health goals with progress tracking
4. **Emergency Contact Manager** - Store and manage emergency contacts
5. **Health Journal** - Daily health diary with mood and energy tracking
6. **Symptom Tracker** - Log and monitor symptoms with severity ratings
7. **Water Intake Tracker** - Track daily hydration with visual progress
8. **Sleep Quality Tracker** - Monitor sleep patterns and quality
9. **BMI Calculator & Tracker** - Calculate and track BMI over time
10. **Health Document Manager** - Upload and organize medical documents

### For Clinicians
- View assigned patient data
- Create clinical notes
- Manage health alerts
- Generate patient reports
- Monitor multiple patients
- Access patient health features data

### For Administrators
- Full system access
- Configure integrations
- Manage user roles
- View audit logs
- Export all data
- Configure threshold alerts
- Manage all health features

## 📊 Database Schema

The plugin creates the following tables:

- `wp_shm_bp` - Blood pressure readings
- `wp_shm_glucose` - Blood glucose readings
- `wp_shm_activity` - Daily activity data
- `wp_shm_alerts` - Health alerts
- `wp_shm_audit_logs` - Audit trail
- `wp_shm_connections` - Wearable connections

## 🔌 REST API

### Base URL
```
https://yoursite.com/wp-json/shm/v1
```

### Endpoints

**Metrics:**
- `GET /metrics/summary` - Get summary statistics
- `GET /metrics/series` - Get time-series data
- `POST /metrics/bp` - Create blood pressure record
- `POST /metrics/glucose` - Create glucose record
- `POST /metrics/activity` - Create activity record

**Alerts:**
- `GET /alerts` - Get user alerts
- `POST /alerts/{id}/resolve` - Resolve an alert

### Authentication

All endpoints require WordPress REST API authentication:

```javascript
fetch('/wp-json/shm/v1/metrics/summary?range=30d', {
  headers: {
    'X-WP-Nonce': wpApiSettings.nonce
  }
})
```

## 🔒 Security

- **Encrypted Tokens**: All OAuth tokens encrypted with AES-256-CBC
- **Nonce Verification**: All write operations protected
- **Prepared Statements**: SQL injection prevention
- **Capability Checks**: Role-based access control
- **Audit Logging**: Complete activity trail
- **Input Validation**: Strict data validation
- **HTTPS Required**: For production use

## 🌐 Internationalization

The plugin and theme are translation-ready:

```php
__( 'Text to translate', 'smart-health-monitoring' )
_e( 'Text to echo', 'smart-health-monitoring' )
```

Generate translation files:
```bash
wp i18n make-pot wp-content/plugins/shm-data-integrations
wp i18n make-pot wp-content/themes/smart-health-monitoring
```

## 🎨 Customization

### Theme Customization

Via **Appearance > Customize**:
- Color scheme (Light/Dark/Auto)
- Logo and site identity
- Menus (Primary, Footer)
- Widgets (Dashboard sidebar, Footer areas)

### Custom CSS Variables

```css
:root {
  --color-primary: #0ea5e9;
  --color-success: #10b981;
  --color-warning: #f59e0b;
}
```

### Hooks and Filters

**Plugin Filters:**
```php
add_filter('shm_metric_thresholds', function($thresholds) {
    $thresholds['bp_systolic_high'] = 150;
    return $thresholds;
});
```

**Plugin Actions:**
```php
add_action('shm_after_sync', function($user_id, $connector, $counts) {
    // Custom logic after wearable sync
}, 10, 3);
```

## 📱 Wearable Integrations

### Fitbit Setup

1. Create app at https://dev.fitbit.com/
2. Set callback URL: `https://yoursite.com/wp-admin/admin-ajax.php?action=shm_fitbit_callback`
3. Add credentials in plugin settings
4. Users connect via Integrations page

### Garmin Setup

Contact Garmin for Health API access and credentials.

### Adding Custom Connectors

```php
class SHM_Connector_Apple extends SHM_Connector_Base {
    protected $name = 'apple';
    protected $label = 'Apple Health';
    
    public function get_auth_url($user_id) { }
    public function handle_callback($code, $user_id) { }
    public function sync($user_id, $access_token, $connection) { }
    public function refresh_token($refresh_token) { }
}

add_filter('shm_register_connectors', function($connectors) {
    $connectors['apple'] = new SHM_Connector_Apple();
    return $connectors;
});
```

## 🧪 Testing

### Manual Testing
- Create test users with different roles
- Add sample health data
- Test wearable connections (sandbox mode)
- Verify exports (CSV/PDF)
- Test accessibility with screen readers

### WP-CLI Testing
```bash
# Seed test data
wp shm seed --user=1 --days=90

# Test sync
wp shm sync --user=1 --connector=fitbit

# Test export
wp shm export --user=1 --range=30d
```

## 📋 Requirements & Compliance

### Technical Requirements
- WordPress 6.0+
- PHP 7.4+ (8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.2+
- HTTPS (required for OAuth)
- 256MB+ PHP memory limit
- Modern browser (Chrome, Firefox, Safari, Edge)

### Compliance
- **WCAG 2.1 AA**: Accessibility compliant
- **GDPR Ready**: Data export and deletion capabilities
- **HIPAA Considerations**: Not a certified medical device (see disclaimer)

## ⚠️ Important Disclaimer

**THIS IS NOT A MEDICAL DEVICE**

This plugin and theme are NOT medical devices and should not be used for medical diagnosis or treatment. All health data displayed is for informational purposes only. Always consult with qualified healthcare professionals for medical advice, diagnosis, and treatment.

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch
3. Follow WordPress coding standards
4. Test thoroughly
5. Submit a pull request

## 📄 License

GPL v2 or later - see [LICENSE](LICENSE) file

## 🆘 Support

For support:
- Check documentation in README files
- Review plugin/theme READMEs
- Submit issues in the project repository
- Contact the development team

## 📚 Additional Resources

- [WordPress Developer Documentation](https://developer.wordpress.org/)
- [REST API Handbook](https://developer.wordpress.org/rest-api/)
- [Theme Development](https://developer.wordpress.org/themes/)
- [Plugin Development](https://developer.wordpress.org/plugins/)
- [WCAG Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

## 👥 Credits

- WordPress Core Team
- Chart.js for data visualization
- Gutenberg Block Editor
- Community contributors

---

**Version:** 1.0.0  
**Last Updated:** 2024

For the latest updates and documentation, visit the project repository.

## 👨‍💻 Author

**Lettu Kes dr. Muhammad Sobri Maulana, S.Kom, CEH, OSCP, OSCE**

### 📬 Contact

- **Email**: [muhammadsobrimaulana31@gmail.com](mailto:muhammadsobrimaulana31@gmail.com)
- **GitHub**: [github.com/sobri3195](https://github.com/sobri3195)
- **YouTube**: [Muhammad Sobri Maulana](https://www.youtube.com/@muhammadsobrimaulana6013)
- **Telegram**: [@winlin_exploit](https://t.me/winlin_exploit)
- **TikTok**: [@dr.sobri](https://www.tiktok.com/@dr.sobri)

### 💝 Support & Donation

Jika Anda merasa proyek ini bermanfaat, Anda dapat mendukung pengembangan lebih lanjut melalui:

- **Donasi**: [https://lynk.id/muhsobrimaulana](https://lynk.id/muhsobrimaulana)

### 👥 Join Our Community

- **WhatsApp Group**: [Join Here](https://chat.whatsapp.com/B8nwRZOBMo64GjTwdXV8Bl)


