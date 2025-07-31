/**
 * WebGL SQLite Integration Module - Fallback Version
 * Provides SQLite-like functionality using IndexedDB and localStorage
 * This is a fallback when SQLite WASM is not available
 */

class WebGLSQLiteIntegrationFallback {
    constructor() {
        this.db = null;
        this.isInitialized = false;
        this.localChanges = [];
        this.syncQueue = [];
        this.storageKey = 'slms_webgl_data';
        
        // Simulated database structure
        this.tables = {
            devices: [],
            network_connections: [],
            webgl_settings: {},
            sync_queue: []
        };
    }
    
    /**
     * Initialize fallback database
     */
    async initialize() {
        try {
            console.log('Initializing fallback database integration...');
            
            // Load data from localStorage
            await this.loadFromStorage();
            
            // Setup sync with server
            this.setupServerSync();
            
            this.isInitialized = true;
            console.log('Fallback database integration initialized successfully');
            return true;
            
        } catch (error) {
            console.error('Failed to initialize fallback database:', error);
            return false;
        }
    }
    
    /**
     * Load data from localStorage
     */
    async loadFromStorage() {
        try {
            const stored = localStorage.getItem(this.storageKey);
            if (stored) {
                this.tables = JSON.parse(stored);
            } else {
                // Initialize with default settings
                this.tables.webgl_settings = this.getDefaultSettings();
            }
        } catch (error) {
            console.error('Failed to load from storage:', error);
            this.tables.webgl_settings = this.getDefaultSettings();
        }
    }
    
    /**
     * Save data to localStorage
     */
    async saveToStorage() {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(this.tables));
        } catch (error) {
            console.error('Failed to save to storage:', error);
        }
    }
    
    /**
     * Get default WebGL settings
     */
    getDefaultSettings() {
        return {
            background_color: '0x1a1a1a',
            auto_refresh_interval: '10',
            device_colors_router: '0x00ff00',
            device_colors_switch: '0x0088ff',
            device_colors_server: '0xff0088',
            device_colors_client: '0xff8800',
            show_connections: 'true',
            show_labels: 'true',
            camera_distance: '50',
            animation_speed: '1.0'
        };
    }
    
    /**
     * Sync data from server to local storage
     */
    async syncFromServer() {
        try {
            const response = await fetch('api/webgl_database_api_clean.php?action=network_data');
            const data = await response.json();
            
            if (data.success) {
                // Update local tables
                this.tables.devices = data.devices || [];
                this.tables.network_connections = data.connections || [];
                this.tables.webgl_settings = data.settings || this.getDefaultSettings();
                
                // Save to storage
                await this.saveToStorage();
                
                console.log(`Synced ${this.tables.devices.length} devices and ${this.tables.network_connections.length} connections from server`);
            }
            
        } catch (error) {
            console.error('Failed to sync from server:', error);
        }
    }
    
    /**
     * Get devices from local storage
     */
    getDevices() {
        return this.tables.devices || [];
    }
    
    /**
     * Get connections from local storage
     */
    getConnections() {
        return this.tables.network_connections || [];
    }
    
    /**
     * Get settings from local storage
     */
    getSettings() {
        return this.tables.webgl_settings || this.getDefaultSettings();
    }
    
    /**
     * Update device position in local storage
     */
    updateDevicePosition(deviceId, x, y, z) {
        try {
            const device = this.tables.devices.find(d => d.id == deviceId);
            if (device) {
                device.position_x = x;
                device.position_y = y;
                device.position_z = z;
                
                // Add to sync queue
                this.addToSyncQueue('device_position_update', {
                    device_id: deviceId,
                    position_x: x,
                    position_y: y,
                    position_z: z
                });
                
                // Save to storage
                this.saveToStorage();
                
                return true;
            }
            return false;
        } catch (error) {
            console.error('Failed to update device position:', error);
            return false;
        }
    }
    
    /**
     * Update device status in local storage
     */
    updateDeviceStatus(deviceId, status) {
        try {
            const device = this.tables.devices.find(d => d.id == deviceId);
            if (device) {
                device.status = status;
                device.last_seen = new Date().toISOString();
                
                // Add to sync queue
                this.addToSyncQueue('device_status_update', {
                    device_id: deviceId,
                    status: status
                });
                
                // Save to storage
                this.saveToStorage();
                
                return true;
            }
            return false;
        } catch (error) {
            console.error('Failed to update device status:', error);
            return false;
        }
    }
    
    /**
     * Update setting in local storage
     */
    updateSetting(settingName, settingValue) {
        try {
            this.tables.webgl_settings[settingName] = settingValue;
            
            // Add to sync queue
            this.addToSyncQueue('setting_update', {
                setting_name: settingName,
                setting_value: settingValue
            });
            
            // Save to storage
            this.saveToStorage();
            
            return true;
        } catch (error) {
            console.error('Failed to update setting:', error);
            return false;
        }
    }
    
    /**
     * Add change to sync queue
     */
    addToSyncQueue(changeType, changeData) {
        try {
            this.tables.sync_queue.push({
                change_type: changeType,
                change_data: changeData,
                timestamp: new Date().toISOString(),
                synced: false
            });
            
            // Save to storage
            this.saveToStorage();
        } catch (error) {
            console.error('Failed to add to sync queue:', error);
        }
    }
    
    /**
     * Get pending changes for sync
     */
    getPendingChanges() {
        try {
            return this.tables.sync_queue
                .filter(item => !item.synced)
                .map(item => ({
                    type: item.change_type,
                    data: item.change_data,
                    timestamp: item.timestamp
                }));
        } catch (error) {
            console.error('Failed to get pending changes:', error);
            return [];
        }
    }
    
    /**
     * Mark changes as synced
     */
    markChangesAsSynced(timestamps) {
        try {
            timestamps.forEach(timestamp => {
                const item = this.tables.sync_queue.find(q => q.timestamp === timestamp);
                if (item) {
                    item.synced = true;
                }
            });
            
            // Save to storage
            this.saveToStorage();
        } catch (error) {
            console.error('Failed to mark changes as synced:', error);
        }
    }
    
    /**
     * Setup server synchronization
     */
    setupServerSync() {
        // Sync every 30 seconds
        setInterval(async () => {
            await this.syncToServer();
        }, 30000);
        
        // Also sync when page becomes visible
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.syncToServer();
            }
        });
    }
    
    /**
     * Sync local changes to server
     */
    async syncToServer() {
        try {
            const changes = this.getPendingChanges();
            
            if (changes.length > 0) {
                const response = await fetch('api/webgl_database_api_clean.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'sync_data',
                        changes: changes
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Mark changes as synced
                    const timestamps = changes.map(change => change.timestamp);
                    this.markChangesAsSynced(timestamps);
                    
                    console.log(`Synced ${result.synced} changes to server`);
                } else {
                    console.error('Server sync failed:', result.error);
                }
            }
            
        } catch (error) {
            console.error('Failed to sync to server:', error);
        }
    }
    
    /**
     * Export database to JSON
     */
    exportDatabase() {
        try {
            return {
                devices: this.tables.devices,
                connections: this.tables.network_connections,
                settings: this.tables.webgl_settings,
                export_timestamp: new Date().toISOString()
            };
        } catch (error) {
            console.error('Failed to export database:', error);
            return null;
        }
    }
    
    /**
     * Import database from JSON
     */
    importDatabase(data) {
        try {
            // Update local tables
            this.tables.devices = data.devices || [];
            this.tables.network_connections = data.connections || [];
            this.tables.webgl_settings = data.settings || this.getDefaultSettings();
            
            // Save to storage
            this.saveToStorage();
            
            console.log('Database imported successfully');
            return true;
            
        } catch (error) {
            console.error('Failed to import database:', error);
            return false;
        }
    }
    
    /**
     * Get database statistics
     */
    getDatabaseStats() {
        try {
            return {
                devices: this.tables.devices.length,
                connections: this.tables.network_connections.length,
                settings: Object.keys(this.tables.webgl_settings).length,
                pending_changes: this.tables.sync_queue.filter(q => !q.synced).length,
                timestamp: new Date().toISOString()
            };
        } catch (error) {
            console.error('Failed to get database stats:', error);
            return {
                devices: 0,
                connections: 0,
                settings: 0,
                pending_changes: 0,
                timestamp: new Date().toISOString()
            };
        }
    }
    
    /**
     * Execute a simulated SQL query (for compatibility)
     */
    exec(query, params = []) {
        try {
            // Simple query parser for demonstration
            if (query.includes('SELECT * FROM devices')) {
                return this.tables.devices;
            } else if (query.includes('SELECT * FROM network_connections')) {
                return this.tables.network_connections;
            } else if (query.includes('SELECT setting_name, setting_value FROM webgl_settings')) {
                return Object.entries(this.tables.webgl_settings).map(([name, value]) => ({
                    setting_name: name,
                    setting_value: value
                }));
            } else if (query.includes('INSERT INTO devices')) {
                // Simulate device insertion
                const newDevice = {
                    id: Date.now(),
                    name: params[1] || 'New Device',
                    type: params[2] || 'other',
                    ip_address: params[3] || '',
                    status: params[4] || 'offline',
                    position_x: params[5] || 0,
                    position_y: params[6] || 0,
                    position_z: params[7] || 0,
                    mac_address: params[8] || null,
                    model: params[9] || null,
                    vendor: params[10] || null,
                    location: params[11] || null,
                    description: params[12] || null,
                    created_at: new Date().toISOString(),
                    last_seen: null
                };
                this.tables.devices.push(newDevice);
                this.saveToStorage();
                return [newDevice];
            } else if (query.includes('UPDATE devices')) {
                // Simulate device update
                const deviceId = params[3];
                const device = this.tables.devices.find(d => d.id == deviceId);
                if (device) {
                    if (query.includes('position_x')) {
                        device.position_x = params[0];
                        device.position_y = params[1];
                        device.position_z = params[2];
                    } else if (query.includes('status')) {
                        device.status = params[0];
                        device.last_seen = params[1];
                    }
                    this.saveToStorage();
                    return [device];
                }
            }
            
            return [];
        } catch (error) {
            console.error('Failed to execute query:', error);
            return [];
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WebGLSQLiteIntegrationFallback;
} else {
    window.WebGLSQLiteIntegrationFallback = WebGLSQLiteIntegrationFallback;
} 