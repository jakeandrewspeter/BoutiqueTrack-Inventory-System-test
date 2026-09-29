INSERT INTO products (category_id, name, brand, description)
SELECT c.id, 'Everyday Linen Dress', 'Juba Studio', 'Lightweight dress for everyday wear.'
FROM categories c
WHERE c.name = 'Dresses'
  AND NOT EXISTS (SELECT 1 FROM products p WHERE p.name = 'Everyday Linen Dress');

INSERT IGNORE INTO product_variants (product_id, sku, size, color, cost_price, selling_price, stock_quantity, reorder_level)
SELECT p.id, 'BT-DRESS-001-S-NVY', 'S', 'Navy', 18000, 28000, 8, 3
FROM products p WHERE p.name = 'Everyday Linen Dress';

INSERT IGNORE INTO product_variants (product_id, sku, size, color, cost_price, selling_price, stock_quantity, reorder_level)
SELECT p.id, 'BT-DRESS-001-M-NVY', 'M', 'Navy', 18000, 28000, 3, 3
FROM products p WHERE p.name = 'Everyday Linen Dress';

INSERT INTO products (category_id, name, brand, description)
SELECT c.id, 'Cotton Weekend Shirt', 'River & Reed', 'Soft cotton shirt in a relaxed fit.'
FROM categories c
WHERE c.name = 'Tops'
  AND NOT EXISTS (SELECT 1 FROM products p WHERE p.name = 'Cotton Weekend Shirt');

INSERT IGNORE INTO product_variants (product_id, sku, size, color, cost_price, selling_price, stock_quantity, reorder_level)
SELECT p.id, 'BT-SHIRT-014-M-WHT', 'M', 'White', 12000, 20000, 11, 4
FROM products p WHERE p.name = 'Cotton Weekend Shirt';

INSERT INTO products (category_id, name, brand, description)
SELECT c.id, 'Classic Leather Sandal', 'Nile Steps', 'Everyday leather sandals.'
FROM categories c
WHERE c.name = 'Shoes'
  AND NOT EXISTS (SELECT 1 FROM products p WHERE p.name = 'Classic Leather Sandal');

INSERT IGNORE INTO product_variants (product_id, sku, size, color, cost_price, selling_price, stock_quantity, reorder_level)
SELECT p.id, 'BT-SANDAL-003-40-TAN', '40', 'Tan', 22000, 35000, 2, 3
FROM products p WHERE p.name = 'Classic Leather Sandal';