# 🔌 sLMS API Reference

## Overview

The sLMS API provides programmatic access to all system functionality through RESTful endpoints. This reference documents all available endpoints, authentication methods, and usage examples.

## 🔐 Authentication

### API Key Authentication

All API requests require authentication using API keys.

```http
Authorization: Bearer YOUR_API_KEY
```

### Session Authentication

For web-based applications, you can use session authentication:

```php
// Login first to establish session
$response = curl_post('/api/auth/login', [
    'username' => 'your_username',
    'password' => 'your_password'
]);

// Subsequent requests will use session cookies
```

### Getting API Keys

1. Log into sLMS as an administrator
2. Navigate to **System Administration** → **API Management**
3. Generate a new API key
4. Store the key securely

## 📋 Base URL

```
Production: https://your-domain.com/api
Development: http://localhost:8000/api
```

## 🔄 Response Format

All API responses follow a consistent format:

### Success Response

```json
{
    "success": true,
    "data": {
        // Response data here
    },
    "meta": {
        "timestamp": "2025-07-20T10:30:00Z",
        "version": "1.0.0"
    }
}
```

### Error Response

```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Validation failed",
        "details": {
            "field": "Error description"
        }
    },
    "meta": {
        "timestamp": "2025-07-20T10:30:00Z",
        "version": "1.0.0"
    }
}
```

### Pagination

For endpoints that return lists, pagination is included:

```json
{
    "success": true,
    "data": [
        // Items array
    ],
    "pagination": {
        "page": 1,
        "limit": 20,
        "total": 150,
        "pages": 8,
        "has_next": true,
        "has_prev": false
    },
    "meta": {
        "timestamp": "2025-07-20T10:30:00Z",
        "version": "1.0.0"
    }
}
```

## 👥 Authentication Endpoints

### Login

```http
POST /api/auth/login
```

**Request Body:**
```json
{
    "username": "admin",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "username": "admin",
            "full_name": "Administrator",
            "email": "admin@example.com",
            "role": "administrator",
            "access_level": {
                "id": 1,
                "name": "Administrator",
                "permissions": ["*"]
            }
        },
        "session_id": "abc123def456",
        "expires_at": "2025-07-20T18:30:00Z"
    }
}
```

### Logout

```http
POST /api/auth/logout
```

**Response:**
```json
{
    "success": true,
    "data": {
        "message": "Successfully logged out"
    }
}
```

### Refresh Token

```http
POST /api/auth/refresh
```

**Response:**
```json
{
    "success": true,
    "data": {
        "session_id": "new_session_id",
        "expires_at": "2025-07-20T18:30:00Z"
    }
}
```

## 👤 User Management

### Get Users

```http
GET /api/users
```

**Query Parameters:**
- `page` (integer): Page number (default: 1)
- `limit` (integer): Items per page (default: 20)
- `search` (string): Search term
- `role` (string): Filter by role
- `status` (string): Filter by status (active/inactive)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "username": "admin",
            "full_name": "Administrator",
            "email": "admin@example.com",
            "role": "administrator",
            "is_active": true,
            "last_login": "2025-07-20T10:30:00Z",
            "created_at": "2025-01-01T00:00:00Z",
            "access_level": {
                "id": 1,
                "name": "Administrator"
            }
        }
    ],
    "pagination": {
        "page": 1,
        "limit": 20,
        "total": 50,
        "pages": 3
    }
}
```

### Get User

```http
GET /api/users/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "username": "admin",
        "full_name": "Administrator",
        "email": "admin@example.com",
        "role": "administrator",
        "is_active": true,
        "last_login": "2025-07-20T10:30:00Z",
        "created_at": "2025-01-01T00:00:00Z",
        "access_level": {
            "id": 1,
            "name": "Administrator",
            "permissions": [
                {
                    "section": "users",
                    "actions": ["read", "write", "delete"]
                }
            ]
        }
    }
}
```

### Create User

```http
POST /api/users
```

**Request Body:**
```json
{
    "username": "newuser",
    "password": "securepassword",
    "full_name": "New User",
    "email": "newuser@example.com",
    "role": "user",
    "access_level_id": 3,
    "is_active": true
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 51,
        "username": "newuser",
        "full_name": "New User",
        "email": "newuser@example.com",
        "role": "user",
        "is_active": true,
        "created_at": "2025-07-20T10:30:00Z"
    }
}
```

### Update User

```http
PUT /api/users/{id}
```

**Request Body:**
```json
{
    "full_name": "Updated Name",
    "email": "updated@example.com",
    "role": "manager",
    "access_level_id": 2
}
```

### Delete User

```http
DELETE /api/users/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "message": "User deleted successfully"
    }
}
```

## 🏢 Client Management

### Get Clients

```http
GET /api/clients
```

**Query Parameters:**
- `page` (integer): Page number
- `limit` (integer): Items per page
- `search` (string): Search term
- `status` (string): Filter by status
- `service_type` (string): Filter by service type

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "company_name": "Example Corp",
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "phone": "+1-555-123-4567",
            "address": "123 Main St",
            "city": "Example City",
            "postal_code": "12345",
            "status": "active",
            "created_at": "2025-01-01T00:00:00Z",
            "services": [
                {
                    "id": 1,
                    "type": "internet",
                    "package": "Premium 100Mbps",
                    "status": "active"
                }
            ]
        }
    ],
    "pagination": {
        "page": 1,
        "limit": 20,
        "total": 150,
        "pages": 8
    }
}
```

### Get Client

```http
GET /api/clients/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "company_name": "Example Corp",
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone": "+1-555-123-4567",
        "address": "123 Main St",
        "city": "Example City",
        "postal_code": "12345",
        "status": "active",
        "created_at": "2025-01-01T00:00:00Z",
        "services": [
            {
                "id": 1,
                "type": "internet",
                "package": "Premium 100Mbps",
                "status": "active",
                "start_date": "2025-01-01T00:00:00Z",
                "end_date": "2025-12-31T23:59:59Z"
            }
        ],
        "devices": [
            {
                "id": 1,
                "name": "Router 1",
                "ip_address": "192.168.1.1",
                "status": "online"
            }
        ],
        "invoices": [
            {
                "id": 1,
                "amount": 99.99,
                "status": "paid",
                "due_date": "2025-07-15T00:00:00Z"
            }
        ]
    }
}
```

### Create Client

```http
POST /api/clients
```

**Request Body:**
```json
{
    "company_name": "New Company",
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@newcompany.com",
    "phone": "+1-555-987-6543",
    "address": "456 Oak Ave",
    "city": "New City",
    "postal_code": "54321",
    "status": "active"
}
```

### Update Client

```http
PUT /api/clients/{id}
```

### Delete Client

```http
DELETE /api/clients/{id}
```

## 🖥️ Device Management

### Get Devices

```http
GET /api/devices
```

**Query Parameters:**
- `page` (integer): Page number
- `limit` (integer): Items per page
- `search` (string): Search term
- `status` (string): Filter by status
- `network_id` (integer): Filter by network
- `client_id` (integer): Filter by client

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Router 1",
            "ip_address": "192.168.1.1",
            "mac_address": "00:11:22:33:44:55",
            "type": "router",
            "model": "MikroTik RB450G",
            "status": "online",
            "last_seen": "2025-07-20T10:30:00Z",
            "client": {
                "id": 1,
                "name": "Example Corp"
            },
            "network": {
                "id": 1,
                "name": "Main Network",
                "subnet": "192.168.1.0/24"
            }
        }
    ]
}
```

### Get Device

```http
GET /api/devices/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Router 1",
        "ip_address": "192.168.1.1",
        "mac_address": "00:11:22:33:44:55",
        "type": "router",
        "model": "MikroTik RB450G",
        "status": "online",
        "last_seen": "2025-07-20T10:30:00Z",
        "snmp_community": "public",
        "snmp_version": "2c",
        "client": {
            "id": 1,
            "name": "Example Corp"
        },
        "network": {
            "id": 1,
            "name": "Main Network",
            "subnet": "192.168.1.0/24"
        },
        "interfaces": [
            {
                "name": "ether1",
                "status": "up",
                "speed": "1000Mbps",
                "traffic_in": 1024000,
                "traffic_out": 512000
            }
        ],
        "monitoring_data": {
            "cpu_usage": 15.5,
            "memory_usage": 45.2,
            "uptime": 86400
        }
    }
}
```

### Create Device

```http
POST /api/devices
```

**Request Body:**
```json
{
    "name": "New Router",
    "ip_address": "192.168.1.100",
    "mac_address": "00:11:22:33:44:66",
    "type": "router",
    "model": "MikroTik RB450G",
    "client_id": 1,
    "network_id": 1,
    "snmp_community": "public",
    "snmp_version": "2c"
}
```

### Update Device

```http
PUT /api/devices/{id}
```

### Delete Device

```http
DELETE /api/devices/{id}
```

### Check Device Status

```http
POST /api/devices/{id}/check
```

**Response:**
```json
{
    "success": true,
    "data": {
        "status": "online",
        "response_time": 15.5,
        "last_check": "2025-07-20T10:30:00Z",
        "interfaces": [
            {
                "name": "ether1",
                "status": "up",
                "speed": "1000Mbps"
            }
        ]
    }
}
```

## 🌐 Network Management

### Get Networks

```http
GET /api/networks
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Main Network",
            "subnet": "192.168.1.0/24",
            "gateway": "192.168.1.1",
            "dns_servers": ["8.8.8.8", "8.8.4.4"],
            "vlan_id": 100,
            "status": "active",
            "device_count": 25,
            "created_at": "2025-01-01T00:00:00Z"
        }
    ]
}
```

### Get Network

```http
GET /api/networks/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Main Network",
        "subnet": "192.168.1.0/24",
        "gateway": "192.168.1.1",
        "dns_servers": ["8.8.8.8", "8.8.4.4"],
        "vlan_id": 100,
        "status": "active",
        "created_at": "2025-01-01T00:00:00Z",
        "devices": [
            {
                "id": 1,
                "name": "Router 1",
                "ip_address": "192.168.1.1",
                "status": "online"
            }
        ],
        "dhcp_range": {
            "start": "192.168.1.100",
            "end": "192.168.1.200"
        },
        "usage_stats": {
            "total_ips": 254,
            "used_ips": 25,
            "available_ips": 229,
            "utilization": 9.8
        }
    }
}
```

### Create Network

```http
POST /api/networks
```

**Request Body:**
```json
{
    "name": "New Network",
    "subnet": "10.0.0.0/24",
    "gateway": "10.0.0.1",
    "dns_servers": ["8.8.8.8", "8.8.4.4"],
    "vlan_id": 200,
    "dhcp_range": {
        "start": "10.0.0.100",
        "end": "10.0.0.200"
    }
}
```

## 💰 Financial Management

### Get Invoices

```http
GET /api/invoices
```

**Query Parameters:**
- `page` (integer): Page number
- `limit` (integer): Items per page
- `client_id` (integer): Filter by client
- `status` (string): Filter by status (paid, unpaid, overdue)
- `date_from` (string): Filter from date (YYYY-MM-DD)
- `date_to` (string): Filter to date (YYYY-MM-DD)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "invoice_number": "INV-2025-001",
            "client": {
                "id": 1,
                "name": "Example Corp"
            },
            "amount": 99.99,
            "tax_amount": 8.00,
            "total_amount": 107.99,
            "status": "paid",
            "due_date": "2025-07-15T00:00:00Z",
            "paid_date": "2025-07-10T00:00:00Z",
            "created_at": "2025-07-01T00:00:00Z",
            "items": [
                {
                    "description": "Internet Service - Premium 100Mbps",
                    "quantity": 1,
                    "unit_price": 99.99,
                    "total": 99.99
                }
            ]
        }
    ]
}
```

### Get Invoice

```http
GET /api/invoices/{id}
```

### Create Invoice

```http
POST /api/invoices
```

**Request Body:**
```json
{
    "client_id": 1,
    "due_date": "2025-08-15T00:00:00Z",
    "items": [
        {
            "description": "Internet Service - Premium 100Mbps",
            "quantity": 1,
            "unit_price": 99.99
        }
    ],
    "notes": "Monthly service invoice"
}
```

### Update Invoice

```http
PUT /api/invoices/{id}
```

### Delete Invoice

```http
DELETE /api/invoices/{id}
```

### Mark Invoice as Paid

```http
POST /api/invoices/{id}/pay
```

**Request Body:**
```json
{
    "payment_method": "bank_transfer",
    "payment_date": "2025-07-20T10:30:00Z",
    "reference": "TXN123456"
}
```

### Get Payments

```http
GET /api/payments
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "invoice": {
                "id": 1,
                "invoice_number": "INV-2025-001"
            },
            "client": {
                "id": 1,
                "name": "Example Corp"
            },
            "amount": 107.99,
            "payment_method": "bank_transfer",
            "payment_date": "2025-07-10T00:00:00Z",
            "reference": "TXN123456",
            "status": "completed"
        }
    ]
}
```

## 📊 Monitoring and Analytics

### Get System Status

```http
GET /api/system/status
```

**Response:**
```json
{
    "success": true,
    "data": {
        "system": {
            "version": "1.0.0",
            "uptime": 86400,
            "load_average": [0.5, 0.3, 0.2],
            "memory_usage": 45.2,
            "disk_usage": 23.1
        },
        "database": {
            "status": "connected",
            "version": "8.0.35",
            "connections": 15,
            "slow_queries": 2
        },
        "services": {
            "web_server": "running",
            "database": "running",
            "monitoring": "running"
        },
        "statistics": {
            "total_clients": 150,
            "total_devices": 500,
            "total_networks": 25,
            "active_services": 200
        }
    }
}
```

### Get Monitoring Data

```http
GET /api/monitoring/data
```

**Query Parameters:**
- `device_id` (integer): Device ID
- `metric` (string): Metric type (cpu, memory, traffic, etc.)
- `period` (string): Time period (1h, 24h, 7d, 30d)
- `interval` (string): Data interval (1m, 5m, 15m, 1h)

**Response:**
```json
{
    "success": true,
    "data": {
        "device_id": 1,
        "metric": "cpu_usage",
        "period": "24h",
        "data": [
            {
                "timestamp": "2025-07-20T10:00:00Z",
                "value": 15.5
            },
            {
                "timestamp": "2025-07-20T10:05:00Z",
                "value": 16.2
            }
        ]
    }
}
```

### Get Analytics

```http
GET /api/analytics/summary
```

**Response:**
```json
{
    "success": true,
    "data": {
        "clients": {
            "total": 150,
            "active": 145,
            "inactive": 5,
            "growth_rate": 2.5
        },
        "revenue": {
            "monthly": 15000.00,
            "yearly": 180000.00,
            "growth_rate": 5.2
        },
        "services": {
            "internet": 120,
            "tv": 80,
            "phone": 45
        },
        "devices": {
            "total": 500,
            "online": 485,
            "offline": 15,
            "uptime": 98.5
        }
    }
}
```

## 🔧 System Administration

### Get Access Levels

```http
GET /api/admin/access-levels
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Administrator",
            "description": "Full system access",
            "permissions": [
                {
                    "section": "users",
                    "actions": ["read", "write", "delete"]
                },
                {
                    "section": "clients",
                    "actions": ["read", "write", "delete"]
                }
            ],
            "user_count": 5
        }
    ]
}
```

### Create Access Level

```http
POST /api/admin/access-levels
```

**Request Body:**
```json
{
    "name": "Custom Level",
    "description": "Custom access level",
    "permissions": [
        {
            "section": "clients",
            "actions": ["read", "write"]
        },
        {
            "section": "devices",
            "actions": ["read"]
        }
    ]
}
```

### Get System Logs

```http
GET /api/admin/logs
```

**Query Parameters:**
- `page` (integer): Page number
- `limit` (integer): Items per page
- `level` (string): Log level (error, warning, info, debug)
- `user_id` (integer): Filter by user
- `date_from` (string): Filter from date
- `date_to` (string): Filter to date

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "level": "info",
            "message": "User login successful",
            "user": {
                "id": 1,
                "username": "admin"
            },
            "ip_address": "192.168.1.100",
            "user_agent": "Mozilla/5.0...",
            "created_at": "2025-07-20T10:30:00Z"
        }
    ]
}
```

## 📝 Error Codes

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `204` - No Content
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Internal Server Error

### Error Codes

| Code | Description |
|------|-------------|
| `AUTHENTICATION_FAILED` | Invalid credentials |
| `AUTHORIZATION_FAILED` | Insufficient permissions |
| `VALIDATION_ERROR` | Request validation failed |
| `RESOURCE_NOT_FOUND` | Requested resource not found |
| `RESOURCE_EXISTS` | Resource already exists |
| `RATE_LIMIT_EXCEEDED` | Too many requests |
| `INTERNAL_ERROR` | Internal server error |

## 📚 SDK Examples

### PHP SDK

```php
<?php
require_once 'vendor/autoload.php';

use SLMS\Api\Client;

$client = new Client([
    'base_url' => 'https://your-domain.com/api',
    'api_key' => 'your_api_key'
]);

// Get clients
$clients = $client->clients()->list([
    'page' => 1,
    'limit' => 20,
    'search' => 'example'
]);

// Create client
$newClient = $client->clients()->create([
    'company_name' => 'New Company',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@newcompany.com'
]);

// Update client
$client->clients()->update(1, [
    'email' => 'updated@newcompany.com'
]);

// Delete client
$client->clients()->delete(1);
```

### JavaScript SDK

```javascript
import { SLMSClient } from '@slms/api-client';

const client = new SLMSClient({
    baseUrl: 'https://your-domain.com/api',
    apiKey: 'your_api_key'
});

// Get clients
const clients = await client.clients.list({
    page: 1,
    limit: 20,
    search: 'example'
});

// Create client
const newClient = await client.clients.create({
    company_name: 'New Company',
    first_name: 'John',
    last_name: 'Doe',
    email: 'john@newcompany.com'
});

// Update client
await client.clients.update(1, {
    email: 'updated@newcompany.com'
});

// Delete client
await client.clients.delete(1);
```

### Python SDK

```python
from slms_api import SLMSClient

client = SLMSClient(
    base_url='https://your-domain.com/api',
    api_key='your_api_key'
)

# Get clients
clients = client.clients.list(
    page=1,
    limit=20,
    search='example'
)

# Create client
new_client = client.clients.create({
    'company_name': 'New Company',
    'first_name': 'John',
    'last_name': 'Doe',
    'email': 'john@newcompany.com'
})

# Update client
client.clients.update(1, {
    'email': 'updated@newcompany.com'
})

# Delete client
client.clients.delete(1)
```

## 🔄 Webhooks

### Configure Webhook

```http
POST /api/webhooks
```

**Request Body:**
```json
{
    "url": "https://your-domain.com/webhook",
    "events": ["client.created", "invoice.paid"],
    "secret": "webhook_secret"
}
```

### Webhook Events

| Event | Description | Payload |
|-------|-------------|---------|
| `client.created` | Client created | Client data |
| `client.updated` | Client updated | Client data |
| `client.deleted` | Client deleted | Client ID |
| `invoice.created` | Invoice created | Invoice data |
| `invoice.paid` | Invoice paid | Invoice data |
| `device.online` | Device came online | Device data |
| `device.offline` | Device went offline | Device data |

### Webhook Payload Example

```json
{
    "event": "client.created",
    "timestamp": "2025-07-20T10:30:00Z",
    "data": {
        "id": 151,
        "company_name": "New Company",
        "email": "contact@newcompany.com"
    }
}
```

## 📊 Rate Limiting

API requests are rate-limited to ensure system stability:

- **Authenticated requests**: 1000 requests per hour
- **Unauthenticated requests**: 100 requests per hour
- **Bulk operations**: 100 requests per hour

Rate limit headers are included in responses:

```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640000000
```

## 🔒 Security

### Best Practices

1. **Store API keys securely**
2. **Use HTTPS for all requests**
3. **Validate all input data**
4. **Implement proper error handling**
5. **Monitor API usage**
6. **Rotate API keys regularly**

### IP Whitelisting

You can restrict API access to specific IP addresses:

```http
POST /api/admin/ip-whitelist
```

**Request Body:**
```json
{
    "ips": ["192.168.1.100", "10.0.0.0/8"]
}
```

---

**Last Updated**: July 20, 2025  
**Version**: sLMS v1.0 API Reference  
**Status**: ✅ **Active** 