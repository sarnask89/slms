-- Fix users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS password VARCHAR(255) NOT NULL AFTER username;

-- Fix clients table
ALTER TABLE clients 
ADD COLUMN IF NOT EXISTS name VARCHAR(100) NOT NULL AFTER id;

-- Fix networks table
ALTER TABLE networks 
ADD COLUMN IF NOT EXISTS subnet VARCHAR(50) NOT NULL AFTER name;

-- Fix services table
ALTER TABLE services 
ADD COLUMN IF NOT EXISTS name VARCHAR(100) NOT NULL AFTER id,
ADD COLUMN IF NOT EXISTS type VARCHAR(50) NOT NULL AFTER name;

-- Fix invoices table
ALTER TABLE invoices 
ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER client_id; 