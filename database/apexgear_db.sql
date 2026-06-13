-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2026 at 09:52 PM
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
-- Table structure for table `admin_activity_tbl`
--

DROP TABLE IF EXISTS `admin_activity_tbl`;
CREATE TABLE `admin_activity_tbl` (
  `activity_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_tbl`
--

INSERT INTO `admin_activity_tbl` (`activity_id`, `admin_id`, `activity_type`, `message`, `created_at`) VALUES
(1, 2, 'order_status', 'Updated order #6 status to Shipped.', '2026-06-14 01:14:06'),
(2, 2, 'order_completed', 'Updated order #6 status to Completed.', '2026-06-14 01:14:20'),
(3, 2, 'order_status', 'Updated order #4 status to Canceled.', '2026-06-14 03:29:26'),
(4, 2, 'order_status', 'Updated order #2 status to Canceled.', '2026-06-14 03:29:29'),
(5, 2, 'order_status', 'Updated order #3 status to Canceled.', '2026-06-14 03:29:31'),
(6, 2, 'order_status', 'Updated order #5 status to Canceled.', '2026-06-14 03:29:33'),
(7, 2, 'order_status', 'Updated order #1 status to Canceled.', '2026-06-14 03:29:35'),
(8, 2, 'order_status', 'Updated order #1 status to Canceled.', '2026-06-14 03:29:38'),
(9, 2, 'order_status', 'Updated order #1 status to Canceled.', '2026-06-14 03:29:40'),
(10, 2, 'product_add', 'Added product: Legion 5 Pro — RTX 4070, 16\" QHD 165Hz (ID 2).', '2026-06-14 03:34:25'),
(11, 2, 'product_add', 'Added product: Razer BlackWidow V4 Pro (ID 3).', '2026-06-14 03:36:56');

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
(1, 'luige_admin', 'admin123'),
(2, 'jhody_admin', 'admin123'),
(3, 'luis_admin', 'admin123');

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

--
-- Dumping data for table `brand_tbl`
--

INSERT INTO `brand_tbl` (`brand_id`, `brand_name`) VALUES
(1, 'Apple'),
(2, 'ASUS'),
(3, 'Corsair'),
(4, 'Dell'),
(5, 'HP'),
(6, 'Lenovo'),
(7, 'Intel'),
(8, 'Logitech'),
(9, 'NVIDIA'),
(10, 'Samsung'),
(11, 'Sony'),
(12, 'Razer');

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

--
-- Dumping data for table `category_tbl`
--

INSERT INTO `category_tbl` (`category_id`, `category_name`) VALUES
(1, 'Laptop'),
(2, 'Desktop / PC'),
(3, 'Tablet'),
(4, 'Phone'),
(5, 'Headphones / Audio'),
(6, 'Accessories / Peripherals'),
(7, 'CPU'),
(8, 'GPU'),
(9, 'peripheral');

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

--
-- Dumping data for table `coupon_code`
--

INSERT INTO `coupon_code` (`coupon_id`, `code_name`, `discount_percentage`, `valid_until`) VALUES
(1, 'LAUNCHAPX26', 15, '2026-06-30 20:34:00');

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

--
-- Dumping data for table `favorites_tbl`
--

INSERT INTO `favorites_tbl` (`favorite_id`, `user_id`, `product_id`) VALUES
(1, 1, 1),
(3, 2, 2);

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

--
-- Dumping data for table `notifications_tbl`
--

INSERT INTO `notifications_tbl` (`notif_id`, `user_id`, `message`, `is_read`) VALUES
(1, 1, 'Your order (APX-6A2D8FACC4A77) status has been updated to: Shipped.', 0),
(2, 1, 'Your order (APX-6A2D8FACC4A77) status has been updated to: Completed.', 0),
(3, 1, 'Your order (APX-6A2C21EF0B3A3) status has been updated to: Canceled.', 0),
(4, 1, 'Your order (APX-6A2C20A0D0FFC) status has been updated to: Canceled.', 0),
(5, 1, 'Your order (APX-6A2C21BED5C1E) status has been updated to: Canceled.', 0),
(6, 1, 'Your order (APX-6A2C21F0A8054) status has been updated to: Canceled.', 0),
(7, 1, 'Your order (APX-6A2C1F911BAD4) status has been updated to: Canceled.', 0),
(8, 1, 'Your order (APX-6A2C1F911BAD4) status has been updated to: Canceled.', 0),
(9, 1, 'Your order (APX-6A2C1F911BAD4) status has been updated to: Canceled.', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders_tbl`
--

DROP TABLE IF EXISTS `orders_tbl`;
CREATE TABLE `orders_tbl` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_ref_code` varchar(50) NOT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders_tbl`
--

INSERT INTO `orders_tbl` (`order_id`, `user_id`, `order_ref_code`, `coupon_id`, `subtotal`, `tax`, `shipping_fee`, `total_amount`, `order_status`, `created_at`) VALUES
(1, 1, 'APX-6A2C1F911BAD4', NULL, 51998.00, 4159.84, 0.00, 56157.84, 'Canceled', '2026-06-12 15:56:35'),
(2, 1, 'APX-6A2C20A0D0FFC', NULL, 51998.00, 4159.84, 0.00, 56157.84, 'Canceled', '2026-06-12 15:56:35'),
(3, 1, 'APX-6A2C21BED5C1E', NULL, 51998.00, 4159.84, 0.00, 56157.84, 'Canceled', '2026-06-12 15:56:35'),
(4, 1, 'APX-6A2C21EF0B3A3', NULL, 0.00, 0.00, 0.00, 0.00, 'Canceled', '2026-06-12 15:56:35'),
(5, 1, 'APX-6A2C21F0A8054', NULL, 0.00, 0.00, 0.00, 0.00, 'Canceled', '2026-06-12 15:56:35'),
(6, 1, 'APX-6A2D8FACC4A77', NULL, 161998.20, 12959.86, 0.00, 174958.06, 'Completed', '2026-06-14 01:13:16'),
(7, 2, 'APX-6A2DB3751570F', NULL, 80999.10, 6479.93, 0.00, 87479.03, 'Pending', '2026-06-14 03:45:57'),
(8, 2, 'APX-6A2DB40EC769D', NULL, 118950.00, 9516.00, 0.00, 128466.00, 'Pending', '2026-06-14 03:48:30');

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

--
-- Dumping data for table `order_items_tbl`
--

INSERT INTO `order_items_tbl` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price_at_checkout`) VALUES
(3, 3, 1, 2, 25999.00),
(4, 6, 1, 2, 80999.10),
(5, 7, 1, 1, 80999.10),
(6, 8, 2, 1, 118950.00);

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

--
-- Dumping data for table `order_status_tbl`
--

INSERT INTO `order_status_tbl` (`status_log_id`, `order_id`, `order_status`, `payment_remarks`, `updated_by_admin`) VALUES
(16, 7, 'Pending', NULL, NULL),
(17, 8, 'Pending', NULL, NULL);

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

--
-- Dumping data for table `payments_tbl`
--

INSERT INTO `payments_tbl` (`payment_id`, `order_id`, `method`, `status`, `qr_screenshot_path`, `card_last_four`, `transaction_id`) VALUES
(1, 3, 'Cash on Delivery', 'Pending', NULL, NULL, NULL),
(2, 4, 'Cash on Delivery', 'Pending', NULL, NULL, NULL),
(3, 5, 'Cash on Delivery', 'Pending', NULL, NULL, NULL),
(4, 6, 'Cash on Delivery', 'Pending', NULL, NULL, NULL),
(5, 7, 'GCash', 'Paid', NULL, NULL, '09632544756'),
(6, 8, 'Cash on Delivery', 'Pending', NULL, NULL, NULL);

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

--
-- Dumping data for table `products_tbl`
--

INSERT INTO `products_tbl` (`product_id`, `brand_id`, `category_id`, `name`, `desc`, `price`, `sale_percent`, `sale_valid_until`, `stock_qty`, `badge`, `badge_type`, `est_shipping_time`, `archived_at`) VALUES
(1, 1, 4, 'iPhone 17 Pro Max Cosmic Orange', 'Apple’s latest flagship smartphone featuring the A19 Bionic chip, 6.9-inch Super Retina XDR display, titanium frame, and exclusive Copper Orange finish. Includes advanced triple-lens camera system, MagSafe support, and up to 1TB storage.', 89999.00, 10, '2026-07-31 00:00:00', 22, 'New Arrival', 'highlight', '3–5 business days', NULL),
(2, 6, 1, 'Legion 5 Pro — RTX 4070, 16\" QHD 165Hz', 'Intel® Core™ i7-13700HX DISPLAY: 16\" WQXGA (2560x1600) IPS 500nits Anti-glare \r\nMEMORY: 2x 8GB SO-DIMM DDR5-4800 \r\nSTORAGE: 1TB SSD M.2 2280 PCIe® 4.0x4 NVMe® \r\nGPU: NVIDIA® GeForce RTX™ 4070 8GB GDDR6 \r\nOS: Windows 11 Home \r\nCOLOR: Onyx Grey   13th Gen Intel® Core', 118950.00, 0, NULL, 18, '', '', '5-10 business days', NULL),
(3, 12, 9, 'Razer BlackWidow V4 Pro', 'Designed to provide secure support for long hours of play, the soft, cushioned wrist rest also features Razer Chroma™ RGB—which perfectly lines up with the keyboard’s underglow. Using a doubleshot molding process to ensure the labelling never wears off, the keycaps also have extra-thick walls which make them extremely tough to withstand intense gaming.', 30818.00, 0, NULL, 10, '', '', '3–5 business days', NULL);

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

--
-- Dumping data for table `product_images_tbl`
--

INSERT INTO `product_images_tbl` (`image_id`, `product_id`, `image_path`) VALUES
(1, 1, 'C:xampphtdocsapexgearassetsimagesproductsiphone17pro.png'),
(2, 2, 'assets/images/products/1781379265_legion5pro.png'),
(3, 3, 'assets/images/products/1781379416_blackwidow.png');

-- --------------------------------------------------------

--
-- Table structure for table `reviews_tbl`
--

DROP TABLE IF EXISTS `reviews_tbl`;
CREATE TABLE `reviews_tbl` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews_tbl`
--

INSERT INTO `reviews_tbl` (`review_id`, `user_id`, `product_id`, `order_id`, `rating`, `comment`) VALUES
(1, 1, 1, 6, 5, 'I Luve this');

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

--
-- Dumping data for table `shipping_address_tbl`
--

INSERT INTO `shipping_address_tbl` (`address_id`, `user_id`, `order_ref_code`, `first_name`, `last_name`, `phone_number`, `street_address`, `city`, `zip_code`) VALUES
(1, 1, 'APX-6A2C21BED5C1E', 'Jhody', 'Atinon', '09976829526', '0616 Purok 2 Turbina, Laguna', 'Calamba City', '4027'),
(2, 1, 'APX-6A2C21EF0B3A3', 'Jhody', 'Atinon', '09976829526', '0616 Purok 2 Turbina, Laguna', 'Calamba City', '4027'),
(3, 1, 'APX-6A2C21F0A8054', 'Jhody', 'Atinon', '09976829526', '0616 Purok 2 Turbina, Laguna', 'Calamba City', '4027'),
(4, 1, 'APX-6A2D8FACC4A77', 'Jhody', 'Atinon', '09976829526', '0616 Purok 2 Turbina, Laguna', 'Calamba City', '4027'),
(5, 2, 'APX-6A2DB3751570F', 'Sebastian Luis', 'Raymundo', '09634425756', 'Sesame Straight', 'SAiss city', '1234'),
(6, 2, 'APX-6A2DB40EC769D', 'Sebastian Luis', 'Raymundo', '09634425756', 'Sesame Straight', 'SAiss city', '1234');

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
  `phone_number` varchar(20) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_profiles_tbl`
--

INSERT INTO `users_profiles_tbl` (`profile_id`, `user_id`, `bio`, `street_address`, `city`, `zip_code`, `phone_number`, `image_path`, `birthday`) VALUES
(1, 1, 'hello its me test', '0616 Purok 2 Turbina, Laguna', 'Calamba City', '4027', '09976829526', 'assets/images/profiles/profile_1_1781327573.png', NULL),
(2, 2, '', 'Sesame Straight', 'SAiss city', '1234', '09634425756', 'assets/images/profiles/profile_2_1781379852.jpg', '2005-07-22');

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
-- Indexes for table `admin_activity_tbl`
--
ALTER TABLE `admin_activity_tbl`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_activity_type` (`activity_type`);

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
  ADD UNIQUE KEY `uniq_user_order_product` (`user_id`,`order_id`,`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

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
-- AUTO_INCREMENT for table `admin_activity_tbl`
--
ALTER TABLE `admin_activity_tbl`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cart_items_tbl`
--
ALTER TABLE `cart_items_tbl`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_tbl`
--
ALTER TABLE `category_tbl`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `coupon_code`
--
ALTER TABLE `coupon_code`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coupon_usage_tbl`
--
ALTER TABLE `coupon_usage_tbl`
  MODIFY `usage_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites_tbl`
--
ALTER TABLE `favorites_tbl`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications_tbl`
--
ALTER TABLE `notifications_tbl`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders_tbl`
--
ALTER TABLE `orders_tbl`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items_tbl`
--
ALTER TABLE `order_items_tbl`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_status_tbl`
--
ALTER TABLE `order_status_tbl`
  MODIFY `status_log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `payments_tbl`
--
ALTER TABLE `payments_tbl`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products_tbl`
--
ALTER TABLE `products_tbl`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_images_tbl`
--
ALTER TABLE `product_images_tbl`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews_tbl`
--
ALTER TABLE `reviews_tbl`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shipping_address_tbl`
--
ALTER TABLE `shipping_address_tbl`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users_profiles_tbl`
--
ALTER TABLE `users_profiles_tbl`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  ADD CONSTRAINT `orders_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_tbl` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_tbl_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupon_code` (`coupon_id`);

--
-- Constraints for table `order_status_tbl`
--
ALTER TABLE `order_status_tbl`
  ADD CONSTRAINT `order_status_tbl_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders_tbl` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_status_tbl_ibfk_2` FOREIGN KEY (`updated_by_admin`) REFERENCES `admin_users_tbl` (`admin_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
