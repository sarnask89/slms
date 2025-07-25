# 🚀 Quick Start Guide

## Welcome to sLMS!

This guide will help you get started with sLMS (Service Level Management System) in just a few minutes.

## 📋 Prerequisites

Before you begin, ensure you have:
- ✅ Web browser (Chrome, Firefox, Safari, Edge)
- ✅ Network access to the sLMS server
- ✅ Valid login credentials

## 🔐 First Login

### 1. Access the System
- Open your web browser
- Navigate to: `http://10.0.222.223:8000`
- You'll be redirected to the login page

### 2. Default Credentials
Use one of these default accounts:

| Role | Username | Password | Access Level |
|------|----------|----------|--------------|
| **Administrator** | `admin` | `admin123` | Full system access |
| **Manager** | `manager` | `manager123` | Manager access |
| **User** | `user` | `user123` | User access |
| **Viewer** | `viewer` | `viewer123` | Read-only access |

### 3. Login Process
1. Enter your username and password
2. Click "Login" or press Enter
3. You'll be redirected to the main dashboard

## 🎯 Dashboard Overview

### Main Interface Elements
- **Top Navigation Bar**: Quick access to main functions
- **Sidebar Menu**: Complete system navigation
- **Content Area**: Main workspace for modules
- **Status Bar**: System status and notifications

### Key Dashboard Features
- **Real-time Updates**: Auto-refresh every 5 minutes
- **Responsive Design**: Works on desktop and mobile
- **Theme Customization**: Personalize the interface
- **Quick Actions**: Fast access to common tasks

## 🧭 Navigation Basics

### Using the Menu System
1. **Main Categories**: Click to expand/collapse
2. **Sub-items**: Click to open specific modules
3. **Breadcrumbs**: Navigate back through pages
4. **Search**: Find functions quickly

### Keyboard Shortcuts
- `Ctrl + N`: New item (context-dependent)
- `Ctrl + S`: Save current form
- `Ctrl + F`: Search/filter
- `F5`: Refresh current page
- `Esc`: Close dialogs/popups

## 📊 Essential Functions

### 1. Client Management
**Location**: Zarządzanie Klientami → Lista Klientów
- View all clients
- Add new clients
- Edit client information
- Search and filter clients

### 2. Device Management
**Location**: Zarządzanie Urządzeniami → Lista Urządzeń
- Monitor network devices
- Check device status
- Add new devices
- Device configuration

### 3. Network Monitoring
**Location**: Monitoring Sieci (Cacti) → Integracja Cacti
- Real-time network monitoring
- Performance graphs
- Alert management
- Capacity planning

### 4. Theme Customization
**Location**: Administracja Systemu → Theme Editor
- Change color schemes
- Modify layout options
- Set auto-refresh intervals
- Preview changes in real-time

## 🎨 Customizing Your Experience

### Theme Editor
1. Go to **Administracja Systemu** → **Theme Editor**
2. Choose from available themes:
   - **Default**: Standard sLMS theme
   - **Dark**: Dark mode for low-light environments
   - **Light**: Bright theme for high-contrast
   - **Green**: Professional green theme
   - **Purple**: Modern purple theme

### Layout Options
- **1 Column**: Compact layout
- **2 Columns**: Balanced layout
- **3 Columns**: Wide layout for large screens

### Auto-refresh Settings
- **15 seconds**: Real-time monitoring
- **30 seconds**: Standard monitoring
- **1 minute**: Regular updates
- **5 minutes**: Low-frequency updates

## 🔍 Finding Help

### Built-in Help System
- **Hover Tooltips**: Hover over functions for quick help
- **Context Help**: Click (?) icons for detailed information
- **Inline Documentation**: Help text in forms and dialogs

### Documentation Access
- **User Manual**: Available in the Documentation section
- **API Reference**: For developers and advanced users
- **System Status**: Check system health and performance

## ⚡ Pro Tips

### Performance Optimization
1. **Use Frame Layout**: Enables faster navigation
2. **Enable Auto-refresh**: Keeps data current
3. **Use Search**: Find functions quickly
4. **Keyboard Shortcuts**: Speed up common tasks

### Best Practices
1. **Regular Logout**: Always log out when finished
2. **Data Backup**: Regular backups for important data
3. **Password Security**: Change default passwords
4. **Session Management**: Monitor active sessions

## 🚨 Troubleshooting

### Common Issues

#### Can't Login?
- Check username/password
- Verify server is running
- Clear browser cache
ls- Try different browser

#### Slow Performance?
- Check internet connection
- Close unnecessary browser tabs
- Clear browser cache
- Contact administrator

#### Missing Functions?
- Check user permissions
- Verify menu access
- Contact administrator
- Review user role settings

## 📞 Getting Help

### Support Channels
- **System Administrator**: For technical issues
- **Documentation**: Self-service help
- **User Community**: Peer support
- **Feedback System**: Feature requests

### Contact Information
- **Email**: admin@slms.local
- **Phone**: Contact your administrator
- **Documentation**: Available in the system

## 🎉 Congratulations!

You've successfully completed the Quick Start Guide! You're now ready to use sLMS effectively.

### Next Steps
1. **Explore Modules**: Try different system functions
2. **Customize Interface**: Set up your preferred theme
3. **Learn Advanced Features**: Read detailed documentation
4. **Practice**: Use the system regularly to become proficient

---

**Need More Help?**
- Check the [User Interface Guide](interface.md)
- Explore [Client Management](client-management.md)
- Review [Network Monitoring](network-monitoring.md)

**Last Updated**: July 20, 2025  
**Version**: sLMS v1.0 Quick Start 

---

## Why Did It Work Then, But Not Now?

**The difference is in the environment:**
- The documentation describes a setup where the code is present in `/var/www/html/slms` and Apache/PHP are installed and configured on the host (or in a properly built container).
- In your current Kubernetes deployment, the container is running, but **your application code is missing from `/var/www/html` inside the pod**. This is why you get 403/404 errors.

---

## What Changed?

- **On bare metal or a VM:**  
  - You had Apache, PHP, and your code in `/var/www/html/slms`—so it worked.
- **In Kubernetes:**  
  - The default `php:8.1-apache` image does NOT include your code unless you build a custom image or mount your code as a volume.
  - Your pod is running, but `/var/www/html` is empty, so Apache cannot serve your app.

---

## **Project Analysis: File-by-File Check**

### 1. **Project Structure**
Your project contains all the necessary PHP files, assets, modules, and documentation for sLMS.  
**Key files:**
- `index.php` (main entry point)
- `modules/` (all app modules)
- `assets/` (CSS, JS)
- `config.php` (configuration)
- `Dockerfile.dev`, `Dockerfile.bridge` (for building images)
- `slms-deployment.yaml` (Kubernetes deployment)
- Documentation and logs

### 2. **What’s Missing in Kubernetes?**
- **Your code is not present in the running container.**
- **No custom Docker image is being used** (or, if you are using one, it was not built with your code).

### 3. **How to Ensure Every File is Included?**
- **Build a Docker image that copies ALL your project files into `/var/www/html`**.
- **Deploy that image in Kubernetes**.

---

## **How to Fix: Step-by-Step**

1. **Build a Docker image with your code:**

   Create a `Dockerfile` in your project root:
   ```Dockerfile
   FROM php:8.1-apache
   COPY . /var/www/html/
   RUN chown -R www-data:www-data /var/www/html
   RUN a2enmod rewrite
   ```

2. **Build and push the image:**
   ```bash
   docker build -t yourdockerhubusername/slms:latest .
   docker push yourdockerhubusername/slms:latest
   ```

3. **Update your `slms-deployment.yaml` to use this image:**
   ```yaml
   containers:
     - name: slms-web
       image: yourdockerhubusername/slms:latest
       ports:
         - containerPort: 80
   ```

4. **Apply the deployment:**
   ```bash
   kubectl apply -f slms-deployment.yaml
   ```

5. **Verify:**
   - Exec into the pod and run `ls -l /var/www/html`—you should see all your files.
   - Access the app via `http://<node-ip>:30080`.

---

## **Summary Table**

| File/Folder         | Should be in Docker image? | Should be in pod? | Purpose                        |
|---------------------|---------------------------|-------------------|--------------------------------|
| index.php           | Yes                       | Yes               | Main entry point               |
| modules/            | Yes                       | Yes               | App modules                    |
| assets/             | Yes                       | Yes               | CSS/JS                         |
| config.php          | Yes (or via secret)       | Yes               | App config                     |
| Dockerfile*         | No                        | No                | Build only                     |
| slms-deployment.yaml| No                        | No                | Deploy only                    |
| logs/               | Optional                  | Optional          | App logs                       |

---

## **Conclusion**

- **Every file in your project should be present in `/var/www/html` inside the running pod.**
- **If you build and deploy your app as described above, it will work in Kubernetes just as it did when the documentation was written.**

---

**If you want, I can generate a complete Dockerfile and a deployment YAML for you, ensuring every file is included. Just say the word!** 