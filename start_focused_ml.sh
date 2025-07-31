#!/bin/bash

# Start Focused ML Services for Adaptive AI Assistant
echo "🎯 Starting Focused ML Services..."

# Start Focused ML Service
systemctl start focused-ml

echo "✅ Focused ML services started!"
echo ""
echo "🌐 Service URLs:"
echo "   - Focused ML Service: http://localhost:8000"
echo "   - API Documentation: http://localhost:8000/docs"
echo "   - Adaptive AI Demo: http://localhost/adaptive_ai_demo.html"
echo ""
echo "🎯 Capabilities:"
echo "   - Module modification and creation"
echo "   - Database updates from raw text"
echo "   - Network issue detection"
echo "   - GUI adaptation and modification"
