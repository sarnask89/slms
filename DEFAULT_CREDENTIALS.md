# 🔐 sLMS Default Credentials

## 👑 Administrator Account
- **Username**: `admin`
- **Password**: `admin123`
- **Role**: Full system access
- **Permissions**: All modules with admin rights

## 👨‍💼 Manager Account
- **Username**: `manager`
- **Password**: `manager123`
- **Role**: Manager
- **Permissions**: Write access to basic modules

## 👤 User Account
- **Username**: `user`
- **Password**: `user123`
- **Role**: User
- **Permissions**: Read access to basic modules

## 👁️ Viewer Account
- **Username**: `viewer`
- **Password**: `viewer123`
- **Role**: Viewer
- **Permissions**: Read-only access to basic modules

## 🌐 Access URLs

### Main System
- **sLMS Web Interface**: http://10.0.222.223:8000
- **Admin Menu**: http://10.0.222.223:8000/admin_menu.php

### Cacti Integration
- **Cacti Integration**: http://10.0.222.223:8000/modules/cacti_integration.php
- **Cacti Direct Access**: http://10.0.222.223:8081
  - Username: `admin`
  - Password: `admin`

## ⚠️ Security Recommendations

1. **Change Default Passwords**: Immediately change all default passwords after first login
2. **Delete Unused Accounts**: Remove or disable accounts that are not needed
3. **Use Strong Passwords**: Implement strong password policies for production use
4. **Regular Updates**: Keep the system updated with security patches
5. **Monitor Activity**: Regularly check the activity log for suspicious activity

## 🔧 Quick Setup Commands

```bash
# Run the credentials setup script
php setup_default_credentials.php

# Start the development server
./run_local_server.sh

# Check system status
php debug_system.php
```

## 📋 Role Permissions

### Administrator (admin)
- Full system access
- User management
- System configuration
- All module access with admin rights

### Manager (manager)
- Write access to basic modules
- Client and device management
- Network configuration
- Limited administrative functions

### User (user)
- Read access to basic modules
- View clients and devices
- Basic reporting access
- No administrative functions

### Viewer (viewer)
- Read-only access to basic modules
- View-only access to system data
- No modification capabilities
- Minimal system interaction

## 🚀 Getting Started

1. **Access the System**: Go to http://10.0.222.223:8000
2. **Login**: Use the admin credentials above
3. **Change Password**: Immediately change the admin password
4. **Configure System**: Set up your network devices and monitoring
5. **Add Users**: Create additional user accounts as needed

## 📞 Support

If you encounter any issues:
1. Check the debug report: `php debug_system.php`
2. Review the logs in the `logs/` directory
3. Verify database connectivity
4. Check file permissions

---

**Remember**: These are development credentials. Always use strong, unique passwords in production environments! 