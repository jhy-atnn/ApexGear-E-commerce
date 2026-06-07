-- ApeX Gear shared seed data
-- Auto-generated from the local database. Do not edit by hand.
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `brands_tbl` (`brand_id`, `brand_name`) VALUES
('1', 'Lenovo'),
('2', 'ASUS'),
('3', 'Razer'),
('4', 'Logitech'),
('5', 'Sony'),
('6', 'Samsung'),
('7', 'Dell'),
('8', 'LG'),
('9', 'Apple'),
('10', 'HP'),
('11', 'Corsair'),
('12', 'NVIDIA')
ON DUPLICATE KEY UPDATE `brand_name` = VALUES(`brand_name`);

ALTER TABLE `brands_tbl` AUTO_INCREMENT = 13;

INSERT INTO `categories_tbl` (`category_id`, `category_name`) VALUES
('1', 'Laptops'),
('2', 'Peripherals'),
('3', 'Audio'),
('4', 'Phones'),
('5', 'Desktops'),
('6', 'GPUs'),
('7', 'Tablet')
ON DUPLICATE KEY UPDATE `category_name` = VALUES(`category_name`);

ALTER TABLE `categories_tbl` AUTO_INCREMENT = 8;

INSERT INTO `products_tbl` (`product_id`, `name`, `brand_id`, `category_id`, `price`, `old_price`, `stock`, `rating`, `badge`, `badge_type`, `image`, `desc`, `is_archived`) VALUES
('1', 'Legion 5 Pro — RTX 4070, 16\" QHD 165Hz', '1', '1', '119995.00', '127999.00', '10', '124', 'New', 'new', 'assets/images/products/1780862872_Copy_of_CAL_COMSOC_Ask.png', 'Dominate the competition with the Legion 5 Pro. Featuring a blistering 165Hz QHD display and the raw power of the RTX 4070, it delivers desktop-level gaming performance in a sleek, portable chassis.', '0'),
('2', 'ROG Zephyrus G14 — Ryzen 9, RTX 4060', '2', '1', '109995.00', '119995.00', '1', '89', 'SALE', 'ribbon', 'assets/images/products/zephyrusg14.png', 'The ultimate blend of power and portability. The ROG Zephyrus G14 packs a Ryzen 9 processor and RTX 4060 into a stunningly thin and light 14-inch form factor, perfect for gamers and creators on the move.', '0'),
('3', 'BlackWidow V4 Pro — Mechanical, RGB, Wireless', '3', '2', '13495.00', '15995.00', '20', '4210', 'Popular', 'normal', 'assets/images/products/blackwidow.png', 'Elevate your setup with the Razer BlackWidow V4 Pro. Experience unmatched tactile feedback with premium mechanical switches, immersive Razer Chroma RGB lighting, and lag-free wireless connectivity.', '0'),
('4', 'G Pro X Superlight 2 — Wireless Gaming Mouse', '4', '2', '7795.00', '8995.00', '15', '4340', NULL, NULL, 'assets/images/products/superlight.png', 'Engineered for esports professionals. The Logitech G Pro X Superlight 2 offers zero-latency wireless performance and an ultra-lightweight design to ensure maximum precision and speed.', '0'),
('5', 'WH-1000XM5 — Noise Cancelling, 30hr Battery', '5', '3', '15499.00', '20999.00', '12', '512', 'New', 'new', 'assets/images/products/sonywh.png', 'Immerse yourself in pure audio. The Sony WH-1000XM5 headphones feature industry-leading active noise cancellation, crystal-clear calls, and up to 30 hours of battery life for all-day comfort.', '0'),
('6', 'Galaxy S24 Ultra — 200MP, 5000mAh, Titanium', '6', '4', '84990.00', '94990.00', '8', '445', 'SALE', 'ribbon', 'assets/images/products/s24ultra.png', 'The pinnacle of mobile innovation. The Galaxy S24 Ultra boasts a premium titanium frame, a breathtaking 200MP camera system, and the integrated S Pen for unmatched productivity and creativity.', '0'),
('7', 'XPS Tower — Intel i9-14900K, RTX 4080, 64GB', '7', '5', '185000.00', '200000.00', '3', '78', NULL, NULL, 'assets/images/products/xpstower.png', 'Uncompromising performance for the most demanding tasks. The Dell XPS Tower is a powerhouse workstation equipped with an Intel i9 processor and RTX 4080, ready to tackle heavy rendering and high-end gaming.', '0'),
('8', 'UltraGear 27\" — 4K, 144Hz, 1ms, IPS, G-Sync', '8', '2', '39995.00', '45995.00', '7', '193', 'On Sale', 'sale', 'assets/images/products/ultragear.png', 'See every detail, feel every frame. The LG UltraGear 27-inch monitor delivers a stunning 4K IPS display with a hyper-fast 144Hz refresh rate and 1ms response time, optimized for G-Sync.', '0'),
('9', 'iPhone 17 Pro — Cosmic Orange - Aluminum', '9', '4', '109990.00', '119990.00', '15', '445', 'On Sale', 'sale', 'assets/images/products/iphone17pro.png', 'Apple\'s most advanced Pro model yet. The iPhone 17 Pro features a revolutionary new camera system, blazing-fast A-series silicon, and a striking Cosmic Orange aluminum finish.', '0'),
('10', 'Spectre x360 14\" — OLED, Intel Evo', '10', '1', '79995.00', '95000.00', '10', '156', '–15%', 'sale', 'assets/images/products/hpspectre.png', 'Versatility meets luxury. The HP Spectre x360 14 is a premium 2-in-1 convertible laptop featuring a vibrant OLED display and Intel Evo certification for exceptional responsiveness and battery life.', '0'),
('11', 'iPhone 15 Pro — 256GB, Natural Titanium', '9', '4', '69990.00', '76990.00', '25', '892', '–10%', 'sale', 'assets/images/products/iph15pro.png', 'Forged in titanium. The iPhone 15 Pro delivers incredible strength-to-weight ratio, the powerful A17 Pro chip for next-level mobile gaming, and a pro-class camera system.', '0'),
('12', 'K100 Air — Ultra-Thin, Wireless, RGB', '11', '2', '11995.00', '16500.00', '15', '340', '–25%', 'sale', 'assets/images/products/corsairk100air.png', 'Incredibly thin, undeniably powerful. The Corsair K100 Air is a premium wireless mechanical keyboard featuring a breathtakingly slim profile, tactile Cherry MX Ultra Low Profile switches, and dynamic RGB.', '0'),
('13', 'GeForce RTX 4070 Super — 12GB GDDR6X', '12', '6', '36500.00', '42000.00', '5', '215', '–13%', 'sale', 'assets/images/products/nvidiartx4070.png', 'Supercharge your PC. The NVIDIA GeForce RTX 4070 Super delivers exceptional frame rates at 1440p and 4K resolutions, powered by the ultra-efficient Ada Lovelace architecture and DLSS 3.', '0'),
('14', 'Omniverse', '12', '7', '6586.00', NULL, '21', '2344', 'New', 'new', 'assets/images/products/1780862851_HP_Logo_Background_PNG_Image.png', NULL, '0'),
('15', 'Ariana Grande', '2', '7', '6969.00', NULL, '70', '6500', 'Popular', 'normal', 'assets/images/products/1780864421_apple_logo_png_index_content_uploads_10.png', NULL, '0'),
('16', 'Justine Luige Malaiba', '2', '4', '122838.00', '139990.00', '11', '123', 'Popular', 'new', 'assets/images/products/1780864853_Malaiba.png', NULL, '0'),
('17', 'Semi-Final 2', '3', '5', '36623.00', NULL, '363', '111', 'New', 'normal', 'assets/images/products/1780865818_original_samsung_logo_10.png', NULL, '0')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `brand_id` = VALUES(`brand_id`), `category_id` = VALUES(`category_id`), `price` = VALUES(`price`), `old_price` = VALUES(`old_price`), `stock` = VALUES(`stock`), `rating` = VALUES(`rating`), `badge` = VALUES(`badge`), `badge_type` = VALUES(`badge_type`), `image` = VALUES(`image`), `desc` = VALUES(`desc`), `is_archived` = VALUES(`is_archived`);

ALTER TABLE `products_tbl` AUTO_INCREMENT = 18;

INSERT INTO `users_tbl` (`user_id`, `username`, `first_name`, `last_name`, `profile_picture`, `bio`, `gender`, `birthday`, `phone`, `email`, `password_hash`, `role`) VALUES
('1', 'Juday', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'atinon.jhody@gmail.com', '$2y$10$o/rJRTsc1AdhVnm/VOhI0eSPCOx9WAK8GDXgYtAANwYCBkYjullWi', 'customer')
ON DUPLICATE KEY UPDATE `user_id` = `user_id`;

ALTER TABLE `users_tbl` AUTO_INCREMENT = 2;

SET FOREIGN_KEY_CHECKS = 1;
