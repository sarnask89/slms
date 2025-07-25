-- Access Levels Table
CREATE TABLE IF NOT EXISTS access_levels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Access Level Permissions Table
CREATE TABLE IF NOT EXISTS access_level_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    access_level_id INT NOT NULL,
    section VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (access_level_id) REFERENCES access_levels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_permission (access_level_id, section, action)
);

-- Add access_level_id column to users table if not exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS access_level_id INT,
ADD FOREIGN KEY (access_level_id) REFERENCES access_levels(id);

-- Get or create admin access level
INSERT IGNORE INTO access_levels (name, description) VALUES 
('Administrator', 'Full system access with all permissions');

-- Get the ID of the admin access level
SELECT @admin_level_id := id FROM access_levels WHERE name = 'Administrator';

-- Clear existing permissions for admin level to avoid duplicates
DELETE FROM access_level_permissions WHERE access_level_id = @admin_level_id;

-- Add all permissions for admin
INSERT INTO access_level_permissions (access_level_id, section, action) VALUES
-- Dashboard permissions
(@admin_level_id, 'dashboard', 'view'),
(@admin_level_id, 'dashboard', 'customize'),
(@admin_level_id, 'dashboard', 'export'),

-- Client permissions
(@admin_level_id, 'clients', 'view'),
(@admin_level_id, 'clients', 'add'),
(@admin_level_id, 'clients', 'edit'),
(@admin_level_id, 'clients', 'delete'),
(@admin_level_id, 'clients', 'export'),

-- Device permissions
(@admin_level_id, 'devices', 'view'),
(@admin_level_id, 'devices', 'add'),
(@admin_level_id, 'devices', 'edit'),
(@admin_level_id, 'devices', 'delete'),
(@admin_level_id, 'devices', 'monitor'),
(@admin_level_id, 'devices', 'configure'),

-- Network permissions
(@admin_level_id, 'networks', 'view'),
(@admin_level_id, 'networks', 'add'),
(@admin_level_id, 'networks', 'edit'),
(@admin_level_id, 'networks', 'delete'),
(@admin_level_id, 'networks', 'dhcp'),

-- Service permissions
(@admin_level_id, 'services', 'view'),
(@admin_level_id, 'services', 'add'),
(@admin_level_id, 'services', 'edit'),
(@admin_level_id, 'services', 'delete'),
(@admin_level_id, 'services', 'assign'),

-- Financial permissions
(@admin_level_id, 'financial', 'view'),
(@admin_level_id, 'financial', 'add'),
(@admin_level_id, 'financial', 'edit'),
(@admin_level_id, 'financial', 'delete'),
(@admin_level_id, 'financial', 'export'),

-- Monitoring permissions
(@admin_level_id, 'monitoring', 'view'),
(@admin_level_id, 'monitoring', 'configure'),
(@admin_level_id, 'monitoring', 'alerts'),
(@admin_level_id, 'monitoring', 'reports'),

-- User management permissions
(@admin_level_id, 'users', 'view'),
(@admin_level_id, 'users', 'add'),
(@admin_level_id, 'users', 'edit'),
(@admin_level_id, 'users', 'delete'),
(@admin_level_id, 'users', 'permissions'),

-- System permissions
(@admin_level_id, 'system', 'view'),
(@admin_level_id, 'system', 'configure'),
(@admin_level_id, 'system', 'backup'),
(@admin_level_id, 'system', 'logs'),
(@admin_level_id, 'system', 'maintenance');

-- Assign admin access level to existing admin users
UPDATE users SET access_level_id = @admin_level_id WHERE role = 'admin'; 