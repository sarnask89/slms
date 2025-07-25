<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/helpers/auth_helper.php';

// Require login
require_login();

$pageTitle = 'Tooltip Data';
ob_start();
?>

/**
 * Tooltip Data Endpoint
 * Serves tooltip data in JSON format for the sLMS tooltip system
 */

header('Content-Type: application/json');
header('Cache-Control: public, max-age=3600'); // Cache for 1 hour

// Allow CORS for development
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

// Tooltip data structure
$tooltipData = [
    // Client Management
    'add-client' => [
        'id' => 'add-client',
        'title' => 'Add New Client',
        'content' => 'Create a new client account with contact information, services, and billing details.',
        'category' => 'Client Management',
        'examples' => ['Basic client info', 'Service assignment', 'Billing setup'],
        'steps' => [
            'Enter client personal information',
            'Add contact details (phone, email)',
            'Select services and packages',
            'Configure billing preferences',
            'Set up payment method'
        ],
        'tips' => [
            'Use unique email addresses for each client',
            'Verify phone numbers before saving',
            'Assign appropriate service packages',
            'Set up automatic billing if possible'
        ]
    ],
    'edit-client' => [
        'id' => 'edit-client',
        'title' => 'Edit Client',
        'content' => 'Modify existing client information including personal details, services, and preferences.',
        'category' => 'Client Management',
        'examples' => ['Update contact info', 'Change services', 'Modify billing'],
        'steps' => [
            'Search for the client to edit',
            'Update personal information',
            'Modify service assignments',
            'Adjust billing settings',
            'Save changes'
        ],
        'tips' => [
            'Keep a log of changes made',
            'Notify client of important updates',
            'Verify service changes are applied',
            'Update billing information carefully'
        ]
    ],
    'client-list' => [
        'id' => 'client-list',
        'title' => 'Client List',
        'content' => 'View and manage all registered clients with search, filter, and bulk operations.',
        'category' => 'Client Management',
        'examples' => ['Search clients', 'Filter by status', 'Bulk actions'],
        'steps' => [
            'Use search to find specific clients',
            'Apply filters to narrow results',
            'Select clients for bulk operations',
            'Export data if needed',
            'Perform bulk actions'
        ],
        'tips' => [
            'Use advanced search for complex queries',
            'Save frequently used filters',
            'Export data regularly for backup',
            'Use bulk operations carefully'
        ]
    ],

    // Device Management
    'add-device' => [
        'id' => 'add-device',
        'title' => 'Add Device',
        'content' => 'Register a new network device with IP configuration, SNMP settings, and monitoring options.',
        'category' => 'Device Management',
        'examples' => ['IP configuration', 'SNMP setup', 'Monitoring enable'],
        'steps' => [
            'Enter device basic information',
            'Configure IP address and network',
            'Set up SNMP credentials',
            'Enable monitoring features',
            'Test connectivity'
        ],
        'tips' => [
            'Use descriptive device names',
            'Verify IP address availability',
            'Test SNMP connectivity before saving',
            'Enable monitoring for critical devices'
        ]
    ],
    'edit-device' => [
        'id' => 'edit-device',
        'title' => 'Edit Device',
        'content' => 'Modify device settings, update configuration, and adjust monitoring parameters.',
        'category' => 'Device Management',
        'examples' => ['Update IP', 'Change SNMP', 'Modify alerts'],
        'steps' => [
            'Select device to edit',
            'Update configuration settings',
            'Modify monitoring parameters',
            'Test new settings',
            'Save changes'
        ],
        'tips' => [
            'Backup current settings before changes',
            'Test configuration changes',
            'Update documentation after changes',
            'Monitor device after modifications'
        ]
    ],
    'device-monitoring' => [
        'id' => 'device-monitoring',
        'title' => 'Device Monitoring',
        'content' => 'Real-time monitoring of device status, performance metrics, and health indicators.',
        'category' => 'Device Management',
        'examples' => ['Status check', 'Performance graphs', 'Alert management'],
        'steps' => [
            'View device status overview',
            'Check performance metrics',
            'Review alert history',
            'Configure alert thresholds',
            'Generate reports'
        ],
        'tips' => [
            'Set appropriate alert thresholds',
            'Regularly review performance data',
            'Configure escalation procedures',
            'Keep monitoring data for analysis'
        ]
    ],

    // Network Management
    'add-network' => [
        'id' => 'add-network',
        'title' => 'Add Network',
        'content' => 'Create a new network segment with subnet configuration and routing information.',
        'category' => 'Network Management',
        'examples' => ['Subnet definition', 'Gateway setup', 'DHCP range'],
        'steps' => [
            'Define network name and description',
            'Configure subnet and mask',
            'Set gateway address',
            'Define DHCP range',
            'Configure routing'
        ],
        'tips' => [
            'Plan IP addressing carefully',
            'Avoid overlapping subnets',
            'Document network topology',
            'Test connectivity after setup'
        ]
    ],
    'edit-network' => [
        'id' => 'edit-network',
        'title' => 'Edit Network',
        'content' => 'Modify network configuration, update routing, and adjust DHCP settings.',
        'category' => 'Network Management',
        'examples' => ['Update subnet', 'Change gateway', 'Modify DHCP'],
        'steps' => [
            'Select network to modify',
            'Update configuration settings',
            'Adjust DHCP parameters',
            'Update routing information',
            'Test network connectivity'
        ],
        'tips' => [
            'Consider impact on existing devices',
            'Update documentation after changes',
            'Test network connectivity',
            'Notify users of network changes'
        ]
    ],
    'network-monitoring' => [
        'id' => 'network-monitoring',
        'title' => 'Network Monitoring',
        'content' => 'Monitor network performance, bandwidth usage, and connectivity status.',
        'category' => 'Network Management',
        'examples' => ['Bandwidth graphs', 'Traffic analysis', 'Connectivity tests'],
        'steps' => [
            'View network overview',
            'Analyze bandwidth usage',
            'Check connectivity status',
            'Review performance metrics',
            'Generate network reports'
        ],
        'tips' => [
            'Set up baseline performance metrics',
            'Monitor during peak usage times',
            'Configure bandwidth alerts',
            'Regularly analyze traffic patterns'
        ]
    ],

    // Services & Packages
    'add-internet-package' => [
        'id' => 'add-internet-package',
        'title' => 'Add Internet Package',
        'content' => 'Create a new internet service package with bandwidth limits and pricing.',
        'category' => 'Services & Packages',
        'examples' => ['Bandwidth limits', 'Pricing setup', 'Service terms'],
        'steps' => [
            'Define package name and description',
            'Set bandwidth limits (upload/download)',
            'Configure pricing structure',
            'Define service terms',
            'Set up usage policies'
        ],
        'tips' => [
            'Research competitive pricing',
            'Consider bandwidth costs',
            'Define clear usage policies',
            'Set appropriate bandwidth limits'
        ]
    ],
    'add-tv-package' => [
        'id' => 'add-tv-package',
        'title' => 'Add TV Package',
        'content' => 'Create a new television service package with channel lineup and pricing.',
        'category' => 'Services & Packages',
        'examples' => ['Channel selection', 'Package pricing', 'Service features'],
        'steps' => [
            'Define package name and description',
            'Select channel lineup',
            'Set pricing structure',
            'Configure service features',
            'Define package terms'
        ],
        'tips' => [
            'Include popular channels',
            'Consider channel licensing costs',
            'Offer multiple package tiers',
            'Include premium content options'
        ]
    ],
    'edit-service' => [
        'id' => 'edit-service',
        'title' => 'Edit Service',
        'content' => 'Modify existing service configurations, pricing, and feature sets.',
        'category' => 'Services & Packages',
        'examples' => ['Update pricing', 'Change features', 'Modify terms'],
        'steps' => [
            'Select service to edit',
            'Update configuration settings',
            'Modify pricing if needed',
            'Adjust feature set',
            'Update service terms'
        ],
        'tips' => [
            'Consider impact on existing customers',
            'Communicate changes to customers',
            'Update billing systems',
            'Test service modifications'
        ]
    ],

    // Financial Management
    'add-invoice' => [
        'id' => 'add-invoice',
        'title' => 'Add Invoice',
        'content' => 'Create a new invoice for client services with itemized billing and payment terms.',
        'category' => 'Financial Management',
        'examples' => ['Service billing', 'Payment terms', 'Tax calculation'],
        'steps' => [
            'Select client for invoicing',
            'Add service items and quantities',
            'Set pricing and discounts',
            'Calculate taxes and totals',
            'Set payment terms and due date'
        ],
        'tips' => [
            'Double-check all calculations',
            'Include clear payment terms',
            'Send invoices promptly',
            'Follow up on overdue payments'
        ]
    ],
    'add-payment' => [
        'id' => 'add-payment',
        'title' => 'Add Payment',
        'content' => 'Record client payments and update account balances.',
        'category' => 'Financial Management',
        'examples' => ['Payment recording', 'Balance update', 'Receipt generation'],
        'steps' => [
            'Select client account',
            'Enter payment amount and method',
            'Apply payment to invoices',
            'Update account balance',
            'Generate payment receipt'
        ],
        'tips' => [
            'Verify payment amounts',
            'Apply payments to oldest invoices first',
            'Keep detailed payment records',
            'Send payment confirmations'
        ]
    ],
    'financial-reports' => [
        'id' => 'financial-reports',
        'title' => 'Financial Reports',
        'content' => 'Generate financial reports including revenue, outstanding invoices, and payment history.',
        'category' => 'Financial Management',
        'examples' => ['Revenue reports', 'Invoice status', 'Payment history'],
        'steps' => [
            'Select report type and date range',
            'Configure report parameters',
            'Generate report data',
            'Review and verify information',
            'Export or print report'
        ],
        'tips' => [
            'Generate reports regularly',
            'Verify data accuracy',
            'Keep historical reports',
            'Use for business planning'
        ]
    ],

    // DHCP Management
    'dhcp-clients' => [
        'id' => 'dhcp-clients',
        'title' => 'DHCP Clients',
        'content' => 'View and manage DHCP client leases, IP assignments, and network configurations.',
        'category' => 'DHCP Management',
        'examples' => ['Lease management', 'IP assignments', 'Network config'],
        'steps' => [
            'View active DHCP leases',
            'Monitor IP address usage',
            'Manage lease renewals',
            'Configure DHCP options',
            'Troubleshoot connectivity issues'
        ],
        'tips' => [
            'Monitor lease utilization',
            'Set appropriate lease times',
            'Reserve IPs for critical devices',
            'Regularly clean up expired leases'
        ]
    ],
    'dhcp-networks' => [
        'id' => 'dhcp-networks',
        'title' => 'DHCP Networks',
        'content' => 'Configure DHCP server settings, IP ranges, and network policies.',
        'category' => 'DHCP Management',
        'examples' => ['IP range setup', 'Server config', 'Policy management'],
        'steps' => [
            'Configure DHCP server settings',
            'Define IP address ranges',
            'Set up DHCP options',
            'Configure network policies',
            'Test DHCP functionality'
        ],
        'tips' => [
            'Plan IP ranges carefully',
            'Avoid IP conflicts',
            'Set appropriate lease times',
            'Monitor DHCP server performance'
        ]
    ],

    // Network Monitoring (Cacti)
    'cacti-integration' => [
        'id' => 'cacti-integration',
        'title' => 'Cacti Integration',
        'content' => 'Integrate with Cacti monitoring system for advanced network monitoring and graphing.',
        'category' => 'Network Monitoring (Cacti)',
        'examples' => ['Device monitoring', 'Performance graphs', 'Alert integration'],
        'steps' => [
            'Configure Cacti connection',
            'Add devices to monitoring',
            'Set up data collection',
            'Configure graphs and alerts',
            'Monitor system performance'
        ],
        'tips' => [
            'Ensure Cacti server is accessible',
            'Configure appropriate polling intervals',
            'Set up meaningful graphs',
            'Monitor Cacti server performance'
        ]
    ],
    'snmp-monitoring' => [
        'id' => 'snmp-monitoring',
        'title' => 'SNMP Monitoring',
        'content' => 'Monitor network devices using SNMP protocol for performance and status data.',
        'category' => 'Network Monitoring (Cacti)',
        'examples' => ['Device polling', 'Performance metrics', 'Status monitoring'],
        'steps' => [
            'Configure SNMP community strings',
            'Add devices to SNMP monitoring',
            'Set up data collection intervals',
            'Configure performance thresholds',
            'Monitor device status'
        ],
        'tips' => [
            'Use secure SNMP community strings',
            'Set appropriate polling intervals',
            'Monitor SNMP traffic impact',
            'Keep SNMP configurations updated'
        ]
    ],
    'interface-monitoring' => [
        'id' => 'interface-monitoring',
        'title' => 'Interface Monitoring',
        'content' => 'Monitor network interface status, traffic, and performance metrics.',
        'category' => 'Network Monitoring (Cacti)',
        'examples' => ['Interface status', 'Traffic analysis', 'Performance graphs'],
        'steps' => [
            'Select interfaces to monitor',
            'Configure monitoring parameters',
            'Set up traffic analysis',
            'Configure performance alerts',
            'Generate interface reports'
        ],
        'tips' => [
            'Monitor critical interfaces closely',
            'Set appropriate traffic thresholds',
            'Track interface utilization trends',
            'Plan for capacity upgrades'
        ]
    ],

    // System Administration
    'user-management' => [
        'id' => 'user-management',
        'title' => 'User Management',
        'content' => 'Manage system users, roles, permissions, and access levels.',
        'category' => 'System Administration',
        'examples' => ['User creation', 'Role assignment', 'Permission management'],
        'steps' => [
            'Create new user accounts',
            'Assign appropriate roles',
            'Configure user permissions',
            'Set up access levels',
            'Monitor user activity'
        ],
        'tips' => [
            'Follow principle of least privilege',
            'Regularly review user permissions',
            'Use strong password policies',
            'Monitor failed login attempts'
        ]
    ],
    'access-level-manager' => [
        'id' => 'access-level-manager',
        'title' => 'Access Level Manager',
        'content' => 'Configure granular access levels with section and action-based permissions.',
        'category' => 'System Administration',
        'examples' => ['Level creation', 'Permission assignment', 'User assignment'],
        'steps' => [
            'Create new access levels',
            'Define section permissions',
            'Configure action permissions',
            'Assign levels to users',
            'Test access controls'
        ],
        'tips' => [
            'Start with restrictive permissions',
            'Test access levels thoroughly',
            'Document permission schemes',
            'Regularly audit access levels'
        ]
    ],
    'system-status' => [
        'id' => 'system-status',
        'title' => 'System Status',
        'content' => 'Monitor system health, performance metrics, and operational status.',
        'category' => 'System Administration',
        'examples' => ['Health monitoring', 'Performance metrics', 'Status alerts'],
        'steps' => [
            'View system overview',
            'Check performance metrics',
            'Monitor resource usage',
            'Review system alerts',
            'Generate status reports'
        ],
        'tips' => [
            'Set up proactive monitoring',
            'Configure meaningful alerts',
            'Keep historical performance data',
            'Plan for capacity upgrades'
        ]
    ],
    'theme-editor' => [
        'id' => 'theme-editor',
        'title' => 'Theme Editor',
        'content' => 'Customize system appearance with color schemes, layouts, and visual preferences.',
        'category' => 'System Administration',
        'examples' => ['Color schemes', 'Layout options', 'Visual customization'],
        'steps' => [
            'Select base theme',
            'Customize color scheme',
            'Configure layout options',
            'Preview changes',
            'Apply and save theme'
        ],
        'tips' => [
            'Test themes on different devices',
            'Consider accessibility requirements',
            'Keep themes consistent',
            'Backup custom themes'
        ]
    ],

    // Documentation
    'user-manual' => [
        'id' => 'user-manual',
        'title' => 'User Manual',
        'content' => 'Comprehensive user documentation with guides, tutorials, and best practices.',
        'category' => 'Documentation',
        'examples' => ['Quick start guide', 'Feature tutorials', 'Best practices'],
        'steps' => [
            'Browse documentation sections',
            'Search for specific topics',
            'Follow step-by-step guides',
            'Review best practices',
            'Access video tutorials'
        ],
        'tips' => [
            'Start with quick start guide',
            'Use search for specific topics',
            'Bookmark frequently used sections',
            'Provide feedback on documentation'
        ]
    ],
    'api-reference' => [
        'id' => 'api-reference',
        'title' => 'API Reference',
        'content' => 'Technical documentation for system APIs, endpoints, and integration methods.',
        'category' => 'Documentation',
        'examples' => ['API endpoints', 'Integration guides', 'Code examples'],
        'steps' => [
            'Review API overview',
            'Understand authentication',
            'Explore available endpoints',
            'Test API calls',
            'Implement integrations'
        ],
        'tips' => [
            'Use API keys securely',
            'Test in development environment',
            'Follow rate limiting guidelines',
            'Keep API documentation updated'
        ]
    ],

    // Field-specific tooltips
    'client-name-field' => [
        'id' => 'client-name-field',
        'title' => 'Client Name',
        'content' => 'Enter the full name of the client (first name and last name).',
        'category' => 'Form Fields',
        'examples' => ['John Doe', 'Jane Smith', 'Company Name'],
        'tips' => [
            'Use consistent naming format',
            'Include company name if applicable',
            'Avoid special characters',
            'Keep names concise but descriptive'
        ]
    ],
    'email-field' => [
        'id' => 'email-field',
        'title' => 'Email Address',
        'content' => 'Enter a valid email address for client communication and account access.',
        'category' => 'Form Fields',
        'examples' => ['john.doe@example.com', 'contact@company.com'],
        'tips' => [
            'Verify email format',
            'Use unique email addresses',
            'Consider business vs personal email',
            'Test email delivery'
        ]
    ],
    'phone-field' => [
        'id' => 'phone-field',
        'title' => 'Phone Number',
        'content' => 'Enter client phone number for contact and support purposes.',
        'category' => 'Form Fields',
        'examples' => ['+1-555-123-4567', '555-123-4567'],
        'tips' => [
            'Use consistent format',
            'Include country code if needed',
            'Verify phone number',
            'Consider multiple contact numbers'
        ]
    ],
    'ip-address-field' => [
        'id' => 'ip-address-field',
        'title' => 'IP Address',
        'content' => 'Enter the IP address for network device configuration.',
        'category' => 'Form Fields',
        'examples' => ['192.168.1.100', '10.0.0.50'],
        'tips' => [
            'Verify IP address format',
            'Check for conflicts',
            'Use appropriate subnet',
            'Document IP assignments'
        ]
    ],
    'subnet-field' => [
        'id' => 'subnet-field',
        'title' => 'Subnet Mask',
        'content' => 'Enter the subnet mask for network configuration.',
        'category' => 'Form Fields',
        'examples' => ['255.255.255.0', '255.255.0.0'],
        'tips' => [
            'Use standard subnet masks',
            'Plan for future growth',
            'Avoid overlapping subnets',
            'Document network topology'
        ]
    ],
    'gateway-field' => [
        'id' => 'gateway-field',
        'title' => 'Gateway Address',
        'content' => 'Enter the default gateway for network routing.',
        'category' => 'Form Fields',
        'examples' => ['192.168.1.1', '10.0.0.1'],
        'tips' => [
            'Use first or last IP in subnet',
            'Ensure gateway is accessible',
            'Configure backup gateways',
            'Test gateway connectivity'
        ]
    ],
    'username-field' => [
        'id' => 'username-field',
        'title' => 'Username',
        'content' => 'Enter a unique username for system access.',
        'category' => 'Form Fields',
        'examples' => ['john.doe', 'jdoe', 'admin'],
        'tips' => [
            'Use lowercase letters',
            'Avoid special characters',
            'Keep usernames short',
            'Use consistent naming convention'
        ]
    ],
    'password-field' => [
        'id' => 'password-field',
        'title' => 'Password',
        'content' => 'Enter a strong password for account security.',
        'category' => 'Form Fields',
        'examples' => ['Complex passwords with mixed characters'],
        'tips' => [
            'Use at least 8 characters',
            'Include uppercase and lowercase',
            'Add numbers and symbols',
            'Avoid common passwords'
        ]
    ],
    'role-field' => [
        'id' => 'role-field',
        'title' => 'User Role',
        'content' => 'Select the appropriate role for user permissions and access.',
        'category' => 'Form Fields',
        'examples' => ['Administrator', 'Manager', 'User', 'Viewer'],
        'tips' => [
            'Follow principle of least privilege',
            'Review role permissions',
            'Consider access level instead',
            'Document role assignments'
        ]
    ]
];

// Return tooltip data as JSON
echo json_encode($tooltipData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/layout.php';
?>
