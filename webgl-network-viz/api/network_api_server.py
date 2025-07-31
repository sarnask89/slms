#!/usr/bin/env python3
"""
Network Scanner API Server
REST API for WebGL Network Visualization Framework

Features:
- Device discovery and management
- Interface and connection data
- Network topology visualization
- Real-time monitoring
- Scan session management
- Performance metrics
"""

import asyncio
import json
import logging
import sqlite3
from datetime import datetime, timedelta
from typing import Dict, List, Optional, Any
from dataclasses import dataclass, asdict
from contextlib import asynccontextmanager

import uvicorn
from fastapi import FastAPI, HTTPException, Query, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from fastapi.staticfiles import StaticFiles
from pydantic import BaseModel, Field
import websockets
from websockets.server import serve

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# ============================================================================
# DATA MODELS
# ============================================================================

class DeviceBase(BaseModel):
    device_id: str
    ip_address: str
    mac_address: Optional[str] = None
    hostname: Optional[str] = None
    device_type: str
    vendor: Optional[str] = None
    model: Optional[str] = None
    firmware_version: Optional[str] = None
    status: str = "unknown"
    location: Optional[str] = None
    description: Optional[str] = None

class Device(DeviceBase):
    id: int
    snmp_community: Optional[str] = None
    snmp_version: int = 2
    discovery_method: Optional[str] = None
    first_seen: datetime
    last_seen: datetime
    created_at: datetime
    updated_at: datetime

class Interface(BaseModel):
    id: int
    device_id: int
    interface_name: str
    interface_index: Optional[int] = None
    description: Optional[str] = None
    mac_address: Optional[str] = None
    ip_address: Optional[str] = None
    subnet_mask: Optional[str] = None
    speed: Optional[int] = None
    status: str = "unknown"
    admin_status: str = "unknown"
    interface_type: str = "ethernet"
    mtu: Optional[int] = None
    bandwidth: Optional[int] = None
    duplex: Optional[str] = None
    created_at: datetime
    updated_at: datetime

class Connection(BaseModel):
    id: int
    source_device_id: int
    source_interface_id: Optional[int] = None
    target_device_id: int
    target_interface_id: Optional[int] = None
    connection_type: str = "ethernet"
    bandwidth: Optional[int] = None
    status: str = "active"
    discovery_method: Optional[str] = None
    created_at: datetime
    updated_at: datetime

class NetworkTopology(BaseModel):
    devices: List[Device]
    connections: List[Connection]
    interfaces: List[Interface]

class ScanSession(BaseModel):
    id: int
    session_name: Optional[str] = None
    scan_type: str
    network_range: str
    status: str
    devices_discovered: int = 0
    connections_discovered: int = 0
    started_at: datetime
    completed_at: Optional[datetime] = None
    created_at: datetime

class DeviceMonitoring(BaseModel):
    id: int
    device_id: int
    cpu_usage: Optional[float] = None
    memory_usage: Optional[float] = None
    temperature: Optional[float] = None
    uptime: Optional[int] = None
    interface_count: Optional[int] = None
    active_connections: Optional[int] = None
    packet_loss: Optional[float] = None
    latency: Optional[int] = None
    bandwidth_in: Optional[int] = None
    bandwidth_out: Optional[int] = None
    monitored_at: datetime

class ScanRequest(BaseModel):
    scan_type: str = "full"  # mndp, snmp, cdp, lldp, full
    network_ranges: List[str] = Field(default_factory=list)
    timeout: int = 300  # seconds

class APIResponse(BaseModel):
    success: bool
    data: Optional[Any] = None
    message: Optional[str] = None
    timestamp: datetime = Field(default_factory=datetime.now)

# ============================================================================
# DATABASE MANAGER
# ============================================================================

class DatabaseManager:
    """Database manager for network scanner data"""
    
    def __init__(self, db_path: str = "network_devices.db"):
        self.db_path = db_path
        self.init_database()
    
    def init_database(self):
        """Initialize database connection and verify schema"""
        try:
            with sqlite3.connect(self.db_path) as conn:
                conn.execute("PRAGMA foreign_keys = ON")
                # Verify database exists by checking for devices table
                cursor = conn.execute("SELECT name FROM sqlite_master WHERE type='table' AND name='devices'")
                if not cursor.fetchone():
                    logger.error("Database schema not found. Please run database_schema.sql first.")
                    raise Exception("Database schema not found")
                logger.info("Database initialized successfully")
        except Exception as e:
            logger.error(f"Database initialization error: {e}")
            raise
    
    def get_connection(self):
        """Get database connection"""
        conn = sqlite3.connect(self.db_path)
        conn.row_factory = sqlite3.Row  # Enable dict-like access
        return conn
    
    # Device operations
    def get_all_devices(self, limit: int = 100, offset: int = 0) -> List[Dict]:
        """Get all devices with pagination"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT * FROM devices 
                ORDER BY last_seen DESC 
                LIMIT ? OFFSET ?
            """, (limit, offset))
            return [dict(row) for row in cursor.fetchall()]
    
    def get_device_by_id(self, device_id: int) -> Optional[Dict]:
        """Get device by ID"""
        with self.get_connection() as conn:
            cursor = conn.execute("SELECT * FROM devices WHERE id = ?", (device_id,))
            row = cursor.fetchone()
            return dict(row) if row else None
    
    def get_device_by_device_id(self, device_id: str) -> Optional[Dict]:
        """Get device by device_id string"""
        with self.get_connection() as conn:
            cursor = conn.execute("SELECT * FROM devices WHERE device_id = ?", (device_id,))
            row = cursor.fetchone()
            return dict(row) if row else None
    
    def get_devices_by_type(self, device_type: str, limit: int = 100) -> List[Dict]:
        """Get devices by type"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT * FROM devices 
                WHERE device_type = ? 
                ORDER BY last_seen DESC 
                LIMIT ?
            """, (device_type, limit))
            return [dict(row) for row in cursor.fetchall()]
    
    def get_devices_by_vendor(self, vendor: str, limit: int = 100) -> List[Dict]:
        """Get devices by vendor"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT * FROM devices 
                WHERE vendor = ? 
                ORDER BY last_seen DESC 
                LIMIT ?
            """, (vendor, limit))
            return [dict(row) for row in cursor.fetchall()]
    
    def get_online_devices(self, limit: int = 100) -> List[Dict]:
        """Get online devices"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT * FROM devices 
                WHERE status = 'online' 
                ORDER BY last_seen DESC 
                LIMIT ?
            """, (limit,))
            return [dict(row) for row in cursor.fetchall()]
    
    def get_recent_devices(self, hours: int = 24, limit: int = 100) -> List[Dict]:
        """Get devices discovered in the last N hours"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT * FROM devices 
                WHERE last_seen >= datetime('now', '-{} hours')
                ORDER BY last_seen DESC 
                LIMIT ?
            """.format(hours), (limit,))
            return [dict(row) for row in cursor.fetchall()]
    
    # Interface operations
    def get_device_interfaces(self, device_id: int) -> List[Dict]:
        """Get interfaces for a device"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT * FROM interfaces 
                WHERE device_id = ? 
                ORDER BY interface_name
            """, (device_id,))
            return [dict(row) for row in cursor.fetchall()]
    
    def get_all_interfaces(self, limit: int = 1000) -> List[Dict]:
        """Get all interfaces"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT i.*, d.hostname as device_hostname, d.ip_address as device_ip
                FROM interfaces i
                JOIN devices d ON i.device_id = d.id
                ORDER BY d.hostname, i.interface_name
                LIMIT ?
            """, (limit,))
            return [dict(row) for row in cursor.fetchall()]
    
    # Connection operations
    def get_device_connections(self, device_id: int) -> List[Dict]:
        """Get connections for a device"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT c.*, 
                       sd.hostname as source_hostname, sd.ip_address as source_ip,
                       td.hostname as target_hostname, td.ip_address as target_ip,
                       si.interface_name as source_interface_name,
                       ti.interface_name as target_interface_name
                FROM connections c
                JOIN devices sd ON c.source_device_id = sd.id
                JOIN devices td ON c.target_device_id = td.id
                LEFT JOIN interfaces si ON c.source_interface_id = si.id
                LEFT JOIN interfaces ti ON c.target_interface_id = ti.id
                WHERE c.source_device_id = ? OR c.target_device_id = ?
                ORDER BY c.created_at DESC
            """, (device_id, device_id))
            return [dict(row) for row in cursor.fetchall()]
    
    def get_all_connections(self, limit: int = 1000) -> List[Dict]:
        """Get all connections"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT c.*, 
                       sd.hostname as source_hostname, sd.ip_address as source_ip,
                       td.hostname as target_hostname, td.ip_address as target_ip,
                       si.interface_name as source_interface_name,
                       ti.interface_name as target_interface_name
                FROM connections c
                JOIN devices sd ON c.source_device_id = sd.id
                JOIN devices td ON c.target_device_id = td.id
                LEFT JOIN interfaces si ON c.source_interface_id = si.id
                LEFT JOIN interfaces ti ON c.target_interface_id = ti.id
                ORDER BY c.created_at DESC
                LIMIT ?
            """, (limit,))
            return [dict(row) for row in cursor.fetchall()]
    
    # Network topology
    def get_network_topology(self) -> Dict:
        """Get complete network topology"""
        with self.get_connection() as conn:
            # Get devices
            devices_cursor = conn.execute("SELECT * FROM devices ORDER BY hostname")
            devices = [dict(row) for row in devices_cursor.fetchall()]
            
            # Get interfaces
            interfaces_cursor = conn.execute("SELECT * FROM interfaces ORDER BY device_id, interface_name")
            interfaces = [dict(row) for row in interfaces_cursor.fetchall()]
            
            # Get connections
            connections_cursor = conn.execute("""
                SELECT c.*, 
                       sd.hostname as source_hostname, sd.ip_address as source_ip,
                       td.hostname as target_hostname, td.ip_address as target_ip,
                       si.interface_name as source_interface_name,
                       ti.interface_name as target_interface_name
                FROM connections c
                JOIN devices sd ON c.source_device_id = sd.id
                JOIN devices td ON c.target_device_id = td.id
                LEFT JOIN interfaces si ON c.source_interface_id = si.id
                LEFT JOIN interfaces ti ON c.target_interface_id = ti.id
                ORDER BY c.created_at DESC
            """)
            connections = [dict(row) for row in connections_cursor.fetchall()]
            
            return {
                "devices": devices,
                "interfaces": interfaces,
                "connections": connections
            }
    
    # Scan sessions
    def get_scan_sessions(self, limit: int = 50) -> List[Dict]:
        """Get recent scan sessions"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT * FROM scan_sessions 
                ORDER BY started_at DESC 
                LIMIT ?
            """, (limit,))
            return [dict(row) for row in cursor.fetchall()]
    
    def create_scan_session(self, scan_type: str, network_range: str, session_name: str = None) -> int:
        """Create a new scan session"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                INSERT INTO scan_sessions (session_name, scan_type, network_range, status)
                VALUES (?, ?, ?, 'running')
            """, (session_name, scan_type, network_range))
            conn.commit()
            return cursor.lastrowid
    
    def update_scan_session(self, session_id: int, status: str, devices_discovered: int = 0, connections_discovered: int = 0):
        """Update scan session status"""
        with self.get_connection() as conn:
            if status == 'completed':
                conn.execute("""
                    UPDATE scan_sessions 
                    SET status = ?, devices_discovered = ?, connections_discovered = ?, completed_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                """, (status, devices_discovered, connections_discovered, session_id))
            else:
                conn.execute("""
                    UPDATE scan_sessions 
                    SET status = ?, devices_discovered = ?, connections_discovered = ?
                    WHERE id = ?
                """, (status, devices_discovered, connections_discovered, session_id))
            conn.commit()
    
    # Device monitoring
    def get_device_monitoring(self, device_id: int, hours: int = 24) -> List[Dict]:
        """Get monitoring data for a device"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT * FROM device_monitoring 
                WHERE device_id = ? AND monitored_at >= datetime('now', '-{} hours')
                ORDER BY monitored_at DESC
            """.format(hours), (device_id,))
            return [dict(row) for row in cursor.fetchall()]
    
    def get_latest_monitoring(self, limit: int = 100) -> List[Dict]:
        """Get latest monitoring data for all devices"""
        with self.get_connection() as conn:
            cursor = conn.execute("""
                SELECT dm.*, d.hostname, d.ip_address, d.device_type
                FROM device_monitoring dm
                JOIN devices d ON dm.device_id = d.id
                WHERE dm.monitored_at = (
                    SELECT MAX(monitored_at) 
                    FROM device_monitoring dm2 
                    WHERE dm2.device_id = dm.device_id
                )
                ORDER BY dm.monitored_at DESC
                LIMIT ?
            """, (limit,))
            return [dict(row) for row in cursor.fetchall()]
    
    # Statistics
    def get_statistics(self) -> Dict:
        """Get network statistics"""
        with self.get_connection() as conn:
            # Device statistics
            device_stats = conn.execute("""
                SELECT 
                    COUNT(*) as total_devices,
                    COUNT(CASE WHEN status = 'online' THEN 1 END) as online_devices,
                    COUNT(CASE WHEN status = 'offline' THEN 1 END) as offline_devices,
                    COUNT(CASE WHEN device_type = 'router' THEN 1 END) as routers,
                    COUNT(CASE WHEN device_type = 'switch' THEN 1 END) as switches,
                    COUNT(CASE WHEN device_type = 'server' THEN 1 END) as servers,
                    COUNT(CASE WHEN vendor = 'MikroTik' THEN 1 END) as mikrotik_devices,
                    COUNT(CASE WHEN vendor = 'Cisco' THEN 1 END) as cisco_devices
                FROM devices
            """).fetchone()
            
            # Interface statistics
            interface_stats = conn.execute("""
                SELECT 
                    COUNT(*) as total_interfaces,
                    COUNT(CASE WHEN status = 'up' THEN 1 END) as active_interfaces,
                    COUNT(CASE WHEN status = 'down' THEN 1 END) as inactive_interfaces
                FROM interfaces
            """).fetchone()
            
            # Connection statistics
            connection_stats = conn.execute("""
                SELECT 
                    COUNT(*) as total_connections,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_connections
                FROM connections
            """).fetchone()
            
            # Recent activity
            recent_activity = conn.execute("""
                SELECT 
                    COUNT(*) as devices_last_24h
                FROM devices 
                WHERE last_seen >= datetime('now', '-24 hours')
            """).fetchone()
            
            return {
                "devices": dict(device_stats),
                "interfaces": dict(interface_stats),
                "connections": dict(connection_stats),
                "recent_activity": dict(recent_activity),
                "last_updated": datetime.now().isoformat()
            }

# ============================================================================
# WEBSOCKET MANAGER
# ============================================================================

class WebSocketManager:
    """WebSocket manager for real-time updates"""
    
    def __init__(self):
        self.clients = set()
        self.db_manager = DatabaseManager()
    
    async def register(self, websocket):
        """Register a new WebSocket client"""
        self.clients.add(websocket)
        logger.info(f"WebSocket client connected. Total clients: {len(self.clients)}")
    
    async def unregister(self, websocket):
        """Unregister a WebSocket client"""
        self.clients.discard(websocket)
        logger.info(f"WebSocket client disconnected. Total clients: {len(self.clients)}")
    
    async def broadcast(self, message: dict):
        """Broadcast message to all connected clients"""
        if not self.clients:
            return
        
        message_str = json.dumps(message)
        disconnected_clients = set()
        
        for client in self.clients:
            try:
                await client.send(message_str)
            except websockets.exceptions.ConnectionClosed:
                disconnected_clients.add(client)
        
        # Remove disconnected clients
        self.clients -= disconnected_clients
    
    async def handle_websocket(self, websocket, path):
        """Handle WebSocket connections"""
        await self.register(websocket)
        try:
            async for message in websocket:
                try:
                    data = json.loads(message)
                    response = await self.handle_message(data)
                    await websocket.send(json.dumps(response))
                except json.JSONDecodeError:
                    await websocket.send(json.dumps({
                        "error": "Invalid JSON format"
                    }))
        except websockets.exceptions.ConnectionClosed:
            pass
        finally:
            await self.unregister(websocket)
    
    async def handle_message(self, data: dict) -> dict:
        """Handle incoming WebSocket messages"""
        command = data.get('command')
        
        if command == 'get_devices':
            devices = self.db_manager.get_all_devices()
            return {
                "type": "devices",
                "data": devices
            }
        
        elif command == 'get_topology':
            topology = self.db_manager.get_network_topology()
            return {
                "type": "topology",
                "data": topology
            }
        
        elif command == 'get_statistics':
            stats = self.db_manager.get_statistics()
            return {
                "type": "statistics",
                "data": stats
            }
        
        elif command == 'get_device_info':
            device_id = data.get('device_id')
            if device_id:
                device = self.db_manager.get_device_by_id(device_id)
                if device:
                    interfaces = self.db_manager.get_device_interfaces(device_id)
                    connections = self.db_manager.get_device_connections(device_id)
                    return {
                        "type": "device_info",
                        "data": {
                            "device": device,
                            "interfaces": interfaces,
                            "connections": connections
                        }
                    }
        
        elif command == 'start_scan':
            # This would trigger a scan in the background
            return {
                "type": "scan_status",
                "data": {"status": "scanning", "message": "Scan started"}
            }
        
        return {
            "type": "error",
            "data": {"message": f"Unknown command: {command}"}
        }

# ============================================================================
# FASTAPI APPLICATION
# ============================================================================

# Initialize database manager
db_manager = DatabaseManager()

# Initialize WebSocket manager
ws_manager = WebSocketManager()

# Create FastAPI application
app = FastAPI(
    title="Network Scanner API",
    description="REST API for Network Scanner with WebGL Visualization",
    version="1.0.0"
)

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Configure appropriately for production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ============================================================================
# API ENDPOINTS
# ============================================================================

@app.get("/", response_model=APIResponse)
async def root():
    """API root endpoint"""
    return APIResponse(
        success=True,
        data={
            "name": "Network Scanner API",
            "version": "1.0.0",
            "endpoints": {
                "devices": "/api/devices",
                "interfaces": "/api/interfaces",
                "connections": "/api/connections",
                "topology": "/api/topology",
                "statistics": "/api/statistics",
                "scan": "/api/scan",
                "websocket": "/ws"
            }
        },
        message="Network Scanner API is running"
    )

# Device endpoints
@app.get("/api/devices", response_model=APIResponse)
async def get_devices(
    limit: int = Query(100, ge=1, le=1000),
    offset: int = Query(0, ge=0),
    device_type: Optional[str] = None,
    vendor: Optional[str] = None,
    status: Optional[str] = None
):
    """Get all devices with optional filtering"""
    try:
        if device_type:
            devices = db_manager.get_devices_by_type(device_type, limit)
        elif vendor:
            devices = db_manager.get_devices_by_vendor(vendor, limit)
        elif status == "online":
            devices = db_manager.get_online_devices(limit)
        else:
            devices = db_manager.get_all_devices(limit, offset)
        
        return APIResponse(
            success=True,
            data=devices,
            message=f"Retrieved {len(devices)} devices"
        )
    except Exception as e:
        logger.error(f"Error getting devices: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/api/devices/{device_id}", response_model=APIResponse)
async def get_device(device_id: int):
    """Get specific device by ID"""
    try:
        device = db_manager.get_device_by_id(device_id)
        if not device:
            raise HTTPException(status_code=404, detail="Device not found")
        
        # Get device interfaces and connections
        interfaces = db_manager.get_device_interfaces(device_id)
        connections = db_manager.get_device_connections(device_id)
        
        return APIResponse(
            success=True,
            data={
                "device": device,
                "interfaces": interfaces,
                "connections": connections
            },
            message="Device retrieved successfully"
        )
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error getting device {device_id}: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/api/devices/recent", response_model=APIResponse)
async def get_recent_devices(hours: int = Query(24, ge=1, le=168)):
    """Get devices discovered in the last N hours"""
    try:
        devices = db_manager.get_recent_devices(hours)
        return APIResponse(
            success=True,
            data=devices,
            message=f"Retrieved {len(devices)} recently discovered devices"
        )
    except Exception as e:
        logger.error(f"Error getting recent devices: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Interface endpoints
@app.get("/api/interfaces", response_model=APIResponse)
async def get_interfaces(limit: int = Query(1000, ge=1, le=10000)):
    """Get all interfaces"""
    try:
        interfaces = db_manager.get_all_interfaces(limit)
        return APIResponse(
            success=True,
            data=interfaces,
            message=f"Retrieved {len(interfaces)} interfaces"
        )
    except Exception as e:
        logger.error(f"Error getting interfaces: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/api/devices/{device_id}/interfaces", response_model=APIResponse)
async def get_device_interfaces(device_id: int):
    """Get interfaces for a specific device"""
    try:
        device = db_manager.get_device_by_id(device_id)
        if not device:
            raise HTTPException(status_code=404, detail="Device not found")
        
        interfaces = db_manager.get_device_interfaces(device_id)
        return APIResponse(
            success=True,
            data=interfaces,
            message=f"Retrieved {len(interfaces)} interfaces for device {device_id}"
        )
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error getting device interfaces: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Connection endpoints
@app.get("/api/connections", response_model=APIResponse)
async def get_connections(limit: int = Query(1000, ge=1, le=10000)):
    """Get all connections"""
    try:
        connections = db_manager.get_all_connections(limit)
        return APIResponse(
            success=True,
            data=connections,
            message=f"Retrieved {len(connections)} connections"
        )
    except Exception as e:
        logger.error(f"Error getting connections: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/api/devices/{device_id}/connections", response_model=APIResponse)
async def get_device_connections(device_id: int):
    """Get connections for a specific device"""
    try:
        device = db_manager.get_device_by_id(device_id)
        if not device:
            raise HTTPException(status_code=404, detail="Device not found")
        
        connections = db_manager.get_device_connections(device_id)
        return APIResponse(
            success=True,
            data=connections,
            message=f"Retrieved {len(connections)} connections for device {device_id}"
        )
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error getting device connections: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Topology endpoint
@app.get("/api/topology", response_model=APIResponse)
async def get_topology():
    """Get complete network topology"""
    try:
        topology = db_manager.get_network_topology()
        return APIResponse(
            success=True,
            data=topology,
            message="Network topology retrieved successfully"
        )
    except Exception as e:
        logger.error(f"Error getting topology: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Statistics endpoint
@app.get("/api/statistics", response_model=APIResponse)
async def get_statistics():
    """Get network statistics"""
    try:
        stats = db_manager.get_statistics()
        return APIResponse(
            success=True,
            data=stats,
            message="Statistics retrieved successfully"
        )
    except Exception as e:
        logger.error(f"Error getting statistics: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Scan endpoints
@app.get("/api/scan/sessions", response_model=APIResponse)
async def get_scan_sessions(limit: int = Query(50, ge=1, le=1000)):
    """Get recent scan sessions"""
    try:
        sessions = db_manager.get_scan_sessions(limit)
        return APIResponse(
            success=True,
            data=sessions,
            message=f"Retrieved {len(sessions)} scan sessions"
        )
    except Exception as e:
        logger.error(f"Error getting scan sessions: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/scan/start", response_model=APIResponse)
async def start_scan(scan_request: ScanRequest, background_tasks: BackgroundTasks):
    """Start a new network scan"""
    try:
        # Create scan session
        session_id = db_manager.create_scan_session(
            scan_request.scan_type,
            ",".join(scan_request.network_ranges) if scan_request.network_ranges else "default",
            f"API Scan - {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}"
        )
        
        # In a real implementation, this would trigger the actual scanner
        # For now, we'll just update the session status
        background_tasks.add_task(simulate_scan_completion, session_id)
        
        return APIResponse(
            success=True,
            data={"session_id": session_id, "status": "started"},
            message="Scan started successfully"
        )
    except Exception as e:
        logger.error(f"Error starting scan: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Monitoring endpoints
@app.get("/api/monitoring/latest", response_model=APIResponse)
async def get_latest_monitoring(limit: int = Query(100, ge=1, le=1000)):
    """Get latest monitoring data for all devices"""
    try:
        monitoring = db_manager.get_latest_monitoring(limit)
        return APIResponse(
            success=True,
            data=monitoring,
            message=f"Retrieved monitoring data for {len(monitoring)} devices"
        )
    except Exception as e:
        logger.error(f"Error getting monitoring data: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/api/devices/{device_id}/monitoring", response_model=APIResponse)
async def get_device_monitoring(device_id: int, hours: int = Query(24, ge=1, le=168)):
    """Get monitoring data for a specific device"""
    try:
        device = db_manager.get_device_by_id(device_id)
        if not device:
            raise HTTPException(status_code=404, detail="Device not found")
        
        monitoring = db_manager.get_device_monitoring(device_id, hours)
        return APIResponse(
            success=True,
            data=monitoring,
            message=f"Retrieved monitoring data for device {device_id}"
        )
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error getting device monitoring: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# WebSocket endpoint
@app.websocket("/ws")
async def websocket_endpoint(websocket):
    """WebSocket endpoint for real-time updates"""
    await ws_manager.handle_websocket(websocket, "")

# ============================================================================
# HELPER FUNCTIONS
# ============================================================================

async def simulate_scan_completion(session_id: int):
    """Simulate scan completion (for demo purposes)"""
    await asyncio.sleep(5)  # Simulate scan time
    db_manager.update_scan_session(session_id, "completed", 10, 15)

# ============================================================================
# MAIN APPLICATION
# ============================================================================

if __name__ == "__main__":
    import argparse
    
    parser = argparse.ArgumentParser(description='Network Scanner API Server')
    parser.add_argument('--host', default='0.0.0.0', help='Host to bind to')
    parser.add_argument('--port', type=int, default=8000, help='Port to bind to')
    parser.add_argument('--reload', action='store_true', help='Enable auto-reload')
    
    args = parser.parse_args()
    
    logger.info(f"Starting Network Scanner API Server on {args.host}:{args.port}")
    
    uvicorn.run(
        "network_api_server:app",
        host=args.host,
        port=args.port,
        reload=args.reload,
        log_level="info"
    ) 