# Network Monitoring System - Debug & Optimization Tools

This collection of tools provides comprehensive debugging and optimization capabilities for your network monitoring system.

## 🚀 Quick Start

1. **Start with the launcher**: Open `run_optimization.php` in your browser
2. **Run comprehensive optimization**: Click "Run Complete Optimization" for a full system analysis
3. **Review results**: Check the generated reports and follow recommendations

## 📁 Tool Overview

### 1. **run_optimization.php** - Main Launcher
- **Purpose**: Central hub for all optimization tools
- **Features**: 
  - Beautiful web interface
  - Quick system status check
  - Easy access to all tools
  - System information display

### 2. **comprehensive_optimization.php** - Complete System Optimization
- **Purpose**: Runs all optimization tools in sequence
- **Features**:
  - System health check
  - Debug analysis
  - Performance optimization
  - Error monitoring setup
  - Database optimization
  - Comprehensive reporting

### 3. **debug_optimization_tool.php** - Debug Analysis
- **Purpose**: Comprehensive debugging and environment analysis
- **Features**:
  - PHP environment check
  - Database connectivity test
  - File system permissions
  - Redis connectivity
  - SNMP functionality
  - Performance analysis

### 4. **performance_optimizer.php** - Performance Optimization
- **Purpose**: Database and system performance optimization
- **Features**:
  - Database table optimization
  - Index creation
  - Query caching with Redis
  - SNMP polling optimization
  - Memory management
  - File operation optimization

### 5. **error_monitor.php** - Error Monitoring
- **Purpose**: Comprehensive error tracking and logging
- **Features**:
  - Error handling and logging
  - Performance monitoring
  - Memory usage tracking
  - Disk space monitoring
  - Network connectivity checks
  - Alert generation

### 6. **system_health_checker.php** - System Health Check
- **Purpose**: Complete system health monitoring
- **Features**:
  - System resource monitoring (CPU, Memory, Disk)
  - Database health check
  - Network service status
  - Application health
  - Security assessment
  - Performance analysis

## 🔧 Usage Instructions

### Step 1: Initial Setup
```bash
# Ensure logs directory exists
mkdir -p logs
chmod 755 logs

# Set proper permissions
chmod 644 *.php
```

### Step 2: Configuration
Create a `config.php` file with your database credentials:
```php
<?php
$db_host = 'localhost';
$db_user = 'your_username';
$db_pass = 'your_password';
$db_name = 'your_database';
?>
```

### Step 3: Run Optimization
1. Open `run_optimization.php` in your web browser
2. Click "Run Complete Optimization" for full analysis
3. Review the generated report
4. Follow the recommendations provided

## 📊 What Each Tool Checks

### Environment Check
- ✅ PHP version and extensions
- ✅ Memory limits and execution time
- ✅ Required extensions (mysqli, curl, json, snmp, redis)

### Database Check
- ✅ Database connectivity
- ✅ Connection pool status
- ✅ Slow query detection
- ✅ Table optimization
- ✅ Index creation

### File System Check
- ✅ Directory permissions
- ✅ Log file accessibility
- ✅ Required directories existence

### Redis Check
- ✅ Redis connectivity
- ✅ Memory usage
- ✅ Key count monitoring
- ✅ Cache performance

### SNMP Check
- ✅ SNMP extension availability
- ✅ Version support
- ✅ Polling optimization

### Performance Analysis
- ✅ Query execution time
- ✅ Memory usage patterns
- ✅ Cache hit rates
- ✅ Response time monitoring

## 🚨 Common Issues and Solutions

### Issue: Database Connection Failed
**Solution**: 
- Check database credentials in `config.php`
- Ensure MySQL/MariaDB service is running
- Verify network connectivity

### Issue: Redis Connection Failed
**Solution**:
- Install Redis: `sudo apt-get install redis-server`
- Start Redis service: `sudo systemctl start redis`
- Check Redis configuration

### Issue: Missing PHP Extensions
**Solution**:
```bash
# Install required extensions
sudo apt-get install php-mysqli php-curl php-json php-snmp php-redis

# Restart web server
sudo systemctl restart apache2
```

### Issue: Permission Denied
**Solution**:
```bash
# Set proper permissions
chmod 755 logs/
chmod 644 *.php
chown www-data:www-data logs/
```

## 📈 Performance Optimization Tips

### Database Optimization
1. **Regular Maintenance**: Run table optimization weekly
2. **Index Management**: Monitor and add indexes for slow queries
3. **Query Optimization**: Use prepared statements and caching
4. **Connection Pooling**: Monitor connection usage

### Memory Management
1. **Monitor Usage**: Keep memory usage below 80%
2. **Garbage Collection**: Enable automatic garbage collection
3. **Cache Management**: Implement Redis with TTL
4. **Log Rotation**: Rotate logs regularly

### SNMP Optimization
1. **Batch Requests**: Group SNMP queries
2. **Timeout Management**: Set appropriate timeouts
3. **Community String Security**: Use SNMPv3 when possible
4. **Polling Intervals**: Optimize based on device capacity

## 🔍 Monitoring and Maintenance

### Regular Tasks
- **Daily**: Check error logs and system health
- **Weekly**: Run performance optimization
- **Monthly**: Complete system health check
- **Quarterly**: Review and update optimization strategies

### Alert Setup
Configure alerts for:
- High CPU usage (>90%)
- High memory usage (>80%)
- Low disk space (<10% free)
- Database connection failures
- Critical errors

## 📝 Log Files

The tools generate logs in the `logs/` directory:
- `debug_optimization.log` - Debug tool output
- `error_monitor.log` - Error monitoring logs
- `system_health.log` - Health check results

## 🛠️ Customization

### Adding Custom Checks
You can extend the tools by adding custom checks:

```php
// Add to SystemHealthChecker class
private function checkCustomService() {
    // Your custom check logic
    $status = $this->checkService('your-service', 8080);
    $this->healthStatus['custom_service'] = $status;
}
```

### Custom Alerts
Modify the alert system in `error_monitor.php`:

```php
private function sendAlert($message, $level) {
    // Add your custom alert mechanism
    // Email, SMS, webhook, etc.
}
```

## 🔒 Security Considerations

1. **File Permissions**: Ensure sensitive files are not web-accessible
2. **Database Security**: Use strong passwords and limit database access
3. **Log Security**: Protect log files from unauthorized access
4. **SSL/TLS**: Enable HTTPS for web interface
5. **Input Validation**: Validate all user inputs

## 📞 Support

If you encounter issues:
1. Check the error logs in `logs/` directory
2. Review the system health report
3. Verify all dependencies are installed
4. Check file permissions and ownership

## 🔄 Version History

- **v1.0**: Initial release with basic optimization tools
- **v1.1**: Added comprehensive health checker
- **v1.2**: Enhanced error monitoring and alerting
- **v1.3**: Added web interface and launcher

---

**Note**: These tools are designed for network monitoring systems. Always test in a development environment before running on production systems.