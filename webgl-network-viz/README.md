# WebGL Network Visualization

A modern, interactive 3D network visualization system built with WebGL and Three.js, featuring real-time device monitoring, interactive device information panels, and comprehensive network topology mapping.

## 🌟 Features

### 🎮 **3D Network Visualization**
- **Interactive 3D environment** with WebGL rendering
- **Multiple layout algorithms** (force-directed, hierarchical, circular, organic)
- **Real-time device status** with visual indicators
- **Dynamic connection lines** showing network topology
- **Camera controls** for immersive navigation

### 📊 **Device Information System**
- **Click-to-select** devices in 3D space
- **Comprehensive device panels** with detailed information
- **Real-time status monitoring** with color-coded indicators
- **Interface management** with IP, MAC, and speed details
- **Connection visualization** showing network relationships

### 🔧 **Network Management**
- **MikroTik integration** via SSH and graphing APIs
- **Multi-protocol scanning** (MNDP, SNMP, CDP, LLDP)
- **Real-time data collection** and monitoring
- **Database storage** with SQLite backend
- **RESTful API** for data access and management

### 🎨 **Modern UI/UX**
- **Responsive design** for all screen sizes
- **Smooth animations** and transitions
- **Interactive hover effects** and visual feedback
- **Professional styling** with modern aesthetics
- **Accessibility features** and keyboard navigation

## 📁 Project Structure

```
webgl-network-viz/
├── index.html                 # Main WebGL application
├── README.md                  # This documentation
├── requirements.txt           # Python dependencies
├── setup.sh                   # Automated setup script
├── assets/                    # Static assets
│   ├── css/                   # Stylesheets
│   ├── js/                    # JavaScript files
│   ├── models/                # 3D models (GLTF/GLB)
│   └── textures/              # Texture files
├── api/                       # Backend API files
│   ├── network_api_server.py  # FastAPI server
│   ├── mikrotik_ssh_integration.py
│   └── mikrotik_graphing_integration.py
├── docs/                      # Documentation
│   └── WEBGL_DEVICE_INFO_ENHANCEMENT_SUMMARY.md
└── tests/                     # Test files
    └── test_webgl_device_info.html
```

## 🚀 Quick Start

### Prerequisites
- Python 3.8+
- Apache2 with mod_proxy and mod_rewrite
- Modern web browser with WebGL support

### Installation

1. **Clone or download** the project files
2. **Run the setup script**:
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```

3. **Configure Apache**:
   ```bash
   sudo cp webgl-network-viz.conf /etc/apache2/sites-available/
   sudo a2ensite webgl-network-viz
   sudo systemctl reload apache2
   ```

4. **Start the API server**:
   ```bash
   cd api
   python network_api_server.py
   ```

5. **Access the application**:
   - Open your browser to `http://webgl-network.local`
   - Or add to `/etc/hosts`: `127.0.0.1 webgl-network.local`

## 🔧 Configuration

### Virtual Host Configuration
The application uses a dedicated Apache virtual host with:
- **Document root**: `/home/sarna/tmpwww/webgl-network-viz`
- **API proxy**: Routes `/api/` requests to Python backend
- **WebSocket proxy**: Routes `/ws/` requests for real-time updates
- **CORS support**: For cross-origin API access
- **Security headers**: XSS protection, content type options

### API Configuration
The Python backend provides:
- **RESTful API** endpoints for device management
- **WebSocket support** for real-time updates
- **Database integration** with SQLite
- **MikroTik device integration** via SSH and graphing

## 🎮 Usage

### 3D Navigation
- **Mouse drag**: Rotate camera around the scene
- **Mouse wheel**: Zoom in/out
- **Right-click drag**: Pan the camera
- **Click devices**: Open device information panel

### Device Information Panel
- **Click any device** in the 3D scene to view details
- **Hover over devices** for visual feedback
- **Use action buttons** to scan interfaces or refresh devices
- **Close panel** by clicking overlay, close button, or pressing Escape

### Layout Controls
- **Force-directed**: Organic, physics-based layout
- **Hierarchical**: Organized by device type
- **Circular**: Devices arranged in a circle
- **Organic**: Natural, flowing arrangement

## 🔌 API Endpoints

### Device Management
- `GET /api/devices` - List all devices
- `GET /api/device/{id}` - Get device details
- `POST /api/device/{id}/refresh` - Refresh device data
- `GET /api/device/{id}/interfaces` - Get device interfaces
- `POST /api/device/{id}/scan-interfaces` - Scan device interfaces

### Network Topology
- `GET /api/topology` - Get network topology
- `GET /api/connections` - List all connections
- `GET /api/interfaces` - List all interfaces

### Monitoring
- `GET /api/statistics` - Get system statistics
- `GET /api/scan/start` - Start network scan
- `GET /api/scan/status` - Get scan status

## 🎨 Customization

### Styling
- Modify CSS in `assets/css/` directory
- Update color schemes and themes
- Customize animations and transitions

### 3D Models
- Add custom models to `assets/models/`
- Support for GLTF/GLB formats
- Texture files in `assets/textures/`

### Device Types
- Extend device type definitions
- Add custom device geometries
- Implement new layout algorithms

## 🔒 Security

### Features
- **CORS configuration** for API access
- **Security headers** (XSS protection, content type options)
- **File access restrictions** for sensitive files
- **Input validation** and sanitization

### Best Practices
- Use HTTPS in production
- Implement proper authentication
- Regular security updates
- Monitor access logs

## 🐛 Troubleshooting

### Common Issues

**WebGL not supported**
- Ensure your browser supports WebGL
- Update graphics drivers
- Check browser settings

**API connection failed**
- Verify Python server is running
- Check Apache proxy configuration
- Review firewall settings

**Device information not loading**
- Check network scanner daemon
- Verify database connectivity
- Review API endpoint responses

### Debug Mode
Enable debug logging in the browser console:
```javascript
localStorage.setItem('debug', 'true');
```

## 📈 Performance

### Optimization Features
- **Level of Detail (LOD)** for large networks
- **Frustum culling** for off-screen objects
- **Geometry instancing** for similar objects
- **Texture compression** and caching
- **Object pooling** for dynamic elements

### Monitoring
- **FPS counter** in the UI
- **Memory usage** tracking
- **Draw call optimization**
- **Performance statistics** display

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

### Code Style
- Follow existing code conventions
- Add comments for complex logic
- Update documentation as needed
- Include tests for new features

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgments

- **Three.js** for 3D graphics library
- **FastAPI** for backend API framework
- **MikroTik** for device integration
- **Apache** for web server configuration

## 📞 Support

For support and questions:
- Check the documentation in `/docs/`
- Review troubleshooting section
- Open an issue on the project repository
- Contact the development team

---

**WebGL Network Visualization** - Modern 3D network monitoring and management 