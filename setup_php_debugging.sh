#!/bin/bash

# 🔧 PHP Debugging & IntelliSense Setup Script
# Comprehensive setup for PHP development environment

set -e

print_section() {
    echo -e "\n================================================"
    echo -e "$1"
    echo -e "================================================\n"
}

print_status() {
    echo -e "✅ $1"
}

print_error() {
    echo -e "❌ $1"
}

print_warning() {
    echo -e "⚠️  $1"
}

print_info() {
    echo -e "ℹ️  $1"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
    print_error "This script should not be run as root"
    exit 1
fi

print_section "🔧 PHP Debugging & IntelliSense Setup"
echo "Setting up comprehensive PHP development environment..."
echo "Date: $(date)"
echo "User: $(whoami)"
echo "PHP Version: $(php -v | head -n1)"

# 1. Install Xdebug
print_section "1. Installing Xdebug Extension"

if ! php -m | grep -q xdebug; then
    print_info "Installing Xdebug..."
    sudo apt update
    sudo apt install -y php-xdebug
    
    if php -m | grep -q xdebug; then
        print_status "Xdebug installed successfully"
    else
        print_error "Failed to install Xdebug"
        exit 1
    fi
else
    print_status "Xdebug already installed"
fi

# 2. Configure Xdebug
print_section "2. Configuring Xdebug"

# Create Xdebug configuration
XDEBUG_CONFIG="
; Xdebug Configuration
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
"

# Find PHP configuration directories
PHP_CLI_CONF="/etc/php/8.2/cli/conf.d/20-xdebug.ini"
PHP_FPM_CONF="/etc/php/8.2/fpm/conf.d/20-xdebug.ini"

# Create CLI configuration
if [ ! -f "$PHP_CLI_CONF" ]; then
    print_info "Creating Xdebug CLI configuration..."
    echo "$XDEBUG_CONFIG" | sudo tee "$PHP_CLI_CONF" > /dev/null
    print_status "CLI configuration created"
else
    print_status "CLI configuration already exists"
fi

# Create FPM configuration
if [ ! -f "$PHP_FPM_CONF" ]; then
    print_info "Creating Xdebug FPM configuration..."
    echo "$XDEBUG_CONFIG" | sudo tee "$PHP_FPM_CONF" > /dev/null
    print_status "FPM configuration created"
else
    print_status "FPM configuration already exists"
fi

# 3. Install PHP IntelliSense Tools
print_section "3. Installing PHP Development Tools"

# Install Composer if not present
if ! command -v composer &> /dev/null; then
    print_info "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    print_status "Composer installed"
else
    print_status "Composer already installed"
fi

# Install PHP CS Fixer
if ! command -v php-cs-fixer &> /dev/null; then
    print_info "Installing PHP CS Fixer..."
    composer global require friendsofphp/php-cs-fixer
    print_status "PHP CS Fixer installed"
else
    print_status "PHP CS Fixer already installed"
fi

# Install PHPStan
if ! command -v phpstan &> /dev/null; then
    print_info "Installing PHPStan..."
    composer global require phpstan/phpstan
    print_status "PHPStan installed"
else
    print_status "PHPStan already installed"
fi

# 4. Create Development Configuration
print_section "4. Creating Development Configuration"

# Create .vscode directory
mkdir -p .vscode

# Create launch.json for debugging
cat > .vscode/launch.json << 'EOF'
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
EOF

# Create settings.json for VS Code
cat > .vscode/settings.json << 'EOF'
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
EOF

print_status "VS Code configuration created"

# 5. Create PHP Development Scripts
print_section "5. Creating Development Scripts"

# Create debug test script
cat > debug_test.php << 'EOF'
<?php
/**
 * PHP Debug Test Script
 * Tests Xdebug configuration and debugging capabilities
 */

echo "=== PHP Debug Test ===\n\n";

// Test Xdebug
if (extension_loaded('xdebug')) {
    echo "✅ Xdebug is loaded\n";
    echo "Version: " . phpversion('xdebug') . "\n";
    echo "Mode: " . ini_get('xdebug.mode') . "\n";
    echo "Client Host: " . ini_get('xdebug.client_host') . "\n";
    echo "Client Port: " . ini_get('xdebug.client_port') . "\n";
    echo "IDE Key: " . ini_get('xdebug.idekey') . "\n";
} else {
    echo "❌ Xdebug is not loaded\n";
}

// Test variables for debugging
$test_var = "Hello Debug!";
$test_array = ['key1' => 'value1', 'key2' => 'value2'];
$test_object = new stdClass();
$test_object->property = "test value";

echo "\n=== Test Variables ===\n";
echo "Test variable: $test_var\n";
echo "Test array: " . print_r($test_array, true);
echo "Test object: " . print_r($test_object, true);

// Test function for breakpoints
function test_function($param) {
    $local_var = "Local variable";
    echo "Function parameter: $param\n";
    echo "Local variable: $local_var\n";
    return "Function result";
}

echo "\n=== Function Test ===\n";
$result = test_function("Test parameter");
echo "Function result: $result\n";

echo "\n=== Debug Instructions ===\n";
echo "1. Set breakpoints in this file\n";
echo "2. Start debugging session in VS Code\n";
echo "3. Access this file via web server\n";
echo "4. Check debug console for variables\n";

// Breakpoint test
echo "\n=== Breakpoint Test ===\n";
$breakpoint_test = "You can set a breakpoint on this line";
echo "Breakpoint test: $breakpoint_test\n";
EOF

print_status "Debug test script created"

# 6. Restart Services
print_section "6. Restarting Services"

# Restart PHP-FPM
if systemctl list-unit-files | grep -q php8.2-fpm; then
    print_info "Restarting PHP-FPM..."
    sudo systemctl restart php8.2-fpm
    print_status "PHP-FPM restarted"
fi

# Restart Apache
if systemctl list-unit-files | grep -q apache2; then
    print_info "Restarting Apache..."
    sudo systemctl restart apache2
    print_status "Apache restarted"
fi

# 7. Test Configuration
print_section "7. Testing Configuration"

# Test PHP with Xdebug
echo "Testing PHP configuration..."
php -m | grep xdebug && print_status "Xdebug module loaded" || print_error "Xdebug module not loaded"

# Test debug configuration
echo "Testing debug configuration..."
php -r "echo 'Xdebug mode: ' . ini_get('xdebug.mode') . PHP_EOL;"

# 8. Create Development Environment Script
print_section "8. Creating Development Environment Script"

cat > start_dev_server.sh << 'EOF'
#!/bin/bash

# Development Server with Xdebug
echo "🚀 Starting PHP Development Server with Xdebug..."

# Set Xdebug environment variables
export XDEBUG_MODE=debug,develop
export XDEBUG_CONFIG="client_host=10.0.222.223 client_port=9003 idekey=VSCODE"

# Start PHP server
echo "Server starting at: http://10.0.222.223:8000"
echo "Debug port: 9003"
echo "Press Ctrl+C to stop"

php -S 10.0.222.223:8000
EOF

chmod +x start_dev_server.sh
print_status "Development server script created"

# 9. Final Instructions
print_section "9. Setup Complete! 🎉"

echo "Your PHP debugging environment is now configured!"
echo ""
echo "📋 Next Steps:"
echo "1. Install VS Code PHP extensions:"
echo "   - PHP Debug (xdebug.php-debug)"
echo "   - Intelephense (bmewburn.vscode-intelephense-client)"
echo "   - PHP DocBlocker (neilbrayfield.php-docblocker)"
echo ""
echo "2. Test debugging:"
echo "   - Run: php debug_test.php"
echo "   - Or start dev server: ./start_dev_server.sh"
echo "   - Set breakpoints in VS Code"
echo "   - Start debugging session"
echo ""
echo "3. Access your application:"
echo "   - Web: http://10.0.222.223/"
echo "   - Debug test: http://10.0.222.223/debug_test.php"
echo ""
echo "🔧 Configuration Files:"
echo "   - Xdebug CLI: $PHP_CLI_CONF"
echo "   - Xdebug FPM: $PHP_FPM_CONF"
echo "   - VS Code: .vscode/launch.json"
echo "   - VS Code: .vscode/settings.json"
echo ""
echo "📝 Logs:"
echo "   - Xdebug: /var/log/xdebug.log"
echo "   - PHP-FPM: /var/log/php8.2-fpm.log"
echo "   - Apache: /var/log/apache2/error.log"

print_section "Setup Complete!" 