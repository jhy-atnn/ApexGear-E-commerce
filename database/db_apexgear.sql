-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2026 at 04:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_apexgear`
--
CREATE DATABASE IF NOT EXISTS `db_apexgear` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_apexgear`;

-- --------------------------------------------------------

--
-- Table structure for table `archived_orders_tbl`
--

DROP TABLE IF EXISTS `archived_orders_tbl`;
CREATE TABLE `archived_orders_tbl` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(20) DEFAULT NULL,
  `reference_number` varchar(100) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_order_items_tbl`
--

DROP TABLE IF EXISTS `archived_order_items_tbl`;
CREATE TABLE `archived_order_items_tbl` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `purchased_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands_tbl`
--

DROP TABLE IF EXISTS `brands_tbl`;
CREATE TABLE `brands_tbl` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands_tbl`
--

INSERT INTO `brands_tbl` (`brand_id`, `brand_name`) VALUES
(1, 'Lenovo'),
(2, 'ASUS'),
(3, 'Razer'),
(4, 'Logitech'),
(5, 'Sony'),
(6, 'Samsung'),
(7, 'Dell'),
(8, 'LG'),
(9, 'Apple'),
(10, 'HP'),
(11, 'Corsair'),
(12, 'NVIDIA');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items_tbl`
--

DROP TABLE IF EXISTS `cart_items_tbl`;
CREATE TABLE `cart_items_tbl` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories_tbl`
--

DROP TABLE IF EXISTS `categories_tbl`;
CREATE TABLE `categories_tbl` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories_tbl`
--

INSERT INTO `categories_tbl` (`category_id`, `category_name`) VALUES
(1, 'Laptops'),
(2, 'Peripherals'),
(3, 'Audio'),
(4, 'Phones'),
(5, 'Desktops'),
(6, 'GPUs');

-- --------------------------------------------------------

--
-- Table structure for table `favorites_tbl`
--

DROP TABLE IF EXISTS `favorites_tbl`;
CREATE TABLE `favorites_tbl` (
  `fav_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_tbl`
--

DROP TABLE IF EXISTS `orders_tbl`;
CREATE TABLE `orders_tbl` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(20) DEFAULT NULL,
  `reference_number` varchar(100) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` varchar(50) DEFAULT 'Pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items_tbl`
--

DROP TABLE IF EXISTS `order_items_tbl`;
CREATE TABLE `order_items_tbl` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `purchased_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products_tbl`
--

DROP TABLE IF EXISTS `products_tbl`;
CREATE TABLE `products_tbl` (
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `rating` int(11) DEFAULT 0,
  `badge` varchar(50) DEFAULT NULL,
  `badge_type` varchar(50) DEFAULT NULL,
  `image` text DEFAULT NULL,
  `desc` text DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products_tbl`
--

INSERT INTO `products_tbl` (`product_id`, `name`, `brand_id`, `category_id`, `price`, `old_price`, `stock`, `rating`, `badge`, `badge_type`, `image`, `desc`, `is_archived`) VALUES
(1, 'Legion 5 Pro — RTX 4070, 16\" QHD 165Hz', 1, 1, 119995.00, 127999.00, 10, 124, 'New', 'new', '../assets/images/products/1780839229_legion5pro.png', 'Dominate the competition with the Legion 5 Pro. Featuring a blistering 165Hz QHD display and the raw power of the RTX 4070, it delivers desktop-level gaming performance in a sleek, portable chassis.', 0),
(2, 'ROG Zephyrus G14 — Ryzen 9, RTX 4060', 2, 1, 109995.00, 119995.00, 1, 89, 'SALE', 'ribbon', 'assets/images/products/zephyrusg14.png', 'The ultimate blend of power and portability. The ROG Zephyrus G14 packs a Ryzen 9 processor and RTX 4060 into a stunningly thin and light 14-inch form factor, perfect for gamers and creators on the move.', 0),
(3, 'BlackWidow V4 Pro — Mechanical, RGB, Wireless', 3, 2, 13495.00, 15995.00, 20, 4210, 'Popular', 'normal', 'assets/images/products/blackwidow.png', 'Elevate your setup with the Razer BlackWidow V4 Pro. Experience unmatched tactile feedback with premium mechanical switches, immersive Razer Chroma RGB lighting, and lag-free wireless connectivity.', 0),
(4, 'G Pro X Superlight 2 — Wireless Gaming Mouse', 4, 2, 7795.00, 8995.00, 15, 4340, NULL, NULL, 'assets/images/products/superlight.png', 'Engineered for esports professionals. The Logitech G Pro X Superlight 2 offers zero-latency wireless performance and an ultra-lightweight design to ensure maximum precision and speed.', 0),
(5, 'WH-1000XM5 — Noise Cancelling, 30hr Battery', 5, 3, 15499.00, 20999.00, 12, 512, 'New', 'new', 'assets/images/products/sonywh.png', 'Immerse yourself in pure audio. The Sony WH-1000XM5 headphones feature industry-leading active noise cancellation, crystal-clear calls, and up to 30 hours of battery life for all-day comfort.', 0),
(6, 'Galaxy S24 Ultra — 200MP, 5000mAh, Titanium', 6, 4, 84990.00, 94990.00, 8, 445, 'SALE', 'ribbon', 'assets/images/products/s24ultra.png', 'The pinnacle of mobile innovation. The Galaxy S24 Ultra boasts a premium titanium frame, a breathtaking 200MP camera system, and the integrated S Pen for unmatched productivity and creativity.', 0),
(7, 'XPS Tower — Intel i9-14900K, RTX 4080, 64GB', 7, 5, 185000.00, 200000.00, 3, 78, NULL, NULL, 'assets/images/products/xpstower.png', 'Uncompromising performance for the most demanding tasks. The Dell XPS Tower is a powerhouse workstation equipped with an Intel i9 processor and RTX 4080, ready to tackle heavy rendering and high-end gaming.', 0),
(8, 'UltraGear 27\" — 4K, 144Hz, 1ms, IPS, G-Sync', 8, 2, 39995.00, 45995.00, 7, 193, 'On Sale', 'sale', 'assets/images/products/ultragear.png', 'See every detail, feel every frame. The LG UltraGear 27-inch monitor delivers a stunning 4K IPS display with a hyper-fast 144Hz refresh rate and 1ms response time, optimized for G-Sync.', 0),
(9, 'iPhone 17 Pro — Cosmic Orange - Aluminum', 9, 4, 109990.00, 119990.00, 15, 445, 'On Sale', 'sale', 'assets/images/products/iphone17pro.png', 'Apple\'s most advanced Pro model yet. The iPhone 17 Pro features a revolutionary new camera system, blazing-fast A-series silicon, and a striking Cosmic Orange aluminum finish.', 0),
(10, 'Spectre x360 14\" — OLED, Intel Evo', 10, 1, 79995.00, 95000.00, 10, 156, '–15%', 'sale', 'assets/images/products/hpspectre.png', 'Versatility meets luxury. The HP Spectre x360 14 is a premium 2-in-1 convertible laptop featuring a vibrant OLED display and Intel Evo certification for exceptional responsiveness and battery life.', 0),
(11, 'iPhone 15 Pro — 256GB, Natural Titanium', 9, 4, 69990.00, 76990.00, 25, 892, '–10%', 'sale', 'assets/images/products/iph15pro.png', 'Forged in titanium. The iPhone 15 Pro delivers incredible strength-to-weight ratio, the powerful A17 Pro chip for next-level mobile gaming, and a pro-class camera system.', 0),
(12, 'K100 Air — Ultra-Thin, Wireless, RGB', 11, 2, 11995.00, 16500.00, 15, 340, '–25%', 'sale', 'assets/images/products/corsairk100air.png', 'Incredibly thin, undeniably powerful. The Corsair K100 Air is a premium wireless mechanical keyboard featuring a breathtakingly slim profile, tactile Cherry MX Ultra Low Profile switches, and dynamic RGB.', 0),
(13, 'GeForce RTX 4070 Super — 12GB GDDR6X', 12, 6, 36500.00, 42000.00, 5, 215, '–13%', 'sale', 'assets/images/products/nvidiartx4070.png', 'Supercharge your PC. The NVIDIA GeForce RTX 4070 Super delivers exceptional frame rates at 1440p and 4K resolutions, powered by the ultra-efficient Ada Lovelace architecture and DLSS 3.', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users_tbl`
--

DROP TABLE IF EXISTS `users_tbl`;
CREATE TABLE `users_tbl` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `profile_picture` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_tbl`
--

INSERT INTO `users_tbl` (`user_id`, `username`, `first_name`, `last_name`, `profile_picture`, `bio`, `gender`, `birthday`, `phone`, `email`, `password_hash`, `role`) VALUES
(1, 'Juday', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'atinon.jhody@gmail.com', '$2y$10$o/rJRTsc1AdhVnm/VOhI0eSPCOx9WAK8GDXgYtAANwYCBkYjullWi', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archived_orders_tbl`
--
ALTER TABLE `archived_orders_tbl`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `archived_order_items_tbl`
--
ALTER TABLE `archived_order_items_tbl`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `brands_tbl`
--
ALTER TABLE `brands_tbl`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `cart_items_tbl`
--
ALTER TABLE `cart_items_tbl`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories_tbl`
--
ALTER TABLE `categories_tbl`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `favorites_tbl`
--
ALTER TABLE `favorites_tbl`
  ADD PRIMARY KEY (`fav_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders_tbl`
--
ALTER TABLE `orders_tbl`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items_tbl`
--
ALTER TABLE `order_items_tbl`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products_tbl`
--
ALTER TABLE `products_tbl`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users_tbl`
--
ALTER TABLE `users_tbl`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands_tbl`
--
ALTER TABLE `brands_tbl`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cart_items_tbl`
--
ALTER TABLE `cart_items_tbl`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories_tbl`
--
ALTER TABLE `categories_tbl`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `favorites_tbl`
--
ALTER TABLE `favorites_tbl`
  MODIFY `fav_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders_tbl`
--
ALTER TABLE `orders_tbl`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items_tbl`
--
ALTER TABLE `order_items_tbl`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products_tbl`
--
ALTER TABLE `products_tbl`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users_tbl`
--
ALTER TABLE `users_tbl`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items_tbl`
--
ALTER TABLE `cart_items_tbl`
  ADD CONSTRAINT `cart_items_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_tbl_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products_tbl` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites_tbl`
--
ALTER TABLE `favorites_tbl`
  ADD CONSTRAINT `favorites_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_tbl_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products_tbl` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders_tbl`
--
ALTER TABLE `orders_tbl`
  ADD CONSTRAINT `orders_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`);

--
-- Constraints for table `order_items_tbl`
--
ALTER TABLE `order_items_tbl`
  ADD CONSTRAINT `order_items_tbl_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders_tbl` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_tbl_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products_tbl` (`product_id`);

--
-- Constraints for table `products_tbl`
--
ALTER TABLE `products_tbl`
  ADD CONSTRAINT `products_tbl_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands_tbl` (`brand_id`),
  ADD CONSTRAINT `products_tbl_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories_tbl` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
