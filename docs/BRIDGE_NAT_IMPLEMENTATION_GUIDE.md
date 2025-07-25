# Bridge NAT/Mangle Traffic Control Implementation Guide

## Overview

This guide shows how to implement a bridge-based captive portal system that controls traffic flow between two interfaces without DHCP dependency. The system uses bridge filters, NAT rules, and mangle rules to manage access based on MAC addresses and user authentication.

## Architecture

```
[Interface 1] ←→ [Bridge] ←→ [Interface 2]
                    ↓
              [Bridge Filters]
                    ↓
              [NAT/Mangle Rules]
                    ↓
              [Captive Portal]
```

## Prerequisites

- Mikrotik RouterOS device
- Two network interfaces to bridge
- PHP environment (or Docker)
- MySQL database

## Step 1: Mikrotik Bridge Configuration

### Basic Bridge Setup

```bash
# Create bridge interface
/interface bridge add name=bridge1

# Add interfaces to bridge
/interface bridge port add bridge=bridge1 interface=ether1
/interface bridge port add bridge=bridge1 interface=ether2

# Enable IP firewall for bridge
/interface bridge settings set use-ip-firewall=yes use-ip-firewall-for-vlan=yes

# Assign IP address to bridge
/ip address add address=192.168.100.1/24 interface=bridge1
```

### Bridge Filter Configuration

```bash
# Create bridge filter chains
/interface bridge filter add chain=forward name=captive_portal_filter
/interface bridge filter add chain=input name=captive_portal_input
/interface bridge filter add chain=output name=captive_portal_output

# Allow ARP traffic
/interface bridge filter add chain=forward protocol=arp action=accept comment="Allow ARP"

# Allow DNS traffic
/interface bridge filter add chain=forward dst-address=8.8.8.8 protocol=udp dst-port=53 action=accept comment="Allow DNS"

# Allow captive portal access
/interface bridge filter add chain=forward dst-address=192.168.100.1 action=accept comment="Allow captive portal access"

# Drop all other traffic by default
/interface bridge filter add chain=forward action=drop comment="Default drop for unauthenticated users"
```

### NAT Configuration

```bash
# Create NAT chain for captive portal
/ip firewall nat add chain=captive_portal_nat action=masquerade comment="Bridge NAT chain"

# Redirect HTTP to captive portal
/ip firewall nat add chain=captive_portal_nat action=redirect dst-port=80 protocol=tcp to-ports=8080 comment="HTTP redirect to captive portal"
```

### Mangle Configuration

```bash
# Create mangle chain for connection marking
/ip firewall mangle add chain=prerouting name=captive_portal_mangle action=mark-connection new-connection-mark=captive_portal comment="Mark captive portal connections"

# Mark packets for bandwidth monitoring
/ip firewall mangle add chain=prerouting connection-mark=captive_portal action=mark-packet new-packet-mark=captive_portal_packet comment="Mark captive portal packets"
```

## Step 2: Database Setup

Run the database setup script:

```bash
# Using Docker
docker run --rm -v $(pwd):/app -w /app bridge-nat-system php setup_bridge_nat.php

# Or manually import the SQL schema
mysql -u username -p slms < sql/bridge_nat_schema.sql
```

## Step 3: PHP Controller Configuration

Update the bridge controller configuration in `modules/bridge_nat_controller.php`:

```php
private function loadConfig() {
    return [
        'bridge_name' => 'bridge1',
        'interface1' => 'ether1',  // Your first interface
        'interface2' => 'ether2',  // Your second interface
        'captive_portal_ip' => '192.168.100.1',
        'captive_portal_port' => '8080',
        'dns_servers' => ['8.8.8.8', '8.8.4.4'],
        'nat_chain' => 'captive_portal_nat',
        'mangle_chain' => 'captive_portal_mangle',
        'bridge_filter_chain' => 'captive_portal_filter',
        'session_timeout' => 3600, // 1 hour
        'enable_bandwidth_monitoring' => true,
        'enable_connection_tracking' => true
    ];
}
```

## Step 4: Captive Portal Integration

### Web Interface

Create a simple captive portal login page:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Network Access Required</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 400px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Network Access Required</h2>
        <p>Please log in to access the network.</p>
        <form id="loginForm">
            <input type="text" id="username" placeholder="Username" required>
            <input type="password" id="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div id="message"></div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const macAddress = getMacAddress(); // You'll need to implement this
            
            fetch('/api/bridge_nat?action=authenticate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `mac_address=${macAddress}&username=${username}&password=${password}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('message').innerHTML = '<p style="color: green;">Login successful! Redirecting...</p>';
                    setTimeout(() => window.location.href = 'http://www.google.com', 2000);
                } else {
                    document.getElementById('message').innerHTML = '<p style="color: red;">Login failed: ' + data.error + '</p>';
                }
            });
        });
        
        function getMacAddress() {
            // This is a simplified example - you'll need to implement proper MAC detection
            return '00:11:22:33:44:55'; // Placeholder
        }
    </script>
</body>
</html>
```

### API Endpoint

The bridge NAT controller provides these API endpoints:

```bash
# Initialize bridge system
POST /api/bridge_nat?action=initialize

# Process new connection
POST /api/bridge_nat?action=connect
Content-Type: application/x-www-form-urlencoded
mac_address=00:11:22:33:44:55&user_role=guest

# Authenticate user
POST /api/bridge_nat?action=authenticate
Content-Type: application/x-www-form-urlencoded
mac_address=00:11:22:33:44:55&username=john&password=secret

# Get statistics
GET /api/bridge_nat?action=stats

# Cleanup expired access
POST /api/bridge_nat?action=cleanup
```

## Step 5: Testing

### Test the System

```bash
# Run the demo
docker run --rm -v $(pwd):/app -w /app bridge-nat-system php demo_bridge_nat.php

# Test with real traffic
# 1. Connect a device to Interface 1
# 2. Try to access the internet - should be redirected to captive portal
# 3. Log in through the portal
# 4. Verify internet access is granted
```

### Monitor Traffic

```bash
# Check bridge filters
/interface bridge filter print

# Check NAT rules
/ip firewall nat print

# Check mangle rules
/ip firewall mangle print

# Monitor bridge traffic
/interface monitor-traffic bridge1
```

## Step 6: Advanced Configuration

### Bandwidth Limiting

```bash
# Create queue tree for bandwidth control
/queue tree add name=captive_portal max-limit=10M parent=global

# Add queue rules for different user roles
/queue simple add name=guest_limit target=192.168.100.0/24 max-limit=1M
/queue simple add name=user_limit target=192.168.100.0/24 max-limit=5M
/queue simple add name=admin_limit target=192.168.100.0/24 max-limit=10M
```

### Walled Garden

```bash
# Allow specific domains for guests
/interface bridge filter add chain=forward dst-address=172.217.160.0/24 action=accept comment="Allow Google"
/interface bridge filter add chain=forward dst-address=157.240.192.0/24 action=accept comment="Allow Facebook"
```

### Session Management

```bash
# Set up automatic cleanup
# Add to crontab or systemd timer
*/5 * * * * /usr/bin/curl -X POST "http://localhost/api/bridge_nat?action=cleanup"
```

## Troubleshooting

### Common Issues

1. **Traffic not being filtered**
   - Check if bridge filters are enabled
   - Verify interface names in configuration
   - Check bridge settings

2. **HTTP redirect not working**
   - Verify NAT rules are in correct chain
   - Check if captive portal is accessible
   - Test with different browsers

3. **Authentication failing**
   - Check database connection
   - Verify user credentials
   - Check API endpoint configuration

### Debug Commands

```bash
# Check bridge status
/interface bridge print

# Check bridge ports
/interface bridge port print

# Check bridge filters
/interface bridge filter print

# Check NAT rules
/ip firewall nat print

# Monitor traffic
/tool sniffer start interface=bridge1
```

## Security Considerations

1. **Use HTTPS** for the captive portal
2. **Implement rate limiting** for login attempts
3. **Log all access attempts** for audit purposes
4. **Regular security updates** for the system
5. **Backup configuration** regularly

## Performance Optimization

1. **Use connection marking** to reduce rule processing
2. **Implement rule caching** for frequently accessed rules
3. **Monitor system resources** during peak usage
4. **Optimize database queries** for large user bases

## Conclusion

This bridge NAT/mangle system provides a flexible and powerful way to control traffic flow between bridged interfaces without DHCP dependency. It can be easily extended with additional features like bandwidth monitoring, user management, and advanced filtering rules.

For production deployment, consider:
- High availability setup
- Load balancing for multiple bridges
- Integration with existing user management systems
- Comprehensive monitoring and alerting 