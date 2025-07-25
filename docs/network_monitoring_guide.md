# Network Monitoring System Guide

## Overview

The Network Monitoring System provides comprehensive interface monitoring for your LAN Management System (LMS). It supports both REST API and SNMP protocols for collecting interface statistics from network devices, with advanced analytics, alerting, and reporting capabilities.

## Features

### ✅ **Multi-Protocol Support**
- **REST API**: Primary method for MikroTik devices
- **SNMP Fallback**: Automatic fallback when REST API fails
- **Hybrid Approach**: Best of both worlds

### ✅ **Real-Time Monitoring**
- Live interface statistics collection
- Historical data storage
- Automatic data cleanup (30-day retention)
- Real-time dashboard updates

### ✅ **Advanced Analytics**
- Growth trend analysis
- Capacity planning predictions
- Bandwidth utilization reports
- Performance metrics

### ✅ **Alert System**
- Email notifications for high usage
- Interface status monitoring
- Customizable thresholds
- Alert history tracking

### ✅ **Interactive Dashboards**
- Chart.js powered graphs
- Multiple chart types (line, area, bar, scatter)
- Device and interface selection
- Real-time data visualization
- Advanced filtering options

### ✅ **Comprehensive Reporting**
- Daily, weekly, monthly reports
- Top interfaces analysis
- CSV export functionality
- Custom date ranges

## Modules

### 1. **Network Monitoring (Basic)**
- **File**: `modules/network_monitoring.php`
- **Purpose**: Simple interface polling and stats display
- **Features**: Manual polling, basic table view

### 2. **Network Monitoring (Enhanced)**
- **File**: `modules/network_monitoring_enhanced.php`
- **Purpose**: Advanced monitoring with SNMP support
- **Features**: 
  - REST API + SNMP fallback
  - Advanced filtering
  - Debug information
  - Enhanced error handling

### 3. **Network Dashboard**
- **File**: `modules/network_dashboard.php`
- **Purpose**: Interactive graphs and visualization
- **Features**:
  - Chart.js graphs
  - Device/interface selection
  - Real-time data display

### 4. **Advanced Graphing**
- **File**: `modules/advanced_graphing.php`
- **Purpose**: Advanced visualization with multiple chart types
- **Features**:
  - Multiple chart types (line, area, bar, scatter)
  - Custom time ranges
  - Real-time updates
  - Chart export and sharing
  - Distribution and peak analysis charts

### 5. **Network Alerts**
- **File**: `modules/network_alerts.php`
- **Purpose**: Alert system for network issues
- **Features**:
  - High usage detection
  - Interface status monitoring
  - Email notifications
  - Alert history
  - Test email functionality

### 6. **Bandwidth Reports**
- **File**: `modules/bandwidth_reports.php`
- **Purpose**: Comprehensive bandwidth analysis
- **Features**:
  - Daily, weekly, monthly reports
  - Top interfaces analysis
  - CSV export
  - Custom device filtering
  - Statistical analysis

### 7. **Capacity Planning**
- **File**: `modules/capacity_planning.php`
- **Purpose**: Growth analysis and capacity predictions
- **Features**:
  - Growth trend analysis
  - Capacity predictions (3, 6, 12 months)
  - Utilization analysis
  - Infrastructure recommendations
  - Priority-based alerts

### 8. **System Status**
- **File**: `modules/system_status.php`
- **Purpose**: Overall system health monitoring
- **Features**:
  - System health score
  - Device status overview
  - Performance metrics
  - Recent activity tracking
  - System information display

### 9. **API Endpoint**
- **File**: `modules/network_monitoring_api.php`
- **Purpose**: JSON API for graph data
- **Usage**: Used by dashboard for chart data

### 10. **Automatic Polling**
- **File**: `cron_poll_interfaces.php`
- **Purpose**: Automated data collection
- **Features**: Cron job script with logging

## Database Schema

### `interface_stats` Table
```sql
CREATE TABLE interface_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    interface_name VARCHAR(64) NOT NULL,
    rx_bytes BIGINT UNSIGNED NOT NULL,
    tx_bytes BIGINT UNSIGNED NOT NULL,
    rx_packets BIGINT UNSIGNED DEFAULT 0,
    tx_packets BIGINT UNSIGNED DEFAULT 0,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX(device_id, interface_name, timestamp)
);
```

### `network_alerts` Table
```sql
CREATE TABLE network_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    interface_name VARCHAR(64) NOT NULL,
    alert_type VARCHAR(32) NOT NULL,
    details JSON,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX(device_id, interface_name, timestamp),
    INDEX(alert_type, timestamp)
);
```

## Installation & Setup

### 1. **Database Setup**
```bash
# Run the SQL initialization
mysql -u slms -p'mlss15gent001' -h localhost slmsdb < lms_db_init.sql
```

### 2. **Menu Integration**
The modules are automatically added to the admin menu:
- Network Monitoring
- Network Dashboard
- Network Monitoring (Enhanced)
- Advanced Graphing
- Network Alerts
- Bandwidth Reports
- Capacity Planning
- System Status

### 3. **SNMP Support (Optional)**
```bash
# Install PHP SNMP extension
sudo apt-get install php-snmp

# Restart web server
sudo systemctl restart apache2
```

## Usage

### **Manual Polling**
1. Navigate to **Network Monitoring** or **Network Monitoring (Enhanced)**
2. Click **"Poll All Devices Now"**
3. View results in the statistics table

### **Interactive Dashboard**
1. Navigate to **Network Dashboard** or **Advanced Graphing**
2. Select device and interface from dropdowns
3. Choose time range and chart type
4. View real-time graphs

### **Alert Management**
1. Navigate to **Network Alerts**
2. Click **"Check All Interfaces"** to scan for issues
3. Configure email alerts and test notifications
4. Review alert history

### **Report Generation**
1. Navigate to **Bandwidth Reports**
2. Select report type (daily, weekly, monthly)
3. Choose device and date range
4. Generate and export reports

### **Capacity Planning**
1. Navigate to **Capacity Planning**
2. Analyze growth trends
3. Generate capacity predictions
4. Review recommendations

### **System Monitoring**
1. Navigate to **System Status**
2. View overall health score
3. Monitor device status
4. Check performance metrics

### **Automatic Polling**
Set up cron job for automatic data collection:

```bash
# Edit crontab
crontab -e

# Add this line (runs every 5 minutes)
*/5 * * * * /usr/bin/php /path/to/your/lms/cron_poll_interfaces.php
```

## API Reference

### **Get Interface Stats**
```
GET /modules/network_monitoring_api.php?device_id=1&iface=ether1
```

**Response:**
```json
{
    "timestamps": ["2025-01-19 16:00:00", "2025-01-19 16:05:00"],
    "rx_bytes": [1000000, 1500000],
    "tx_bytes": [500000, 750000]
}
```

### **Get Available Interfaces**
```
GET /modules/network_monitoring_api.php?device_id=1&action=interfaces
```

**Response:**
```json
["ether1", "ether2", "wlan1"]
```

## Configuration

### **Device Setup**
Ensure your skeleton devices have:
- Valid IP address
- API username and password
- Network connectivity

### **SNMP Configuration**
For SNMP fallback:
- Default community: `public`
- Standard SNMP v2c
- Interface statistics OIDs

### **Alert Configuration**
- Email server configuration
- Alert thresholds (default: 50 Mbps)
- Notification recipients

## Troubleshooting

### **Common Issues**

1. **"SNMP extension not loaded"**
   ```bash
   sudo apt-get install php-snmp
   sudo systemctl restart apache2
   ```

2. **"No devices found with API credentials"**
   - Check skeleton_devices table
   - Ensure api_username and api_password are set

3. **"REST API failed"**
   - Verify device connectivity
   - Check API credentials
   - SNMP will automatically be used as fallback

4. **"No data in graphs"**
   - Run manual polling first
   - Check if devices are responding
   - Verify database connectivity

5. **"Email alerts not working"**
   - Check mail server configuration
   - Verify recipient email addresses
   - Test email functionality

### **Debug Information**
The enhanced monitoring module provides detailed debug output showing:
- Device processing status
- Interface discovery
- Polling results
- Error details

## Performance Considerations

### **Data Retention**
- Automatic cleanup after 30 days
- Configurable in `cron_poll_interfaces.php`

### **Polling Frequency**
- Recommended: Every 5 minutes
- Adjust based on network size and requirements

### **Database Optimization**
- Indexes on device_id, interface_name, timestamp
- Efficient queries for dashboard
- JSON storage for alert details

### **Memory Usage**
- Monitor PHP memory usage
- Adjust memory_limit in php.ini if needed
- Optimize chart rendering for large datasets

## Integration with Existing LMS

### **Menu Integration**
- Automatically added to admin menu
- Consistent with existing UI design
- Bootstrap 5 styling

### **Device Management**
- Links to device management
- Integrates with existing device records
- API credential management

### **Network Management**
- Connects to network monitoring
- Supports existing network structure
- VLAN and subnet integration

## Advanced Features

### **Real-Time Updates**
- WebSocket-like polling for live data
- Configurable update intervals
- Background data collection

### **Chart Customization**
- Multiple chart types
- Custom color schemes
- Export to PNG/PDF
- Shareable chart URLs

### **Advanced Analytics**
- Machine learning-based predictions
- Anomaly detection
- Trend analysis
- Capacity forecasting

### **Multi-Device Support**
- Bulk operations
- Device grouping
- Comparative analysis
- Cross-device reporting

## Future Enhancements

### **Planned Features**
- [x] Email alerts for high usage
- [x] Bandwidth utilization reports
- [x] Interface status monitoring
- [x] Capacity planning tools
- [x] Custom SNMP community strings
- [x] Advanced graphing options
- [ ] Webhook notifications
- [ ] Mobile app support
- [ ] API rate limiting
- [ ] Multi-tenant support
- [ ] Custom dashboard widgets
- [ ] Integration with external monitoring tools

### **Customization**
- Modify polling intervals
- Add custom SNMP OIDs
- Extend data retention periods
- Custom alert thresholds
- Branded reports
- Custom chart themes

## Support

For issues or questions:
1. Check debug information in enhanced monitoring
2. Verify device connectivity
3. Review log files in `/logs/interface_polling.log`
4. Check database connectivity and permissions
5. Test email configuration
6. Review system status dashboard

## System Requirements

### **Server Requirements**
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- SNMP extension (optional)

### **Browser Requirements**
- Modern web browser with JavaScript enabled
- Chart.js support
- Bootstrap 5 compatibility

### **Network Requirements**
- Access to MikroTik devices via REST API
- SNMP access (if using SNMP fallback)
- Email server access (for alerts)

---

**Version**: 2.0  
**Last Updated**: January 2025  
**Compatibility**: LMS v1.0+  
**Features**: Complete network monitoring suite with analytics, alerting, and reporting 