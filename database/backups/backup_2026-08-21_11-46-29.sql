-- IMS Backup 2026-08-21 11:46:29
-- Database: inventory_system

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_user_action` (`user_id`,`action`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `activity_logs` VALUES ('1','1','Login','User logged in','::1','2026-05-29 10:41:55');
INSERT INTO `activity_logs` VALUES ('2','1','Login','User logged in','::1','2026-05-30 20:47:21');
INSERT INTO `activity_logs` VALUES ('3','1','Backup','Database backed up: backup_2026-05-30_17-55-47.sql','::1','2026-05-30 20:55:47');
INSERT INTO `activity_logs` VALUES ('4','1','Update Settings','Settings updated','::1','2026-05-30 20:56:11');
INSERT INTO `activity_logs` VALUES ('5','1','Update Settings','Settings updated','::1','2026-05-30 20:56:26');
INSERT INTO `activity_logs` VALUES ('6','1','Logout','User logged out','::1','2026-05-30 21:02:44');
INSERT INTO `activity_logs` VALUES ('7','2','Login','User logged in','::1','2026-05-30 21:02:52');
INSERT INTO `activity_logs` VALUES ('8','1','Login','User logged in','::1','2026-08-08 12:25:42');
INSERT INTO `activity_logs` VALUES ('9','1','Edit Category','Updated ID: 8','::1','2026-08-08 12:26:26');
INSERT INTO `activity_logs` VALUES ('10','1','Add Category','Added: IT','::1','2026-08-08 12:26:42');
INSERT INTO `activity_logs` VALUES ('11','1','Backup','Database backed up: backup_2026-08-08_09-28-40.sql','::1','2026-08-08 12:28:40');
INSERT INTO `activity_logs` VALUES ('12','1','Logout','User logged out','::1','2026-08-08 12:29:02');
INSERT INTO `activity_logs` VALUES ('13','2','Login','User logged in','::1','2026-08-08 12:29:10');
INSERT INTO `activity_logs` VALUES ('14','2','Logout','User logged out','::1','2026-08-08 12:29:58');
INSERT INTO `activity_logs` VALUES ('15','3','Login','User logged in','::1','2026-08-08 12:30:09');
INSERT INTO `activity_logs` VALUES ('16','1','Login','User logged in','::1','2026-08-12 21:59:03');
INSERT INTO `activity_logs` VALUES ('17','1','Logout','User logged out','::1','2026-08-12 21:59:50');
INSERT INTO `activity_logs` VALUES ('18','3','Login','User logged in','::1','2026-08-12 22:00:02');
INSERT INTO `activity_logs` VALUES ('19','3','Create Invoice','Invoice: INV-00006 Total: 59400','0.0.0.0','2026-08-12 22:12:46');
INSERT INTO `activity_logs` VALUES ('20','3','Create Invoice','Invoice: INV-00007 Total: 12960','::1','2026-08-12 22:13:21');
INSERT INTO `activity_logs` VALUES ('21','3','Create Invoice','Invoice: INV-00008 Total: 16325','::1','2026-08-12 22:14:43');
INSERT INTO `activity_logs` VALUES ('22','3','Return','Returned 1 x Samsung Galaxy S23','0.0.0.0','2026-08-12 22:17:03');
INSERT INTO `activity_logs` VALUES ('23','3','Return','Returned 1 x Loreal Shampoo','::1','2026-08-12 22:17:47');
INSERT INTO `activity_logs` VALUES ('24','3','Create Invoice','Invoice: INV-00009 Total: 648000','::1','2026-08-12 22:19:14');
INSERT INTO `activity_logs` VALUES ('25','3','Return','Returned 2 x Samsung Galaxy S23','::1','2026-08-12 22:19:48');
INSERT INTO `activity_logs` VALUES ('26','3','Return','Returned 2 x Samsung Galaxy S23','0.0.0.0','2026-08-12 22:28:17');
INSERT INTO `activity_logs` VALUES ('27','3','Create Invoice','Invoice: INV-00010 Total: 518400','::1','2026-08-12 22:29:58');
INSERT INTO `activity_logs` VALUES ('28','3','Return','Returned 1 x Samsung Galaxy S23','::1','2026-08-12 22:30:40');
INSERT INTO `activity_logs` VALUES ('29','3','Create Invoice','Invoice: INV-00011 Total: 11000','::1','2026-08-12 22:32:53');
INSERT INTO `activity_logs` VALUES ('30','3','Return','Returned 2 x Dumbbells Set','::1','2026-08-12 22:33:39');
INSERT INTO `activity_logs` VALUES ('31','3','Logout','User logged out','::1','2026-08-12 22:40:21');
INSERT INTO `activity_logs` VALUES ('32','1','Login','User logged in','::1','2026-08-12 22:40:34');
INSERT INTO `activity_logs` VALUES ('33','1','Update Settings','Settings updated','::1','2026-08-12 22:58:11');
INSERT INTO `activity_logs` VALUES ('34','1','Logout','User logged out','::1','2026-08-12 22:59:38');
INSERT INTO `activity_logs` VALUES ('35','3','Login','User logged in','::1','2026-08-12 22:59:51');
INSERT INTO `activity_logs` VALUES ('36','3','Logout','User logged out','::1','2026-08-12 23:00:11');
INSERT INTO `activity_logs` VALUES ('37','1','Login','User logged in','::1','2026-08-12 23:00:20');
INSERT INTO `activity_logs` VALUES ('38','1','Stock In','Product ID 19 Qty: 5','::1','2026-08-12 23:09:02');
INSERT INTO `activity_logs` VALUES ('39','1','Edit Product','Updated: Loreal Shampoo','::1','2026-08-12 23:09:32');
INSERT INTO `activity_logs` VALUES ('40','1','Logout','User logged out','::1','2026-08-12 23:11:23');
INSERT INTO `activity_logs` VALUES ('41','1','Login','User logged in','::1','2026-08-12 23:11:30');
INSERT INTO `activity_logs` VALUES ('42','1','Logout','User logged out','::1','2026-08-12 23:12:43');
INSERT INTO `activity_logs` VALUES ('43','1','Login','User logged in','::1','2026-08-12 23:13:08');
INSERT INTO `activity_logs` VALUES ('44','1','Logout','User logged out','::1','2026-08-12 23:15:51');
INSERT INTO `activity_logs` VALUES ('45','1','Login','User logged in','::1','2026-08-12 23:15:52');
INSERT INTO `activity_logs` VALUES ('46','1','Login','User logged in','::1','2026-08-12 23:22:27');
INSERT INTO `activity_logs` VALUES ('47','1','Logout','User logged out','::1','2026-08-12 23:28:08');
INSERT INTO `activity_logs` VALUES ('48','2','Login','User logged in','::1','2026-08-12 23:28:20');
INSERT INTO `activity_logs` VALUES ('49','2','Stock In','Product ID 9 Qty: 3','::1','2026-08-12 23:29:48');
INSERT INTO `activity_logs` VALUES ('50','2','Add Supplier','Added: Electronics Hub','::1','2026-08-12 23:30:33');
INSERT INTO `activity_logs` VALUES ('51','2','Edit Category','Updated ID: 8','::1','2026-08-12 23:31:03');
INSERT INTO `activity_logs` VALUES ('52','2','Edit Category','Updated ID: 8','::1','2026-08-12 23:31:15');
INSERT INTO `activity_logs` VALUES ('53','2','Edit Category','Updated ID: 8','::1','2026-08-12 23:31:31');
INSERT INTO `activity_logs` VALUES ('54','2','Logout','User logged out','::1','2026-08-12 23:34:10');
INSERT INTO `activity_logs` VALUES ('55','3','Login','User logged in','::1','2026-08-12 23:34:16');
INSERT INTO `activity_logs` VALUES ('56','3','Logout','User logged out','::1','2026-08-12 23:34:48');
INSERT INTO `activity_logs` VALUES ('57','2','Login','User logged in','::1','2026-08-12 23:34:51');
INSERT INTO `activity_logs` VALUES ('58','2','Add Category','Added: spark','::1','2026-08-12 23:48:48');
INSERT INTO `activity_logs` VALUES ('59','2','Logout','User logged out','::1','2026-08-12 23:51:26');
INSERT INTO `activity_logs` VALUES ('60','2','Login','User logged in','::1','2026-08-12 23:51:31');
INSERT INTO `activity_logs` VALUES ('61','2','Update Profile','Updated profile details','0.0.0.0','2026-08-12 23:57:03');
INSERT INTO `activity_logs` VALUES ('62','2','Update Profile','Updated profile details','0.0.0.0','2026-08-12 23:57:31');
INSERT INTO `activity_logs` VALUES ('63','2','Update Profile','Updated profile details','::1','2026-08-12 23:59:05');
INSERT INTO `activity_logs` VALUES ('64','2','Update Profile','Updated profile details','::1','2026-08-13 00:03:46');
INSERT INTO `activity_logs` VALUES ('65','2','Logout','User logged out','::1','2026-08-13 00:04:18');
INSERT INTO `activity_logs` VALUES ('66','1','Login','User logged in','::1','2026-08-13 00:04:23');
INSERT INTO `activity_logs` VALUES ('67','1','Update Profile','Updated profile details','::1','2026-08-13 00:04:51');
INSERT INTO `activity_logs` VALUES ('68','1','Update Profile','Updated profile details','::1','2026-08-13 00:08:55');
INSERT INTO `activity_logs` VALUES ('69','1','Update Profile','Updated profile details','::1','2026-08-13 00:09:35');
INSERT INTO `activity_logs` VALUES ('70','1','Logout','User logged out','::1','2026-08-13 00:14:52');
INSERT INTO `activity_logs` VALUES ('71','1','Login','User logged in','::1','2026-08-13 00:15:58');
INSERT INTO `activity_logs` VALUES ('72','1','Logout','User logged out','::1','2026-08-13 00:16:42');
INSERT INTO `activity_logs` VALUES ('73','1','Login','User logged in','::1','2026-08-13 00:17:59');
INSERT INTO `activity_logs` VALUES ('74','1','Logout','User logged out','::1','2026-08-13 00:19:41');
INSERT INTO `activity_logs` VALUES ('75','1','Login','User logged in','::1','2026-08-13 00:30:24');
INSERT INTO `activity_logs` VALUES ('76','1','Backup','Database backed up: backup_2026-08-12_22-10-22.sql','::1','2026-08-13 01:10:22');
INSERT INTO `activity_logs` VALUES ('77','1','Backup','Database backed up: backup_2026-08-12_22-10-38.sql','::1','2026-08-13 01:10:38');
INSERT INTO `activity_logs` VALUES ('78','1','Stock Out','Product ID 15 Qty: 20','::1','2026-08-13 01:12:51');
INSERT INTO `activity_logs` VALUES ('79','1','Logout','User logged out','::1','2026-08-13 01:13:25');
INSERT INTO `activity_logs` VALUES ('80','3','Login','User logged in','::1','2026-08-13 01:13:35');
INSERT INTO `activity_logs` VALUES ('81','3','Logout','User logged out','::1','2026-08-13 01:14:25');
INSERT INTO `activity_logs` VALUES ('82','1','Login','User logged in','::1','2026-08-13 01:14:40');
INSERT INTO `activity_logs` VALUES ('83','1','Update Settings','Settings updated','::1','2026-08-13 01:15:09');
INSERT INTO `activity_logs` VALUES ('84','1','Update Settings','Settings updated','::1','2026-08-13 01:15:56');
INSERT INTO `activity_logs` VALUES ('85','1','Update Settings','Settings updated','::1','2026-08-13 01:16:16');
INSERT INTO `activity_logs` VALUES ('86','1','Logout','User logged out','::1','2026-08-13 01:17:22');
INSERT INTO `activity_logs` VALUES ('87','1','Login','User logged in','::1','2026-08-13 01:23:31');
INSERT INTO `activity_logs` VALUES ('88','1','Update Profile','Updated profile details','::1','2026-08-13 01:24:16');
INSERT INTO `activity_logs` VALUES ('89','1','Logout','User logged out','::1','2026-08-13 01:24:19');
INSERT INTO `activity_logs` VALUES ('90','1','Login','User logged in','::1','2026-08-13 01:24:56');
INSERT INTO `activity_logs` VALUES ('91','1','Logout','User logged out','::1','2026-08-13 01:29:47');
INSERT INTO `activity_logs` VALUES ('92','1','Login','User logged in','::1','2026-08-13 01:29:53');
INSERT INTO `activity_logs` VALUES ('93','1','Edit Product','Updated: Samsung Galaxy S23','::1','2026-08-13 01:31:08');
INSERT INTO `activity_logs` VALUES ('94','1','Logout','User logged out','::1','2026-08-13 01:31:51');
INSERT INTO `activity_logs` VALUES ('95','1','Login','User logged in','::1','2026-08-13 12:38:29');
INSERT INTO `activity_logs` VALUES ('96','1','Logout','User logged out','::1','2026-08-13 12:38:54');
INSERT INTO `activity_logs` VALUES ('97','3','Login','User logged in','::1','2026-08-13 12:39:03');
INSERT INTO `activity_logs` VALUES ('98','3','Logout','User logged out','::1','2026-08-13 12:39:22');
INSERT INTO `activity_logs` VALUES ('99','2','Login','User logged in','::1','2026-08-13 12:39:29');
INSERT INTO `activity_logs` VALUES ('100','2','Logout','User logged out','::1','2026-08-13 12:39:49');
INSERT INTO `activity_logs` VALUES ('101','1','Login','User logged in','::1','2026-08-13 22:40:12');
INSERT INTO `activity_logs` VALUES ('102','1','Logout','User logged out','::1','2026-08-13 22:59:21');
INSERT INTO `activity_logs` VALUES ('103','3','Login','User logged in','::1','2026-08-13 22:59:32');
INSERT INTO `activity_logs` VALUES ('104','3','Logout','User logged out','::1','2026-08-13 22:59:42');
INSERT INTO `activity_logs` VALUES ('105','2','Login','User logged in','::1','2026-08-13 22:59:50');
INSERT INTO `activity_logs` VALUES ('106','2','Logout','User logged out','::1','2026-08-13 23:09:30');
INSERT INTO `activity_logs` VALUES ('107','3','Login','User logged in','::1','2026-08-13 23:09:38');
INSERT INTO `activity_logs` VALUES ('108','3','Logout','User logged out','::1','2026-08-13 23:14:21');
INSERT INTO `activity_logs` VALUES ('109','1','Login','User logged in','::1','2026-08-13 23:14:27');
INSERT INTO `activity_logs` VALUES ('110','1','Login','User logged in','::1','2026-08-21 14:14:38');
INSERT INTO `activity_logs` VALUES ('111','1','Logout','User logged out','::1','2026-08-21 14:17:39');
INSERT INTO `activity_logs` VALUES ('112','2','Login','User logged in','::1','2026-08-21 14:17:47');
INSERT INTO `activity_logs` VALUES ('113','2','Logout','User logged out','::1','2026-08-21 14:17:53');
INSERT INTO `activity_logs` VALUES ('114','1','Login','User logged in','::1','2026-08-21 14:39:50');
INSERT INTO `activity_logs` VALUES ('115','1','Stock In','Product ID 4 Qty: 10','::1','2026-08-21 14:40:51');
INSERT INTO `activity_logs` VALUES ('116','1','Stock In','Product ID 1 Qty: 9','::1','2026-08-21 14:41:26');

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` VALUES ('1','Electronics','Electronic devices and accessories','2026-05-29 10:31:28','active');
INSERT INTO `categories` VALUES ('2','Clothing','Apparel and fashion items','2026-05-29 10:31:28','active');
INSERT INTO `categories` VALUES ('3','Food & Beverages','Food products and drinks','2026-05-29 10:31:28','active');
INSERT INTO `categories` VALUES ('4','Home & Kitchen','Home appliances and kitchen items','2026-05-29 10:31:28','active');
INSERT INTO `categories` VALUES ('5','Books & Stationery','Books, notebooks, and office supplies','2026-05-29 10:31:28','active');
INSERT INTO `categories` VALUES ('6','Toys & Games','Children toys and games','2026-05-29 10:31:28','active');
INSERT INTO `categories` VALUES ('7','Sports & Fitness','Sports equipment and fitness items','2026-05-29 10:31:28','active');
INSERT INTO `categories` VALUES ('8','Beauty Health','Cosmetics and health products by','2026-05-29 10:31:28','active');
INSERT INTO `categories` VALUES ('9','IT','Web dev','2026-08-08 12:26:42','active');
INSERT INTO `categories` VALUES ('10','spark','','2026-08-12 23:48:48','active');

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`customer_id`),
  KEY `idx_customer_name` (`customer_name`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `customers` VALUES ('1','Ahmed Ali','ahmed@email.com','0300-1111111','House 123, Street 45, F-7','Islamabad','0.00','2026-05-29 10:31:28','2026-05-29 10:31:28','active');
INSERT INTO `customers` VALUES ('2','Sarah Khan','sarah@email.com','0300-2222222','Flat 456, Block B, Gulberg','Lahore','0.00','2026-05-29 10:31:28','2026-05-29 10:31:28','active');
INSERT INTO `customers` VALUES ('3','Bilal Ahmed','bilal@email.com','0300-3333333','Shop 78, Main Market','Karachi','0.00','2026-05-29 10:31:28','2026-05-29 10:31:28','active');
INSERT INTO `customers` VALUES ('4','Fatima Hassan','fatima@email.com','0300-4444444','House 90, Sector G-10','Islamabad','0.00','2026-05-29 10:31:28','2026-05-29 10:31:28','active');
INSERT INTO `customers` VALUES ('5','Usman Tariq','usman@email.com','0300-5555555','Plaza 234, Blue Area','Islamabad','0.00','2026-05-29 10:31:28','2026-05-29 10:31:28','active');
INSERT INTO `customers` VALUES ('6','Ayesha Malik','ayesha@email.com','0300-6666666','Apartment 12, DHA','Karachi','0.00','2026-05-29 10:31:28','2026-05-29 10:31:28','active');
INSERT INTO `customers` VALUES ('7','Hassan Raza','hassan@email.com','0300-7777777','House 567, Model Town','Lahore','0.00','2026-05-29 10:31:28','2026-05-29 10:31:28','active');
INSERT INTO `customers` VALUES ('8','Zainab Hussain','zainab@email.com','0300-8888888','Flat 89, Bahria Town','Rawalpindi','0.00','2026-05-29 10:31:28','2026-05-29 10:31:28','active');

DROP TABLE IF EXISTS `low_stock_products`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `low_stock_products` AS select `p`.`product_id` AS `product_id`,`p`.`product_name` AS `product_name`,`c`.`category_name` AS `category_name`,`p`.`quantity` AS `quantity`,`p`.`min_quantity` AS `min_quantity`,`p`.`selling_price` AS `selling_price` from (`products` `p` join `categories` `c` on(`p`.`category_id` = `c`.`category_id`)) where `p`.`quantity` <= `p`.`min_quantity` and `p`.`status` = 'active' order by `p`.`quantity`;

INSERT INTO `low_stock_products` VALUES ('15','LEGO City Set','Toys & Games','5','8','12000.00');

DROP TABLE IF EXISTS `product_sales_stats`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_sales_stats` AS select `p`.`product_id` AS `product_id`,`p`.`product_name` AS `product_name`,`c`.`category_name` AS `category_name`,sum(`si`.`quantity`) AS `total_sold`,sum(`si`.`subtotal`) AS `total_revenue` from ((`products` `p` join `categories` `c` on(`p`.`category_id` = `c`.`category_id`)) left join `sale_items` `si` on(`p`.`product_id` = `si`.`product_id`)) group by `p`.`product_id`,`p`.`product_name`,`c`.`category_name` order by sum(`si`.`quantity`) desc;

INSERT INTO `product_sales_stats` VALUES ('1','Samsung Galaxy S23','Electronics','11','1255000.00');
INSERT INTO `product_sales_stats` VALUES ('17','Dumbbells Set','Sports & Fitness','2','11000.00');
INSERT INTO `product_sales_stats` VALUES ('6','Nike Air Max','Clothing','2','24000.00');
INSERT INTO `product_sales_stats` VALUES ('12','Philips Air Fryer','Home & Kitchen','1','16500.00');
INSERT INTO `product_sales_stats` VALUES ('11','Samsung LED TV 55\"','Home & Kitchen','1','89000.00');
INSERT INTO `product_sales_stats` VALUES ('4','MacBook Air M2','Electronics','1','165000.00');
INSERT INTO `product_sales_stats` VALUES ('3','Sony WH-1000XM5','Electronics','1','35000.00');
INSERT INTO `product_sales_stats` VALUES ('18','Yoga Mat','Sports & Fitness',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('8','Levi\'s Jeans','Clothing',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('7','Adidas T-Shirt','Clothing',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('14','Notebook A4','Books & Stationery',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('10','Nestle Coffee','Food & Beverages',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('13','Harry Potter Book Set','Books & Stationery',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('9','Coca Cola 1.5L','Food & Beverages',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('20','Nivea Face Cream','Beauty Health',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('5','Dell XPS 15','Electronics',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('19','Loreal Shampoo','Beauty Health',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('16','PS5 Console','Toys & Games',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('2','iPhone 14 Pro','Electronics',NULL,NULL);
INSERT INTO `product_sales_stats` VALUES ('15','LEGO City Set','Toys & Games',NULL,NULL);

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(200) NOT NULL,
  `category_id` int(11) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_quantity` int(11) DEFAULT 10,
  `description` text DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `category_id` (`category_id`),
  KEY `idx_product_name` (`product_name`),
  KEY `idx_barcode` (`barcode`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` VALUES ('1','Samsung Galaxy S23','1','Samsung','8801234567890','85000.00','120000.00','30','5','0','6a7cd80cd8b52_1786566668.png','active','2026-05-29 10:31:28','2026-08-21 14:41:26');
INSERT INTO `products` VALUES ('2','iPhone 14 Pro','1','Apple','8801234567891','150000.00','195000.00','15','5','Apple iPhone 14 Pro 256GB',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');
INSERT INTO `products` VALUES ('3','Sony WH-1000XM5','1','Sony','8801234567892','25000.00','35000.00','30','10','Premium noise-cancelling headphones',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');
INSERT INTO `products` VALUES ('4','MacBook Air M2','1','Apple','8801234567893','120000.00','165000.00','20','3','Apple MacBook Air with M2 chip',NULL,'active','2026-05-29 10:31:28','2026-08-21 14:40:51');
INSERT INTO `products` VALUES ('5','Dell XPS 15','1','Dell','8801234567894','140000.00','185000.00','8','3','Dell XPS 15 laptop',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');
INSERT INTO `products` VALUES ('6','Nike Air Max','2','Nike','8801234567895','8000.00','12000.00','49','15','Nike running shoes',NULL,'active','2026-05-29 10:31:28','2026-08-12 22:13:21');
INSERT INTO `products` VALUES ('7','Adidas T-Shirt','2','Adidas','8801234567896','1500.00','2500.00','100','20','Sports t-shirt',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');
INSERT INTO `products` VALUES ('8','Levi\'s Jeans','2','Levi\'s','8801234567897','3500.00','6000.00','60','15','Classic denim jeans',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');
INSERT INTO `products` VALUES ('9','Coca Cola 1.5L','3','Coca Cola','8801234567898','80.00','120.00','203','50','Coca Cola soft drink',NULL,'active','2026-05-29 10:31:28','2026-08-12 23:29:48');
INSERT INTO `products` VALUES ('10','Nestle Coffee','3','Nestle','8801234567899','450.00','650.00','80','20','Instant coffee',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');
INSERT INTO `products` VALUES ('11','Samsung LED TV 55\"','4','Samsung','8801234567900','65000.00','89000.00','12','5','55 inch Smart LED TV',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');
INSERT INTO `products` VALUES ('12','Philips Air Fryer','4','Philips','8801234567901','12000.00','16500.00','19','8','Digital air fryer 4.1L',NULL,'active','2026-05-29 10:31:28','2026-08-12 22:14:43');
INSERT INTO `products` VALUES ('13','Harry Potter Book Set','5','Bloomsbury','8801234567902','3500.00','5500.00','40','10','Complete Harry Potter series',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');
INSERT INTO `products` VALUES ('14','Notebook A4','5','Local','8801234567903','100.00','180.00','500','100','A4 size notebook',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');
INSERT INTO `products` VALUES ('15','LEGO City Set','6','LEGO','8801234567904','8000.00','12000.00','5','8','LEGO building blocks set',NULL,'active','2026-05-29 10:31:28','2026-08-13 01:12:51');
INSERT INTO `products` VALUES ('16','PS5 Console','6','Sony','8801234567905','75000.00','105000.00','8','3','PlayStation 5 gaming console',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');
INSERT INTO `products` VALUES ('17','Dumbbells Set','7','PowerMax','8801234567906','3500.00','5500.00','35','10','Adjustable dumbbells 20kg',NULL,'active','2026-05-29 10:31:28','2026-08-12 22:33:39');
INSERT INTO `products` VALUES ('18','Yoga Mat','7','FitPro','8801234567907','800.00','1500.00','60','15','Premium yoga mat',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');
INSERT INTO `products` VALUES ('19','Loreal Shampoo','8','Loreal','8801234567908','450.00','750.00','126','30','0','6a7cb6dcba1ce_1786558172.png','active','2026-05-29 10:31:28','2026-08-12 23:09:32');
INSERT INTO `products` VALUES ('20','Nivea Face Cream','8','Nivea','8801234567909','600.00','950.00','90','25','Moisturizing face cream',NULL,'active','2026-05-29 10:31:28','2026-05-29 10:31:28');

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
  `purchase_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `purchase_date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`purchase_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_purchase_date` (`purchase_date`),
  CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  CONSTRAINT `purchases_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `returns`;
CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `return_date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`return_id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_return_date` (`return_date`),
  CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  CONSTRAINT `returns_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `returns` VALUES ('1',NULL,'1','1','Automated Verification Return','0000-00-00','3','2026-08-12 22:17:03','0.00');
INSERT INTO `returns` VALUES ('2',NULL,'19','1','not good','0000-00-00','3','2026-08-12 22:17:47','0.00');
INSERT INTO `returns` VALUES ('3',NULL,'1','2','mind chnaged','0000-00-00','3','2026-08-12 22:19:48','0.00');
INSERT INTO `returns` VALUES ('4',NULL,'1','2','Customer return test','0000-00-00','3','2026-08-12 22:28:17','120000.00');
INSERT INTO `returns` VALUES ('5',NULL,'1','1','mind','0000-00-00','3','2026-08-12 22:30:40','120000.00');
INSERT INTO `returns` VALUES ('6',NULL,'17','2','expired','0000-00-00','3','2026-08-12 22:33:39','5500.00');

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` VALUES ('1','Admin','Full system access','2026-05-29 10:31:28');
INSERT INTO `roles` VALUES ('2','Manager','Inventory and sales management','2026-05-29 10:31:28');
INSERT INTO `roles` VALUES ('3','Salesman','Sales and invoice management only','2026-05-29 10:31:28');

DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE `sale_items` (
  `sale_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`sale_item_id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sale_items` VALUES ('1','1','1','Samsung Galaxy S23','1','120000.00','120000.00');
INSERT INTO `sale_items` VALUES ('2','2','3','Sony WH-1000XM5','1','35000.00','35000.00');
INSERT INTO `sale_items` VALUES ('3','3','6','Nike Air Max','1','12000.00','12000.00');
INSERT INTO `sale_items` VALUES ('4','4','4','MacBook Air M2','1','165000.00','165000.00');
INSERT INTO `sale_items` VALUES ('5','5','11','Samsung LED TV 55\"','1','89000.00','89000.00');
INSERT INTO `sale_items` VALUES ('6','6','1','Samsung Galaxy A54','1','55000.00','55000.00');
INSERT INTO `sale_items` VALUES ('7','7','6','Nike Air Max','1','12000.00','12000.00');
INSERT INTO `sale_items` VALUES ('8','8','12','Philips Air Fryer','1','16500.00','16500.00');
INSERT INTO `sale_items` VALUES ('9','9','1','Samsung Galaxy S23','5','120000.00','600000.00');
INSERT INTO `sale_items` VALUES ('10','10','1','Samsung Galaxy S23','4','120000.00','480000.00');
INSERT INTO `sale_items` VALUES ('11','11','17','Dumbbells Set','2','5500.00','11000.00');

DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Card','Bank Transfer','Cheque') DEFAULT 'Cash',
  `payment_status` enum('Paid','Pending','Partial') DEFAULT 'Paid',
  `sale_date` date NOT NULL,
  `sale_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`sale_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_invoice` (`invoice_number`),
  KEY `idx_sale_date` (`sale_date`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL,
  CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sales` VALUES ('1','INV-20240501-001','1','Ahmed Ali','3','120000.00','9600.00','5000.00','124600.00','Card','Paid','2024-05-01','10:30:00',NULL,'2026-05-29 10:31:28','0.00','0.00','0.00');
INSERT INTO `sales` VALUES ('2','INV-20240501-002','2','Sarah Khan','3','35000.00','2800.00','1000.00','36800.00','Cash','Paid','2024-05-01','14:20:00',NULL,'2026-05-29 10:31:28','0.00','0.00','0.00');
INSERT INTO `sales` VALUES ('3','INV-20240502-003','3','Bilal Ahmed','3','12000.00','960.00','500.00','12460.00','Cash','Paid','2024-05-02','11:15:00',NULL,'2026-05-29 10:31:28','0.00','0.00','0.00');
INSERT INTO `sales` VALUES ('4','INV-20240503-004','4','Fatima Hassan','3','165000.00','13200.00','8000.00','170200.00','','Paid','2024-05-03','15:45:00',NULL,'2026-05-29 10:31:28','0.00','0.00','0.00');
INSERT INTO `sales` VALUES ('5','INV-20240504-005','5','Usman Tariq','3','89000.00','7120.00','3000.00','93120.00','Card','Paid','2024-05-04','09:30:00',NULL,'2026-05-29 10:31:28','0.00','0.00','0.00');
INSERT INTO `sales` VALUES ('6','INV-00006','1','Ahmed Ali','3','55000.00','4400.00','0.00','0.00','Cash','Paid','0000-00-00','00:00:00','Automated Verification Notes','2026-08-12 22:12:46','59400.00','8.00','0.00');
INSERT INTO `sales` VALUES ('7','INV-00007','6','Ayesha Malik','3','12000.00','960.00','0.00','0.00','Card','Paid','0000-00-00','00:00:00','','2026-08-12 22:13:21','12960.00','8.00','0.00');
INSERT INTO `sales` VALUES ('8','INV-00008','3','Bilal Ahmed','3','16500.00','825.00','0.00','0.00','Cash','Paid','0000-00-00','00:00:00','','2026-08-12 22:14:42','16325.00','5.00','1000.00');
INSERT INTO `sales` VALUES ('9','INV-00009','2','Sarah Khan','3','600000.00','48000.00','0.00','0.00','Cash','Paid','0000-00-00','00:00:00','','2026-08-12 22:19:14','648000.00','8.00','0.00');
INSERT INTO `sales` VALUES ('10','INV-00010',NULL,'Ahmed Ali','3','480000.00','38400.00','0.00','0.00','Cash','Paid','0000-00-00','00:00:00','','2026-08-12 22:29:58','518400.00','8.00','0.00');
INSERT INTO `sales` VALUES ('11','INV-00011','7','Hassan Raza','3','11000.00','0.00','0.00','0.00','Cash','Paid','0000-00-00','00:00:00','','2026-08-12 22:32:53','11000.00','0.00','0.00');

DROP TABLE IF EXISTS `sales_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `sales_summary` AS select cast(`sales`.`sale_date` as date) AS `date`,count(0) AS `total_sales`,sum(`sales`.`grand_total`) AS `total_revenue`,sum(`sales`.`grand_total` - `sales`.`subtotal` + `sales`.`tax_amount`) AS `total_profit` from `sales` group by cast(`sales`.`sale_date` as date) order by cast(`sales`.`sale_date` as date) desc;

INSERT INTO `sales_summary` VALUES ('2024-05-04','1','93120.00','11240.00');
INSERT INTO `sales_summary` VALUES ('2024-05-03','1','170200.00','18400.00');
INSERT INTO `sales_summary` VALUES ('2024-05-02','1','12460.00','1420.00');
INSERT INTO `sales_summary` VALUES ('2024-05-01','2','161400.00','18800.00');
INSERT INTO `sales_summary` VALUES (NULL,'6','0.00','-1081915.00');

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `settings` VALUES ('1','company_name','Margalla Super Store','2026-08-13 01:16:16');
INSERT INTO `settings` VALUES ('2','company_email','info@margalla.com','2026-08-12 22:58:11');
INSERT INTO `settings` VALUES ('3','company_phone','051-1234567','2026-05-29 10:31:28');
INSERT INTO `settings` VALUES ('4','company_address','E-11/4, Street# 16, Islamabad','2026-08-12 22:58:11');
INSERT INTO `settings` VALUES ('5','tax_rate','2','2026-08-12 22:58:11');
INSERT INTO `settings` VALUES ('6','currency','PKR','2026-05-29 10:31:28');
INSERT INTO `settings` VALUES ('7','low_stock_alert','10','2026-05-29 10:31:28');

DROP TABLE IF EXISTS `stock_logs`;
CREATE TABLE `stock_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `type` enum('in','out','sale','return') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_product_action` (`product_id`,`type`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `stock_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  CONSTRAINT `stock_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `stock_logs` VALUES ('1','1','','30','PO-001','Initial stock purchase','2','2026-05-29 10:31:28');
INSERT INTO `stock_logs` VALUES ('2','1','sale','1','INV-20240501-001','Sold to Ahmed Ali','3','2026-05-29 10:31:28');
INSERT INTO `stock_logs` VALUES ('3','3','','40','PO-002','Initial stock purchase','2','2026-05-29 10:31:28');
INSERT INTO `stock_logs` VALUES ('4','3','sale','1','INV-20240501-002','Sold to Sarah Khan','3','2026-05-29 10:31:28');
INSERT INTO `stock_logs` VALUES ('5','6','','60','PO-003','Initial stock purchase','2','2026-05-29 10:31:28');
INSERT INTO `stock_logs` VALUES ('6','6','sale','1','INV-20240502-003','Sold to Bilal Ahmed','3','2026-05-29 10:31:28');
INSERT INTO `stock_logs` VALUES ('7','1','sale','1','INV-00006',NULL,'3','2026-08-12 22:12:46');
INSERT INTO `stock_logs` VALUES ('8','6','sale','1','INV-00007',NULL,'3','2026-08-12 22:13:21');
INSERT INTO `stock_logs` VALUES ('9','12','sale','1','INV-00008',NULL,'3','2026-08-12 22:14:43');
INSERT INTO `stock_logs` VALUES ('10','1','return','1',NULL,'Return: Automated Verification Return','3','2026-08-12 22:17:03');
INSERT INTO `stock_logs` VALUES ('11','19','return','1',NULL,'Return: not good','3','2026-08-12 22:17:47');
INSERT INTO `stock_logs` VALUES ('12','1','sale','5','INV-00009',NULL,'3','2026-08-12 22:19:14');
INSERT INTO `stock_logs` VALUES ('13','1','return','2',NULL,'Return: mind chnaged','3','2026-08-12 22:19:48');
INSERT INTO `stock_logs` VALUES ('14','1','return','2',NULL,'Return: Customer return test','3','2026-08-12 22:28:17');
INSERT INTO `stock_logs` VALUES ('15','1','sale','4','INV-00010',NULL,'3','2026-08-12 22:29:58');
INSERT INTO `stock_logs` VALUES ('16','1','return','1',NULL,'Return: mind','3','2026-08-12 22:30:40');
INSERT INTO `stock_logs` VALUES ('17','17','sale','2','INV-00011',NULL,'3','2026-08-12 22:32:53');
INSERT INTO `stock_logs` VALUES ('18','17','return','2',NULL,'Return: expired','3','2026-08-12 22:33:39');
INSERT INTO `stock_logs` VALUES ('19','19','in','5','','','1','2026-08-12 23:09:02');
INSERT INTO `stock_logs` VALUES ('20','9','in','3','','','2','2026-08-12 23:29:48');
INSERT INTO `stock_logs` VALUES ('21','15','out','20','','','1','2026-08-13 01:12:51');
INSERT INTO `stock_logs` VALUES ('22','4','in','10','','','1','2026-08-21 14:40:51');
INSERT INTO `stock_logs` VALUES ('23','1','in','9','','','1','2026-08-21 14:41:26');

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(100) NOT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`supplier_id`),
  KEY `idx_supplier_name` (`supplier_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `suppliers` VALUES ('1','Tech Distributors','Tech Distributors Pvt Ltd','info@techdist.com','051-1111111','Plaza 123, I-9','Islamabad','2026-05-29 10:31:28','2026-05-29 10:31:28','active');
INSERT INTO `suppliers` VALUES ('2','Fashion Wholesale','Fashion Wholesale Co','sales@fashionwholesale.com','042-2222222','Market Road, Gulberg','Lahore','2026-05-29 10:31:28','2026-05-29 10:31:28','active');
INSERT INTO `suppliers` VALUES ('3','Food Suppliers Inc','Food Suppliers Inc','contact@foodsuppliers.com','021-3333333','Warehouse 45, SITE','Karachi','2026-05-29 10:31:28','2026-05-29 10:31:28','active');
INSERT INTO `suppliers` VALUES ('5','Home Essentials','Home Essentials Traders','info@homeessentials.com','042-5555555','Johar Town Market','Lahore','2026-05-29 10:31:28','2026-05-29 10:31:28','active');
INSERT INTO `suppliers` VALUES ('6','Electronics Hub','Electronics Hub Ltd','orders@electronicshub.com','051-4444444','',NULL,'2026-08-12 23:30:33','2026-08-12 23:30:33','active');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  FULLTEXT KEY `password` (`password`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES ('1','admin','admin@inventory.com','$2y$10$TZUklBusM1KLVOI2CaIoqeayQe/Jz1fiw6dRA7tiCL0PBolDhM6fa','SUFYAN','0300-1234567','1','active','2026-08-21 14:39:50','2026-05-29 10:31:28','2026-08-21 14:39:50','6a7cc3d381406_1786561491.jpeg');
INSERT INTO `users` VALUES ('2','manager','manager_updated@store.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Manager','0300-1234567','2','active','2026-08-21 14:17:47','2026-05-29 10:31:28','2026-08-21 14:17:47','6a7cc3929e613_1786561426.png');
INSERT INTO `users` VALUES ('3','salesman','salesman@inventory.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Sales Person','0300-3456789','3','active','2026-08-13 23:09:38','2026-05-29 10:31:28','2026-08-13 23:09:38',NULL);

