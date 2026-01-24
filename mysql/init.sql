CREATE TABLE location (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(100) UNIQUE NOT NULL
);

INSERT INTO location (location_name) VALUES
('RGCB-C2-IT'),
('DrRajesh_LAB'),
('BSL 3'),
('DrPallabi_LAB'),
('DrTRSK_LAB'),
('CCRC'),
('BioNest'),
('DrTRSK_LAB'),
('RGCB-C2-Store'),
('RGCB-C2-Substn'),
('RGCB-C2-INST'),
('DrShijulal_LAB'),
('RGCB-C2-Admin'),
('RGCB-C2-Reception')
ON DUPLICATE KEY UPDATE location_name=location_name;

CREATE TABLE asset_type (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_type_name VARCHAR(100) UNIQUE NOT NULL
);
INSERT INTO asset_type (asset_type_name) VALUES
('Printer'),
('Assembled Desktop'),
('All-in-One Desktop PC'),
('Desktop PC'),
('LAPTOP'),
('Workstation'),
('Equipment Supporting System'),
('Tablet Computer')
ON DUPLICATE KEY UPDATE asset_type_name=asset_type_name;

CREATE TABLE IF NOT EXISTS asset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_type_id INT NOT NULL,
    location_id INT NOT NULL,
    host_name VARCHAR(100) NULL,
    make VARCHAR(100) NULL,
    model VARCHAR(100) NULL,
    serial_number VARCHAR(100) NULL,
    remarks TEXT NULL,
    installation_date DATE NULL,
    warranty_period VARCHAR(50) NULL,
    instrument_id VARCHAR(100) NULL,
    company_name VARCHAR(100) NULL,
    contact_number VARCHAR(20) NULL,
    po_number VARCHAR(50) NULL,
    document_name VARCHAR(255) NULL,
    document_path VARCHAR(255) NULL,
    warranty_document_name VARCHAR(255) NULL,
    warranty_document_path VARCHAR(255) NULL,
    created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_type_id) REFERENCES asset_type(id),
    FOREIGN KEY (location_id) REFERENCES location(id)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

INSERT INTO users (username, password)
VALUES ('admin', 'admin123');
