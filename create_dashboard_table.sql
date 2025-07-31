-- Create dashboard_stats table
CREATE TABLE IF NOT EXISTS dashboard_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_clients INT DEFAULT 0,
    total_devices INT DEFAULT 0,
    active_networks INT DEFAULT 0,
    system_status VARCHAR(50) DEFAULT 'Online',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert initial dashboard data
INSERT INTO dashboard_stats (total_clients, total_devices, active_networks, system_status) 
VALUES (5, 10, 3, 'Online'); 