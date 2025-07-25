# 🔐 Access Level Management System

## Overview

The sLMS Access Level Management System provides granular control over user permissions through a flexible, role-based access control (RBAC) system. This system allows administrators to create custom access levels with specific permissions for different sections and actions within the system.

## 🏗️ System Architecture

### Database Structure

The access level system uses three main database tables:

1. **`access_levels`** - Stores access level definitions
2. **`access_level_permissions`** - Stores permissions for each access level
3. **`users`** - Modified to include `access_level_id` reference

### Permission Model

The system uses a **section:action** permission model:

- **Sections**: Logical groupings of functionality (e.g., `clients`, `devices`, `financial`)
- **Actions**: Specific operations within sections (e.g., `view`, `add`, `edit`, `delete`)

## 📋 Default Access Levels

### 1. Administrator
- **Description**: Full system access with all permissions
- **Use Case**: System administrators who need complete control
- **Permissions**: All sections and actions

### 2. Manager
- **Description**: Manager access with write permissions to most modules
- **Use Case**: Department managers who need to manage data but not system settings
- **Permissions**: Write access to basic modules, read access to system administration

### 3. User
- **Description**: Standard user access with read permissions
- **Use Case**: Regular users who need to view and work with data
- **Permissions**: Read access to all modules

### 4. Viewer
- **Description**: Read-only access to basic modules
- **Use Case**: Users who only need to view information
- **Permissions**: Read access to basic modules only

## 🛠️ Managing Access Levels

### Accessing the Access Level Manager

1. Log in as an administrator
2. Navigate to **Administracja Systemu** → **User Management** → **Access Levels**
3. Or directly access: `http://10.0.222.223:8000/modules/access_level_manager.php`

### Creating a New Access Level

1. Click **"Nowy poziom dostępu"** button
2. Fill in the form:
   - **Nazwa**: Name of the access level
   - **Opis**: Description of the access level
   - **Uprawnienia**: Select specific permissions for each section
3. Click **"Utwórz poziom dostępu"**

### Editing an Access Level

1. Click the **Edit** button (pencil icon) next to the access level
2. Modify the name, description, or permissions
3. Click **"Zapisz zmiany"**

### Deleting an Access Level

1. Click the **Delete** button (trash icon) next to the access level
2. Confirm the deletion
3. **Note**: Access levels cannot be deleted if they are assigned to users

### Assigning Access Levels to Users

1. In the **"Przypisywanie poziomów dostępu"** section
2. Click **"Przypisz"** next to a user
3. Select the desired access level from the dropdown
4. Click **"Przypisz"**

## 📊 Available Sections and Actions

### Dashboard
- **view**: View dashboard
- **customize**: Customize dashboard layout
- **export**: Export dashboard data

### Client Management
- **view**: View client list
- **add**: Add new clients
- **edit**: Edit client information
- **delete**: Delete clients
- **export**: Export client data

### Device Management
- **view**: View device list
- **add**: Add new devices
- **edit**: Edit device information
- **delete**: Delete devices
- **monitor**: Monitor device status
- **configure**: Configure devices

### Network Management
- **view**: View network list
- **add**: Add new networks
- **edit**: Edit network settings
- **delete**: Delete networks
- **dhcp**: Manage DHCP settings

### Services & Packages
- **view**: View services list
- **add**: Add new services
- **edit**: Edit service information
- **delete**: Delete services
- **assign**: Assign services to clients

### Financial Management
- **view**: View financial data
- **add**: Add invoices/payments
- **edit**: Edit financial records
- **delete**: Delete financial records
- **export**: Export financial reports

### Network Monitoring
- **view**: View monitoring data
- **configure**: Configure monitoring
- **alerts**: Manage alerts
- **reports**: Generate reports

### User Management
- **view**: View user list
- **add**: Add new users
- **edit**: Edit user information
- **delete**: Delete users
- **permissions**: Manage user permissions

### System Administration
- **view**: View system status
- **configure**: Configure system settings
- **backup**: Manage backups
- **logs**: View system logs
- **maintenance**: Perform maintenance

## 💻 Using Access Levels in Code

### Basic Permission Checking

```php
<?php
require_once 'helpers/auth_helper.php';

// Check if user can view clients
if (has_access_permission('clients', 'view')) {
    // Show client list
    display_clients();
}

// Check if user can add clients
if (has_access_permission('clients', 'add')) {
    // Show add client form
    show_add_client_form();
}
?>
```

### Requiring Specific Permissions

```php
<?php
require_once 'helpers/auth_helper.php';

// Require specific permission - redirects to login if not authorized
require_access_permission('financial', 'export');

// This code only runs if user has financial:export permission
generate_financial_report();
?>
```

### Getting User Information

```php
<?php
// Get current user's access level
$accessLevel = get_user_access_level();

// Get all user permissions
$permissions = get_user_permissions();

// Display user's access level
echo "Access Level: " . $accessLevel['name'];
echo "Description: " . $accessLevel['description'];
?>
```

### Conditional UI Elements

```php
<?php if (has_access_permission('clients', 'add')): ?>
    <a href="add_client.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Add Client
    </a>
<?php endif; ?>

<?php if (has_access_permission('clients', 'delete')): ?>
    <button class="btn btn-danger" onclick="deleteClient(id)">
        <i class="bi bi-trash"></i> Delete
    </button>
<?php endif; ?>
```

## 🔧 Setup and Installation

### Initial Setup

1. Run the setup script:
   ```bash
   php setup_access_level_tables.php
   ```

2. This will:
   - Create necessary database tables
   - Create default access levels
   - Assign access levels to existing users based on their roles

### Database Schema

```sql
-- Access levels table
CREATE TABLE access_levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Access level permissions table
CREATE TABLE access_level_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    access_level_id INT NOT NULL,
    section VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (access_level_id) REFERENCES access_levels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_permission (access_level_id, section, action)
);

-- Modified users table
ALTER TABLE users ADD COLUMN access_level_id INT NULL AFTER role,
ADD FOREIGN KEY (access_level_id) REFERENCES access_levels(id) ON DELETE SET NULL;
```

## 🚀 Best Practices

### 1. Principle of Least Privilege
- Only grant permissions that users actually need
- Start with minimal permissions and add as needed
- Regularly review and audit access levels

### 2. Naming Conventions
- Use descriptive names for access levels
- Include the purpose or department in the name
- Use consistent naming patterns

### 3. Documentation
- Document the purpose of each access level
- Keep track of which users have which access levels
- Document any custom permissions

### 4. Testing
- Test access levels with different user accounts
- Verify that permissions work as expected
- Test edge cases and error conditions

### 5. Security
- Regularly review access levels for security
- Remove unused access levels
- Monitor for unusual access patterns

## 🔍 Troubleshooting

### Common Issues

1. **User can't access expected features**
   - Check if user has the correct access level assigned
   - Verify that the access level has the required permissions
   - Check if the permission check is implemented correctly

2. **Access level can't be deleted**
   - Ensure no users are assigned to the access level
   - Check for any foreign key constraints

3. **Permissions not working**
   - Verify database tables exist and are properly configured
   - Check if the auth_helper.php file is included
   - Ensure session is started

### Debugging

```php
<?php
// Debug user permissions
$permissions = get_user_permissions();
echo "<pre>";
print_r($permissions);
echo "</pre>";

// Debug access level
$accessLevel = get_user_access_level();
echo "<pre>";
print_r($accessLevel);
echo "</pre>";
?>
```

## 📈 Monitoring and Auditing

### Activity Logging

The system automatically logs user activity. Monitor these logs to:
- Track permission usage
- Identify security issues
- Audit user actions

### Access Level Reports

Use the Access Level Manager to:
- View which users have which access levels
- See permission counts for each access level
- Monitor access level assignments

## 🔄 Migration from Legacy System

The access level system is designed to work alongside the existing role-based system:

1. **Legacy roles** (admin, manager, user, viewer) are still supported
2. **Access levels** provide additional granular control
3. **Both systems** can be used simultaneously during migration
4. **Gradual migration** is recommended

### Migration Steps

1. Set up access level tables
2. Create custom access levels as needed
3. Assign access levels to users
4. Update code to use new permission functions
5. Test thoroughly
6. Remove legacy permission checks

---

**For more information, see the [User Management Guide](../user-guide/user-management.md) and [API Reference](../api-reference/README.md).** 