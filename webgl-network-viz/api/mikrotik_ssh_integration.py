#!/usr/bin/env python3
"""
MikroTik SSH Integration Module
Connects to RouterOS devices via SSH for configuration management

Features:
- SSH connection to MikroTik devices
- Configuration retrieval and modification
- DHCP lease management
- Queue management
- Interface configuration
- Firewall rules management
- System monitoring
"""

import asyncio
import json
import logging
import sqlite3
import time
from datetime import datetime, timedelta
from typing import Dict, List, Optional, Any, Tuple
from dataclasses import dataclass, asdict
import paramiko
import re
from concurrent.futures import ThreadPoolExecutor

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@dataclass
class MikroTikSSHConfig:
    """SSH connection configuration for MikroTik devices"""
    hostname: str
    port: int = 22
    username: str = "admin"
    password: str = ""
    private_key_path: Optional[str] = None
    timeout: int = 30
    banner_timeout: int = 60
    auth_timeout: int = 60

@dataclass
class DHCPLease:
    """DHCP lease information"""
    address: str
    mac_address: str
    client_id: str
    address_lists: str
    server: str
    rate_limit: str
    last_seen: str
    active_address: str
    active_mac_address: str
    host_name: str
    radius: str
    dynamic: bool
    blocked: bool

@dataclass
class QueueItem:
    """Queue configuration item"""
    name: str
    target: str
    parent: str
    packet_mark: str
    priority: int
    max_limit: str
    limit_at: str
    burst_limit: str
    burst_threshold: str
    burst_time: str
    comment: str
    disabled: bool

@dataclass
class InterfaceConfig:
    """Interface configuration"""
    name: str
    type: str
    mtu: int
    mac_address: str
    speed: str
    disabled: bool
    running: bool
    comment: str

@dataclass
class FirewallRule:
    """Firewall rule configuration"""
    chain: str
    action: str
    protocol: str
    src_address: str
    dst_address: str
    src_port: str
    dst_port: str
    comment: str
    disabled: bool

class MikroTikSSHClient:
    """SSH client for MikroTik RouterOS devices"""
    
    def __init__(self, config: MikroTikSSHConfig):
        self.config = config
        self.client = None
        self.shell = None
        self.connected = False
        self.executor = ThreadPoolExecutor(max_workers=5)
    
    async def connect(self) -> bool:
        """Connect to MikroTik device via SSH"""
        try:
            logger.info(f"Connecting to {self.config.hostname}:{self.config.port}")
            
            self.client = paramiko.SSHClient()
            self.client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
            
            # Connection parameters
            connect_kwargs = {
                'hostname': self.config.hostname,
                'port': self.config.port,
                'username': self.config.username,
                'timeout': self.config.timeout,
                'banner_timeout': self.config.banner_timeout,
                'auth_timeout': self.config.auth_timeout
            }
            
            # Use password or private key
            if self.config.private_key_path:
                connect_kwargs['key_filename'] = self.config.private_key_path
            else:
                connect_kwargs['password'] = self.config.password
            
            # Connect in thread to avoid blocking
            loop = asyncio.get_event_loop()
            await loop.run_in_executor(self.executor, self._connect_sync, connect_kwargs)
            
            # Get shell
            self.shell = self.client.invoke_shell()
            self.shell.settimeout(self.config.timeout)
            
            # Wait for prompt
            await self._wait_for_prompt()
            
            self.connected = True
            logger.info(f"Successfully connected to {self.config.hostname}")
            return True
            
        except Exception as e:
            logger.error(f"SSH connection failed to {self.config.hostname}: {e}")
            self.connected = False
            return False
    
    def _connect_sync(self, connect_kwargs):
        """Synchronous connection method for executor"""
        return self.client.connect(**connect_kwargs)
    
    async def disconnect(self):
        """Disconnect from MikroTik device"""
        try:
            if self.shell:
                self.shell.close()
            if self.client:
                self.client.close()
            self.connected = False
            logger.info(f"Disconnected from {self.config.hostname}")
        except Exception as e:
            logger.error(f"Error disconnecting from {self.config.hostname}: {e}")
    
    async def _wait_for_prompt(self, timeout: int = 10):
        """Wait for RouterOS prompt"""
        start_time = time.time()
        buffer = ""
        
        while time.time() - start_time < timeout:
            if self.shell.recv_ready():
                chunk = self.shell.recv(1024).decode('utf-8', errors='ignore')
                buffer += chunk
                
                if '[admin@' in buffer and '] > ' in buffer:
                    return True
            
            await asyncio.sleep(0.1)
        
        raise TimeoutError("Timeout waiting for RouterOS prompt")
    
    async def execute_command(self, command: str) -> Tuple[str, str]:
        """Execute RouterOS command and return output"""
        if not self.connected:
            raise ConnectionError("Not connected to device")
        
        try:
            # Send command
            self.shell.send(command + '\n')
            
            # Wait for response
            await asyncio.sleep(1)
            
            # Read output
            output = ""
            error_output = ""
            
            while self.shell.recv_ready():
                chunk = self.shell.recv(4096).decode('utf-8', errors='ignore')
                output += chunk
            
            # Wait for prompt
            await self._wait_for_prompt()
            
            # Parse output
            lines = output.split('\n')
            command_output = []
            error_lines = []
            
            for line in lines:
                line = line.strip()
                if line and not line.startswith('[admin@') and not line.endswith('] >'):
                    if 'error' in line.lower() or 'failed' in line.lower():
                        error_lines.append(line)
                    else:
                        command_output.append(line)
            
            return '\n'.join(command_output), '\n'.join(error_lines)
            
        except Exception as e:
            logger.error(f"Command execution failed: {e}")
            return "", str(e)
    
    async def get_system_info(self) -> Dict[str, Any]:
        """Get system information"""
        try:
            output, error = await self.execute_command('/system resource print')
            if error:
                logger.error(f"Error getting system info: {error}")
                return {}
            
            info = {}
            for line in output.split('\n'):
                if ':' in line:
                    key, value = line.split(':', 1)
                    info[key.strip()] = value.strip()
            
            return info
            
        except Exception as e:
            logger.error(f"Failed to get system info: {e}")
            return {}
    
    async def get_dhcp_leases(self) -> List[DHCPLease]:
        """Get DHCP server leases"""
        try:
            output, error = await self.execute_command('/ip dhcp-server lease print detail')
            if error:
                logger.error(f"Error getting DHCP leases: {error}")
                return []
            
            leases = []
            current_lease = {}
            
            for line in output.split('\n'):
                line = line.strip()
                if not line:
                    continue
                
                if line.startswith('Flags:'):
                    if current_lease:
                        leases.append(DHCPLease(**current_lease))
                        current_lease = {}
                elif ':' in line:
                    key, value = line.split(':', 1)
                    key = key.strip().lower().replace(' ', '_')
                    value = value.strip()
                    
                    # Convert boolean values
                    if value == 'true':
                        value = True
                    elif value == 'false':
                        value = False
                    
                    current_lease[key] = value
            
            # Add last lease
            if current_lease:
                leases.append(DHCPLease(**current_lease))
            
            logger.info(f"Retrieved {len(leases)} DHCP leases")
            return leases
            
        except Exception as e:
            logger.error(f"Failed to get DHCP leases: {e}")
            return []
    
    async def add_dhcp_lease(self, address: str, mac_address: str, comment: str = "") -> bool:
        """Add DHCP lease"""
        try:
            command = f'/ip dhcp-server lease add address={address} mac-address={mac_address}'
            if comment:
                command += f' comment="{comment}"'
            
            output, error = await self.execute_command(command)
            
            if error:
                logger.error(f"Error adding DHCP lease: {error}")
                return False
            
            logger.info(f"Added DHCP lease: {address} -> {mac_address}")
            return True
            
        except Exception as e:
            logger.error(f"Failed to add DHCP lease: {e}")
            return False
    
    async def remove_dhcp_lease(self, address: str) -> bool:
        """Remove DHCP lease"""
        try:
            command = f'/ip dhcp-server lease remove [find address={address}]'
            output, error = await self.execute_command(command)
            
            if error:
                logger.error(f"Error removing DHCP lease: {error}")
                return False
            
            logger.info(f"Removed DHCP lease: {address}")
            return True
            
        except Exception as e:
            logger.error(f"Failed to remove DHCP lease: {e}")
            return False
    
    async def get_queues(self) -> List[QueueItem]:
        """Get queue configurations"""
        try:
            output, error = await self.execute_command('/queue simple print detail')
            if error:
                logger.error(f"Error getting queues: {error}")
                return []
            
            queues = []
            current_queue = {}
            
            for line in output.split('\n'):
                line = line.strip()
                if not line:
                    continue
                
                if line.startswith('Flags:'):
                    if current_queue:
                        queues.append(QueueItem(**current_queue))
                        current_queue = {}
                elif ':' in line:
                    key, value = line.split(':', 1)
                    key = key.strip().lower().replace(' ', '_')
                    value = value.strip()
                    
                    # Convert numeric values
                    if key == 'priority':
                        try:
                            value = int(value)
                        except ValueError:
                            value = 0
                    
                    # Convert boolean values
                    if value == 'true':
                        value = True
                    elif value == 'false':
                        value = False
                    
                    current_queue[key] = value
            
            # Add last queue
            if current_queue:
                queues.append(QueueItem(**current_queue))
            
            logger.info(f"Retrieved {len(queues)} queue configurations")
            return queues
            
        except Exception as e:
            logger.error(f"Failed to get queues: {e}")
            return []
    
    async def add_queue(self, name: str, target: str, max_limit: str, comment: str = "") -> bool:
        """Add queue configuration"""
        try:
            command = f'/queue simple add name="{name}" target={target} max-limit={max_limit}'
            if comment:
                command += f' comment="{comment}"'
            
            output, error = await self.execute_command(command)
            
            if error:
                logger.error(f"Error adding queue: {error}")
                return False
            
            logger.info(f"Added queue: {name} -> {target}")
            return True
            
        except Exception as e:
            logger.error(f"Failed to add queue: {e}")
            return False
    
    async def remove_queue(self, name: str) -> bool:
        """Remove queue configuration"""
        try:
            command = f'/queue simple remove [find name="{name}"]'
            output, error = await self.execute_command(command)
            
            if error:
                logger.error(f"Error removing queue: {error}")
                return False
            
            logger.info(f"Removed queue: {name}")
            return True
            
        except Exception as e:
            logger.error(f"Failed to remove queue: {e}")
            return False
    
    async def get_interfaces(self) -> List[InterfaceConfig]:
        """Get interface configurations"""
        try:
            output, error = await self.execute_command('/interface print detail')
            if error:
                logger.error(f"Error getting interfaces: {error}")
                return []
            
            interfaces = []
            current_interface = {}
            
            for line in output.split('\n'):
                line = line.strip()
                if not line:
                    continue
                
                if line.startswith('Flags:'):
                    if current_interface:
                        interfaces.append(InterfaceConfig(**current_interface))
                        current_interface = {}
                elif ':' in line:
                    key, value = line.split(':', 1)
                    key = key.strip().lower().replace(' ', '_')
                    value = value.strip()
                    
                    # Convert numeric values
                    if key == 'mtu':
                        try:
                            value = int(value)
                        except ValueError:
                            value = 1500
                    
                    # Convert boolean values
                    if value == 'true':
                        value = True
                    elif value == 'false':
                        value = False
                    
                    current_interface[key] = value
            
            # Add last interface
            if current_interface:
                interfaces.append(InterfaceConfig(**current_interface))
            
            logger.info(f"Retrieved {len(interfaces)} interface configurations")
            return interfaces
            
        except Exception as e:
            logger.error(f"Failed to get interfaces: {e}")
            return []
    
    async def set_interface_mtu(self, interface_name: str, mtu: int) -> bool:
        """Set interface MTU"""
        try:
            command = f'/interface set [find name="{interface_name}"] mtu={mtu}'
            output, error = await self.execute_command(command)
            
            if error:
                logger.error(f"Error setting interface MTU: {error}")
                return False
            
            logger.info(f"Set MTU {mtu} for interface {interface_name}")
            return True
            
        except Exception as e:
            logger.error(f"Failed to set interface MTU: {e}")
            return False
    
    async def get_firewall_rules(self) -> List[FirewallRule]:
        """Get firewall rules"""
        try:
            output, error = await self.execute_command('/ip firewall filter print detail')
            if error:
                logger.error(f"Error getting firewall rules: {error}")
                return []
            
            rules = []
            current_rule = {}
            
            for line in output.split('\n'):
                line = line.strip()
                if not line:
                    continue
                
                if line.startswith('Flags:'):
                    if current_rule:
                        rules.append(FirewallRule(**current_rule))
                        current_rule = {}
                elif ':' in line:
                    key, value = line.split(':', 1)
                    key = key.strip().lower().replace(' ', '_')
                    value = value.strip()
                    
                    # Convert boolean values
                    if value == 'true':
                        value = True
                    elif value == 'false':
                        value = False
                    
                    current_rule[key] = value
            
            # Add last rule
            if current_rule:
                rules.append(FirewallRule(**current_rule))
            
            logger.info(f"Retrieved {len(rules)} firewall rules")
            return rules
            
        except Exception as e:
            logger.error(f"Failed to get firewall rules: {e}")
            return []
    
    async def add_firewall_rule(self, chain: str, action: str, protocol: str = "tcp", 
                               src_address: str = "", dst_address: str = "",
                               src_port: str = "", dst_port: str = "", comment: str = "") -> bool:
        """Add firewall rule"""
        try:
            command = f'/ip firewall filter add chain={chain} action={action} protocol={protocol}'
            
            if src_address:
                command += f' src-address={src_address}'
            if dst_address:
                command += f' dst-address={dst_address}'
            if src_port:
                command += f' src-port={src_port}'
            if dst_port:
                command += f' dst-port={dst_port}'
            if comment:
                command += f' comment="{comment}"'
            
            output, error = await self.execute_command(command)
            
            if error:
                logger.error(f"Error adding firewall rule: {error}")
                return False
            
            logger.info(f"Added firewall rule: {chain} -> {action}")
            return True
            
        except Exception as e:
            logger.error(f"Failed to add firewall rule: {e}")
            return False
    
    async def get_wireless_interfaces(self) -> List[Dict[str, Any]]:
        """Get wireless interface information"""
        try:
            output, error = await self.execute_command('/interface wireless print detail')
            if error:
                logger.error(f"Error getting wireless interfaces: {error}")
                return []
            
            interfaces = []
            current_interface = {}
            
            for line in output.split('\n'):
                line = line.strip()
                if not line:
                    continue
                
                if line.startswith('Flags:'):
                    if current_interface:
                        interfaces.append(current_interface)
                        current_interface = {}
                elif ':' in line:
                    key, value = line.split(':', 1)
                    key = key.strip().lower().replace(' ', '_')
                    value = value.strip()
                    current_interface[key] = value
            
            # Add last interface
            if current_interface:
                interfaces.append(current_interface)
            
            logger.info(f"Retrieved {len(interfaces)} wireless interfaces")
            return interfaces
            
        except Exception as e:
            logger.error(f"Failed to get wireless interfaces: {e}")
            return []
    
    async def get_wireless_clients(self) -> List[Dict[str, Any]]:
        """Get wireless clients"""
        try:
            output, error = await self.execute_command('/interface wireless registration-table print detail')
            if error:
                logger.error(f"Error getting wireless clients: {error}")
                return []
            
            clients = []
            current_client = {}
            
            for line in output.split('\n'):
                line = line.strip()
                if not line:
                    continue
                
                if line.startswith('Flags:'):
                    if current_client:
                        clients.append(current_client)
                        current_client = {}
                elif ':' in line:
                    key, value = line.split(':', 1)
                    key = key.strip().lower().replace(' ', '_')
                    value = value.strip()
                    current_client[key] = value
            
            # Add last client
            if current_client:
                clients.append(current_client)
            
            logger.info(f"Retrieved {len(clients)} wireless clients")
            return clients
            
        except Exception as e:
            logger.error(f"Failed to get wireless clients: {e}")
            return []

class MikroTikSSHManager:
    """Manager for multiple MikroTik SSH connections"""
    
    def __init__(self, db_path: str = "network_devices.db"):
        self.db_path = db_path
        self.clients = {}
        self.configs = {}
    
    def add_device(self, device_id: str, config: MikroTikSSHConfig):
        """Add device configuration"""
        self.configs[device_id] = config
        logger.info(f"Added device configuration for {device_id}")
    
    async def connect_to_device(self, device_id: str) -> bool:
        """Connect to specific device"""
        if device_id not in self.configs:
            logger.error(f"No configuration found for device {device_id}")
            return False
        
        config = self.configs[device_id]
        client = MikroTikSSHClient(config)
        
        if await client.connect():
            self.clients[device_id] = client
            return True
        
        return False
    
    async def disconnect_from_device(self, device_id: str):
        """Disconnect from specific device"""
        if device_id in self.clients:
            await self.clients[device_id].disconnect()
            del self.clients[device_id]
    
    async def disconnect_all(self):
        """Disconnect from all devices"""
        for device_id in list(self.clients.keys()):
            await self.disconnect_from_device(device_id)
    
    def get_client(self, device_id: str) -> Optional[MikroTikSSHClient]:
        """Get SSH client for device"""
        return self.clients.get(device_id)
    
    async def execute_on_device(self, device_id: str, command: str) -> Tuple[str, str]:
        """Execute command on specific device"""
        client = self.get_client(device_id)
        if not client:
            if not await self.connect_to_device(device_id):
                return "", "Failed to connect to device"
            client = self.get_client(device_id)
        
        return await client.execute_command(command)
    
    def save_configuration_data(self, device_id: str, data_type: str, data: Any):
        """Save configuration data to database"""
        try:
            conn = sqlite3.connect(self.db_path)
            cursor = conn.cursor()
            
            # Create configuration data table
            cursor.execute('''
                CREATE TABLE IF NOT EXISTS mikrotik_config_data (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    device_id TEXT NOT NULL,
                    data_type TEXT NOT NULL,
                    data_json TEXT NOT NULL,
                    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ''')
            
            # Insert data
            cursor.execute('''
                INSERT INTO mikrotik_config_data (device_id, data_type, data_json)
                VALUES (?, ?, ?)
            ''', (device_id, data_type, json.dumps(data, default=str)))
            
            conn.commit()
            conn.close()
            
            logger.info(f"Saved {data_type} data for device {device_id}")
            
        except Exception as e:
            logger.error(f"Error saving configuration data: {e}")
    
    def get_configuration_data(self, device_id: str, data_type: str, hours: int = 24) -> List[Dict]:
        """Get configuration data from database"""
        try:
            conn = sqlite3.connect(self.db_path)
            cursor = conn.cursor()
            
            cursor.execute('''
                SELECT data_json, timestamp FROM mikrotik_config_data
                WHERE device_id = ? AND data_type = ? 
                AND timestamp >= datetime('now', '-{} hours')
                ORDER BY timestamp DESC
            '''.format(hours), (device_id, data_type))
            
            rows = cursor.fetchall()
            conn.close()
            
            data = []
            for row in rows:
                try:
                    data.append({
                        'data': json.loads(row[0]),
                        'timestamp': row[1]
                    })
                except json.JSONDecodeError:
                    continue
            
            return data
            
        except Exception as e:
            logger.error(f"Error getting configuration data: {e}")
            return []

# Example usage
async def main():
    """Example usage of MikroTik SSH integration"""
    
    # Create SSH configuration
    config = MikroTikSSHConfig(
        hostname="192.168.1.1",
        username="admin",
        password="password"
    )
    
    # Create manager
    manager = MikroTikSSHManager()
    manager.add_device("router_1", config)
    
    try:
        # Connect to device
        if await manager.connect_to_device("router_1"):
            client = manager.get_client("router_1")
            
            # Get system info
            system_info = await client.get_system_info()
            print("System Info:", system_info)
            
            # Get DHCP leases
            dhcp_leases = await client.get_dhcp_leases()
            print(f"DHCP Leases: {len(dhcp_leases)}")
            
            # Get queues
            queues = await client.get_queues()
            print(f"Queues: {len(queues)}")
            
            # Get interfaces
            interfaces = await client.get_interfaces()
            print(f"Interfaces: {len(interfaces)}")
            
            # Save data to database
            manager.save_configuration_data("router_1", "dhcp_leases", [asdict(lease) for lease in dhcp_leases])
            manager.save_configuration_data("router_1", "queues", [asdict(queue) for queue in queues])
            manager.save_configuration_data("router_1", "interfaces", [asdict(interface) for interface in interfaces])
            
    except Exception as e:
        logger.error(f"Error in main: {e}")
    
    finally:
        # Disconnect
        await manager.disconnect_all()

if __name__ == "__main__":
    asyncio.run(main()) 