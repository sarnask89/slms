<?php
/**
 * Fix Database Schema Issues
 * Adds missing columns and tables to resolve module errors
 */

require_once 'modules/config.php';

echo "🔧 Fixing Database Schema Issues...\n\n";

try {
    $pdo = get_pdo();
    
    // Check if clients table has 'name' column
    $stmt = $pdo->query("DESCRIBE clients");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('name', $columns)) {
        echo "Adding 'name' column to clients table...\n";
        $pdo->exec("ALTER TABLE clients ADD COLUMN name VARCHAR(255) AFTER id");
        echo "✅ Added 'name' column to clients table\n";
    } else {
        echo "✅ 'name' column already exists in clients table\n";
    }
    
    // Check if clients table has 'email' column
    if (!in_array('email', $columns)) {
        echo "Adding 'email' column to clients table...\n";
        $pdo->exec("ALTER TABLE clients ADD COLUMN email VARCHAR(255) AFTER name");
        echo "✅ Added 'email' column to clients table\n";
    } else {
        echo "✅ 'email' column already exists in clients table\n";
    }
    
    // Check if clients table has 'phone' column
    if (!in_array('phone', $columns)) {
        echo "Adding 'phone' column to clients table...\n";
        $pdo->exec("ALTER TABLE clients ADD COLUMN phone VARCHAR(50) AFTER email");
        echo "✅ Added 'phone' column to clients table\n";
    } else {
        echo "✅ 'phone' column already exists in clients table\n";
    }
    
    // Check if clients table has 'address' column
    if (!in_array('address', $columns)) {
        echo "Adding 'address' column to clients table...\n";
        $pdo->exec("ALTER TABLE clients ADD COLUMN address TEXT AFTER phone");
        echo "✅ Added 'address' column to clients table\n";
    } else {
        echo "✅ 'address' column already exists in clients table\n";
    }
    
    // Check if invoices table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'invoices'");
    if ($stmt->rowCount() == 0) {
        echo "Creating invoices table...\n";
        $pdo->exec("
            CREATE TABLE invoices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_id INT,
                invoice_number VARCHAR(50),
                amount DECIMAL(10,2),
                status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                due_date DATE,
                FOREIGN KEY (client_id) REFERENCES clients(id)
            )
        ");
        echo "✅ Created invoices table\n";
    } else {
        echo "✅ Invoices table already exists\n";
    }
    
    // Check if payments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'payments'");
    if ($stmt->rowCount() == 0) {
        echo "Creating payments table...\n";
        $pdo->exec("
            CREATE TABLE payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_id INT,
                amount DECIMAL(10,2),
                payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                payment_method VARCHAR(50),
                status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
                FOREIGN KEY (invoice_id) REFERENCES invoices(id)
            )
        ");
        echo "✅ Created payments table\n";
    } else {
        echo "✅ Payments table already exists\n";
    }
    
    // Add some sample data to clients if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients");
    $clientCount = $stmt->fetch()['count'];
    
    if ($clientCount == 0) {
        echo "Adding sample client data...\n";
        $pdo->exec("
            INSERT INTO clients (name, email, phone, address) VALUES 
            ('John Doe', 'john@example.com', '+1234567890', '123 Main St'),
            ('Jane Smith', 'jane@example.com', '+0987654321', '456 Oak Ave'),
            ('Bob Johnson', 'bob@example.com', '+1122334455', '789 Pine Rd')
        ");
        echo "✅ Added sample client data\n";
    } else {
        echo "✅ Clients table already has data ({$clientCount} clients)\n";
    }
    
    echo "\n🎉 Database schema fixes completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 