-- Insert Super Admin
INSERT INTO users (username, password, email, role) VALUES
('superadmin', '$2y$10$eW5Z1z5Q8b1f3k7F9a1eOe4j6h5d3k7F9a1eOe4j6h5d3k7F9a1eOe', 'superadmin@example.com', 'superadmin');

-- Insert Brands (8)
INSERT INTO brands (name, logo) VALUES
('Nike', 'nike_logo.png'),
('Adidas', 'adidas_logo.png'),
('Puma', 'puma_logo.png'),
('Reebok', 'reebok_logo.png'),
('New Balance', 'newbalance_logo.png'),
('Under Armour', 'underarmour_logo.png'),
('ASICS', 'asics_logo.png'),
('Converse', 'converse_logo.png');

-- Insert Categories (8)
INSERT INTO categories (name, parent_id) VALUES
('Men', NULL),
('Women', NULL),
('Kids', NULL),
('Running', 1),
('Basketball', 1),
('Casual', 2),
('Training', 1),
('Lifestyle', 2);

-- Insert Products (30)
INSERT INTO products (name, brand_id, category_id, base_price, description, release_date, featured_image, status) VALUES
('Nike Air Max 90', 1, 4, 120.00, 'Classic running shoe with Air Max cushioning.', '2023-01-15', 'nike_airmax90.png', 'active'),
('Adidas Ultraboost 21', 2, 4, 180.00, 'High-performance running shoe with Boost technology.', '2023-02-10', 'adidas_ultraboost21.png', 'active'),
('Puma RS-X', 3, 6, 110.00, 'Retro-inspired casual sneaker with bold design.', '2023-03-05', 'puma_rsx.png', 'active'),
('Reebok Nano X', 4, 7, 130.00, 'Versatile training shoe for all workouts.', '2023-04-01', 'reebok_nano_x.png', 'active'),
('New Balance 990v5', 5, 8, 175.00, 'Premium lifestyle sneaker with classic style.', '2023-05-20', 'newbalance_990v5.png', 'active'),
('Under Armour HOVR Phantom', 6, 4, 140.00, 'Running shoe with energy return technology.', '2023-06-15', 'ua_hovr_phantom.png', 'active'),
('ASICS Gel-Kayano 27', 7, 4, 160.00, 'Stability running shoe with gel cushioning.', '2023-07-10', 'asics_gel_kayano27.png', 'active'),
('Converse Chuck Taylor All Star', 8, 6, 60.00, 'Iconic casual sneaker with timeless design.', '2023-08-05', 'converse_chucktaylor.png', 'active'),

('Nike Air Jordan 1', 1, 5, 150.00, 'Legendary basketball shoe with premium materials.', '2023-01-20', 'nike_airjordan1.png', 'active'),
('Adidas Forum Low', 2, 5, 100.00, 'Classic basketball sneaker with modern updates.', '2023-02-25', 'adidas_forum_low.png', 'active'),
('Puma Cali', 3, 6, 90.00, 'Casual sneaker with vintage style.', '2023-03-15', 'puma_cali.png', 'active'),
('Reebok Classic Leather', 4, 6, 85.00, 'Timeless casual sneaker with leather upper.', '2023-04-10', 'reebok_classic_leather.png', 'active'),
('New Balance Fresh Foam 1080', 5, 4, 150.00, 'Comfortable running shoe with Fresh Foam cushioning.', '2023-05-25', 'newbalance_freshfoam1080.png', 'active'),
('Under Armour Charged Rogue', 6, 7, 110.00, 'Training shoe with charged cushioning.', '2023-06-20', 'ua_charged_rogue.png', 'active'),
('ASICS Gel-Nimbus 23', 7, 4, 150.00, 'Cushioned running shoe for long distances.', '2023-07-15', 'asics_gel_nimbus23.png', 'active'),
('Converse One Star', 8, 6, 70.00, 'Casual sneaker with suede upper.', '2023-08-10', 'converse_onestar.png', 'active'),

('Nike React Infinity Run', 1, 4, 160.00, 'Running shoe designed to reduce injury.', '2023-01-30', 'nike_react_infinity.png', 'active'),
('Adidas NMD_R1', 2, 8, 140.00, 'Lifestyle sneaker with Boost midsole.', '2023-02-28', 'adidas_nmd_r1.png', 'active'),
('Puma Suede Classic', 3, 6, 75.00, 'Classic suede casual sneaker.', '2023-03-20', 'puma_suede_classic.png', 'active'),
('Reebok Zig Kinetica', 4, 7, 120.00, 'Innovative training shoe with zigzag sole.', '2023-04-15', 'reebok_zig_kinetica.png', 'active'),
('New Balance 574', 5, 6, 80.00, 'Casual sneaker with retro design.', '2023-05-30', 'newbalance_574.png', 'active'),
('Under Armour Curry 8', 6, 5, 140.00, 'Basketball shoe endorsed by Stephen Curry.', '2023-06-25', 'ua_curry8.png', 'active'),
('ASICS Gel-Quantum 360', 7, 7, 130.00, 'Training shoe with 360 gel cushioning.', '2023-07-20', 'asics_gel_quantum360.png', 'active'),
('Converse Run Star Hike', 8, 8, 100.00, 'Lifestyle sneaker with chunky sole.', '2023-08-15', 'converse_runstarhike.png', 'active'),

('Nike Zoom Freak 3', 1, 5, 130.00, 'Basketball shoe for Giannis Antetokounmpo.', '2023-01-10', 'nike_zoomfreak3.png', 'active'),
('Adidas Adizero Boston', 2, 4, 130.00, 'Lightweight running shoe for speed.', '2023-02-18', 'adidas_adizeroboston.png', 'active'),
('Puma LQDCell Origin', 3, 7, 110.00, 'Training shoe with LQD cushioning.', '2023-03-25', 'puma_lqdcell_origin.png', 'active'),
('Reebok Classic Nylon', 4, 6, 70.00, 'Retro casual sneaker with nylon upper.', '2023-04-20', 'reebok_classic_nylon.png', 'active'),
('New Balance 860v11', 5, 4, 130.00, 'Stability running shoe.', '2023-05-15', 'newbalance_860v11.png', 'active'),
('Under Armour Micro G', 6, 7, 90.00, 'Training shoe with Micro G foam.', '2023-06-10', 'ua_microg.png', 'active'),
('ASICS Gel-DS Trainer 26', 7, 4, 120.00, 'Lightweight running shoe.', '2023-07-05', 'asics_gel_ds_trainer26.png', 'active'),
('Converse All Star Pro BB', 8, 5, 120.00, 'Basketball sneaker with modern design.', '2023-08-01', 'converse_allstar_probb.png', 'active');

-- Insert Product Variants (62+)
-- For simplicity, assuming product IDs auto increment from 1 to 30

-- Nike Air Max 90 (id=1)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(1, '7', 'White/Red', 10, 'NA90-WR-7', NULL),
(1, '8', 'White/Red', 15, 'NA90-WR-8', NULL),
(1, '9', 'White/Red', 20, 'NA90-WR-9', NULL),
(1, '10', 'Black/White', 25, 'NA90-BW-10', NULL),
(1, '11', 'Black/White', 5, 'NA90-BW-11', NULL);

-- Adidas Ultraboost 21 (id=2)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(2, '7', 'Core Black', 20, 'AU21-CB-7', NULL),
(2, '8', 'Core Black', 25, 'AU21-CB-8', NULL),
(2, '9', 'Core Black', 30, 'AU21-CB-9', NULL),
(2, '10', 'Cloud White', 18, 'AU21-CW-10', NULL),
(2, '11', 'Cloud White', 12, 'AU21-CW-11', NULL);

-- Puma RS-X (id=3)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(3, '7', 'Red/White', 12, 'PRSX-RW-7', NULL),
(3, '8', 'Red/White', 18, 'PRSX-RW-8', NULL),
(3, '9', 'Red/White', 10, 'PRSX-RW-9', NULL),
(3, '10', 'Black/Blue', 15, 'PRSX-BB-10', NULL);

-- Reebok Nano X (id=4)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(4, '8', 'Black/White', 14, 'RNX-BW-8', NULL),
(4, '9', 'Black/White', 16, 'RNX-BW-9', NULL),
(4, '10', 'Black/Red', 10, 'RNX-BR-10', NULL);

-- New Balance 990v5 (id=5)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(5, '7', 'Grey', 10, 'NB990-G-7', NULL),
(5, '8', 'Grey', 8, 'NB990-G-8', NULL),
(5, '9', 'Grey', 6, 'NB990-G-9', NULL);

-- Under Armour HOVR Phantom (id=6)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(6, '8', 'Black/Red', 20, 'UAHP-BR-8', NULL),
(6, '9', 'Black/Red', 25, 'UAHP-BR-9', NULL),
(6, '10', 'White/Black', 15, 'UAHP-WB-10', NULL);

-- ASICS Gel-Kayano 27 (id=7)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(7, '8', 'Blue/White', 18, 'AGK-BW-8', NULL),
(7, '9', 'Blue/White', 20, 'AGK-BW-9', NULL),
(7, '10', 'Black/Yellow', 12, 'AGK-BY-10', NULL);

-- Converse Chuck Taylor All Star (id=8)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(8, '6', 'White', 30, 'CCTS-W-6', NULL),
(8, '7', 'White', 25, 'CCTS-W-7', NULL),
(8, '8', 'Black', 20, 'CCTS-B-8', NULL);

-- Nike Air Jordan 1 (id=9)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(9, '8', 'Red/Black', 15, 'NAJ1-RB-8', NULL),
(9, '9', 'Red/Black', 10, 'NAJ1-RB-9', NULL),
(9, '10', 'White/Black', 20, 'NAJ1-WB-10', NULL);

-- Adidas Forum Low (id=10)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(10, '7', 'White/Blue', 18, 'AFL-WB-7', NULL),
(10, '8', 'White/Blue', 20, 'AFL-WB-8', NULL),
(10, '9', 'Black/White', 15, 'AFL-BW-9', NULL);

-- Puma Cali (id=11)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(11, '6', 'White', 20, 'PC-W-6', NULL),
(11, '7', 'White', 15, 'PC-W-7', NULL),
(11, '8', 'Black', 10, 'PC-B-8', NULL);

-- Reebok Classic Leather (id=12)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(12, '7', 'White', 20, 'RCL-W-7', NULL),
(12, '8', 'White', 15, 'RCL-W-8', NULL),
(12, '9', 'Black', 10, 'RCL-B-9', NULL);

-- New Balance Fresh Foam 1080 (id=13)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(13, '8', 'Blue', 18, 'NBFF1080-B-8', NULL),
(13, '9', 'Blue', 20, 'NBFF1080-B-9', NULL),
(13, '10', 'Grey', 15, 'NBFF1080-G-10', NULL);

-- Under Armour Charged Rogue (id=14)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(14, '8', 'Black', 20, 'UACR-B-8', NULL),
(14, '9', 'Black', 18, 'UACR-B-9', NULL),
(14, '10', 'White', 15, 'UACR-W-10', NULL);

-- ASICS Gel-Nimbus 23 (id=15)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(15, '8', 'Grey', 20, 'AGN23-G-8', NULL),
(15, '9', 'Grey', 22, 'AGN23-G-9', NULL),
(15, '10', 'Blue', 18, 'AGN23-B-10', NULL);

-- Converse One Star (id=16)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(16, '7', 'Black', 25, 'COS-B-7', NULL),
(16, '8', 'Black', 20, 'COS-B-8', NULL),
(16, '9', 'White', 15, 'COS-W-9', NULL);

-- Nike React Infinity Run (id=17)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(17, '8', 'White', 20, 'NRIW-8', NULL),
(17, '9', 'White', 18, 'NRIW-9', NULL),
(17, '10', 'Black', 15, 'NRIB-10', NULL);

-- Adidas NMD_R1 (id=18)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(18, '7', 'Black', 20, 'ANMD-B-7', NULL),
(18, '8', 'Black', 22, 'ANMD-B-8', NULL),
(18, '9', 'White', 18, 'ANMD-W-9', NULL);

-- Puma Suede Classic (id=19)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(19, '7', 'Red', 15, 'PSC-R-7', NULL),
(19, '8', 'Red', 18, 'PSC-R-8', NULL),
(19, '9', 'Blue', 12, 'PSC-B-9', NULL);

-- Reebok Zig Kinetica (id=20)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(20, '8', 'Black', 20, 'RZK-B-8', NULL),
(20, '9', 'Black', 22, 'RZK-B-9', NULL),
(20, '10', 'White', 18, 'RZK-W-10', NULL);

-- New Balance 574 (id=21)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(21, '7', 'Grey', 20, 'NB574-G-7', NULL),
(21, '8', 'Grey', 18, 'NB574-G-8', NULL),
(21, '9', 'Blue', 15, 'NB574-B-9', NULL);

-- Under Armour Curry 8 (id=22)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(22, '9', 'White/Blue', 20, 'UAC8-WB-9', NULL),
(22, '10', 'White/Blue', 18, 'UAC8-WB-10', NULL),
(22, '11', 'Black/Red', 15, 'UAC8-BR-11', NULL);

-- ASICS Gel-Quantum 360 (id=23)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(23, '8', 'Grey', 20, 'AGQ360-G-8', NULL),
(23, '9', 'Grey', 22, 'AGQ360-G-9', NULL),
(23, '10', 'Black/Blue', 18, 'AGQ360-BB-10', NULL);

-- Converse Run Star Hike (id=24)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(24, '7', 'White', 20, 'CRSH-W-7', NULL),
(24, '8', 'White', 18, 'CRSH-W-8', NULL),
(24, '9', 'Black', 15, 'CRSH-B-9', NULL);

-- Nike Zoom Freak 3 (id=25)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(25, '8', 'Black/Green', 20, 'NZF3-BG-8', NULL),
(25, '9', 'Black/Green', 18, 'NZF3-BG-9', NULL),
(25, '10', 'White/Black', 15, 'NZF3-WB-10', NULL);

-- Adidas Adizero Boston (id=26)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(26, '7', 'White/Red', 20, 'AADB-WR-7', NULL),
(26, '8', 'White/Red', 18, 'AADB-WR-8', NULL),
(26, '9', 'Black/White', 15, 'AADB-BW-9', NULL);

-- Puma LQDCell Origin (id=27)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(27, '8', 'Black/Orange', 20, 'PLCO-BO-8', NULL),
(27, '9', 'Black/Orange', 18, 'PLCO-BO-9', NULL),
(27, '10', 'White/Blue', 15, 'PLCO-WB-10', NULL);

-- Reebok Classic Nylon (id=28)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(28, '7', 'White', 20, 'RCN-W-7', NULL),
(28, '8', 'White', 18, 'RCN-W-8', NULL),
(28, '9', 'Black', 15, 'RCN-B-9', NULL);

-- New Balance 860v11 (id=29)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(29, '8', 'Blue', 20, 'NB860-B-8', NULL),
(29, '9', 'Blue', 18, 'NB860-B-9', NULL),
(29, '10', 'Grey', 15, 'NB860-G-10', NULL);

-- Under Armour Micro G (id=30)
INSERT INTO product_variants (product_id, size, color, stock, sku, price_override) VALUES
(30, '7', 'Black', 20, 'UAMG-B-7', NULL),
(30, '8', 'Black', 18, 'UAMG-B-8', NULL),
(30, '9', 'White', 15, 'UAMG-W-9', NULL);
