# 🔧 PHP Debugging & IntelliSense Setup Guide

## Overview
This guide provides comprehensive setup instructions for PHP debugging with Xdebug and enhanced IntelliSense in VS Code for the sLMS project.

## 📋 Prerequisites

### System Requirements
- **PHP Version**: 8.2.29 (confirmed)
- **Operating System**: Linux (Debian/Ubuntu)
- **Web Server**: Apache2 with PHP-FPM
- **IDE**: Visual Studio Code

### Required Extensions
Install these VS Code extensions for optimal PHP development:

1. **PHP Debug** (`xdebug.php-debug`)
   - Provides Xdebug integration
   - Enables breakpoint debugging
   - Variable inspection and watch

2. **Intelephense** (`bmewburn.vscode-intelephense-client`)
   - Advanced PHP IntelliSense
   - Code completion and suggestions
   - Error detection and validation

3. **PHP DocBlocker** (`neilbrayfield.php-docblocker`)
   - Automatic PHPDoc generation
   - Code documentation helpers

4. **PHP CS Fixer** (`junstyle.php-cs-fixer`)
   - Code formatting and standards
   - PSR-2/PSR-12 compliance

5. **PHP Namespace Resolver** (`mehedidracula.php-namespace-resolver`)
   - Automatic namespace imports
   - Class organization

## 🚀 Quick Setup

### 1. Run the Setup Script
```bash
cd /var/www/html
chmod +x setup_php_debugging.sh
./setup_php_debugging.sh
```

### 2. Install VS Code Extensions
```bash
code --install-extension xdebug.php-debug
code --install-extension bmewburn.vscode-intelephense-client
code --install-extension neilbrayfield.php-docblocker
code --install-extension junstyle.php-cs-fixer
code --install-extension mehedidracula.php-namespace-resolver
```

## ⚙️ Configuration Details

### Xdebug Configuration
The setup script creates Xdebug configuration files:

**CLI Configuration**: `/etc/php/8.2/cli/conf.d/20-xdebug.ini`
**FPM Configuration**: `/etc/php/8.2/fpm/conf.d/20-xdebug.ini`

```ini
zend_extension=xdebug.so
xdebug.mode=debug,develop
xdebug.start_with_request=yes
xdebug.client_host=10.0.222.223
xdebug.client_port=9003
xdebug.idekey=VSCODE
xdebug.discover_client_host=true
xdebug.log=/var/log/xdebug.log
xdebug.log_level=7
xdebug.max_nesting_level=256
xdebug.var_display_max_children=128
xdebug.var_display_max_data=512
xdebug.var_display_max_depth=3
```

### VS Code Configuration

#### Workspace Settings (`.vscode/settings.json`)
```json
{
    "php.validate.enable": true,
    "php.validate.executablePath": "/usr/bin/php",
    "php.validate.run": "onType",
    "php.suggest.basic": false,
    "php.completion.insertUseDeclaration": true,
    "php.completion.autoInsertUseStatement": true,
    "php.format.enable": true,
    "php.format.rules": {
        "@PSR2": true
    },
    "php.debug.ideKey": "VSCODE",
    "php.debug.port": 9003,
    "files.associations": {
        "*.php": "php"
    },
    "emmet.includeLanguages": {
        "php": "html"
    },
    "editor.tabSize": 4,
    "editor.insertSpaces": true,
    "editor.rulers": [120],
    "files.watcherExclude": {
        "**/cache/**": true,
        "**/logs/**": true,
        "**/vendor/**": true
    }
}
```

#### Debug Configuration (`.vscode/launch.json`)
```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            },
            "xdebugSettings": {
                "max_children": 128,
                "max_data": 512,
                "max_depth": 3
            },
            "log": true
        },
        {
            "name": "Launch currently open script",
            "type": "php",
            "request": "launch",
            "program": "${file}",
            "cwd": "${fileDirname}",
            "port": 0,
            "runtimeArgs": [
                "-dxdebug.start_with_request=yes"
            ],
            "env": {
                "XDEBUG_MODE": "debug,develop",
                "XDEBUG_CONFIG": "client_port=${port}"
            }
        }
    ]
}
```

## 🐛 Debugging Workflow

### 1. Start Debugging Session
1. Open VS Code
2. Set breakpoints in your PHP files
3. Press `F5` or go to Run → Start Debugging
4. Select "Listen for Xdebug"

### 2. Trigger Debug Session
**Option A: Web Server**
```bash
# Start development server
./start_dev_server.sh

# Access your application
# http://10.0.222.223:8000
```

**Option B: Direct Script Execution**
```bash
# Run PHP script directly
php debug_test.php
```

### 3. Debug Features
- **Breakpoints**: Click in the gutter to set breakpoints
- **Step Over**: `F10` - Execute current line
- **Step Into**: `F11` - Enter function calls
- **Step Out**: `Shift+F11` - Exit current function
- **Continue**: `F5` - Continue execution
- **Variables**: Inspect variables in Debug panel
- **Watch**: Add expressions to watch panel
- **Call Stack**: View function call hierarchy

## 🔍 IntelliSense Features

### Code Completion
- **Class Methods**: Automatic method suggestions
- **Function Parameters**: Parameter hints and types
- **Variable Types**: Type inference and suggestions
- **Namespace Resolution**: Automatic imports

### Error Detection
- **Syntax Errors**: Real-time syntax checking
- **Type Errors**: Type mismatch detection
- **Undefined Variables**: Variable usage validation
- **Missing Imports**: Class/function import suggestions

### Code Navigation
- **Go to Definition**: `F12` or `Ctrl+Click`
- **Find All References**: `Shift+F12`
- **Symbol Search**: `Ctrl+T`
- **Outline View**: File structure overview

## 📝 Development Best Practices

### 1. Code Organization
```php
<?php
/**
 * File: modules/example_module.php
 * Description: Example module with proper documentation
 * Author: Your Name
 * Date: 2024-12-19
 */

declare(strict_types=1);

namespace SLMS\Modules;

use SLMS\Helpers\DatabaseHelper;
use SLMS\Helpers\AuthHelper;

/**
 * Example Module Class
 * 
 * @package SLMS\Modules
 */
class ExampleModule
{
    private DatabaseHelper $db;
    private AuthHelper $auth;
    
    public function __construct()
    {
        $this->db = new DatabaseHelper();
        $this->auth = new AuthHelper();
    }
    
    /**
     * Example method with proper documentation
     * 
     * @param string $param Description of parameter
     * @return array Description of return value
     * @throws \Exception When something goes wrong
     */
    public function exampleMethod(string $param): array
    {
        // Set breakpoint here for debugging
        $result = $this->db->query("SELECT * FROM table WHERE field = ?", [$param]);
        
        return $result;
    }
}
```

### 2. Debugging Tips
- **Use Descriptive Variable Names**: Makes debugging easier
- **Add Comments**: Explain complex logic
- **Use Type Hints**: Helps IntelliSense and debugging
- **Log Important Values**: Use `error_log()` for debugging
- **Test Incrementally**: Debug small sections at a time

### 3. Performance Monitoring
```php
// Performance measurement
$startTime = microtime(true);
$startMemory = memory_get_usage();

// Your code here
$result = performOperation();

$endTime = microtime(true);
$endMemory = memory_get_usage();

$executionTime = ($endTime - $startTime) * 1000; // milliseconds
$memoryUsed = $endMemory - $startMemory;

error_log("Operation took {$executionTime}ms and used {$memoryUsed} bytes");
```

## 🔧 Troubleshooting

### Common Issues

#### 1. Xdebug Not Connecting
**Symptoms**: Breakpoints not hit, no debug session
**Solutions**:
```bash
# Check Xdebug is loaded
php -m | grep xdebug

# Check Xdebug configuration
php -i | grep xdebug

# Check Xdebug log
sudo tail -f /var/log/xdebug.log

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart apache2
```

#### 2. IntelliSense Not Working
**Symptoms**: No code completion, errors not detected
**Solutions**:
```bash
# Reload VS Code window
Ctrl+Shift+P → "Developer: Reload Window"

# Restart Intelephense
Ctrl+Shift+P → "Intelephense: Restart Server"

# Check PHP executable path
which php
```

#### 3. Performance Issues
**Symptoms**: Slow IntelliSense, high CPU usage
**Solutions**:
```json
// Add to settings.json
{
    "intelephense.files.maxSize": 1000000,
    "intelephense.files.exclude": [
        "**/vendor/**",
        "**/node_modules/**",
        "**/cache/**"
    ]
}
```

### Debug Logs
- **Xdebug Log**: `/var/log/xdebug.log`
- **PHP-FPM Log**: `/var/log/php8.2-fpm.log`
- **Apache Log**: `/var/log/apache2/error.log`
- **VS Code Log**: Help → Toggle Developer Tools

## 📚 Additional Resources

### Documentation
- [Xdebug Documentation](https://xdebug.org/docs/)
- [Intelephense Documentation](https://intelephense.com/)
- [VS Code PHP Debug](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug)

### Useful Commands
```bash
# Check PHP configuration
php -i | grep xdebug

# Test Xdebug connection
php -r "var_dump(xdebug_info());"

# Validate PHP syntax
find . -name "*.php" -exec php -l {} \;

# Check for syntax errors
php -l your_file.php

# Profile PHP performance
php -d xdebug.profiler_enable=1 your_script.php
```

### VS Code Shortcuts
- `F5`: Start debugging
- `F9`: Toggle breakpoint
- `F10`: Step over
- `F11`: Step into
- `Shift+F11`: Step out
- `Ctrl+Shift+P`: Command palette
- `F12`: Go to definition
- `Shift+F12`: Find all references

## 🎯 Project-Specific Configuration

### sLMS Module Structure
The sLMS project uses a modular structure. Configure IntelliSense for:

```json
{
    "intelephense.environment.includePaths": [
        "/var/www/html",
        "/var/www/html/modules",
        "/var/www/html/modules/helpers",
        "/var/www/html/api"
    ]
}
```

### Database Integration
For database debugging, use prepared statements and log queries:

```php
// Enable query logging in debug mode
if ($debug_mode) {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}
```

This comprehensive setup provides a professional PHP development environment with full debugging capabilities and enhanced IntelliSense for the sLMS project. 