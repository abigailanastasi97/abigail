CREATE DATABASE IF NOT EXISTS kahuna;
USE kahuna;

-- User table
CREATE TABLE User (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(10) NOT NULL DEFAULT 'client',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product table
CREATE TABLE Product (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    serialNumber VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    warrantyYears INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Registered products (user purchases)
CREATE TABLE RegisteredProduct (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    purchaseDate DATE NOT NULL,
    userId INT NOT NULL,
    productId INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT c_registered_user
        FOREIGN KEY (userId) REFERENCES User(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT c_registered_product
        FOREIGN KEY (productId) REFERENCES Product(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- Access tokens for authentication
CREATE TABLE AccessToken (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255) NOT NULL UNIQUE,
    expiryDate TIMESTAMP NOT NULL,
    userId INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT c_accesstoken_user
        FOREIGN KEY (userId) REFERENCES User(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- Default Kahuna products
INSERT INTO Product (serialNumber, name, warrantyYears) VALUES
('KHWM8199911', 'CombiSpin Washing Machine', 2),
('KHWM8199912', 'CombiSpin + Dry Washing Machine', 2),
('KHMW789991', 'CombiGrill Microwave', 1),
('KHWP890001', 'K5 Water Pump', 5),
('KHWP890002', 'K5 Heated Water Pump', 5),
('KHSS988881', 'Smart Switch Lite', 2),
('KHSS988882', 'Smart Switch Pro', 2),
('KHSS988883', 'Smart Switch Pro V2', 2),
('KHHM89762', 'Smart Heated Mug', 1),
('KHSB0001', 'Smart Bulb 001', 1);