INSERT IGNORE INTO categories_tbl (`category_id`, `category_name`) VALUES
(7, 'Tablet');

ALTER TABLE categories_tbl AUTO_INCREMENT = 8;

INSERT IGNORE INTO products_tbl (`product_id`, `name`, `brand_id`, `category_id`, `price`, `old_price`, `stock`, `rating`, `badge`, `badge_type`, `image`, `desc`, `is_archived`) VALUES
(14, 'Omniverse', 12, 7, 6586.00, NULL, 21, 2344, 'New', 'new', 'assets/images/products/1780862851_HP_Logo_Background_PNG_Image.png', NULL, 0);

ALTER TABLE products_tbl AUTO_INCREMENT = 15;
