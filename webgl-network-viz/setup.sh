#!/bin/bash

# Network Scanner Setup Script
# This script sets up the network scanner daemon and API server

set -e  # Exit on any error

echo "=========================================="
echo "Network Scanner Setup Script"
echo "=========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        print_warning "Running as root. Some operations may require elevated privileges."
    fi
}

# Check Python version
check_python() {
    print_status "Checking Python version..."
    
    if command -v python3 &> /dev/null; then
        PYTHON_VERSION=$(python3 --version 2>&1 | awk '{print $2}')
        print_success "Python $PYTHON_VERSION found"
        
        # Check if version is 3.7 or higher
        if python3 -c "import sys; exit(0 if sys.version_info >= (3, 7) else 1)"; then
            print_success "Python version is compatible (3.7+)"
        else
            print_error "Python 3.7 or higher is required"
            exit 1
        fi
    else
        print_error "Python 3 is not installed"
        exit 1
    fi
}

# Install system dependencies
install_system_deps() {
    print_status "Installing system dependencies..."
    
    # Detect OS
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        # Linux
        if command -v apt-get &> /dev/null; then
            # Debian/Ubuntu
            print_status "Installing dependencies for Debian/Ubuntu..."
            sudo apt-get update
            sudo apt-get install -y python3-pip python3-venv build-essential libssl-dev libffi-dev python3-dev
        elif command -v yum &> /dev/null; then
            # CentOS/RHEL
            print_status "Installing dependencies for CentOS/RHEL..."
            sudo yum install -y python3-pip python3-devel gcc openssl-devel libffi-devel
        elif command -v dnf &> /dev/null; then
            # Fedora
            print_status "Installing dependencies for Fedora..."
            sudo dnf install -y python3-pip python3-devel gcc openssl-devel libffi-devel
        else
            print_warning "Could not detect package manager. Please install Python 3.7+ and pip manually."
        fi
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        print_status "Installing dependencies for macOS..."
        if command -v brew &> /dev/null; then
            brew install python3 openssl
        else
            print_warning "Homebrew not found. Please install Python 3.7+ manually."
        fi
    else
        print_warning "Unsupported OS. Please install Python 3.7+ manually."
    fi
}

# Create virtual environment
create_venv() {
    print_status "Creating Python virtual environment..."
    
    if [ -d "venv" ]; then
        print_warning "Virtual environment already exists. Removing..."
        rm -rf venv
    fi
    
    python3 -m venv venv
    print_success "Virtual environment created"
}

# Activate virtual environment and install Python dependencies
install_python_deps() {
    print_status "Installing Python dependencies..."
    
    # Activate virtual environment
    source venv/bin/activate
    
    # Upgrade pip
    pip install --upgrade pip
    
    # Install core dependencies
    print_status "Installing core dependencies..."
    pip install fastapi uvicorn[standard] websockets pysnmp scapy aiohttp pydantic
    
    # Install optional dependencies
    print_status "Installing optional dependencies..."
    pip install python-daemon pysnmp-mibs python-nmap dpkt pyyaml colorlog psutil jinja2
    
    # Install development dependencies (optional)
    if [ "$1" == "--dev" ]; then
        print_status "Installing development dependencies..."
        pip install pytest pytest-asyncio black flake8 mypy
    fi
    
    print_success "Python dependencies installed"
}

# Initialize database
init_database() {
    print_status "Initializing database..."
    
    # Activate virtual environment
    source venv/bin/activate
    
    # Create database schema
    if [ -f "database_schema.sql" ]; then
        python3 -c "
import sqlite3
import sys

try:
    conn = sqlite3.connect('network_devices.db')
    with open('database_schema.sql', 'r') as f:
        conn.executescript(f.read())
    conn.close()
    print('Database initialized successfully')
except Exception as e:
    print(f'Error initializing database: {e}')
    sys.exit(1)
"
        print_success "Database initialized"
    else
        print_error "database_schema.sql not found"
        exit 1
    fi
}

# Create configuration file
create_config() {
    print_status "Creating configuration file..."
    
    cat > scanner_config.json << EOF
{
    "network_ranges": [
        "192.168.1.0/24",
        "10.0.0.0/24",
        "172.16.0.0/24"
    ],
    "scan_interval": 300,
    "websocket_port": 8080,
    "http_port": 8000,
    "database_path": "network_devices.db",
    "snmp_communities": [
        "public",
        "private",
        "community"
    ],
    "enable_mndp": true,
    "enable_snmp": true,
    "enable_cdp": true,
    "enable_lldp": true,
    "log_level": "INFO",
    "max_concurrent_scans": 10,
    "snmp_timeout": 5,
    "snmp_retries": 3
}
EOF
    
    print_success "Configuration file created: scanner_config.json"
}

# Create systemd service (Linux only)
create_systemd_service() {
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        print_status "Creating systemd service..."
        
        # Get current directory
        CURRENT_DIR=$(pwd)
        
        cat > network-scanner.service << EOF
[Unit]
Description=Network Scanner Daemon
After=network.target

[Service]
Type=simple
User=$USER
WorkingDirectory=$CURRENT_DIR
Environment=PATH=$CURRENT_DIR/venv/bin
ExecStart=$CURRENT_DIR/venv/bin/python network_scanner_daemon.py --daemon
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF
        
        print_success "Systemd service file created: network-scanner.service"
        print_warning "To install the service, run: sudo cp network-scanner.service /etc/systemd/system/ && sudo systemctl enable network-scanner"
    fi
}

# Create startup scripts
create_startup_scripts() {
    print_status "Creating startup scripts..."
    
    # Start API server script
    cat > start_api.sh << 'EOF'
#!/bin/bash
# Start Network Scanner API Server

cd "$(dirname "$0")"
source venv/bin/activate

echo "Starting Network Scanner API Server..."
python network_api_server.py --host 0.0.0.0 --port 8000
EOF
    
    # Start scanner daemon script
    cat > start_scanner.sh << 'EOF'
#!/bin/bash
# Start Network Scanner Daemon

cd "$(dirname "$0")"
source venv/bin/activate

echo "Starting Network Scanner Daemon..."
python network_scanner_daemon.py
EOF
    
    # Start both services script
    cat > start_all.sh << 'EOF'
#!/bin/bash
# Start both Network Scanner services

cd "$(dirname "$0")"

echo "Starting Network Scanner services..."

# Start API server in background
./start_api.sh &
API_PID=$!

# Start scanner daemon in background
./start_scanner.sh &
SCANNER_PID=$!

echo "API Server PID: $API_PID"
echo "Scanner Daemon PID: $SCANNER_PID"

# Wait for both processes
wait $API_PID $SCANNER_PID
EOF
    
    # Make scripts executable
    chmod +x start_api.sh start_scanner.sh start_all.sh
    
    print_success "Startup scripts created"
}

# Create documentation
create_docs() {
    print_status "Creating documentation..."
    
    cat > README.md << 'EOF'
# Network Scanner with WebGL Visualization

A comprehensive network discovery and visualization system that scans networks using MNDP, SNMP, CDP, and LLDP protocols, with a WebGL-based 3D visualization interface.

## Features

- **Multi-Protocol Scanning**: MNDP (MikroTik), SNMP, CDP (Cisco), LLDP
- **Real-time Discovery**: Automatic network device discovery and monitoring
- **3D Visualization**: WebGL-based network topology visualization
- **REST API**: Comprehensive API for data access and control
- **Database Storage**: SQLite database for persistent data storage
- **Performance Monitoring**: Real-time device performance metrics

## Quick Start

1. **Setup** (first time only):
   ```bash
   ./setup.sh
   ```

2. **Start the services**:
   ```bash
   ./start_all.sh
   ```

3. **Access the WebGL interface**:
   Open `webgl_network_visualization_api.html` in your browser

4. **API Documentation**:
   Visit `http://localhost:8000/docs` for interactive API documentation

## Components

- `network_scanner_daemon.py`: Main scanning daemon
- `network_api_server.py`: REST API server
- `webgl_network_visualization_api.html`: WebGL visualization interface
- `database_schema.sql`: Database schema
- `scanner_config.json`: Configuration file

## API Endpoints

- `GET /api/devices`: List all devices
- `GET /api/topology`: Get network topology
- `GET /api/statistics`: Get network statistics
- `POST /api/scan/start`: Start network scan
- `WebSocket /ws`: Real-time updates

## Configuration

Edit `scanner_config.json` to customize:
- Network ranges to scan
- Scan intervals
- Protocol settings
- Database settings

## Troubleshooting

1. **Permission Issues**: Run with sudo if needed for network scanning
2. **Port Conflicts**: Change ports in config file
3. **Database Issues**: Delete `network_devices.db` and restart
4. **API Connection**: Check if API server is running on port 8000

## Development

To install development dependencies:
```bash
./setup.sh --dev
```

## License

This project is open source and available under the MIT License.
EOF
    
    print_success "Documentation created: README.md"
}

# Main setup function
main() {
    print_status "Starting Network Scanner setup..."
    
    check_root
    check_python
    install_system_deps
    create_venv
    install_python_deps "$1"
    init_database
    create_config
    create_systemd_service
    create_startup_scripts
    create_docs
    
    print_success "Setup completed successfully!"
    echo ""
    echo "Next steps:"
    echo "1. Edit scanner_config.json to configure your network ranges"
    echo "2. Run: ./start_all.sh"
    echo "3. Open webgl_network_visualization_api.html in your browser"
    echo "4. Visit http://localhost:8000/docs for API documentation"
    echo ""
    echo "For systemd service installation (Linux):"
    echo "sudo cp network-scanner.service /etc/systemd/system/"
    echo "sudo systemctl enable network-scanner"
    echo "sudo systemctl start network-scanner"
}

# Run main function with all arguments
main "$@" 