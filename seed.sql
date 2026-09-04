-- This is a genuine bcrypt hash and verifies correctly with PHP's password_verify().
SET @pw = '$2b$10$ed5UQtznok7UPI3k6fImKuzEQO5doFGeLSn9VX38XjwYROZXPNmqW';

-- ------------------------------------------------------------
-- USERS
-- ------------------------------------------------------------
INSERT INTO users (name, email, password, role, phone) VALUES
('Grace Wanjiru',  'retailer@reflex.test',   @pw, 'retailer',   '0711000001'),
('Peter Mwangi',   'retailer2@reflex.test',  @pw, 'retailer',   '0711000002'),
('Alice Njeri',    'dispatcher@reflex.test', @pw, 'dispatcher', '0711000003'),
('Brian Otieno',   'rider1@reflex.test',     @pw, 'rider',      '0711000004'),
('Collins Kiptoo',  'rider2@reflex.test',     @pw, 'rider',      '0711000005'),
('Dennis Mutua',   'rider3@reflex.test',     @pw, 'rider',      '0711000006');

-- Capture generated IDs (fresh install => 1..6 in insert order)
SET @retailer1 = (SELECT id FROM users WHERE email = 'retailer@reflex.test');
SET @retailer2 = (SELECT id FROM users WHERE email = 'retailer2@reflex.test');
SET @dispatcher1 = (SELECT id FROM users WHERE email = 'dispatcher@reflex.test');
SET @rider1 = (SELECT id FROM users WHERE email = 'rider1@reflex.test');
SET @rider2 = (SELECT id FROM users WHERE email = 'rider2@reflex.test');
SET @rider3 = (SELECT id FROM users WHERE email = 'rider3@reflex.test');

-- ------------------------------------------------------------
-- DELIVERIES (mixed statuses so the demo shows the full flow)
-- ------------------------------------------------------------

-- 1. Still pending, unassigned
INSERT INTO deliveries (retailer_id, rider_id, customer_name, customer_phone, pickup_location, delivery_location, package_description, notes, status)
VALUES (@retailer1, NULL, 'John Otieno', '0712345678', 'Reflex Store, Kisumu', 'Maseno University', 'Electronics accessories', 'Call customer before arrival.', 'PENDING');
SET @d1 = LAST_INSERT_ID();
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d1, 'PENDING', @retailer1, 'Delivery created');

-- 2. Assigned to a rider, not yet picked up
INSERT INTO deliveries (retailer_id, rider_id, customer_name, customer_phone, pickup_location, delivery_location, package_description, notes, status)
VALUES (@retailer1, @rider1, 'Mary Achieng', '0722334455', 'Reflex Store, Kisumu', 'Nyalenda Estate', 'Clothing parcel', NULL, 'ASSIGNED');
SET @d2 = LAST_INSERT_ID();
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d2, 'PENDING', @retailer1, 'Delivery created');
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d2, 'ASSIGNED', @dispatcher1, 'Assigned to Brian Otieno');

-- 3. Picked up
INSERT INTO deliveries (retailer_id, rider_id, customer_name, customer_phone, pickup_location, delivery_location, package_description, notes, status)
VALUES (@retailer2, @rider2, 'Samuel Kimani', '0733445566', 'City Electronics, Nairobi CBD', 'Westlands', 'Phone charger and earphones', 'Leave with security if unavailable.', 'PICKED_UP');
SET @d3 = LAST_INSERT_ID();
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d3, 'PENDING', @retailer2, 'Delivery created');
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d3, 'ASSIGNED', @dispatcher1, 'Assigned to Collins Kiptoo');
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d3, 'PICKED_UP', @rider2, 'Package picked up from store');

-- 4. In transit
INSERT INTO deliveries (retailer_id, rider_id, customer_name, customer_phone, pickup_location, delivery_location, package_description, notes, status)
VALUES (@retailer2, @rider2, 'Faith Chebet', '0744556677', 'City Electronics, Nairobi CBD', 'Kilimani', 'Laptop bag', NULL, 'IN_TRANSIT');
SET @d4 = LAST_INSERT_ID();
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d4, 'PENDING', @retailer2, 'Delivery created');
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d4, 'ASSIGNED', @dispatcher1, 'Assigned to Collins Kiptoo');
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d4, 'PICKED_UP', @rider2, 'Package picked up from store');
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d4, 'IN_TRANSIT', @rider2, 'On the way to customer');

-- 5. Delivered (completed flow)
INSERT INTO deliveries (retailer_id, rider_id, customer_name, customer_phone, pickup_location, delivery_location, package_description, notes, status)
VALUES (@retailer1, @rider3, 'Kevin Odhiambo', '0755667788', 'Reflex Store, Kisumu', 'Kondele', 'Kitchenware set', NULL, 'DELIVERED');
SET @d5 = LAST_INSERT_ID();
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d5, 'PENDING', @retailer1, 'Delivery created');
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d5, 'ASSIGNED', @dispatcher1, 'Assigned to Dennis Mutua');
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d5, 'PICKED_UP', @rider3, 'Package picked up from store');
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d5, 'IN_TRANSIT', @rider3, 'On the way to customer');
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d5, 'DELIVERED', @rider3, 'Delivered and confirmed by customer');

-- 6. A second pending delivery from retailer 2 to show "no rider yet" state
INSERT INTO deliveries (retailer_id, rider_id, customer_name, customer_phone, pickup_location, delivery_location, package_description, notes, status)
VALUES (@retailer2, NULL, 'Esther Wambui', '0766778899', 'City Electronics, Nairobi CBD', 'Lavington', 'Bluetooth speaker', 'Fragile - handle with care.', 'PENDING');
SET @d6 = LAST_INSERT_ID();
INSERT INTO delivery_status_history (delivery_id, status, changed_by, note) VALUES (@d6, 'PENDING', @retailer2, 'Delivery created');
