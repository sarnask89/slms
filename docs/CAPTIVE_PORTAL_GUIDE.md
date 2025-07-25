# Captive Portal System Documentation

## Overview

The **Captive Portal System** is a comprehensive walled garden portal solution designed for ISP management, hotels, guest networks, and any environment requiring user authentication before internet access. It provides a modern, responsive interface with advanced features for managing VLAN-based networks.

## Features

### 🔐 Authentication & Security
- **Multi-factor authentication** support
- **Session management** with configurable timeouts
- **Account lockout** protection after failed attempts
- **Role-based access control** (Admin, User, Guest)
- **Secure password hashing** using bcrypt

### 🌐 Walled Garden
- **Domain whitelisting** for allowed services
- **Configurable walled garden** per VLAN
- **Automatic redirect** to portal for unauthorized access
- **Social media access** without authentication

### 📊 Management & Monitoring
- **Real-time session monitoring**
- **Bandwidth usage tracking**
- **User activity logging**
- **VLAN statistics** and reporting
- **API endpoints** for integration

### 🎨 User Interface
- **Modern responsive design**
- **Mobile-friendly interface**
- **Customizable branding**
- **Multi-language support** ready
- **Progressive Web App** features

## Architecture

### System Components

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web Browser   │    │  Captive Portal │    │   Database      │
│                 │◄──►│   Interface     │◄──►│   (MySQL)       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Router/FW     │    │   API Layer     │    │   Logging       │
│   Redirect      │    │   (REST)        │    │   System        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Database Schema

The system uses the following main tables:

- **`vlans`** - VLAN configuration and settings
- **`captive_portal_sessions`** - Active user sessions
- **`captive_portal_users`** - User accounts and permissions
- **`captive_portal_access_logs`** - Authentication and access logs
- **`captive_portal_settings`** - System configuration

## Installation

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (Apache)

### Quick Setup

1. **Clone or download** the system files
2. **Configure database** connection in `config/captive_portal.php`
3. **Run setup script**:
   ```bash
   php setup_captive_portal.php
   ```
4. **Configure web server** to redirect traffic to portal
5. **Test the system** with demo credentials

### Manual Installation

1. **Create database tables**:
   ```bash
   mysql -u username -p database_name < sql/captive_portal_schema.sql
   ```

2. **Configure web server**:
   ```apache
   # Add to .htaccess or virtual host
   RewriteEngine On
   RewriteCond %{REQUEST_URI} !^/modules/captive_portal\.php
   RewriteCond %{HTTP_COOKIE} !captive_portal_authenticated
   RewriteRule ^(.*)$ /modules/captive_portal.php?redirect=%{REQUEST_URI} [L,R=302]
   ```

3. **Set up cron job** for maintenance:
   ```bash
   */5 * * * * php /path/to/maintenance/cleanup_sessions.php
   ```

## Configuration

### Portal Settings

Edit `config/captive_portal.php`:

```php
return [
    'portal' => [
        'title' => 'Welcome to Our Network',
        'subtitle' => 'Please login to access the internet',
        'company_name' => 'Your ISP Name',
        'logo_url' => '/assets/images/logo.png',
        'session_timeout' => 3600, // 1 hour
        'max_attempts' => 3,
        'lockout_time' => 900 // 15 minutes
    ],
    'walled_garden' => [
        'default_domains' => [
            'google.com',
            'gmail.com',
            'facebook.com',
            'twitter.com'
        ]
    ]
];
```

### VLAN Configuration

Create VLANs through the management interface or API:

```json
{
    "vlan_id": 100,
    "name": "Guest Network",
    "description": "Public guest network",
    "network_address": "192.168.100.0/24",
    "gateway": "192.168.100.1",
    "captive_portal_enabled": true,
    "walled_garden_domains": ["google.com", "gmail.com"],
    "session_timeout": 3600,
    "max_bandwidth": 5
}
```

## Usage

### For End Users

1. **Connect to network** (WiFi or Ethernet)
2. **Open any website** - automatically redirected to portal
3. **Enter credentials** or use guest access
4. **Access granted** to internet based on permissions

### For Administrators

#### Web Interface

Access the management interface at `/modules/vlan_captive_portal.php`:

- **VLAN Management** - Create and configure VLANs
- **User Management** - Add/edit users and permissions
- **Session Monitoring** - View active sessions
- **Statistics** - Usage reports and analytics

#### API Usage

The system provides RESTful API endpoints:

```bash
# Get all VLANs
GET /api/captive_portal_api.php/vlans

# Create new VLAN
POST /api/captive_portal_api.php/vlans
{
    "vlan_id": 200,
    "name": "Hotel Network",
    "network_address": "192.168.200.0/24",
    "gateway": "192.168.200.1"
}

# Get active sessions
GET /api/captive_portal_api.php/sessions?vlan_id=100

# Disconnect user
DELETE /api/captive_portal_api.php/sessions
{
    "session_id": 123
}
```

## Network Integration

### Router/Firewall Configuration

#### Mikrotik RouterOS

```routeros
# Create VLAN interface
/interface vlan add name=vlan100 vlan-id=100 interface=ether1

# Configure IP address
/ip address add address=192.168.100.1/24 interface=vlan100

# Create DHCP server
/ip dhcp-server setup interface=vlan100

# Configure DNS redirect
/ip dns static add name=* address=192.168.100.1

# Create firewall rules
/ip firewall nat add chain=dstnat dst-port=80 protocol=tcp action=redirect to-ports=80
/ip firewall nat add chain=dstnat dst-port=443 protocol=tcp action=redirect to-ports=443
```

#### Cisco IOS

```cisco
# Create VLAN
vlan 100
 name Guest-Network

# Configure interface
interface vlan 100
 ip address 192.168.100.1 255.255.255.0
 no shutdown

# Configure DHCP
ip dhcp pool GUEST
 network 192.168.100.0 255.255.255.0
 default-router 192.168.100.1
 dns-server 192.168.100.1

# DNS redirect
ip dns server
ip name-server 192.168.100.1
```

#### FortiGate

```fortios
# Create VLAN interface
config system interface
    edit "vlan100"
        set vdom "root"
        set ip 192.168.100.1 255.255.255.0
        set allowaccess ping
        set vlanid 100
        set interface "port1"
    next
end

# Configure DHCP
config system dhcp server
    edit 1
        set dns-service default
        set default-gateway 192.168.100.1
        set netmask 255.255.255.0
        set interface "vlan100"
        config ip-range
            edit 1
                set start-ip 192.168.100.100
                set end-ip 192.168.100.200
            next
        end
    next
end
```

### DNS Configuration

Configure DNS to redirect all requests to the portal server:

```bash
# BIND DNS Server
zone "." {
    type master;
    file "/etc/bind/db.captive";
};

# /etc/bind/db.captive
$TTL 86400
@ IN SOA captive.local. admin.captive.local. (
    2023010101 ; Serial
    3600       ; Refresh
    1800       ; Retry
    1209600    ; Expire
    86400      ; Minimum TTL
)

@ IN NS captive.local.
* IN A 192.168.100.1
```

## Customization

### Branding

Edit the portal appearance in `modules/captive_portal.php`:

```css
.portal-header {
    background: linear-gradient(135deg, #YOUR_COLOR1 0%, #YOUR_COLOR2 100%);
}

.company-logo {
    background-image: url('/path/to/your/logo.png');
}
```

### Themes

Create custom themes by modifying the CSS variables:

```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
}
```

### Language Support

Add multi-language support by creating language files:

```php
// languages/en.php
return [
    'welcome' => 'Welcome to Our Network',
    'login' => 'Login to Network',
    'username' => 'Username',
    'password' => 'Password'
];

// languages/pl.php
return [
    'welcome' => 'Witamy w naszej sieci',
    'login' => 'Zaloguj się do sieci',
    'username' => 'Nazwa użytkownika',
    'password' => 'Hasło'
];
```

## Security Considerations

### Best Practices

1. **Use HTTPS** for all portal communications
2. **Implement rate limiting** on login attempts
3. **Regular security updates** for all components
4. **Monitor access logs** for suspicious activity
5. **Use strong passwords** and enforce policies
6. **Implement session timeout** and automatic cleanup

### Security Features

- **CSRF protection** on all forms
- **SQL injection prevention** using prepared statements
- **XSS protection** with output escaping
- **Session hijacking prevention** with secure cookies
- **Brute force protection** with account lockout

## Troubleshooting

### Common Issues

#### Users can't access the portal

1. **Check DNS configuration** - ensure all domains redirect to portal
2. **Verify firewall rules** - check for blocked ports 80/443
3. **Test network connectivity** - ping portal server
4. **Check web server logs** - look for errors

#### Portal not redirecting properly

1. **Verify .htaccess rules** - ensure mod_rewrite is enabled
2. **Check cookie settings** - ensure cookies are being set
3. **Test redirect logic** - manually visit portal URL
4. **Review browser cache** - clear cache and cookies

#### Database connection errors

1. **Check database credentials** in configuration
2. **Verify database server** is running
3. **Test connection** manually
4. **Check firewall rules** for database port

### Debug Mode

Enable debug mode by setting:

```php
define('CAPTIVE_PORTAL_DEBUG', true);
```

This will show detailed error messages and log additional information.

### Log Files

Check these log files for troubleshooting:

- `logs/captive_portal/access.log` - User access logs
- `logs/captive_portal/error.log` - System errors
- `logs/captive_portal/cleanup.log` - Maintenance logs

## API Reference

### Authentication

```http
POST /api/captive_portal_api.php/auth
Content-Type: application/json

{
    "username": "user",
    "password": "password",
    "mac_address": "00:11:22:33:44:55",
    "ip_address": "192.168.100.100"
}
```

### VLANs

```http
GET /api/captive_portal_api.php/vlans
GET /api/captive_portal_api.php/vlans?vlan_id=100

POST /api/captive_portal_api.php/vlans
PUT /api/captive_portal_api.php/vlans
```

### Sessions

```http
GET /api/captive_portal_api.php/sessions
GET /api/captive_portal_api.php/sessions?vlan_id=100

DELETE /api/captive_portal_api.php/sessions
```

### Statistics

```http
GET /api/captive_portal_api.php/stats
GET /api/captive_portal_api.php/stats?vlan_id=100
```

## Performance Optimization

### Database Optimization

1. **Add indexes** for frequently queried columns
2. **Optimize queries** using EXPLAIN
3. **Regular maintenance** with OPTIMIZE TABLE
4. **Monitor slow queries** and optimize

### Caching

Implement caching for:

- **User sessions** in Redis/Memcached
- **VLAN configurations** in application cache
- **Static assets** with browser caching
- **Database queries** with query cache

### Load Balancing

For high-traffic environments:

1. **Multiple web servers** behind load balancer
2. **Database replication** for read scaling
3. **CDN** for static assets
4. **Session sharing** across servers

## Maintenance

### Regular Tasks

1. **Clean expired sessions** (automated via cron)
2. **Backup database** daily
3. **Monitor disk space** and logs
4. **Update system** and dependencies
5. **Review access logs** for anomalies

### Monitoring

Set up monitoring for:

- **Portal availability** and response time
- **Database performance** and connections
- **Disk space** and log file sizes
- **Active sessions** and bandwidth usage
- **Error rates** and failed logins

## Support

### Getting Help

1. **Check documentation** and troubleshooting guide
2. **Review log files** for error details
3. **Test with different browsers** and devices
4. **Verify network configuration** step by step

### Community

- **GitHub Issues** - Report bugs and request features
- **Documentation** - Comprehensive guides and examples
- **Examples** - Sample configurations and use cases

## License

This captive portal system is provided under the MIT License. See LICENSE file for details.

## Changelog

### Version 1.0.0
- Initial release
- Basic captive portal functionality
- VLAN management
- User authentication
- API endpoints
- Web management interface

---

**For more information, visit the project documentation or contact support.** 