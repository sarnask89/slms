# 🛠️ System Administration Guide

## Overview

This guide covers all aspects of system administration for sLMS, including user management, system configuration, monitoring, and maintenance.

## 👥 User Management

### Access Level System

sLMS uses a granular access level system with the following hierarchy:

#### Default Access Levels

1. **Administrator** (Level 1)
   - Full system access
   - User management
   - System configuration
   - Database administration

2. **Manager** (Level 2)
   - Client management
   - Device management
   - Financial operations
   - Reporting access

3. **User** (Level 3)
   - Basic client operations
   - Device monitoring
   - Limited reporting

4. **Viewer** (Level 4)
   - Read-only access
   - Basic viewing permissions

### Managing Access Levels

#### Create New Access Level

1. Navigate to **Administracja Systemu** → **Access Level Manager**
2. Click **"Add New Access Level"**
3. Configure:
   - **Name**: Descriptive name for the level
   - **Description**: Purpose and scope
   - **Permissions**: Select sections and actions

#### Permission Categories

- **Client Management**: Add, edit, delete, view clients
- **Device Management**: Add, edit, delete, monitor devices
- **Network Management**: Configure networks, DHCP, monitoring
- **Financial Management**: Invoices, payments, reports
- **System Administration**: Users, settings, maintenance
- **Documentation**: View, edit documentation

#### Assign Access Levels

1. Go to **User Management**
2. Select user to modify
3. Choose appropriate access level
4. Save changes

### User Account Management

#### Creating New Users

```php
// Example: Create user programmatically
$stmt = $pdo->prepare("
    INSERT INTO users (username, password, full_name, email, role, access_level_id, is_active) 
    VALUES (?, ?, ?, ?, ?, ?, 1)
");

$password_hash = password_hash('secure_password', PASSWORD_DEFAULT);
$stmt->execute(['newuser', $password_hash, 'Full Name', 'email@example.com', 'user', 3]);
```

#### Password Policies

Configure password requirements in `config.php`:

```php
// Password policy settings
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('PASSWORD_EXPIRY_DAYS', 90);
```

#### Account Security

- **Session Timeout**: Configure automatic logout
- **Failed Login Attempts**: Set lockout thresholds
- **Two-Factor Authentication**: Enable for sensitive accounts
- **IP Restrictions**: Limit access to specific IP ranges

## ⚙️ System Configuration

### Database Configuration

#### Connection Settings

```php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'slms');
define('DB_USER', 'slms_user');
define('DB_PASS', 'secure_password');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');
```

#### Performance Optimization

```sql
-- Optimize database tables
OPTIMIZE TABLE clients, devices, networks, services, invoices, payments;

-- Analyze table statistics
ANALYZE TABLE clients, devices, networks, services, invoices, payments;

-- Check table status
CHECK TABLE clients, devices, networks, services, invoices, payments;
```

### Email Configuration

#### SMTP Settings

```php
// Email configuration
define('SMTP_HOST', 'smtp.your-domain.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@your-domain.com');
define('SMTP_PASS', 'secure_password');
define('SMTP_SECURE', 'tls');
define('SMTP_FROM_NAME', 'sLMS System');
```

#### Email Templates

Configure email templates for:
- Welcome emails
- Password reset
- Invoice notifications
- System alerts
- Maintenance notifications

### Monitoring Configuration

#### SNMP Settings

```php
// SNMP configuration
define('SNMP_COMMUNITY', 'public');
define('SNMP_TIMEOUT', 1000000);
define('SNMP_RETRIES', 3);
define('SNMP_VERSION', '2c');
```

#### Cacti Integration

```php
// Cacti integration
define('CACTI_URL', 'http://cacti.your-domain.com');
define('CACTI_USER', 'admin');
define('CACTI_PASS', 'secure_password');
define('CACTI_API_VERSION', '1');
```

## 📊 System Monitoring

### Health Monitoring

#### System Status Dashboard

Access via **System Administration** → **System Status**

Monitors:
- **Database**: Connection status, performance metrics
- **Web Server**: Apache/Nginx status, response times
- **PHP**: Version, extensions, memory usage
- **Disk Space**: Available space, usage trends
- **Memory**: RAM usage, swap utilization
- **Network**: Connectivity, bandwidth usage

#### Performance Metrics

```bash
# Check system performance
php system_health_checker.php

# Monitor database performance
mysql -u slms_user -p -e "
SELECT 
    table_name,
    table_rows,
    data_length,
    index_length,
    (data_length + index_length) as total_size
FROM information_schema.tables 
WHERE table_schema = 'slms'
ORDER BY total_size DESC;
"
```

### Log Management

#### Log Files Location

```bash
# Application logs
/var/www/html/slms/logs/

# Web server logs
/var/log/apache2/slms_error.log
/var/log/apache2/slms_access.log

# PHP logs
/var/log/php8.0-fpm.log
```

#### Log Rotation

Configure `/etc/logrotate.d/slms`:

```bash
/var/www/html/slms/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload apache2
    endscript
}
```

#### Log Analysis

```bash
# Check for errors
grep -i error /var/www/html/slms/logs/*.log

# Monitor access patterns
tail -f /var/log/apache2/slms_access.log | grep -E "(GET|POST)"

# Check PHP errors
tail -f /var/log/php8.0-fpm.log | grep -i error
```

## 🔧 Maintenance Tasks

### Regular Maintenance Schedule

#### Daily Tasks

```bash
#!/bin/bash
# Daily maintenance script

# Backup database
mysqldump -u slms_user -p$DB_PASS slms > /backup/daily/slms_$(date +%Y%m%d).sql

# Clean old logs
find /var/www/html/slms/logs -name "*.log" -mtime +7 -delete

# Check disk space
df -h | grep -E "(/$|/var)"

# Monitor system resources
top -bn1 | head -20
```

#### Weekly Tasks

```bash
#!/bin/bash
# Weekly maintenance script

# Optimize database
mysql -u slms_user -p$DB_PASS -e "OPTIMIZE TABLE slms.*;"

# Update system statistics
php /var/www/html/slms/run_optimization.php

# Check for security updates
apt list --upgradable

# Review error logs
grep -i error /var/www/html/slms/logs/*.log | tail -100
```

#### Monthly Tasks

```bash
#!/bin/bash
# Monthly maintenance script

# Full system backup
tar -czf /backup/monthly/slms_full_$(date +%Y%m).tar.gz /var/www/html/slms

# Database maintenance
mysql -u slms_user -p$DB_PASS -e "
ANALYZE TABLE slms.*;
CHECK TABLE slms.*;
REPAIR TABLE slms.*;
"

# Review user access
mysql -u slms_user -p$DB_PASS -e "
SELECT username, last_login, is_active 
FROM users 
ORDER BY last_login DESC;
"
```

### Backup Strategy

#### Database Backup

```bash
#!/bin/bash
# Database backup script

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/database"
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Full database backup
mysqldump -u slms_user -p$DB_PASS \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    slms > $BACKUP_DIR/slms_full_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/slms_full_$DATE.sql

# Clean old backups
find $BACKUP_DIR -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete

echo "Database backup completed: slms_full_$DATE.sql.gz"
```

#### File Backup

```bash
#!/bin/bash
# File backup script

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/files"
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup application files
tar -czf $BACKUP_DIR/slms_files_$DATE.tar.gz \
    --exclude='logs/*' \
    --exclude='cache/*' \
    --exclude='uploads/temp/*' \
    /var/www/html/slms

# Clean old backups
find $BACKUP_DIR -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete

echo "File backup completed: slms_files_$DATE.tar.gz"
```

### Recovery Procedures

#### Database Recovery

```bash
#!/bin/bash
# Database recovery script

BACKUP_FILE=$1
DATABASE="slms"

if [ -z "$BACKUP_FILE" ]; then
    echo "Usage: $0 <backup_file.sql>"
    exit 1
fi

# Stop web server
systemctl stop apache2

# Drop and recreate database
mysql -u root -p -e "DROP DATABASE IF EXISTS $DATABASE;"
mysql -u root -p -e "CREATE DATABASE $DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Restore from backup
mysql -u slms_user -p$DB_PASS $DATABASE < $BACKUP_FILE

# Start web server
systemctl start apache2

echo "Database recovery completed"
```

#### File Recovery

```bash
#!/bin/bash
# File recovery script

BACKUP_FILE=$1

if [ -z "$BACKUP_FILE" ]; then
    echo "Usage: $0 <backup_file.tar.gz>"
    exit 1
fi

# Stop web server
systemctl stop apache2

# Backup current files
mv /var/www/html/slms /var/www/html/slms_backup_$(date +%Y%m%d_%H%M%S)

# Extract backup
tar -xzf $BACKUP_FILE -C /var/www/html/

# Set permissions
chown -R www-data:www-data /var/www/html/slms
chmod -R 755 /var/www/html/slms
chmod -R 775 /var/www/html/slms/logs
chmod -R 775 /var/www/html/slms/uploads

# Start web server
systemctl start apache2

echo "File recovery completed"
```

## 🔒 Security Management

### Access Control

#### IP Whitelisting

```apache
# Apache configuration for IP restrictions
<Directory /var/www/html/slms>
    Order Deny,Allow
    Deny from all
    Allow from 192.168.1.0/24
    Allow from 10.0.0.0/8
    Allow from 127.0.0.1
</Directory>
```

#### SSL/TLS Configuration

```apache
# SSL configuration
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/www/html/slms
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/slms.crt
    SSLCertificateKeyFile /etc/ssl/private/slms.key
    SSLCertificateChainFile /etc/ssl/certs/slms-chain.crt
    
    # Security headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

### Security Monitoring

#### Failed Login Monitoring

```bash
#!/bin/bash
# Monitor failed login attempts

LOG_FILE="/var/log/apache2/slms_access.log"
ALERT_EMAIL="admin@your-domain.com"

# Check for failed login attempts
FAILED_ATTEMPTS=$(grep "POST.*login" $LOG_FILE | grep " 401 " | wc -l)

if [ $FAILED_ATTEMPTS -gt 10 ]; then
    echo "High number of failed login attempts: $FAILED_ATTEMPTS" | \
    mail -s "sLMS Security Alert" $ALERT_EMAIL
fi
```

#### File Integrity Monitoring

```bash
#!/bin/bash
# File integrity monitoring

INTEGRITY_FILE="/var/www/html/slms/.file_integrity"
CURRENT_HASHES="/tmp/current_hashes"

# Generate current file hashes
find /var/www/html/slms -type f -name "*.php" -exec sha256sum {} \; > $CURRENT_HASHES

# Compare with stored hashes
if [ -f "$INTEGRITY_FILE" ]; then
    diff $INTEGRITY_FILE $CURRENT_HASHES
    if [ $? -ne 0 ]; then
        echo "File integrity check failed" | \
        mail -s "sLMS Security Alert" admin@your-domain.com
    fi
fi

# Update integrity file
mv $CURRENT_HASHES $INTEGRITY_FILE
```

## 📈 Performance Optimization

### Database Optimization

#### Query Optimization

```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_clients_email ON clients(email);
CREATE INDEX idx_devices_ip ON devices(ip_address);
CREATE INDEX idx_invoices_date ON invoices(created_at);
CREATE INDEX idx_payments_date ON payments(payment_date);

-- Analyze query performance
EXPLAIN SELECT * FROM clients WHERE email = 'test@example.com';
```

#### Configuration Optimization

```ini
# MySQL optimization (/etc/mysql/mysql.conf.d/mysqld.cnf)
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 64M
query_cache_type = 1
max_connections = 200
```

### PHP Optimization

#### OPcache Configuration

```ini
# PHP OPcache (/etc/php/8.0/apache2/conf.d/10-opcache.ini)
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.enable_cli=1
```

#### PHP-FPM Configuration

```ini
# PHP-FPM pool configuration
[www]
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### Web Server Optimization

#### Apache Optimization

```apache
# Apache optimization
<IfModule mpm_prefork_module>
    StartServers          5
    MinSpareServers       5
    MaxSpareServers      10
    MaxRequestWorkers    150
    MaxConnectionsPerChild   0
</IfModule>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

## 🚨 Troubleshooting

### Common Issues

#### Database Connection Issues

```bash
# Check MySQL service
systemctl status mysql

# Test database connection
mysql -u slms_user -p -e "SELECT 1;"

# Check MySQL error log
tail -f /var/log/mysql/error.log
```

#### Performance Issues

```bash
# Check system resources
htop
iotop
nethogs

# Check Apache status
systemctl status apache2
apache2ctl -M

# Check PHP-FPM status
systemctl status php8.0-fpm
```

#### Security Issues

```bash
# Check for suspicious files
find /var/www/html/slms -name "*.php" -exec grep -l "eval\|base64_decode\|system\|shell_exec" {} \;

# Check file permissions
find /var/www/html/slms -type f -perm -o+w

# Check for unauthorized access
grep "POST.*login" /var/log/apache2/slms_access.log | grep " 401 "
```

### Emergency Procedures

#### System Recovery

```bash
#!/bin/bash
# Emergency recovery script

echo "Starting emergency recovery..."

# Stop all services
systemctl stop apache2
systemctl stop mysql
systemctl stop php8.0-fpm

# Restore from latest backup
LATEST_BACKUP=$(ls -t /backup/database/*.sql.gz | head -1)
gunzip -c $LATEST_BACKUP | mysql -u slms_user -p$DB_PASS slms

# Restart services
systemctl start mysql
systemctl start php8.0-fpm
systemctl start apache2

echo "Emergency recovery completed"
```

## 📞 Support and Maintenance

### Regular Maintenance Checklist

- [ ] Daily backups completed
- [ ] Error logs reviewed
- [ ] System performance checked
- [ ] Security updates applied
- [ ] User access reviewed
- [ ] Database optimized
- [ ] Logs rotated
- [ ] SSL certificates valid

### Support Resources

- **Documentation**: Built-in help system
- **Logs**: System and application logs
- **Monitoring**: Real-time system status
- **Community**: User forums and support
- **Professional Support**: Contact for critical issues

---

**Last Updated**: July 20, 2025  
**Version**: sLMS v1.0 System Administration Guide  
**Status**: ✅ **Active** 