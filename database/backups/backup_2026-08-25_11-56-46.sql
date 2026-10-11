-- IMS Backup 2026-08-25 11:56:46
-- Database: inventory_system

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `activity_logs` VALUES ('1','1','Login','User logged in','::1','2026-08-25 09:13:02');
INSERT INTO `activity_logs` VALUES ('2','1','Logout','User logged out','::1','2026-08-25 09:21:28');
INSERT INTO `activity_logs` VALUES ('3','1','Login','User logged in','::1','2026-08-25 09:21:40');
INSERT INTO `activity_logs` VALUES ('4','1','Update Settings','Settings updated','::1','2026-08-25 09:23:24');
INSERT INTO `activity_logs` VALUES ('5','1','Logout','User logged out','::1','2026-08-25 09:24:01');
INSERT INTO `activity_logs` VALUES ('6','2','Login','User logged in','::1','2026-08-25 09:24:09');
INSERT INTO `activity_logs` VALUES ('7','2','Logout','User logged out','::1','2026-08-25 09:24:35');
INSERT INTO `activity_logs` VALUES ('8','3','Login','User logged in','::1','2026-08-25 09:24:43');
INSERT INTO `activity_logs` VALUES ('9','3','Create Invoice','Invoice: INV-00006 Total: 288750','::1','2026-08-25 09:25:30');
INSERT INTO `activity_logs` VALUES ('10','3','Return','Returned 2 x Samsung Galaxy A54','::1','2026-08-25 09:26:09');
INSERT INTO `activity_logs` VALUES ('11','3','Logout','User logged out','::1','2026-08-25 09:26:22');
INSERT INTO `activity_logs` VALUES ('12','1','Login','User logged in','::1','2026-08-25 14:47:10');
INSERT INTO `activity_logs` VALUES ('13','1','Logout','User logged out','::1','2026-08-25 14:47:22');
INSERT INTO `activity_logs` VALUES ('14','1','Login','User logged in','::1','2026-08-25 14:49:02');
INSERT INTO `activity_logs` VALUES ('15','1','Logout','User logged out','::1','2026-08-25 14:49:07');
INSERT INTO `activity_logs` VALUES ('16','1','Login','User logged in','::1','2026-08-25 14:49:46');
INSERT INTO `activity_logs` VALUES ('17','1','Logout','User logged out','::1','2026-08-25 14:50:50');
INSERT INTO `activity_logs` VALUES ('18','1','Login','User logged in','::1','2026-08-25 14:51:10');
INSERT INTO `activity_logs` VALUES ('19','1','Logout','User logged out','::1','2026-08-25 14:51:12');
INSERT INTO `activity_logs` VALUES ('20','2','Login','User logged in','::1','2026-08-25 14:52:04');
INSERT INTO `activity_logs` VALUES ('21','2','Logout','User logged out','::1','2026-08-25 14:52:08');
INSERT INTO `activity_logs` VALUES ('22','3','Login','User logged in','::1','2026-08-25 14:54:09');
INSERT INTO `activity_logs` VALUES ('23','3','Logout','User logged out','::1','2026-08-25 14:54:38');
INSERT INTO `activity_logs` VALUES ('24','1','Login','User logged in','::1','2026-08-25 14:54:45');
INSERT INTO `activity_logs` VALUES ('25','1','Stock In','Product ID 5 Qty: 100','::1','2026-08-25 14:55:20');
INSERT INTO `activity_logs` VALUES ('26','1','Stock Out','Product ID 5 Qty: 45','::1','2026-08-25 14:55:45');

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` VALUES ('1','Electronics','Electronic devices and accessories','active','2026-08-23 22:38:47');
INSERT INTO `categories` VALUES ('2','Clothing','Apparel and garments','active','2026-08-23 22:38:47');
INSERT INTO `categories` VALUES ('3','Food & Beverages','Food and drink items','active','2026-08-23 22:38:47');
INSERT INTO `categories` VALUES ('4','Stationery','Office and school supplies','active','2026-08-23 22:38:47');
INSERT INTO `categories` VALUES ('5','Furniture','Home and office furniture','active','2026-08-23 22:38:47');
INSERT INTO `categories` VALUES ('6','Sports','Sports equipment','active','2026-08-23 22:38:47');
INSERT INTO `categories` VALUES ('7','Health & Beauty','Personal care products','active','2026-08-23 22:38:47');
INSERT INTO `categories` VALUES ('8','Toys','Toys and games','active','2026-08-23 22:38:47');

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `customers` VALUES ('1','Ahmed Ali','ahmed@email.com','03001234567','House 12, Islamabad','active','2026-08-23 22:38:47');
INSERT INTO `customers` VALUES ('2','Sara Khan','sara@email.com','03009876543','DHA, Lahore','active','2026-08-23 22:38:47');
INSERT INTO `customers` VALUES ('3','Muhammad Usman','usman@email.com','03125678901','Saddar, Karachi','active','2026-08-23 22:38:47');
INSERT INTO `customers` VALUES ('4','Fatima Malik','fatima@email.com','03331234567','Gulberg, Lahore','active','2026-08-23 22:38:47');
INSERT INTO `customers` VALUES ('5','Hassan Raza','hassan@email.com','03451234567','F-7, Islamabad','active','2026-08-23 22:38:47');
INSERT INTO `customers` VALUES ('6','Ayesha Siddiqui','ayesha@email.com','03561234567','Model Town, Lahore','active','2026-08-23 22:38:47');
INSERT INTO `customers` VALUES ('7','Bilal Ahmed','bilal@email.com','03671234567','Blue Area, Islamabad','active','2026-08-23 22:38:47');
INSERT INTO `customers` VALUES ('8','Zainab Noor','zainab@email.com','03781234567','Clifton, Karachi','active','2026-08-23 22:38:47');

DROP TABLE IF EXISTS `product_sales_stats`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_sales_stats` AS select `p`.`product_id` AS `product_id`,`p`.`product_name` AS `product_name`,`c`.`category_name` AS `category_name`,sum(`si`.`quantity`) AS `total_sold`,sum(`si`.`subtotal`) AS `total_revenue` from ((`products` `p` join `categories` `c` on(`p`.`category_id` = `c`.`category_id`)) left join `sale_items` `si` on(`p`.`product_id` = `si`.`product_id`)) group by `p`.`product_id`,`p`.`product_name`,`c`.`category_name` order by sum(`si`.`quantity`) desc;

INSERT INTO `product_sales_stats` VALUES ('1','Samsung Galaxy A54','Electronics','6','330000.00');
INSERT INTO `product_sales_stats` VALUES ('5','Men T-Shirt','Clothing','3','2397.00');
INSERT INTO `product_sales_stats` VALUES ('9','A4 Paper Ream','Stationery','2','2200.00');
INSERT INTO `product_sales_stats` VALUES ('11','Office Chair','Furniture','1','12000.00');
INSERT INTO `product_sales_stats` VALUES ('10','Ball Pen Box','Stationery','1','900.00');
INSERT INTO `product_sales_stats` VALUES ('13','Cricket Bat','Sports','1','4000.00');
INSERT INTO `product_sales_stats` VALUES ('6','Women Jeans','Clothing','1','5500.00');
INSERT INTO `product_sales_stats` VALUES ('4','USB-C Cable 2m','Electronics','1','900.00');
INSERT INTO `product_sales_stats` VALUES ('19','Wireless Earbuds','Electronics',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('18','Remote Control Car','Toys',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('17','Lego Set','Toys',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('16','Face Wash','Health & Beauty',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('15','Hand Sanitizer','Health & Beauty',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('14','Football','Sports',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('12','Study Table','Furniture',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('8','Cooking Oil 1L','Food & Beverages',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('7','Rice 5kg','Food & Beverages',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('3','Laptop HP 15','Electronics',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('20','Power Bank 20000mAh','Electronics',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('2','iPhone 14','Electronics',NULL,NULL);

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` VALUES ('1','Samsung Galaxy A54','1','Samsung','SGA54001','45000.00','55000.00','22','5','Android smartphone',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('2','iPhone 14','1','Apple','IPH14001','180000.00','210000.00','10','3','Apple iPhone 14 128GB',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('3','Laptop HP 15','1','HP','HPL15001','85000.00','99000.00','15','5','HP Laptop Core i5',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('4','USB-C Cable 2m','1','Generic','USBC001','500.00','900.00','100','20','USB Type-C cable',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('5','Men T-Shirt','2','Local Brand','MTSH001','400.00','799.00','255','30','Cotton t-shirt',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('6','Women Jeans','2','Levis','WJNS001','3500.00','5500.00','80','20','Slim fit jeans',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('7','Rice 5kg','3','Super Fine','RICE5KG','1200.00','1500.00','500','100','Basmati rice 5kg',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('8','Cooking Oil 1L','3','Dalda','COIL1L','350.00','420.00','300','50','Cooking oil 1 liter',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('9','A4 Paper Ream','4','IK','A4PRM','800.00','1100.00','150','30','A4 paper 500 sheets',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('10','Ball Pen Box','4','Pilot','BPEN','600.00','900.00','200','50','Box of 12 pens',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('11','Office Chair','5','Master','OCHR01','8000.00','12000.00','20','5','Ergonomic chair',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('12','Study Table','5','Molty','STBL01','6000.00','9500.00','15','5','Wooden study table',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('13','Cricket Bat','6','Gray Nicolls','CBAT01','2500.00','4000.00','30','10','Full size bat',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('14','Football','6','Adidas','FBALL01','2000.00','3000.00','40','10','Size 5 football',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('15','Hand Sanitizer','7','Dettol','HSAN01','150.00','220.00','400','100','500ml sanitizer',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('16','Face Wash','7','Garnier','FWSH01','450.00','650.00','150','30','100ml face wash',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('17','Lego Set','8','Lego','LEGO01','3500.00','5000.00','35','10','Classic Lego blocks',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('18','Remote Control Car','8','Hot Wheels','RCAR01','1800.00','2800.00','25','10','RC toy car',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('19','Wireless Earbuds','1','JBL','JBLEAR','3500.00','5000.00','60','15','Bluetooth earbuds',NULL,'active','2026-08-23 22:38:47');
INSERT INTO `products` VALUES ('20','Power Bank 20000mAh','1','Anker','PBNK01','2800.00','4000.00','45','10','20000mAh powerbank',NULL,'active','2026-08-23 22:38:47');

DROP TABLE IF EXISTS `returns`;
CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`return_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `returns` VALUES ('1',NULL,'1','3','2','xyz','2026-08-25 09:26:09','55000.00');

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` VALUES ('1','Admin');
INSERT INTO `roles` VALUES ('2','Manager');
INSERT INTO `roles` VALUES ('3','Salesman');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sale_items` VALUES ('1','1','1','Samsung Galaxy A54','1','55000.00','55000.00');
INSERT INTO `sale_items` VALUES ('2','1','4','USB-C Cable 2m','1','900.00','900.00');
INSERT INTO `sale_items` VALUES ('3','2','5','Men T-Shirt','3','799.00','2397.00');
INSERT INTO `sale_items` VALUES ('4','2','6','Women Jeans','1','5500.00','5500.00');
INSERT INTO `sale_items` VALUES ('5','3','13','Cricket Bat','1','4000.00','4000.00');
INSERT INTO `sale_items` VALUES ('6','4','9','A4 Paper Ream','2','1100.00','2200.00');
INSERT INTO `sale_items` VALUES ('7','4','10','Ball Pen Box','1','900.00','900.00');
INSERT INTO `sale_items` VALUES ('8','5','11','Office Chair','1','12000.00','12000.00');
INSERT INTO `sale_items` VALUES ('9','6','1','Samsung Galaxy A54','5','55000.00','275000.00');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`sale_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sales` VALUES ('1','INV-00001','1','Ahmed Ali','3','55900.00','8.00','4472.00','0.00','60372.00','Cash','Paid',NULL,'2026-08-23 22:38:47');
INSERT INTO `sales` VALUES ('2','INV-00002','2','Sara Khan','3','7297.00','8.00','583.76','200.00','7680.76','Card','Paid',NULL,'2026-08-23 22:38:47');
INSERT INTO `sales` VALUES ('3','INV-00003','3','Muhammad Usman','3','4000.00','0.00','0.00','0.00','4000.00','Cash','Paid',NULL,'2026-08-23 22:38:47');
INSERT INTO `sales` VALUES ('4','INV-00004','4','Fatima Malik','3','3100.00','8.00','248.00','0.00','3348.00','Bank Transfer','Paid',NULL,'2026-08-23 22:38:47');
INSERT INTO `sales` VALUES ('5','INV-00005','5','Hassan Raza','3','12000.00','8.00','960.00','500.00','12460.00','Cash','Paid',NULL,'2026-08-23 22:38:47');
INSERT INTO `sales` VALUES ('6','INV-00006','6','Ayesha Siddiqui','3','275000.00','5.00','13750.00','0.00','288750.00','Cash','Paid','','2026-08-25 09:25:30');

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `settings` VALUES ('1','company_name','Margalla Super Store');
INSERT INTO `settings` VALUES ('2','company_email','info@margalla.com');
INSERT INTO `settings` VALUES ('3','company_phone','051-1234567');
INSERT INTO `settings` VALUES ('4','company_address','E-11, Islamabad');
INSERT INTO `settings` VALUES ('5','tax_rate','5');
INSERT INTO `settings` VALUES ('6','currency','PKR');
INSERT INTO `settings` VALUES ('7','low_stock_alert','10');
INSERT INTO `settings` VALUES ('8','company_logo','');

DROP TABLE IF EXISTS `stock_logs`;
CREATE TABLE `stock_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `type` enum('in','out','sale','return') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `stock_logs` VALUES ('1','1','3','sale','5','INV-00006',NULL,'2026-08-25 09:25:30');
INSERT INTO `stock_logs` VALUES ('2','1','3','return','2',NULL,'Return: xyz','2026-08-25 09:26:09');
INSERT INTO `stock_logs` VALUES ('3','5','1','in','100','','','2026-08-25 14:55:20');
INSERT INTO `stock_logs` VALUES ('4','5','1','out','45','','New branch shift','2026-08-25 14:55:45');

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(100) NOT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `suppliers` VALUES ('1','Tariq Electronics','Tariq Electronics Ltd','tariq@sup.com','03001111111','Hafeez Center, Lahore','active','2026-08-23 22:38:47');
INSERT INTO `suppliers` VALUES ('2','Fashion World','Fashion World Pvt Ltd','fashion@sup.com','03002222222','Anarkali, Lahore','active','2026-08-23 22:38:47');
INSERT INTO `suppliers` VALUES ('3','Grocery Plus','Grocery Plus Traders','grocery@sup.com','03003333333','Sabzi Mandi, Islamabad','active','2026-08-23 22:38:47');
INSERT INTO `suppliers` VALUES ('4','Office Mart','Office Mart Supplies','office@sup.com','03004444444','I-9, Islamabad','active','2026-08-23 22:38:47');
INSERT INTO `suppliers` VALUES ('5','Sports Hub','Sports Hub Pakistan','sports@sup.com','03005555555','MM Alam Road, Lahore','active','2026-08-23 22:38:47');

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
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES ('1','admin','admin@ims.com','admin123','Admin User','03001234567','1','active','2026-08-25 14:54:45','2026-08-23 22:38:47',NULL);
INSERT INTO `users` VALUES ('2','manager','manager@ims.com','password','Sales Manager','03009876543','2','active','2026-08-25 14:52:04','2026-08-23 22:38:47',NULL);
INSERT INTO `users` VALUES ('3','salesman','salesman@ims.com','password','Sales Person','03005678901','3','active','2026-08-25 14:54:09','2026-08-23 22:38:47',NULL);

