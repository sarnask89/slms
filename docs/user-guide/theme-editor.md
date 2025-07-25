# 🎨 Theme Editor Guide

## Overview

The sLMS Theme Editor is a powerful tool that allows you to customize the appearance and behavior of your dashboard. With real-time preview and comprehensive customization options, you can create a personalized experience that matches your preferences and workflow.

## 🚀 Accessing the Theme Editor

### Navigation Path
1. **Administracja Systemu** → **Theme Editor**
2. Direct URL: `http://10.0.222.223:8000/modules/dashboard_editor.php`

### Prerequisites
- ✅ Administrator or Manager role
- ✅ Active session
- ✅ JavaScript enabled in browser

## 🎯 Main Features

### 1. Color Theme Selection
**Tooltip**: "Choose from predefined color schemes or create custom themes"

#### Available Themes
- **Default Theme** 🎨
  - *Tooltip*: "Standard sLMS theme with professional blue color scheme"
  - Primary: #007bff (Bootstrap Blue)
  - Secondary: #6c757d (Gray)
  - Accent: #28a745 (Green)

- **Dark Theme** 🌙
  - *Tooltip*: "Dark mode theme for low-light environments and reduced eye strain"
  - Primary: #343a40 (Dark Gray)
  - Secondary: #495057 (Medium Gray)
  - Accent: #17a2b8 (Cyan)

- **Light Theme** ☀️
  - *Tooltip*: "Bright theme with high contrast for maximum readability"
  - Primary: #ffffff (White)
  - Secondary: #f8f9fa (Light Gray)
  - Accent: #dc3545 (Red)

- **Green Theme** 🌿
  - *Tooltip*: "Professional green theme for nature and eco-friendly environments"
  - Primary: #28a745 (Green)
  - Secondary: #20c997 (Teal)
  - Accent: #ffc107 (Yellow)

- **Purple Theme** 🟣
  - *Tooltip*: "Modern purple theme for creative and innovative environments"
  - Primary: #6f42c1 (Purple)
  - Secondary: #e83e8c (Pink)
  - Accent: #fd7e14 (Orange)

### 2. Layout Configuration
**Tooltip**: "Configure the number of columns and overall layout structure"

#### Layout Options
- **1 Column Layout** 📱
  - *Tooltip*: "Compact single-column layout ideal for mobile devices and narrow screens"
  - Best for: Mobile devices, tablets, narrow monitors
  - Content width: 100%

- **2 Column Layout** 📊
  - *Tooltip*: "Balanced two-column layout providing optimal space utilization"
  - Best for: Standard desktop monitors, balanced content display
  - Content width: 50% each column

- **3 Column Layout** 🖥️
  - *Tooltip*: "Wide three-column layout for large screens and maximum content visibility"
  - Best for: Large monitors, multi-tasking workflows
  - Content width: 33.33% each column

### 3. Auto-refresh Settings
**Tooltip**: "Configure how often the dashboard automatically refreshes data"

#### Refresh Intervals
- **15 Seconds** ⚡
  - *Tooltip*: "Real-time monitoring with frequent updates for critical systems"
  - Use case: Network monitoring, critical alerts
  - Data freshness: Near real-time

- **30 Seconds** 🔄
  - *Tooltip*: "Standard monitoring interval for most operational tasks"
  - Use case: General monitoring, daily operations
  - Data freshness: 30 seconds old

- **1 Minute** ⏱️
  - *Tooltip*: "Regular updates suitable for most business applications"
  - Use case: Business reporting, routine monitoring
  - Data freshness: 1 minute old

- **5 Minutes** 🕐
  - *Tooltip*: "Low-frequency updates to reduce server load and bandwidth usage"
  - Use case: Background monitoring, resource conservation
  - Data freshness: 5 minutes old

### 4. Component Toggles
**Tooltip**: "Enable or disable specific dashboard components"

#### Cacti Integration Components
- **Cacti Devices** 📡
  - *Tooltip*: "Display network devices monitored by Cacti system"
  - Shows: Device list, status, performance metrics
  - Data source: Cacti API integration

- **Cacti Graphs** 📈
  - *Tooltip*: "Show performance graphs and historical data from Cacti"
  - Shows: Network performance, bandwidth usage, trends
  - Data source: Cacti graphing system

- **Cacti Status** 🟢
  - *Tooltip*: "Display overall Cacti system status and health indicators"
  - Shows: System health, alerts, warnings
  - Data source: Cacti status monitoring

#### SNMP Monitoring Components
- **SNMP Monitoring** 🔍
  - *Tooltip*: "Enable SNMP-based network device monitoring"
  - Shows: Device availability, response times
  - Data source: SNMP polling

- **SNMP Graphs** 📊
  - *Tooltip*: "Display SNMP-based performance graphs and metrics"
  - Shows: Interface statistics, traffic patterns
  - Data source: SNMP data collection

- **SNMP Alerts** 🚨
  - *Tooltip*: "Show SNMP-based alerts and notifications"
  - Shows: Threshold violations, device issues
  - Data source: SNMP alert system

## 🎨 Advanced Customization

### Custom Color Schemes
**Tooltip**: "Create your own color schemes by modifying CSS variables"

#### Color Variables
```css
--primary-color: #007bff;    /* Main brand color */
--secondary-color: #6c757d;  /* Secondary elements */
--accent-color: #28a745;     /* Highlight color */
--background-color: #ffffff; /* Page background */
--text-color: #212529;       /* Text color */
```

### CSS Customization
**Tooltip**: "Add custom CSS for advanced styling and layout modifications"

#### Common Customizations
- **Font Changes**: Modify font family, size, weight
- **Spacing Adjustments**: Customize margins, padding, gaps
- **Border Styling**: Add borders, shadows, rounded corners
- **Animation Effects**: Add transitions, hover effects

## 🔄 Real-time Preview

### Live Preview Features
**Tooltip**: "See changes instantly without saving or refreshing"

#### Preview Capabilities
- **Instant Updates**: Changes appear immediately
- **Theme Switching**: Click themes to preview instantly
- **Layout Changes**: See layout modifications in real-time
- **Component Toggles**: Enable/disable components instantly

### Preview Controls
- **Apply Changes**: Save current preview to system
- **Reset to Default**: Restore original settings
- **Undo Changes**: Revert last modification
- **Full Screen Preview**: Expand preview to full screen

## 💾 Saving and Managing Themes

### Save Options
**Tooltip**: "Save your theme configuration for future use"

#### Save Methods
- **Save Current Theme**: Save current configuration
- **Save As New Theme**: Create a new theme variant
- **Export Theme**: Download theme configuration file
- **Import Theme**: Load theme from file

### Theme Management
**Tooltip**: "Manage multiple theme configurations and switch between them"

#### Management Features
- **Theme Library**: Store multiple themes
- **Theme Switching**: Quick theme changes
- **Theme Backup**: Automatic theme backups
- **Theme Sharing**: Share themes with other users

## 🔧 Technical Details

### Browser Compatibility
**Tooltip**: "Theme editor works best with modern browsers"

#### Supported Browsers
- **Chrome**: Full support, recommended
- **Firefox**: Full support
- **Safari**: Full support
- **Edge**: Full support
- **Internet Explorer**: Limited support

### Performance Considerations
**Tooltip**: "Theme changes may affect system performance"

#### Performance Impact
- **CSS Loading**: Minimal impact
- **JavaScript**: Low overhead
- **Memory Usage**: Negligible increase
- **Network**: No additional traffic

## 🚨 Troubleshooting

### Common Issues

#### Theme Not Applying
**Tooltip**: "If theme changes don't appear, try these solutions"

**Solutions:**
1. Clear browser cache
2. Refresh the page
3. Check JavaScript console for errors
4. Verify user permissions

#### Preview Not Working
**Tooltip**: "If preview doesn't update, check these settings"

**Solutions:**
1. Enable JavaScript
2. Check browser compatibility
3. Clear browser cache
4. Restart browser

#### Performance Issues
**Tooltip**: "If system becomes slow, optimize theme settings"

**Solutions:**
1. Reduce auto-refresh frequency
2. Disable unnecessary components
3. Use simpler color schemes
4. Clear browser cache

## 📚 Best Practices

### Theme Design
**Tooltip**: "Follow these guidelines for optimal theme design"

#### Design Principles
1. **Contrast**: Ensure good text readability
2. **Consistency**: Use consistent color schemes
3. **Accessibility**: Consider color-blind users
4. **Performance**: Keep themes lightweight

### User Experience
**Tooltip**: "Optimize themes for better user experience"

#### UX Guidelines
1. **Intuitive Navigation**: Clear visual hierarchy
2. **Responsive Design**: Work on all screen sizes
3. **Fast Loading**: Minimize theme complexity
4. **Accessibility**: Follow WCAG guidelines

## 🔮 Future Features

### Upcoming Enhancements
**Tooltip**: "Planned features for future theme editor versions"

#### Planned Features
- **Theme Templates**: Pre-built theme templates
- **Advanced Animations**: CSS animations and transitions
- **Dark Mode Toggle**: Automatic dark/light mode switching
- **Theme Marketplace**: Share and download themes
- **Custom Icons**: Upload custom icons and logos

---

## 📞 Support

### Getting Help
- **Documentation**: Check this guide for detailed information
- **Tooltips**: Hover over elements for quick help
- **System Administrator**: Contact for technical issues
- **User Community**: Share themes and get feedback

### Contact Information
- **Email**: admin@slms.local
- **Documentation**: Available in the system
- **Feedback**: Use the feedback system

---

**Last Updated**: July 20, 2025  
**Version**: sLMS v1.0 Theme Editor  
**Status**: ✅ **Active** 