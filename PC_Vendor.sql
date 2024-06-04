-- MySQL dump 10.13  Distrib 8.0.34, for Win64 (x86_64)
--
-- Host: localhost    Database: pc_vendor
-- ------------------------------------------------------
-- Server version	8.0.34

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `Category_ID` int NOT NULL AUTO_INCREMENT,
  `Category_Name` varchar(100) DEFAULT 'Unspecified',
  `Category_Desc` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`Category_ID`),
  UNIQUE KEY `UC_Category_Name` (`Category_Name`),
  UNIQUE KEY `Category_Name` (`Category_Name`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Unspecified','Warning : This column must not be deleted or changed.'),(14,'PC Gamer','PC Gamer'),(15,'Laptop','aeaeae'),(16,'Component','Component'),(17,'Devices',''),(18,'Chairs & Desks',''),(19,'Network',''),(26,'Image & Sound','Image & Sound');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `manufacturers`
--

DROP TABLE IF EXISTS `manufacturers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `manufacturers` (
  `Manufacturer_ID` int NOT NULL AUTO_INCREMENT,
  `Manufacturer_Name` varchar(100) DEFAULT 'Unspecified',
  `Manufacturer_Desc` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`Manufacturer_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manufacturers`
--

LOCK TABLES `manufacturers` WRITE;
/*!40000 ALTER TABLE `manufacturers` DISABLE KEYS */;
INSERT INTO `manufacturers` VALUES (1,'Unspecified','Warning : This column must not be deleted or changed.'),(2,'NVIDIA','Nvidia Corporation is an American multinational corporation and technology company headquartered in Santa Clara, California, and incorporated in Delaware.It is a software and fabless company which designs and supplies graphics processing units (GPUs), application programming interfaces (APIs) for data science and high-performance computing as well as system on a chip units (SoCs) for the mobile computing and automotive market. Nvidia is also a dominant supplier of artificial intelligence (AI) hardware and software.'),(5,'AMD','AMD stuff                    '),(7,'Gigabyte','GigabyteGigabyteGigabyte'),(8,'Intel','Intel'),(9,'MSI','MSI'),(10,'Aerocool','Aerocool'),(11,'Connect','Connect'),(12,'Corsair','Corsair'),(13,'Samsung','Samsung');
/*!40000 ALTER TABLE `manufacturers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orderitems`
--

DROP TABLE IF EXISTS `orderitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orderitems` (
  `OrderItem_ID` int NOT NULL AUTO_INCREMENT,
  `Order_ID` int DEFAULT NULL,
  `Product_ID` int DEFAULT NULL,
  `OrderItem_Quantity` int DEFAULT NULL,
  `OrderItem_UnitPrice` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`OrderItem_ID`),
  KEY `Order_ID` (`Order_ID`),
  KEY `Product_ID` (`Product_ID`),
  CONSTRAINT `orderitems_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `orders` (`Order_ID`),
  CONSTRAINT `orderitems_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`Product_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orderitems`
--

LOCK TABLES `orderitems` WRITE;
/*!40000 ALTER TABLE `orderitems` DISABLE KEYS */;
/*!40000 ALTER TABLE `orderitems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `Order_ID` int NOT NULL AUTO_INCREMENT,
  `Order_Date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Order_TotalAmount` decimal(10,2) DEFAULT NULL,
  `Order_ShippingAddress` varchar(255) DEFAULT NULL,
  `Order_Status` varchar(255) DEFAULT 'Pending',
  `Order_PaymentMethod` varchar(255) DEFAULT NULL,
  `Order_PhoneNumber` varchar(255) DEFAULT NULL,
  `Order_Notes` text,
  `User_ID` int DEFAULT NULL,
  PRIMARY KEY (`Order_ID`),
  KEY `User_ID` (`User_ID`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `Product_ID` int NOT NULL AUTO_INCREMENT,
  `Product_Name` varchar(255) DEFAULT NULL,
  `Category_ID` int DEFAULT NULL,
  `SubCategory_ID` int DEFAULT NULL,
  `Product_Desc` varchar(2000) DEFAULT NULL,
  `Manufacturer_ID` int DEFAULT NULL,
  `Buying_Price` decimal(10,2) DEFAULT NULL,
  `Selling_Price` decimal(10,2) DEFAULT NULL,
  `Product_Quantity` int DEFAULT NULL,
  `Product_Visibility` enum('Visible','Invisible') NOT NULL DEFAULT 'Visible',
  `Date_Created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Product_Picture` varchar(255) DEFAULT NULL,
  `Discount_Price` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`Product_ID`),
  KEY `Category_ID` (`Category_ID`),
  KEY `SubCategory_ID` (`SubCategory_ID`),
  KEY `Manufacturer_ID` (`Manufacturer_ID`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `categories` (`Category_ID`),
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`SubCategory_ID`) REFERENCES `subcategories` (`SubCategory_ID`),
  CONSTRAINT `products_ibfk_3` FOREIGN KEY (`Manufacturer_ID`) REFERENCES `manufacturers` (`Manufacturer_ID`),
  CONSTRAINT `chk_buying_price` CHECK ((`Buying_Price` <= `Selling_Price`))
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (16,'MSI GeForce RTX 4060 VENTUS 2X BLACK OC 8GB GDDR6',16,42,'Based on NVIDIA\'s Ada Lovelace architecture, the MSI GeForce RTX 4060 VENTUS 2X BLACK 8G OC graphics card utilizes DLSS 3 technology and hardware ray tracing to enhance the latest games, providing you with an immersive and realistic gaming experience. Beyond gaming, the NVIDIA GeForce RTX 4060 graphics cards offer high performance for creating and streaming.',2,3999.00,8000.00,38,'Visible','2024-05-13 23:09:09','Product_Pictures/665a77d417b91_.jpg',4000.00),(17,'PC Gamer UltraPC Core i5 12400F/512GB SSD/16GB/RX6600',14,51,'Affordable Gaming PC, the UltraPC Core i5 12400F/512GB SSD/16GB/RX6600 will be a great ally for playing the latest games without emptying your bank account. Particularly efficient, this gaming computer will allow you to get to the point. It is equipped with an Intel 6-Core processor from the 12th generation (Intel Alder Lake), an H610M motherboard, 16 GB of DDR4 RAM and an AMD RADEON RX 6600 8Go GDDR6 graphics card. It also has a 512Go NVMe SSD disk.',1,6790.00,7590.00,19,'Visible','2024-05-16 23:56:51','Product_Pictures/pc-gamer-ultrapc-core-i5-12400f-512gb-ssd-16gb-rx6600.png',0.00),(22,'PC Gamer UltraPC Ryzen 7 5700X/512GB SSD/16GB/RTX4060',14,52,'Trust the PC Gamer UltraPC Ryzen 7 5700X/512GB SSD/16GB/RTX4060 to guide you through your first steps in the world of video games. Simple and efficient, this computer will allow you to get to the essentials while keeping costs minimal. It features an AMD Ryzen 7 5700X 8-core processor, 16GB of DDR4 RAM, a 512GB SSD, and the NVIDIA GeForce RTX 4060 graphics card with 8GB of video memory, which are at the heart of this exceptional performance-to-price ratio system.',1,5799.00,9699.00,9,'Visible','2024-05-22 23:46:42','Product_Pictures/pc-gamer-ultrapc-ryzen-7-5700x-512gb-ssd-16gb-rtx4060.jpg',0.00),(23,'PC Gamer UltraPC Core i9 14900K/4TB SSD/64GB DDR5/RTX4090',14,53,'The PC Gamer UltraPC Core i9 14900K/4TB SSD/64GB DDR5/RTX4090 24GB is configured to offer the best performance in the latest games. It will accompany you no matter what your desires are. With a 14th generation Intel Core i9 processor, 64GB of DDR5 memory, and a 4TB NVMe SSD system disk, the PC Gamer UPC-I9-14900K-RTX4090 leaves nothing to chance and will allow you to play your favorite PC Hits in excellent resolution and frame rate conditions.',1,49749.00,52249.00,2,'Visible','2024-05-25 19:41:27','Product_Pictures/pc-gamer-ultrapc-core-i9-14900k-4tb-ssd-64gb-ddr5-rtx4090.png',0.00),(24,'Gigabyte G5 KF5 i5 13500H/16GB/1TB SSD/RTX4060 8GB/15.4\'\' 144Hz',15,54,'Gain comfort and play in excellent conditions with the Gigabyte G5 laptop! With ultra-performing components, a 144Hz IPS screen, a backlit gaming keyboard, and Nahimic audio system, it has everything you need to offer a high-quality mobile gaming experience. The Gigabyte G5 KF5 laptop offers high performance and fast operation thanks to its Intel Core i5-13500H processor, 16GB of DDR4 memory, a 1TB M.2 PCIe SSD, and an NVIDIA GeForce RTX 4060 graphics card with 8GB of dedicated memory.',7,12490.00,13490.00,19,'Visible','2024-05-25 19:46:42','Product_Pictures/gigabyte-g5-kf5-i5-13500h-16gb-1tb-ssd-rtx4060-8gb-154-144hz.png',0.00),(25,'Intel Core i5 12400F (2.5 GHz / 4.4 GHz)',16,1,'With more cores and more power, Intel\'s 12th generation processors (Alder Lake) are ready for next-generation gaming, PCI-Express 5.0 graphics cards, or DDR5 RAM. They will allow you to design powerful machines capable of handling all tasks, from video games to productivity applications or intense multitasking.',8,1000.00,1000.00,21,'Visible','2024-05-25 19:49:20','Product_Pictures/intel-core-i5-12400f-25-ghz-44-ghz-processeurs.jpg',0.00),(26,'MSI MAG B550 TOMAHAWK',16,40,'The MSI MAG B550 TOMAHAWK motherboard features the AMD B550 chipset and an AM4 socket, designed to accommodate 3rd generation AMD Ryzen processors. It allows for the creation of a gaming configuration equipped with the latest technological advancements, including PCI-Express 4.0 for graphics cards and M.2 SSDs, as well as 128GB of DDR4 RAM management. Everything is in place for an exceptional gaming experience, it\'s your turn to play!',9,1500.00,2199.00,30,'Visible','2024-05-25 20:37:51','Product_Pictures/msi-mag-b550-tomahawk-cartes-meres.jpg',0.00),(27,'Aerocool Cosmo 12',16,41,'120mm fan with Molex connector featuring a sleek fixed RGB LED lighting design to add an extra touch to your setup. Equipped with curved fan blades to increase air pressure, maximize cooling performance, and minimize air resistance and noise.',10,50.00,79.00,53,'Visible','2024-05-25 21:00:41','Product_Pictures/aerocool-cosmo-12-refroidissement.jpg',0.00),(29,'Corsair T3 Rush (grey/silver)',18,1,'The Corsair T3 Rush gaming chair has a very comfortable seat and excellent molecularity to allow you to play in the best conditions. Made of breathable fabric, the T3 is very pleasant to use during hot periods or your long gaming sessions. Thanks to its reinforced casters, it is extremely resistant to wear and will retain its place by your side for many years.',12,3500.00,4199.00,11,'Visible','2024-05-27 17:57:05','Product_Pictures/corsair-t3-rush-grey-silver-chaisebureau.png',0.00),(39,'Corsair Vengeance RGB DDR5 64Go (2 x 32Go) 5200 MHz CL40',16,43,'The Corsair Vengeance RGB DDR5 memory offers higher performance and frequency with increased capacity optimized for AMD motherboards, while illuminating your PC with dynamic RGB lighting in ten individually customizable zones. The high-frequency memory chips, carefully selected, enable faster processing, rendering, and buffering than ever before, with integrated voltage regulation for easy and precise overclocking. Take control with the Corsair iCUE software, which allows for RGB lighting customization, real-time frequency monitoring, integrated voltage regulation, and Intel XMP 3.0 profile customization. A custom-designed printed circuit board provides optimal signal quality for exceptional performance and stability on the latest Intel DDR5 motherboards. With its cutting-edge performance and dazzling RGB lighting, the DDR5 Vengeance RGB memory opens the way.',12,2500.00,2999.00,5,'Visible','2024-05-27 23:57:47','Product_Pictures/corsair-vengeance-rgb-ddr5-64go-2-x-32go-5200-mhz-cl40.jpg',0.00),(56,'Samsung SSD 980 M.2 PCIe NVMe 1TB',16,44,'The Samsung 1TB 980 SSD enables you to transcend your machine\'s performance and responsiveness. It offers high transfer speeds and excellent endurance, thanks to the PCI-E 3.0 x4 interface and NVMe technology. It is equipped with Samsung V-NAND 3-bit MLC memory and a Samsung Pablo controller.\r\n\r\nThe Samsung 980 M.2 PCIe 3.0 4x NVMe is ideal for users seeking a high-performance PCIe 3.0 x4 SSD. Its high-performance bandwidth for heavy workloads in gaming, graphics, data analysis, and more allows you to rediscover fluidity and responsiveness in all tasks.',13,800.00,1149.00,15,'Visible','2024-05-28 00:32:16','Product_Pictures/665526106d0fa_Samsung_SSD_980_M.2_PCIe_NVMe_1TB.jpg',0.00),(115,'aaa',1,1,'',1,1.00,1000.00,1,'Visible','2024-06-04 15:35:40','Product_Pictures/Default_Product_Picture.jpg',500.00);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productspecifications`
--

DROP TABLE IF EXISTS `productspecifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productspecifications` (
  `Product_ID` int NOT NULL,
  `Specification_Name` varchar(255) NOT NULL,
  `Specification_Value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Product_ID`,`Specification_Name`),
  CONSTRAINT `productspecifications_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`Product_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productspecifications`
--

LOCK TABLES `productspecifications` WRITE;
/*!40000 ALTER TABLE `productspecifications` DISABLE KEYS */;
INSERT INTO `productspecifications` VALUES (16,'Brand','MSI'),(16,'Chipset Brand','Nvidia'),(16,'Computing units','3072 CUDA Cores'),(16,'Frequency','2505 Mhz'),(16,'Graphics Card','NVIDIA GeForce RTX 4060 8GB GDDR6'),(16,'Memory','8 Go'),(16,'Memory Frequency','17000 Mhz'),(16,'Warranty','12 Months'),(17,'Case','XTRMLAB Optix'),(17,'Cooler','Mars Gaming MCPU120.'),(17,'Graphics Card','    AMD Radeon RX 6600 8GB GDDR6'),(17,'Memory','Corsair Vengeance LPX Series Low Profile 16Go (2x 8Go) DDR4 3200 MHz CL16'),(17,'Motherboard','MSI PRO H610M-E DDR4.'),(17,'Power Supply','    Mars Gaming MPB550 80 PLUS Bronze 550W'),(17,'Processor','    Intel Core i5 12400F (2.5 GHz / 4.4 GHz)'),(17,'SSD','    Lexar NM620 M.2 PCIe NVMe 512GB'),(17,'Warranty','12 Months'),(22,'Case','XTRMLAB Optix'),(22,'Cooler','    DeepCool Gammaxx AG400'),(22,'Graphics Card','    NVIDIA GeForce RTX 4060 8GB GDDR6'),(22,'Memory','    Corsair Vengeance LPX Series Low Profile 16Go (2x 8Go) DDR4 3200 MHz CL16'),(22,'Motherboard','    MSI B450M PRO-VDH MAX'),(22,'Power Supply','    Mars Gaming MPB650 80 PLUS Bronze 650W'),(22,'Processeur','    AMD Ryzen 7 5700X (3.4 GHz / 4.6 GHz)'),(22,'SSD','    Lexar NM620 M.2 PCIe NVMe 512GB'),(22,'Warranty','12 Months'),(23,'Case','Corsair Crystal 680X RGB White'),(23,'Cooler','MSI MAG CORELIQUID 360R V2'),(23,'Graphics Card','NVIDIA GeForce RTX 4090 24GB GDDR6X'),(23,'Memory','G.Skill Trident Z5 Neo Series 64Go (2x 32Go) DDR5 6000 MHz CL30'),(23,'Motherboard','MSI MAG Z790 TOMAHAWK MAX WIFI'),(23,'Power Supply','MSI MPG A1000G 80PLUS Gold 1000W'),(23,'Processor','    Intel Core i9 14900K (3.2 GHz / 5.8 GHz)'),(23,'SSD','Samsung SSD 990 PRO M.2 PCIe NVMe 4TB'),(23,'Warranty','12 Months'),(24,'Brand','Gigabyte'),(24,'Graphics Card','NVIDIA GeForce RTX 4060 8GB GDDR6'),(24,'Keyboard','QWERTY'),(24,'Memory','16 Go DDR4'),(24,'Operation System','FreeDOS'),(24,'Processor','Intel Core i5-13500H (4 Performance-Cores 4.7 GHz Turbo + 8 Efficient-Cores 2.6 GHz Turbo - 16 Threads - Cache 18 Mo)'),(24,'Screen','15.6'),(24,'SSD','1TB NVMe PCIe'),(24,'Warranty','12 Months'),(26,'Brand','MSI'),(26,'Chipset','AMD B550'),(26,'Format','ATX'),(26,'Memory Slots','4'),(26,'Port(s) PCI-Express 16x','2'),(26,'Socket','AMD AM4'),(26,'Warranty','12 Months'),(27,'Brand','Aerocool'),(27,'Diamater','120 mm'),(27,'Max Debit','26.2 CFM'),(27,'Max Noise','23.9 dB'),(27,'Max Speed','1000 RPM'),(27,'Warranty','12 Months'),(29,'Adjustable armrests','Yes'),(29,'Backrest height','85 cm'),(29,'Maximum supported weight','120 kg'),(29,'Reclining backrest','180°'),(29,'Type of armrests','4D'),(29,'Weight','22.5 kg'),(39,'CAS Latency','CL40'),(39,'Memory Frequency','5200 Mhz'),(39,'Memory Type','DDR5'),(39,'Number of Chip(s)','2'),(39,'Tension','1.25 Volts'),(39,'Total Capacity','64 Go'),(39,'Warranty','12 Months'),(56,'Disk Capacity','1 To'),(56,'Disk Format','Carte M.2'),(56,'Interface','PCI-E 3.0 4x'),(56,'Reading Speed','3500 Mo/s'),(56,'Warranty','12 Months'),(56,'Writing Speed','3000 Mo/s');
/*!40000 ALTER TABLE `productspecifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shoppingcart`
--

DROP TABLE IF EXISTS `shoppingcart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shoppingcart` (
  `CartItem_ID` int NOT NULL AUTO_INCREMENT,
  `User_ID` int DEFAULT NULL,
  `Product_ID` int DEFAULT NULL,
  `Quantity` int DEFAULT NULL,
  PRIMARY KEY (`CartItem_ID`),
  KEY `User_ID` (`User_ID`),
  KEY `Product_ID` (`Product_ID`),
  CONSTRAINT `shoppingcart_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`),
  CONSTRAINT `shoppingcart_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`Product_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=293 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shoppingcart`
--

LOCK TABLES `shoppingcart` WRITE;
/*!40000 ALTER TABLE `shoppingcart` DISABLE KEYS */;
INSERT INTO `shoppingcart` VALUES (143,NULL,22,1),(144,NULL,22,1),(279,2,56,1);
/*!40000 ALTER TABLE `shoppingcart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subcategories`
--

DROP TABLE IF EXISTS `subcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subcategories` (
  `SubCategory_ID` int NOT NULL AUTO_INCREMENT,
  `SubCategory_Name` varchar(100) DEFAULT NULL,
  `SubCategory_Desc` varchar(2000) DEFAULT NULL,
  `Category_ID` int DEFAULT NULL,
  PRIMARY KEY (`SubCategory_ID`),
  KEY `Category_ID` (`Category_ID`),
  CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `categories` (`Category_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subcategories`
--

LOCK TABLES `subcategories` WRITE;
/*!40000 ALTER TABLE `subcategories` DISABLE KEYS */;
INSERT INTO `subcategories` VALUES (1,'Unspecified','Warning : This column must not be deleted or changed. ',1),(40,'Motherboards','------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------',16),(41,'Coolers','',16),(42,'Graphics Cards','',16),(43,'Memory','Memory',16),(44,'HDDs & SSDs','',16),(45,'Power Supply Units','',16),(46,'Cases','',16),(47,'Monitors','',17),(48,'Keyboards','',17),(49,'Mouses','',17),(50,'Headphones','',17),(51,'PC Gamer Standard','',14),(52,'PC Gamer Advanced','',14),(53,'PC Gamer Ultra','',14),(54,'Gamer Laptop','',15);
/*!40000 ALTER TABLE `subcategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `User_ID` int NOT NULL AUTO_INCREMENT,
  `User_Username` varchar(255) DEFAULT NULL,
  `User_FirstName` varchar(255) DEFAULT NULL,
  `User_LastName` varchar(255) DEFAULT NULL,
  `User_Phone` varchar(255) DEFAULT NULL,
  `User_Country` varchar(255) DEFAULT NULL,
  `User_Address` varchar(255) DEFAULT NULL,
  `User_Email` varchar(255) DEFAULT NULL,
  `User_Password` varchar(255) DEFAULT NULL,
  `User_RegisterationDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `User_Role` enum('Owner','Admin','Client') NOT NULL DEFAULT 'Client',
  `Account_Status` varchar(255) DEFAULT 'Unlocked',
  PRIMARY KEY (`User_ID`),
  UNIQUE KEY `User_Username` (`User_Username`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'yem0417','Youssef','EL MOUMEN','0636523432','Morocco','Casa, Bernoussi','dinactiprefected@gmail.com','$2y$10$1ODDWv1kBmnpcxsJH5aBFeiFyZDkdHE2/9UvqD7HHSrxG6D39YYJq','2024-05-02 16:11:44','Owner',NULL),(2,'Admin','Admin','Aura','0607302999','Morocco','Admin Address','heavenly.onyx401@gmail.com','$2y$10$VaZCjIQ.VL.szpKtWcLDIOqXZb2KYKULf4AvfR6dV4oRSEMKeH/h2','2024-05-25 16:57:29','Admin','✔️ Unlocked'),(3,'Client','Ali','Tazi','0607302999','Morocco','Tazi Ali','repliqua.destroyer@gmail.com','$2y$10$M2Nqx1E5qG/wu6divUvKHuKsibmR.Cbbe8V9vrrAAWHChLt5R0yca','2024-06-02 00:26:50','Client',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-06-04 16:56:45
