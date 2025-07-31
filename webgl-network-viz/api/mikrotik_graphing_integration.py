#!/usr/bin/env python3
"""
MikroTik Graphing Integration Module
Integrates MikroTik's built-in graphing functionality with network scanner

Features:
- Low-resource data collection from MikroTik devices
- Built-in graphing API integration
- MNDP discovery integration
- SNMP fallback for external tools
- Efficient data storage and processing
"""

import asyncio
import json
import logging
import sqlite3
import time
from datetime import datetime, timedelta
from typing import Dict, List, Optional, Any
from dataclasses import dataclass, asdict
import aiohttp
import paramiko
from pysnmp.hlapi import *

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@dataclass
class MikroTikGraphData:
    """MikroTik graphing data structure"""
    device_id: str
    timestamp: datetime
    metric_type: str  # interface, system, wireless, queue
    metric_name: str
    value: float
    unit: str
    interface_name: Optional[str] = None

@dataclass
class MikroTikDevice:
    """MikroTik device information"""
    device_id: str
    ip_address: str
    hostname: str
    model: str
    firmware_version: str
    routeros_version: str
    cpu_count: int
    memory_total: int
    uptime: int
    last_seen: datetime
    graphing_enabled: bool = False
    snmp_enabled: bool = False
    mndp_enabled: bool = True

class MikroTikGraphingCollector:
    """Collects data from MikroTik built-in graphing system"""
    
    def __init__(self, db_path: str = "network_devices.db"):
        self.db_path = db_path
        self.session = None
        self.devices = {}
        self.collection_interval = 60  # seconds
        self.retention_days = 30
        
    async def init_session(self):
        """Initialize HTTP session for API calls"""
        if not self.session:
            timeout = aiohttp.ClientTimeout(total=30)
            connector = aiohttp.TCPConnector(limit=100, limit_per_host=10)
            self.session = aiohttp.ClientSession(
                timeout=timeout,
                connector=connector,
                headers={'User-Agent': 'MikroTik-Graphing-Collector/1.0'}
            )
    
    async def close_session(self):
        """Close HTTP session"""
        if self.session:
            await self.session.close()
            self.session = None
    
    async def discover_mikrotik_devices(self, network_ranges: List[str]) -> List[MikroTikDevice]:
        """Discover MikroTik devices using MNDP"""
        logger.info("Starting MikroTik device discovery")
        
        devices = []
        
        for network_range in network_ranges:
            try:
                # Use MNDP discovery (implemented in network_scanner_daemon.py)
                # This is a simplified version for demonstration
                discovered_devices = await self._mndp_scan_network(network_range)
                
                for device_info in discovered_devices:
                    if device_info.get('vendor') == 'MikroTik':
                        device = MikroTikDevice(
                            device_id=f"mikrotik_{device_info['ip_address']}",
                            ip_address=device_info['ip_address'],
                            hostname=device_info.get('hostname', f"MikroTik-{device_info['ip_address']}"),
                            model=device_info.get('model', 'Unknown'),
                            firmware_version=device_info.get('firmware_version', 'Unknown'),
                            routeros_version=device_info.get('firmware_version', 'Unknown'),
                            cpu_count=1,  # Will be updated via API
                            memory_total=0,  # Will be updated via API
                            uptime=0,  # Will be updated via API
                            last_seen=datetime.now()
                        )
                        devices.append(device)
                        
            except Exception as e:
                logger.error(f"Error discovering devices in {network_range}: {e}")
        
        logger.info(f"Discovered {len(devices)} MikroTik devices")
        return devices
    
    async def _mndp_scan_network(self, network_range: str) -> List[Dict]:
        """Simplified MNDP scan (full implementation in network_scanner_daemon.py)"""
        # This would use the MNDP scanner from the main daemon
        # For now, return empty list
        return []
    
    async def check_graphing_availability(self, device: MikroTikDevice) -> bool:
        """Check if device has graphing enabled and accessible"""
        try:
            await self.init_session()
            
            # Try to access graphing API
            url = f"http://{device.ip_address}/rest/tool/graphing"
            
            async with self.session.get(url, timeout=10) as response:
                if response.status == 200:
                    device.graphing_enabled = True
                    logger.info(f"Graphing enabled on {device.hostname}")
                    return True
                else:
                    logger.debug(f"Graphing not accessible on {device.hostname}")
                    return False
                    
        except Exception as e:
            logger.debug(f"Could not check graphing on {device.hostname}: {e}")
            return False
    
    async def collect_graphing_data(self, device: MikroTikDevice) -> List[MikroTikGraphData]:
        """Collect data from MikroTik built-in graphing"""
        if not device.graphing_enabled:
            return []
        
        try:
            await self.init_session()
            data_points = []
            
            # Collect interface data
            interface_data = await self._collect_interface_data(device)
            data_points.extend(interface_data)
            
            # Collect system data
            system_data = await self._collect_system_data(device)
            data_points.extend(system_data)
            
            # Collect wireless data (if applicable)
            wireless_data = await self._collect_wireless_data(device)
            data_points.extend(wireless_data)
            
            # Collect queue data
            queue_data = await self._collect_queue_data(device)
            data_points.extend(queue_data)
            
            logger.info(f"Collected {len(data_points)} data points from {device.hostname}")
            return data_points
            
        except Exception as e:
            logger.error(f"Error collecting graphing data from {device.hostname}: {e}")
            return []
    
    async def _collect_interface_data(self, device: MikroTikDevice) -> List[MikroTikGraphData]:
        """Collect interface traffic data"""
        try:
            url = f"http://{device.ip_address}/rest/interface"
            
            async with self.session.get(url, timeout=10) as response:
                if response.status == 200:
                    interfaces = await response.json()
                    
                    data_points = []
                    timestamp = datetime.now()
                    
                    for interface in interfaces:
                        interface_name = interface.get('name', 'unknown')
                        
                        # Traffic data
                        if 'tx-byte' in interface:
                            data_points.append(MikroTikGraphData(
                                device_id=device.device_id,
                                timestamp=timestamp,
                                metric_type='interface',
                                metric_name='tx_bytes',
                                value=float(interface['tx-byte']),
                                unit='bytes',
                                interface_name=interface_name
                            ))
                        
                        if 'rx-byte' in interface:
                            data_points.append(MikroTikGraphData(
                                device_id=device.device_id,
                                timestamp=timestamp,
                                metric_type='interface',
                                metric_name='rx_bytes',
                                value=float(interface['rx-byte']),
                                unit='bytes',
                                interface_name=interface_name
                            ))
                        
                        # Packet data
                        if 'tx-packet' in interface:
                            data_points.append(MikroTikGraphData(
                                device_id=device.device_id,
                                timestamp=timestamp,
                                metric_type='interface',
                                metric_name='tx_packets',
                                value=float(interface['tx-packet']),
                                unit='packets',
                                interface_name=interface_name
                            ))
                        
                        if 'rx-packet' in interface:
                            data_points.append(MikroTikGraphData(
                                device_id=device.device_id,
                                timestamp=timestamp,
                                metric_type='interface',
                                metric_name='rx_packets',
                                value=float(interface['rx-packet']),
                                unit='packets',
                                interface_name=interface_name
                            ))
                    
                    return data_points
                    
        except Exception as e:
            logger.error(f"Error collecting interface data from {device.hostname}: {e}")
            return []
    
    async def _collect_system_data(self, device: MikroTikDevice) -> List[MikroTikGraphData]:
        """Collect system performance data"""
        try:
            url = f"http://{device.ip_address}/rest/system/resource"
            
            async with self.session.get(url, timeout=10) as response:
                if response.status == 200:
                    resource_data = await response.json()
                    
                    data_points = []
                    timestamp = datetime.now()
                    
                    # CPU usage
                    if 'cpu-load' in resource_data:
                        data_points.append(MikroTikGraphData(
                            device_id=device.device_id,
                            timestamp=timestamp,
                            metric_type='system',
                            metric_name='cpu_load',
                            value=float(resource_data['cpu-load']),
                            unit='percent'
                        ))
                    
                    # Memory usage
                    if 'free-memory' in resource_data and 'total-memory' in resource_data:
                        free_memory = float(resource_data['free-memory'])
                        total_memory = float(resource_data['total-memory'])
                        used_memory = total_memory - free_memory
                        memory_usage = (used_memory / total_memory) * 100
                        
                        data_points.append(MikroTikGraphData(
                            device_id=device.device_id,
                            timestamp=timestamp,
                            metric_type='system',
                            metric_name='memory_usage',
                            value=memory_usage,
                            unit='percent'
                        ))
                    
                    # Uptime
                    if 'uptime' in resource_data:
                        data_points.append(MikroTikGraphData(
                            device_id=device.device_id,
                            timestamp=timestamp,
                            metric_type='system',
                            metric_name='uptime',
                            value=float(resource_data['uptime']),
                            unit='seconds'
                        ))
                    
                    return data_points
                    
        except Exception as e:
            logger.error(f"Error collecting system data from {device.hostname}: {e}")
            return []
    
    async def _collect_wireless_data(self, device: MikroTikDevice) -> List[MikroTikGraphData]:
        """Collect wireless interface data"""
        try:
            url = f"http://{device.ip_address}/rest/interface/wireless"
            
            async with self.session.get(url, timeout=10) as response:
                if response.status == 200:
                    wireless_interfaces = await response.json()
                    
                    data_points = []
                    timestamp = datetime.now()
                    
                    for interface in wireless_interfaces:
                        interface_name = interface.get('name', 'unknown')
                        
                        # Signal strength
                        if 'signal-strength' in interface:
                            data_points.append(MikroTikGraphData(
                                device_id=device.device_id,
                                timestamp=timestamp,
                                metric_type='wireless',
                                metric_name='signal_strength',
                                value=float(interface['signal-strength']),
                                unit='dBm',
                                interface_name=interface_name
                            ))
                        
                        # Noise floor
                        if 'noise-floor' in interface:
                            data_points.append(MikroTikGraphData(
                                device_id=device.device_id,
                                timestamp=timestamp,
                                metric_type='wireless',
                                metric_name='noise_floor',
                                value=float(interface['noise-floor']),
                                unit='dBm',
                                interface_name=interface_name
                            ))
                    
                    return data_points
                    
        except Exception as e:
            logger.error(f"Error collecting wireless data from {device.hostname}: {e}")
            return []
    
    async def _collect_queue_data(self, device: MikroTikDevice) -> List[MikroTikGraphData]:
        """Collect queue monitoring data"""
        try:
            url = f"http://{device.ip_address}/rest/queue/simple"
            
            async with self.session.get(url, timeout=10) as response:
                if response.status == 200:
                    queues = await response.json()
                    
                    data_points = []
                    timestamp = datetime.now()
                    
                    for queue in queues:
                        queue_name = queue.get('name', 'unknown')
                        
                        # Queue length
                        if 'queue' in queue:
                            data_points.append(MikroTikGraphData(
                                device_id=device.device_id,
                                timestamp=timestamp,
                                metric_type='queue',
                                metric_name='queue_length',
                                value=float(queue['queue']),
                                unit='packets',
                                interface_name=queue_name
                            ))
                        
                        # Queue drops
                        if 'dropped' in queue:
                            data_points.append(MikroTikGraphData(
                                device_id=device.device_id,
                                timestamp=timestamp,
                                metric_type='queue',
                                metric_name='queue_drops',
                                value=float(queue['dropped']),
                                unit='packets',
                                interface_name=queue_name
                            ))
                    
                    return data_points
                    
        except Exception as e:
            logger.error(f"Error collecting queue data from {device.hostname}: {e}")
            return []
    
    def save_graphing_data(self, data_points: List[MikroTikGraphData]):
        """Save graphing data to database"""
        try:
            conn = sqlite3.connect(self.db_path)
            cursor = conn.cursor()
            
            # Create graphing data table if not exists
            cursor.execute('''
                CREATE TABLE IF NOT EXISTS mikrotik_graphing_data (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    device_id TEXT NOT NULL,
                    timestamp TIMESTAMP NOT NULL,
                    metric_type TEXT NOT NULL,
                    metric_name TEXT NOT NULL,
                    value REAL NOT NULL,
                    unit TEXT NOT NULL,
                    interface_name TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (device_id) REFERENCES devices(device_id)
                )
            ''')
            
            # Insert data points
            for data_point in data_points:
                cursor.execute('''
                    INSERT INTO mikrotik_graphing_data 
                    (device_id, timestamp, metric_type, metric_name, value, unit, interface_name)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ''', (
                    data_point.device_id,
                    data_point.timestamp.isoformat(),
                    data_point.metric_type,
                    data_point.metric_name,
                    data_point.value,
                    data_point.unit,
                    data_point.interface_name
                ))
            
            conn.commit()
            conn.close()
            
            logger.info(f"Saved {len(data_points)} data points to database")
            
        except Exception as e:
            logger.error(f"Error saving graphing data: {e}")
    
    def cleanup_old_data(self):
        """Clean up old graphing data based on retention policy"""
        try:
            conn = sqlite3.connect(self.db_path)
            cursor = conn.cursor()
            
            cutoff_date = datetime.now() - timedelta(days=self.retention_days)
            
            cursor.execute('''
                DELETE FROM mikrotik_graphing_data 
                WHERE timestamp < ?
            ''', (cutoff_date.isoformat(),))
            
            deleted_count = cursor.rowcount
            conn.commit()
            conn.close()
            
            if deleted_count > 0:
                logger.info(f"Cleaned up {deleted_count} old data points")
                
        except Exception as e:
            logger.error(f"Error cleaning up old data: {e}")
    
    async def collect_from_all_devices(self, devices: List[MikroTikDevice]):
        """Collect data from all MikroTik devices"""
        logger.info(f"Starting data collection from {len(devices)} devices")
        
        tasks = []
        for device in devices:
            task = asyncio.create_task(self._collect_from_device(device))
            tasks.append(task)
        
        # Run collection with concurrency limit
        semaphore = asyncio.Semaphore(10)  # Max 10 concurrent connections
        
        async def limited_collection(task):
            async with semaphore:
                return await task
        
        results = await asyncio.gather(*[limited_collection(task) for task in tasks], return_exceptions=True)
        
        # Process results
        total_data_points = 0
        for result in results:
            if isinstance(result, list):
                total_data_points += len(result)
            elif isinstance(result, Exception):
                logger.error(f"Collection task failed: {result}")
        
        logger.info(f"Collection completed: {total_data_points} total data points")
    
    async def _collect_from_device(self, device: MikroTikDevice) -> List[MikroTikGraphData]:
        """Collect data from a single device"""
        try:
            # Check if graphing is available
            if not device.graphing_enabled:
                await self.check_graphing_availability(device)
            
            if device.graphing_enabled:
                data_points = await self.collect_graphing_data(device)
                self.save_graphing_data(data_points)
                return data_points
            else:
                logger.debug(f"Graphing not available on {device.hostname}")
                return []
                
        except Exception as e:
            logger.error(f"Error collecting from {device.hostname}: {e}")
            return []

class MikroTikSNMPCollector:
    """Fallback SNMP collector for MikroTik devices"""
    
    def __init__(self, communities: List[str] = ['public', 'private']):
        self.communities = communities
    
    async def collect_snmp_data(self, device: MikroTikDevice) -> List[MikroTikGraphData]:
        """Collect data via SNMP as fallback"""
        data_points = []
        timestamp = datetime.now()
        
        for community in self.communities:
            try:
                # System CPU load
                cpu_load = await self._snmp_get(device.ip_address, community, '1.3.6.1.2.1.25.3.3.1.2.1')
                if cpu_load:
                    data_points.append(MikroTikGraphData(
                        device_id=device.device_id,
                        timestamp=timestamp,
                        metric_type='system',
                        metric_name='cpu_load',
                        value=float(cpu_load),
                        unit='percent'
                    ))
                
                # System memory
                memory_used = await self._snmp_get(device.ip_address, community, '1.3.6.1.2.1.25.2.3.1.6.1')
                memory_total = await self._snmp_get(device.ip_address, community, '1.3.6.1.2.1.25.2.3.1.5.1')
                
                if memory_used and memory_total:
                    memory_usage = (float(memory_used) / float(memory_total)) * 100
                    data_points.append(MikroTikGraphData(
                        device_id=device.device_id,
                        timestamp=timestamp,
                        metric_type='system',
                        metric_name='memory_usage',
                        value=memory_usage,
                        unit='percent'
                    ))
                
                # If we got data, break (successful community)
                if data_points:
                    break
                    
            except Exception as e:
                logger.debug(f"SNMP collection failed for {device.hostname} with community '{community}': {e}")
                continue
        
        return data_points
    
    async def _snmp_get(self, ip_address: str, community: str, oid: str) -> Optional[str]:
        """Get SNMP value"""
        try:
            iterator = getCmd(SnmpEngine(),
                            CommunityData(community),
                            UdpTransportTarget((ip_address, 161)),
                            ContextData(),
                            ObjectType(ObjectIdentity(oid)))
            
            errorIndication, errorStatus, errorIndex, varBinds = next(iterator)
            
            if errorIndication or errorStatus:
                return None
            
            return str(varBinds[0][1])
            
        except Exception as e:
            logger.debug(f"SNMP get failed for {ip_address} {oid}: {e}")
            return None

class MikroTikGraphingManager:
    """Main manager for MikroTik graphing integration"""
    
    def __init__(self, db_path: str = "network_devices.db"):
        self.db_path = db_path
        self.graphing_collector = MikroTikGraphingCollector(db_path)
        self.snmp_collector = MikroTikSNMPCollector()
        self.running = False
        self.collection_interval = 60  # seconds
    
    async def start_collection(self, network_ranges: List[str]):
        """Start continuous data collection"""
        logger.info("Starting MikroTik graphing collection")
        self.running = True
        
        try:
            while self.running:
                # Discover devices
                devices = await self.graphing_collector.discover_mikrotik_devices(network_ranges)
                
                # Collect data from all devices
                await self.graphing_collector.collect_from_all_devices(devices)
                
                # Cleanup old data
                self.graphing_collector.cleanup_old_data()
                
                # Wait for next collection cycle
                await asyncio.sleep(self.collection_interval)
                
        except Exception as e:
            logger.error(f"Collection loop error: {e}")
        finally:
            await self.graphing_collector.close_session()
    
    def stop_collection(self):
        """Stop data collection"""
        logger.info("Stopping MikroTik graphing collection")
        self.running = False
    
    def get_graphing_data(self, device_id: str, metric_type: str = None, 
                         hours: int = 24) -> List[Dict]:
        """Get graphing data from database"""
        try:
            conn = sqlite3.connect(self.db_path)
            cursor = conn.cursor()
            
            query = '''
                SELECT * FROM mikrotik_graphing_data 
                WHERE device_id = ? AND timestamp >= datetime('now', '-{} hours')
            '''.format(hours)
            
            params = [device_id]
            
            if metric_type:
                query += ' AND metric_type = ?'
                params.append(metric_type)
            
            query += ' ORDER BY timestamp DESC'
            
            cursor.execute(query, params)
            rows = cursor.fetchall()
            
            data = []
            for row in rows:
                data.append({
                    'id': row[0],
                    'device_id': row[1],
                    'timestamp': row[2],
                    'metric_type': row[3],
                    'metric_name': row[4],
                    'value': row[5],
                    'unit': row[6],
                    'interface_name': row[7]
                })
            
            conn.close()
            return data
            
        except Exception as e:
            logger.error(f"Error getting graphing data: {e}")
            return []
    
    def get_device_statistics(self, device_id: str) -> Dict:
        """Get device statistics summary"""
        try:
            conn = sqlite3.connect(self.db_path)
            cursor = conn.cursor()
            
            # Get latest data for each metric type
            cursor.execute('''
                SELECT metric_type, metric_name, value, unit, timestamp
                FROM mikrotik_graphing_data 
                WHERE device_id = ? 
                AND timestamp = (
                    SELECT MAX(timestamp) 
                    FROM mikrotik_graphing_data 
                    WHERE device_id = ? AND metric_type = mikrotik_graphing_data.metric_type
                )
                ORDER BY metric_type, metric_name
            ''', (device_id, device_id))
            
            rows = cursor.fetchall()
            
            stats = {
                'device_id': device_id,
                'last_updated': datetime.now().isoformat(),
                'metrics': {}
            }
            
            for row in rows:
                metric_type = row[0]
                metric_name = row[1]
                value = row[2]
                unit = row[3]
                timestamp = row[4]
                
                if metric_type not in stats['metrics']:
                    stats['metrics'][metric_type] = {}
                
                stats['metrics'][metric_type][metric_name] = {
                    'value': value,
                    'unit': unit,
                    'timestamp': timestamp
                }
            
            conn.close()
            return stats
            
        except Exception as e:
            logger.error(f"Error getting device statistics: {e}")
            return {}

# Example usage
async def main():
    """Example usage of MikroTik graphing integration"""
    
    # Initialize manager
    manager = MikroTikGraphingManager()
    
    # Network ranges to scan
    network_ranges = ['192.168.1.0/24', '10.0.0.0/24']
    
    try:
        # Start collection
        await manager.start_collection(network_ranges)
        
    except KeyboardInterrupt:
        logger.info("Stopping collection...")
        manager.stop_collection()

if __name__ == "__main__":
    asyncio.run(main()) 