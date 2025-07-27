-- Tabela CMS do dynamicznych stron/modułów
CREATE TABLE IF NOT EXISTS cms_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    type ENUM('html','php','form','dashboard') NOT NULL,
    content MEDIUMTEXT,
    permissions VARCHAR(255),
    status ENUM('draft','published') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
