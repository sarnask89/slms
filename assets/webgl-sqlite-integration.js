/**
 * WebGL SQLite Integration Module
 * Provides SQLite WASM integration for client-side database operations
 */

class WebGLSQLiteIntegration {
    constructor() {
        this.sqlite3 = null;
        this.db = null;
        this.isInitialized = false;
        this.localChanges = [];
        this.syncQueue = [];
        
        // SQLite WASM CDN URLs
        this.sqliteUrls = {
            js: 'https://cdn.jsdelivr.net/npm/sqlite3@5.1.6/dist/sqlite3.js',
            wasm: 'https://cdn.jsdelivr.net/npm/sqlite3@5.1.6/dist/sqlite3.wasm'
        };
    }
    
    /**
     * Initialize SQLite WASM
     */
    async initialize() {
        try {
            console.log('Initializing SQLite WASM integration...');
            
            // Load SQLite WASM from CDN
            await this.loadSQLiteWASM();
            
            // Initialize database
            await this.initDatabase();
            
            // Setup sync with server
            this.setupServerSync();
            
            this.isInitialized = true;
            console.log('SQLite WASM integration initialized successfully');
            return true;
            
        } catch (error) {
            console.error('Failed to initialize SQLite WASM:', error);
            return false;
        }
    }
    
    /**
     * Load SQLite WASM from CDN
     */
    async loadSQLiteWASM() {
        return new Promise((resolve, reject) => {
            // Check if SQLite is already loaded
            if (window.sqlite3) {
                this.sqlite3 = window.sqlite3;
                resolve();
                return;
            }
            
            // Load SQLite WASM script
            const script = document.createElement('script');
            script.src = this.sqliteUrls.js;
            script.onload = () => {
                // Initialize SQLite module
                if (window.sqlite3InitModule) {
                    window.sqlite3InitModule().then((sqlite3) => {
                        this.sqlite3 = sqlite3;
                        resolve();
                    }).catch(reject);
                } else {
                    reject(new Error('SQLite WASM module not found'));
                }
            };
            script.onerror = () => reject(new Error('Failed to load SQLite WASM'));
            document.head.appendChild(script);
        });
    }
    
    /**
     * Initialize local database
     */
    async initDatabase() {
        try {
            // Create database connection
            this.db = new this.sqlite3.oo1.DB("slms_webgl.db");
            
            // Create schema
            await this.createSchema();
            
            // Load initial data from server
            await this.syncFromServer();
            
            console.log('Local database initialized');
            
        } catch (error) {
            console.error('Failed to initialize local database:', error);
            throw error;
        }
    }
    
    /**
     * Create database schema
     */
    async createSchema() {
        const schema = `
            CREATE TABLE IF NOT EXISTS devices (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                type TEXT NOT NULL,
                ip_address TEXT,
                status TEXT DEFAULT 'offline',
                position_x REAL DEFAULT 0,
                position_y REAL DEFAULT 0,
                position_z REAL DEFAULT 0,
                mac_address TEXT,
                model TEXT,
                vendor TEXT,
                location TEXT,
                description TEXT,
                created_at TEXT,
                last_seen TEXT
            );
            
            CREATE TABLE IF NOT EXISTS network_connections (
                id INTEGER PRIMARY KEY,
                from_device_id INTEGER,
                to_device_id INTEGER,
                connection_type TEXT,
                bandwidth INTEGER,
                status TEXT DEFAULT 'active',
                from_device_name TEXT,
                from_device_type TEXT,
                to_device_name TEXT,
                to_device_type TEXT
            );
            
            CREATE TABLE IF NOT EXISTS webgl_settings (
                id INTEGER PRIMARY KEY,
                setting_name TEXT UNIQUE,
                setting_value TEXT,
                updated_at TEXT
            );
            
            CREATE TABLE IF NOT EXISTS sync_queue (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                change_type TEXT,
                change_data TEXT,
                timestamp TEXT,
                synced INTEGER DEFAULT 0
            );
        `;
        
        this.db.exec(schema);
    }
    
    /**
     * Sync data from server to local database
     */
    async syncFromServer() {
        try {
            const response = await fetch('api/webgl_database_api_clean.php?action=network_data');
            const data = await response.json();
            
            if (data.success) {
                // Clear existing data
                this.db.exec('DELETE FROM devices');
                this.db.exec('DELETE FROM network_connections');
                this.db.exec('DELETE FROM webgl_settings');
                
                // Insert devices
                data.devices.forEach(device => {
                    this.db.exec(`
                        INSERT INTO devices 
                        (id, name, type, ip_address, status, position_x, position_y, position_z, 
                         mac_address, model, vendor, location, description, created_at, last_seen)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    `, [
                        device.id, device.name, device.type, device.ip_address, device.status,
                        device.position_x, device.position_y, device.position_z,
                        device.mac_address, device.model, device.vendor, device.location,
                        device.description, device.created_at, device.last_seen
                    ]);
                });
                
                // Insert connections
                data.connections.forEach(connection => {
                    this.db.exec(`
                        INSERT INTO network_connections
                        (id, from_device_id, to_device_id, connection_type, bandwidth, status,
                         from_device_name, from_device_type, to_device_name, to_device_type)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    `, [
                        connection.id, connection.from_device_id, connection.to_device_id,
                        connection.connection_type, connection.bandwidth, connection.status,
                        connection.from_device_name, connection.from_device_type,
                        connection.to_device_name, connection.to_device_type
                    ]);
                });
                
                // Insert settings
                Object.entries(data.settings).forEach(([name, value]) => {
                    this.db.exec(`
                        INSERT INTO webgl_settings (setting_name, setting_value, updated_at)
                        VALUES (?, ?, ?)
                    `, [name, value, new Date().toISOString()]);
                });
                
                console.log(`Synced ${data.devices.length} devices and ${data.connections.length} connections from server`);
            }
            
        } catch (error) {
            console.error('Failed to sync from server:', error);
        }
    }
    
    /**
     * Get devices from local database
     */
    getDevices() {
        try {
            return this.db.exec("SELECT * FROM devices ORDER BY type, name");
        } catch (error) {
            console.error('Failed to get devices:', error);
            return [];
        }
    }
    
    /**
     * Get connections from local database
     */
    getConnections() {
        try {
            return this.db.exec("SELECT * FROM network_connections ORDER BY from_device_id, to_device_id");
        } catch (error) {
            console.error('Failed to get connections:', error);
            return [];
        }
    }
    
    /**
     * Get settings from local database
     */
    getSettings() {
        try {
            const result = this.db.exec("SELECT setting_name, setting_value FROM webgl_settings");
            const settings = {};
            result.forEach(row => {
                settings[row.setting_name] = row.setting_value;
            });
            return settings;
        } catch (error) {
            console.error('Failed to get settings:', error);
            return {};
        }
    }
    
    /**
     * Update device position in local database
     */
    updateDevicePosition(deviceId, x, y, z) {
        try {
            this.db.exec(`
                UPDATE devices 
                SET position_x = ?, position_y = ?, position_z = ?
                WHERE id = ?
            `, [x, y, z, deviceId]);
            
            // Add to sync queue
            this.addToSyncQueue('device_position_update', {
                device_id: deviceId,
                position_x: x,
                position_y: y,
                position_z: z
            });
            
            return true;
        } catch (error) {
            console.error('Failed to update device position:', error);
            return false;
        }
    }
    
    /**
     * Update device status in local database
     */
    updateDeviceStatus(deviceId, status) {
        try {
            this.db.exec(`
                UPDATE devices 
                SET status = ?, last_seen = ?
                WHERE id = ?
            `, [status, new Date().toISOString(), deviceId]);
            
            // Add to sync queue
            this.addToSyncQueue('device_status_update', {
                device_id: deviceId,
                status: status
            });
            
            return true;
        } catch (error) {
            console.error('Failed to update device status:', error);
            return false;
        }
    }
    
    /**
     * Update setting in local database
     */
    updateSetting(settingName, settingValue) {
        try {
            this.db.exec(`
                INSERT OR REPLACE INTO webgl_settings (setting_name, setting_value, updated_at)
                VALUES (?, ?, ?)
            `, [settingName, settingValue, new Date().toISOString()]);
            
            // Add to sync queue
            this.addToSyncQueue('setting_update', {
                setting_name: settingName,
                setting_value: settingValue
            });
            
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
            this.db.exec(`
                INSERT INTO sync_queue (change_type, change_data, timestamp)
                VALUES (?, ?, ?)
            `, [changeType, JSON.stringify(changeData), new Date().toISOString()]);
        } catch (error) {
            console.error('Failed to add to sync queue:', error);
        }
    }
    
    /**
     * Get pending changes for sync
     */
    getPendingChanges() {
        try {
            const result = this.db.exec(`
                SELECT change_type, change_data, timestamp 
                FROM sync_queue 
                WHERE synced = 0 
                ORDER BY timestamp
            `);
            
            return result.map(row => ({
                type: row.change_type,
                data: JSON.parse(row.change_data),
                timestamp: row.timestamp
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
                this.db.exec(`
                    UPDATE sync_queue 
                    SET synced = 1 
                    WHERE timestamp = ?
                `, [timestamp]);
            });
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
            const devices = this.getDevices();
            const connections = this.getConnections();
            const settings = this.getSettings();
            
            return {
                devices: devices,
                connections: connections,
                settings: settings,
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
            // Clear existing data
            this.db.exec('DELETE FROM devices');
            this.db.exec('DELETE FROM network_connections');
            this.db.exec('DELETE FROM webgl_settings');
            
            // Import devices
            data.devices.forEach(device => {
                this.db.exec(`
                    INSERT INTO devices 
                    (id, name, type, ip_address, status, position_x, position_y, position_z, 
                     mac_address, model, vendor, location, description, created_at, last_seen)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                `, [
                    device.id, device.name, device.type, device.ip_address, device.status,
                    device.position_x, device.position_y, device.position_z,
                    device.mac_address, device.model, device.vendor, device.location,
                    device.description, device.created_at, device.last_seen
                ]);
            });
            
            // Import connections
            data.connections.forEach(connection => {
                this.db.exec(`
                    INSERT INTO network_connections
                    (id, from_device_id, to_device_id, connection_type, bandwidth, status,
                     from_device_name, from_device_type, to_device_name, to_device_type)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                `, [
                    connection.id, connection.from_device_id, connection.to_device_id,
                    connection.connection_type, connection.bandwidth, connection.status,
                    connection.from_device_name, connection.from_device_type,
                    connection.to_device_name, connection.to_device_type
                ]);
            });
            
            // Import settings
            Object.entries(data.settings).forEach(([name, value]) => {
                this.db.exec(`
                    INSERT INTO webgl_settings (setting_name, setting_value, updated_at)
                    VALUES (?, ?, ?)
                `, [name, value, new Date().toISOString()]);
            });
            
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
            const deviceCount = this.db.exec("SELECT COUNT(*) as count FROM devices")[0].count;
            const connectionCount = this.db.exec("SELECT COUNT(*) as count FROM network_connections")[0].count;
            const settingCount = this.db.exec("SELECT COUNT(*) as count FROM webgl_settings")[0].count;
            const pendingChanges = this.db.exec("SELECT COUNT(*) as count FROM sync_queue WHERE synced = 0")[0].count;
            
            return {
                devices: deviceCount,
                connections: connectionCount,
                settings: settingCount,
                pending_changes: pendingChanges,
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
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WebGLSQLiteIntegration;
} else {
    window.WebGLSQLiteIntegration = WebGLSQLiteIntegration;
} 