# 👥 Client Management Modules

## Overview
The Client Management modules provide complete customer relationship management functionality, including client registration, device management, service assignment, and contract handling for the AI SERVICE NETWORK MANAGEMENT SYSTEM.

---

## 📋 Available Modules

### 1. **Clients Module** (`clients.php`)
Core client listing and management interface.

#### Features
- ✅ Client listing with pagination
- ✅ Advanced search and filtering
- ✅ Quick actions (edit, delete, view)
- ✅ Export functionality (CSV, Excel, PDF)
- ✅ Bulk operations
- ✅ Client status management

#### Installation
```bash
# Create clients table
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(10),
    country VARCHAR(50),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    registration_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_email (email),
    INDEX idx_status (status)
);
```

#### Configuration
```php
// config/clients.php
return [
    'items_per_page' => 25,
    'export_formats' => ['csv', 'excel', 'pdf'],
    'searchable_fields' => ['name', 'email', 'phone', 'address'],
    'default_sort' => 'name',
    'enable_bulk_operations' => true,
    'client_id_format' => 'CL{year}{month}{id}' // e.g., CL202501001
];
```

#### API Endpoints
```php
// List clients
GET /api/clients?page=1&per_page=25&search=john

// Get single client
GET /api/clients/{id}

// Search clients
GET /api/clients/search?q=john&fields=name,email

// Export clients
GET /api/clients/export?format=csv&filters[status]=active
```

---

### 2. **Add Client Module** (`add_client.php`)
Comprehensive client registration system.

#### Features
- ✅ Multi-step registration wizard
- ✅ Field validation
- ✅ Duplicate detection
- ✅ Document upload
- ✅ Service package selection
- ✅ Initial device assignment

#### Form Fields
```php
// Required fields
$requiredFields = [
    'name' => 'Full name or company name',
    'email' => 'Valid email address',
    'phone' => 'Contact phone number',
    'address' => 'Physical address'
];

// Optional fields
$optionalFields = [
    'company' => 'Company name',
    'tax_id' => 'Tax identification number',
    'contact_person' => 'Primary contact person',
    'alternate_phone' => 'Alternative phone',
    'website' => 'Company website',
    'social_media' => 'Social media handles'
];
```

#### Validation Rules
```php
// config/validation/client.php
return [
    'name' => 'required|min:3|max:100',
    'email' => 'required|email|unique:clients',
    'phone' => 'required|phone',
    'address' => 'required|min:10',
    'tax_id' => 'nullable|tax_id',
    'postal_code' => 'nullable|postal_code'
];
```

#### Usage Example
```php
// Process client registration
$clientData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
    'address' => '123 Main St',
    'service_package' => 'premium'
];

$clientId = createClient($clientData);

// Send welcome email
sendWelcomeEmail($clientId);

// Assign initial services
assignInitialServices($clientId, $clientData['service_package']);
```

---

### 3. **Edit Client Module** (`edit_client.php`)
Client profile editing and management.

#### Features
- ✅ Inline editing
- ✅ Change history tracking
- ✅ Field-level permissions
- ✅ Audit trail
- ✅ Custom field support
- ✅ API integration

#### Advanced Features
```php
// Track changes
$changes = trackClientChanges($clientId, $oldData, $newData);

// Log audit trail
logAudit('client_updated', [
    'client_id' => $clientId,
    'changes' => $changes,
    'user_id' => getCurrentUserId()
]);

// Notify relevant parties
notifyClientUpdate($clientId, $changes);
```

---

### 4. **Client Devices Module** (`client_devices.php`)
Manage devices associated with clients.

#### Features
- ✅ Device assignment
- ✅ Device status tracking
- ✅ MAC address management
- ✅ IP allocation
- ✅ Device history
- ✅ Bandwidth monitoring per device

#### Installation
```bash
# Create client_devices table
CREATE TABLE client_devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    device_id INT,
    mac_address VARCHAR(17) UNIQUE,
    ip_address VARCHAR(45),
    device_type VARCHAR(50),
    hostname VARCHAR(100),
    location VARCHAR(100),
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    installed_date DATE,
    last_seen TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    INDEX idx_client (client_id),
    INDEX idx_mac (mac_address),
    INDEX idx_ip (ip_address)
);
```

#### Device Management
```php
// Assign device to client
assignDeviceToClient($clientId, $deviceData);

// Update device status
updateDeviceStatus($deviceId, 'maintenance');

// Get client devices
$devices = getClientDevices($clientId, [
    'status' => 'active',
    'type' => 'router'
]);

// Monitor device
monitorDevice($deviceId);
```

---

### 5. **DHCP Import Modules**
Import clients from DHCP servers.

#### a) **Import DHCP Clients** (`import_dhcp_clients_improved.php`)

##### Features
- ✅ MikroTik RouterOS integration
- ✅ Bulk import with validation
- ✅ Duplicate prevention
- ✅ Mapping configuration
- ✅ Import history
- ✅ Rollback capability

##### Configuration
```php
// config/dhcp_import.php
return [
    'mikrotik_servers' => [
        [
            'name' => 'Main Router',
            'host' => '192.168.1.1',
            'username' => 'admin',
            'password' => 'password',
            'port' => 8728
        ]
    ],
    'import_rules' => [
        'skip_dynamic' => true,
        'skip_expired' => true,
        'auto_create_client' => false,
        'default_status' => 'pending'
    ]
];
```

##### Import Process
```php
// Connect to MikroTik
$api = new MikroTikAPI();
$api->connect($config['host'], $config['username'], $config['password']);

// Get DHCP leases
$leases = $api->comm('/ip/dhcp-server/lease/print');

// Process imports
foreach ($leases as $lease) {
    if (shouldImportLease($lease)) {
        importDHCPClient($lease);
    }
}
```

#### b) **Import DHCP Networks** (`import_dhcp_networks_improved.php`)

##### Features
- ✅ Network topology import
- ✅ Subnet management
- ✅ VLAN configuration
- ✅ IP pool management
- ✅ Network visualization
- ✅ Conflict detection

##### Network Import
```php
// Import network configuration
$networks = importNetworkConfig($routerId);

// Create network entries
foreach ($networks as $network) {
    createNetwork([
        'name' => $network['name'],
        'subnet' => $network['address'],
        'gateway' => $network['gateway'],
        'vlan_id' => $network['vlan'],
        'dhcp_enabled' => true
    ]);
}
```

---

### 6. **Service Management Integration**

#### Service Assignment
```php
// Assign service to client
assignService($clientId, $serviceId, [
    'start_date' => date('Y-m-d'),
    'billing_cycle' => 'monthly',
    'discount' => 10
]);

// Suspend service
suspendService($clientId, $serviceId, 'Non-payment');

// Reactivate service
reactivateService($clientId, $serviceId);
```

#### Package Management
```php
// Get available packages for client
$packages = getAvailablePackages($clientId);

// Upgrade/downgrade package
changePackage($clientId, $currentPackageId, $newPackageId);

// Calculate prorated charges
$charges = calculateProration($clientId, $packageChange);
```

---

## 🔧 Advanced Features

### Custom Fields
```php
// Define custom fields
defineCustomField('clients', 'customer_type', 'select', [
    'options' => ['residential', 'business', 'enterprise'],
    'required' => true
]);

// Get custom field values
$customFields = getCustomFields('clients', $clientId);

// Update custom field
updateCustomField('clients', $clientId, 'customer_type', 'enterprise');
```

### Client Portal Integration
```php
// Generate portal credentials
$credentials = generatePortalAccess($clientId);

// Send portal invitation
sendPortalInvitation($clientId, $credentials);

// Track portal usage
trackPortalActivity($clientId, 'login');
```

### Notification System
```php
// config/notifications.php
return [
    'client_notifications' => [
        'welcome' => ['email', 'sms'],
        'payment_due' => ['email', 'sms', 'portal'],
        'service_update' => ['email', 'portal'],
        'maintenance' => ['email', 'sms']
    ]
];

// Send notification
notify($clientId, 'payment_due', [
    'amount' => 99.99,
    'due_date' => '2025-02-01'
]);
```

---

## 📊 Reporting & Analytics

### Client Reports
```php
// Generate client summary report
$report = generateClientReport($clientId, [
    'period' => 'last_month',
    'include' => ['services', 'payments', 'tickets']
]);

// Bulk reporting
$bulkReport = generateBulkReport([
    'status' => 'active',
    'service' => 'premium'
]);
```

### Analytics Dashboard
```php
// Get client statistics
$stats = getClientStatistics();
// Returns: total_clients, active_clients, revenue, churn_rate

// Growth metrics
$growth = getGrowthMetrics('monthly', 'last_year');

// Service distribution
$distribution = getServiceDistribution();
```

---

## 🚨 Troubleshooting

### Common Issues

#### 1. Duplicate Client Detection
```php
// Check for duplicates
$duplicates = findDuplicateClients([
    'email' => $email,
    'phone' => $phone
]);

if ($duplicates) {
    // Handle merge or rejection
}
```

#### 2. Import Failures
```bash
# Check import logs
tail -f logs/dhcp_import.log

# Validate import file
php validate_import.php import_file.csv
```

#### 3. Performance Issues
```sql
-- Add missing indexes
ALTER TABLE clients ADD INDEX idx_created (created_at);
ALTER TABLE client_devices ADD INDEX idx_last_seen (last_seen);

-- Optimize queries
ANALYZE TABLE clients;
OPTIMIZE TABLE client_devices;
```

---

## 🔄 Migration Tools

### Import from Other Systems
```bash
# Import from CSV
php import_clients_csv.php --file=clients.csv --map=mapping.json

# Import from another CRM
php migrate_from_crm.php --source=whmcs --config=migration.conf

# Validate imported data
php validate_clients.php --fix-issues
```

---

## 🔗 Related Modules
- [Service Management](../services/README.md)
- [Billing & Invoicing](../financial/README.md)
- [Device Management](../network/device-management.md)
- [Ticketing System](../support/ticketing.md)

---

**Module Version**: 3.1.0  
**Last Updated**: January 2025  
**Maintainer**: Client Services Team