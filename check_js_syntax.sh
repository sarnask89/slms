#!/bin/bash

echo "🔍 Checking JavaScript syntax..."

# Check if node is available for syntax checking
if command -v node &> /dev/null; then
    echo "Using Node.js to check syntax..."
    node -c webgl_interface.js
    if [ $? -eq 0 ]; then
        echo "✅ JavaScript syntax is valid"
    else
        echo "❌ JavaScript syntax errors found"
    fi
else
    echo "Node.js not available, using basic syntax check..."
    
    # Basic syntax check - look for common issues
    echo "Checking for common syntax issues..."
    
    # Check for balanced braces
    open_braces=$(grep -o '{' webgl_interface.js | wc -l)
    close_braces=$(grep -o '}' webgl_interface.js | wc -l)
    
    echo "Open braces: $open_braces"
    echo "Close braces: $close_braces"
    
    if [ $open_braces -eq $close_braces ]; then
        echo "✅ Brace count is balanced"
    else
        echo "❌ Brace count mismatch: $open_braces open, $close_braces close"
    fi
    
    # Check for class definition
    if grep -q "class SLMSWebGLInterface" webgl_interface.js; then
        echo "✅ SLMSWebGLInterface class found"
    else
        echo "❌ SLMSWebGLInterface class not found"
    fi
    
    # Check for proper class closing
    if grep -q "^}" webgl_interface.js; then
        echo "✅ Class appears to be properly closed"
    else
        echo "❌ Class closing may be incorrect"
    fi
fi

echo "🎯 Syntax check complete" 