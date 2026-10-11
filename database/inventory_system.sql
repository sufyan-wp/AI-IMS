-- ============================================
-- Inventory Management System v2.0
-- Complete Database with All Columns Fixed
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `inventory_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `inventory_system`;

-- -----------------------------------------------
-- Table: roles
-- -----------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` VALUES (1,'Admin'),(2,'Manager'),(3,'Salesman');

-- -----------------------------------------------
-- Table: users
-- -----------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role_id` int(11) NOT NULL DEFAULT 3,
  `status` enum('active','inactive') DEFAULT 'active',
  `profile_image` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default password for all users: admin123
INSERT INTO `users` (`username`,`email`,`password`,`full_name`,`phone`,`role_id`,`status`) VALUES
('admin','admin@ims.com','admin123','Admin User','03001234567',1,'active'),
('manager','manager@ims.com','admin123','Sales Manager','03009876543',2,'active'),
('salesman','salesman@ims.com','admin123','Sales Person','03005678901',3,'active');

-- -----------------------------------------------
-- Table: categories
-- -----------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`category_name`,`description`) VALUES
('Electronics','Electronic devices and accessories'),
('Clothing','Apparel and garments'),
('Food & Beverages','Food and drink items'),
('Stationery','Office and school supplies'),
('Furniture','Home and office furniture'),
('Sports','Sports equipment'),
('Health & Beauty','Personal care products'),
('Toys','Toys and games');

-- -----------------------------------------------
-- Table: products
-- -----------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_quantity` int(11) NOT NULL DEFAULT 5,
  `description` text DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`product_name`,`category_id`,`brand`,`barcode`,`cost_price`,`selling_price`,`quantity`,`min_quantity`,`description`) VALUES
('Samsung Galaxy A54',1,'Samsung','SGA54001',45000.00,55000.00,25,5,'Android smartphone'),
('iPhone 14',1,'Apple','IPH14001',180000.00,210000.00,10,3,'Apple iPhone 14 128GB'),
('Laptop HP 15',1,'HP','HPL15001',85000.00,99000.00,15,5,'HP Laptop Core i5'),
('USB-C Cable 2m',1,'Generic','USBC001',500.00,900.00,100,20,'USB Type-C cable'),
('Men T-Shirt',2,'Local Brand','MTSH001',400.00,799.00,200,30,'Cotton t-shirt'),
('Women Jeans',2,'Levis','WJNS001',3500.00,5500.00,80,20,'Slim fit jeans'),
('Rice 5kg',3,'Super Fine','RICE5KG',1200.00,1500.00,500,100,'Basmati rice 5kg'),
('Cooking Oil 1L',3,'Dalda','COIL1L',350.00,420.00,300,50,'Cooking oil 1 liter'),
('A4 Paper Ream',4,'IK','A4PRM',800.00,1100.00,150,30,'A4 paper 500 sheets'),
('Ball Pen Box',4,'Pilot','BPEN',600.00,900.00,200,50,'Box of 12 pens'),
('Office Chair',5,'Master','OCHR01',8000.00,12000.00,20,5,'Ergonomic chair'),
('Study Table',5,'Molty','STBL01',6000.00,9500.00,15,5,'Wooden study table'),
('Cricket Bat',6,'Gray Nicolls','CBAT01',2500.00,4000.00,30,10,'Full size bat'),
('Football',6,'Adidas','FBALL01',2000.00,3000.00,40,10,'Size 5 football'),
('Hand Sanitizer',7,'Dettol','HSAN01',150.00,220.00,400,100,'500ml sanitizer'),
('Face Wash',7,'Garnier','FWSH01',450.00,650.00,150,30,'100ml face wash'),
('Lego Set',8,'Lego','LEGO01',3500.00,5000.00,35,10,'Classic Lego blocks'),
('Remote Control Car',8,'Hot Wheels','RCAR01',1800.00,2800.00,25,10,'RC toy car'),
('Wireless Earbuds',1,'JBL','JBLEAR',3500.00,5000.00,60,15,'Bluetooth earbuds'),
('Power Bank 20000mAh',1,'Anker','PBNK01',2800.00,4000.00,45,10,'20000mAh powerbank');

-- -----------------------------------------------
-- Table: customers
-- -----------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `customers` (`customer_name`,`email`,`phone`,`address`) VALUES
('Ahmed Ali','ahmed@email.com','03001234567','House 12, Islamabad'),
('Sara Khan','sara@email.com','03009876543','DHA, Lahore'),
('Muhammad Usman','usman@email.com','03125678901','Saddar, Karachi'),
('Fatima Malik','fatima@email.com','03331234567','Gulberg, Lahore'),
('Hassan Raza','hassan@email.com','03451234567','F-7, Islamabad'),
('Ayesha Siddiqui','ayesha@email.com','03561234567','Model Town, Lahore'),
('Bilal Ahmed','bilal@email.com','03671234567','Blue Area, Islamabad'),
('Zainab Noor','zainab@email.com','03781234567','Clifton, Karachi');

-- -----------------------------------------------
-- Table: suppliers
-- -----------------------------------------------
DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(100) NOT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `suppliers` (`supplier_name`,`company_name`,`email`,`phone`,`address`) VALUES
('Tariq Electronics','Tariq Electronics Ltd','tariq@sup.com','03001111111','Hafeez Center, Lahore'),
('Fashion World','Fashion World Pvt Ltd','fashion@sup.com','03002222222','Anarkali, Lahore'),
('Grocery Plus','Grocery Plus Traders','grocery@sup.com','03003333333','Sabzi Mandi, Islamabad'),
('Office Mart','Office Mart Supplies','office@sup.com','03004444444','I-9, Islamabad'),
('Sports Hub','Sports Hub Pakistan','sports@sup.com','03005555555','MM Alam Road, Lahore');

-- -----------------------------------------------
-- Table: sales
-- -----------------------------------------------
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('Cash','Card','Bank Transfer','Cheque') DEFAULT 'Cash',
  `payment_status` enum('Paid','Pending','Partial') DEFAULT 'Paid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sale_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sales` (`invoice_number`,`customer_id`,`customer_name`,`user_id`,`subtotal`,`tax_rate`,`tax_amount`,`discount`,`total_amount`,`payment_method`,`payment_status`) VALUES
('INV-00001',1,'Ahmed Ali',3,55900.00,8.00,4472.00,0.00,60372.00,'Cash','Paid'),
('INV-00002',2,'Sara Khan',3,7297.00,8.00,583.76,200.00,7680.76,'Card','Paid'),
('INV-00003',3,'Muhammad Usman',3,4000.00,0.00,0.00,0.00,4000.00,'Cash','Paid'),
('INV-00004',4,'Fatima Malik',3,3100.00,8.00,248.00,0.00,3348.00,'Bank Transfer','Paid'),
('INV-00005',5,'Hassan Raza',3,12000.00,8.00,960.00,500.00,12460.00,'Cash','Paid');

-- -----------------------------------------------
-- Table: sale_items
-- -----------------------------------------------
DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE `sale_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `sale_id` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sale_items` (`sale_id`,`product_id`,`product_name`,`quantity`,`unit_price`,`subtotal`) VALUES
(1,1,'Samsung Galaxy A54',1,55000.00,55000.00),
(1,4,'USB-C Cable 2m',1,900.00,900.00),
(2,5,'Men T-Shirt',3,799.00,2397.00),
(2,6,'Women Jeans',1,5500.00,5500.00),
(3,13,'Cricket Bat',1,4000.00,4000.00),
(4,9,'A4 Paper Ream',2,1100.00,2200.00),
(4,10,'Ball Pen Box',1,900.00,900.00),
(5,11,'Office Chair',1,12000.00,12000.00);

-- -----------------------------------------------
-- Table: stock_logs
-- -----------------------------------------------
DROP TABLE IF EXISTS `stock_logs`;
CREATE TABLE `stock_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `type` enum('in','out','sale','return') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------
-- Table: returns
-- -----------------------------------------------
DROP TABLE IF EXISTS `returns`;
CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`return_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------
-- Table: activity_logs
-- -----------------------------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------
-- Table: settings
-- -----------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`,`setting_value`) VALUES
('company_name','ABC Trading Company'),
('company_email','info@abctrading.com'),
('company_phone','051-1234567'),
('company_address','Plaza 123, Blue Area, Islamabad'),
('tax_rate','8'),
('currency','PKR'),
('low_stock_alert','10'),
('company_logo','');

SET FOREIGN_KEY_CHECKS = 1;
