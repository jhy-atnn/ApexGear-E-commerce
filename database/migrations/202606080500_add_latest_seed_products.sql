INSERT IGNORE INTO categories_tbl (`category_id`, `category_name`) VALUES
(7, 'Tablet');

ALTER TABLE categories_tbl AUTO_INCREMENT = 8;

INSERT IGNORE INTO products_tbl (`product_id`, `name`, `brand_id`, `category_id`, `price`, `old_price`, `stock`, `rating`, `badge`, `badge_type`, `image`, `desc`, `is_archived`) VALUES
(15, 'Ariana Grande', 2, 7, 6969.00, NULL, 70, 6500, 'Popular', 'normal', 'assets/images/products/1780864421_apple_logo_png_index_content_uploads_10.png', NULL, 0),
(16, 'Justine Luige Malaiba', 2, 4, 122838.00, 139990.00, 11, 123, 'Popular', 'new', 'assets/images/products/1780864853_Malaiba.png', NULL, 0);

ALTER TABLE products_tbl AUTO_INCREMENT = 17;
