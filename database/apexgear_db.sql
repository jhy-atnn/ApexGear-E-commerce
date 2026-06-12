-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 11:16 AM
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
-- Database: `apexgear_db`
--
CREATE DATABASE IF NOT EXISTS `apexgear_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `apexgear_db`;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users_tbl`
--

DROP TABLE IF EXISTS `admin_users_tbl`;
CREATE TABLE `admin_users_tbl` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users_tbl`
--

INSERT INTO `admin_users_tbl` (`admin_id`, `username`, `password`) VALUES
(1, 'luige_admin', 'admin 123'),
(2, 'jhody_admin', 'admin 123'),
(3, 'luis_admin', 'admin 123');

-- --------------------------------------------------------

--
-- Table structure for table `archived_admin_users_tbl`
--

DROP TABLE IF EXISTS `archived_admin_users_tbl`;
CREATE TABLE `archived_admin_users_tbl` (
  `archive_admin_id` int(11) NOT NULL,
  `original_admin_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `archived_by` int(11) DEFAULT NULL,
  `archived_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_orders_tbl`
--

DROP TABLE IF EXISTS `archived_orders_tbl`;
CREATE TABLE `archived_orders_tbl` (
  `archive_order_id` int(11) NOT NULL,
  `original_order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_ref_code` varchar(50) DEFAULT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT NULL,
  `shipping_fee` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `final_status` varchar(50) DEFAULT NULL,
  `archived_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_order_items_tbl`
--

DROP TABLE IF EXISTS `archived_order_items_tbl`;
CREATE TABLE `archived_order_items_tbl` (
  `archive_item_id` int(11) NOT NULL,
  `archive_order_id` int(11) NOT NULL,
  `original_product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price_at_checkout` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_users_tbl`
--

DROP TABLE IF EXISTS `archived_users_tbl`;
CREATE TABLE `archived_users_tbl` (
  `archive_id` int(11) NOT NULL,
  `original_user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `m_name` varchar(50) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `archived_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brand_tbl`
--

DROP TABLE IF EXISTS `brand_tbl`;
CREATE TABLE `brand_tbl` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items_tbl`
--

DROP TABLE IF EXISTS `cart_items_tbl`;
CREATE TABLE `cart_items_tbl` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_tbl`
--

DROP TABLE IF EXISTS `category_tbl`;
CREATE TABLE `category_tbl` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_code`
--

DROP TABLE IF EXISTS `coupon_code`;
CREATE TABLE `coupon_code` (
  `coupon_id` int(11) NOT NULL,
  `code_name` varchar(50) NOT NULL,
  `discount_percentage` int(11) NOT NULL,
  `valid_until` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage_tbl`
--

DROP TABLE IF EXISTS `coupon_usage_tbl`;
CREATE TABLE `coupon_usage_tbl` (
  `usage_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites_tbl`
--

DROP TABLE IF EXISTS `favorites_tbl`;
CREATE TABLE `favorites_tbl` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications_tbl`
--

DROP TABLE IF EXISTS `notifications_tbl`;
CREATE TABLE `notifications_tbl` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_tbl`
--

DROP TABLE IF EXISTS `orders_tbl`;
CREATE TABLE `orders_tbl` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_ref_code` varchar(50) NOT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` varchar(50) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items_tbl`
--

DROP TABLE IF EXISTS `order_items_tbl`;
CREATE TABLE `order_items_tbl` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_checkout` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_tbl`
--

DROP TABLE IF EXISTS `order_status_tbl`;
CREATE TABLE `order_status_tbl` (
  `status_log_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_status` varchar(50) NOT NULL,
  `payment_remarks` text DEFAULT NULL,
  `updated_by_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments_tbl`
--

DROP TABLE IF EXISTS `payments_tbl`;
CREATE TABLE `payments_tbl` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `method` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `qr_screenshot_path` varchar(255) DEFAULT NULL,
  `card_last_four` varchar(4) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products_tbl`
--

DROP TABLE IF EXISTS `products_tbl`;
CREATE TABLE `products_tbl` (
  `product_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `desc` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_percent` int(11) DEFAULT 0,
  `sale_valid_until` datetime DEFAULT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `badge` varchar(50) DEFAULT NULL,
  `badge_type` varchar(50) DEFAULT NULL,
  `est_shipping_time` varchar(50) DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images_tbl`
--

DROP TABLE IF EXISTS `product_images_tbl`;
CREATE TABLE `product_images_tbl` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews_tbl`
--

DROP TABLE IF EXISTS `reviews_tbl`;
CREATE TABLE `reviews_tbl` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_address_tbl`
--

DROP TABLE IF EXISTS `shipping_address_tbl`;
CREATE TABLE `shipping_address_tbl` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_ref_code` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `street_address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_profiles_tbl`
--

DROP TABLE IF EXISTS `users_profiles_tbl`;
CREATE TABLE `users_profiles_tbl` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_tbl`
--

DROP TABLE IF EXISTS `users_tbl`;
CREATE TABLE `users_tbl` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `m_name` varchar(50) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `otp_code` int(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_tbl`
--

INSERT INTO `users_tbl` (`user_id`, `first_name`, `last_name`, `m_name`, `gender`, `username`, `email`, `password`, `otp_code`) VALUES
(1, 'Jhody', 'Atinon', 'Mesina', 'Female', 'Jhody', 'atinon.jhody@email.com', 'pass123', NULL),
(2, 'Sebastian Luis', 'Raymundo', 'Ebora', 'Male', 'Luis', 'sebastianluisebora@gmail.com', 'pass123', NULL),
(3, 'Justine Luige', 'Agojo', 'Malaiba', 'Male', 'Luige', 'justine05luige@gmail.com', 'pass123', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users_tbl`
--
ALTER TABLE `admin_users_tbl`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `archived_admin_users_tbl`
--
ALTER TABLE `archived_admin_users_tbl`
  ADD PRIMARY KEY (`archive_admin_id`);

--
-- Indexes for table `archived_orders_tbl`
--
ALTER TABLE `archived_orders_tbl`
  ADD PRIMARY KEY (`archive_order_id`);

--
-- Indexes for table `archived_order_items_tbl`
--
ALTER TABLE `archived_order_items_tbl`
  ADD PRIMARY KEY (`archive_item_id`),
  ADD KEY `archive_order_id` (`archive_order_id`);

--
-- Indexes for table `archived_users_tbl`
--
ALTER TABLE `archived_users_tbl`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `brand_tbl`
--
ALTER TABLE `brand_tbl`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `cart_items_tbl`
--
ALTER TABLE `cart_items_tbl`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `category_tbl`
--
ALTER TABLE `category_tbl`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `coupon_code`
--
ALTER TABLE `coupon_code`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `code_name` (`code_name`);

--
-- Indexes for table `coupon_usage_tbl`
--
ALTER TABLE `coupon_usage_tbl`
  ADD PRIMARY KEY (`usage_id`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `favorites_tbl`
--
ALTER TABLE `favorites_tbl`
  ADD PRIMARY KEY (`favorite_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `notifications_tbl`
--
ALTER TABLE `notifications_tbl`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders_tbl`
--
ALTER TABLE `orders_tbl`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_ref_code` (`order_ref_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `coupon_id` (`coupon_id`);

--
-- Indexes for table `order_items_tbl`
--
ALTER TABLE `order_items_tbl`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_status_tbl`
--
ALTER TABLE `order_status_tbl`
  ADD PRIMARY KEY (`status_log_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `updated_by_admin` (`updated_by_admin`);

--
-- Indexes for table `payments_tbl`
--
ALTER TABLE `payments_tbl`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `products_tbl`
--
ALTER TABLE `products_tbl`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_images_tbl`
--
ALTER TABLE `product_images_tbl`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reviews_tbl`
--
ALTER TABLE `reviews_tbl`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `shipping_address_tbl`
--
ALTER TABLE `shipping_address_tbl`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users_profiles_tbl`
--
ALTER TABLE `users_profiles_tbl`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users_tbl`
--
ALTER TABLE `users_tbl`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users_tbl`
--
ALTER TABLE `admin_users_tbl`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `archived_admin_users_tbl`
--
ALTER TABLE `archived_admin_users_tbl`
  MODIFY `archive_admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `archived_orders_tbl`
--
ALTER TABLE `archived_orders_tbl`
  MODIFY `archive_order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `archived_order_items_tbl`
--
ALTER TABLE `archived_order_items_tbl`
  MODIFY `archive_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `archived_users_tbl`
--
ALTER TABLE `archived_users_tbl`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brand_tbl`
--
ALTER TABLE `brand_tbl`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items_tbl`
--
ALTER TABLE `cart_items_tbl`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_tbl`
--
ALTER TABLE `category_tbl`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupon_code`
--
ALTER TABLE `coupon_code`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupon_usage_tbl`
--
ALTER TABLE `coupon_usage_tbl`
  MODIFY `usage_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites_tbl`
--
ALTER TABLE `favorites_tbl`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications_tbl`
--
ALTER TABLE `notifications_tbl`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders_tbl`
--
ALTER TABLE `orders_tbl`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items_tbl`
--
ALTER TABLE `order_items_tbl`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status_tbl`
--
ALTER TABLE `order_status_tbl`
  MODIFY `status_log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments_tbl`
--
ALTER TABLE `payments_tbl`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products_tbl`
--
ALTER TABLE `products_tbl`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images_tbl`
--
ALTER TABLE `product_images_tbl`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews_tbl`
--
ALTER TABLE `reviews_tbl`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_address_tbl`
--
ALTER TABLE `shipping_address_tbl`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_profiles_tbl`
--
ALTER TABLE `users_profiles_tbl`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_tbl`
--
ALTER TABLE `users_tbl`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `archived_order_items_tbl`
--
ALTER TABLE `archived_order_items_tbl`
  ADD CONSTRAINT `archived_order_items_tbl_ibfk_1` FOREIGN KEY (`archive_order_id`) REFERENCES `archived_orders_tbl` (`archive_order_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items_tbl`
--
ALTER TABLE `cart_items_tbl`
  ADD CONSTRAINT `cart_items_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_tbl_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products_tbl` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `coupon_usage_tbl`
--
ALTER TABLE `coupon_usage_tbl`
  ADD CONSTRAINT `coupon_usage_tbl_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupon_code` (`coupon_id`),
  ADD CONSTRAINT `coupon_usage_tbl_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`);

--
-- Constraints for table `favorites_tbl`
--
ALTER TABLE `favorites_tbl`
  ADD CONSTRAINT `favorites_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_tbl_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products_tbl` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications_tbl`
--
ALTER TABLE `notifications_tbl`
  ADD CONSTRAINT `notifications_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders_tbl`
--
ALTER TABLE `orders_tbl`
  ADD CONSTRAINT `orders_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`),
  ADD CONSTRAINT `orders_tbl_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupon_code` (`coupon_id`);

--
-- Constraints for table `order_items_tbl`
--
ALTER TABLE `order_items_tbl`
  ADD CONSTRAINT `order_items_tbl_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders_tbl` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_tbl_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products_tbl` (`product_id`);

--
-- Constraints for table `order_status_tbl`
--
ALTER TABLE `order_status_tbl`
  ADD CONSTRAINT `order_status_tbl_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders_tbl` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_status_tbl_ibfk_2` FOREIGN KEY (`updated_by_admin`) REFERENCES `admin_users_tbl` (`admin_id`);

--
-- Constraints for table `payments_tbl`
--
ALTER TABLE `payments_tbl`
  ADD CONSTRAINT `payments_tbl_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders_tbl` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `products_tbl`
--
ALTER TABLE `products_tbl`
  ADD CONSTRAINT `products_tbl_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brand_tbl` (`brand_id`),
  ADD CONSTRAINT `products_tbl_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category_tbl` (`category_id`);

--
-- Constraints for table `product_images_tbl`
--
ALTER TABLE `product_images_tbl`
  ADD CONSTRAINT `product_images_tbl_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products_tbl` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews_tbl`
--
ALTER TABLE `reviews_tbl`
  ADD CONSTRAINT `reviews_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`),
  ADD CONSTRAINT `reviews_tbl_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products_tbl` (`product_id`);

--
-- Constraints for table `shipping_address_tbl`
--
ALTER TABLE `shipping_address_tbl`
  ADD CONSTRAINT `shipping_address_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`);

--
-- Constraints for table `users_profiles_tbl`
--
ALTER TABLE `users_profiles_tbl`
  ADD CONSTRAINT `users_profiles_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
