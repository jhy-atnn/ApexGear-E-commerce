-- Migration: add order_id to reviews_tbl for per-order review tracking
-- Safe to run on existing data: column is nullable, existing rows are untouched.

ALTER TABLE `reviews_tbl`
  ADD COLUMN `order_id` INT(11) NULL AFTER `product_id`;

ALTER TABLE `reviews_tbl`
  ADD KEY `order_id` (`order_id`);

-- Optional: prevent duplicate reviews for the same order+product combo
-- (allows reviewing the same product again from a different order).
ALTER TABLE `reviews_tbl`
  ADD UNIQUE KEY `uniq_user_order_product` (`user_id`, `order_id`, `product_id`);
