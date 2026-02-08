-- database/clean_data.sql

-- 1. ROUTES (Kochi to Trivandrum)
INSERT INTO routes (source, destination, distance_km)
VALUES ('Kochi', 'Trivandrum', 220);

-- 2. BUS (AC Bus on Route 1)
INSERT INTO buses (route_id, bus_number, bus_type, total_seats, departure_time, arrival_time)
VALUES (1, 'KL01AB1234', 'AC', 40, '08:00:00', '14:00:00');

-- 3. SEATS (Generate 10 sample seats for the Bus above)
-- Note: In a real app, PHP would generate these. Here we add them manually for testing.
INSERT INTO seats (bus_id, seat_number, is_available) VALUES
(1, 'L1', TRUE), (1, 'L2', TRUE), (1, 'L3', TRUE), (1, 'L4', TRUE), (1, 'L5', TRUE),
(1, 'A1', TRUE), (1, 'A2', TRUE), (1, 'A3', TRUE), (1, 'A4', TRUE), (1, 'A5', TRUE);

-- Optional: A Sleeper Bus to test that layout too
INSERT INTO routes (source, destination, distance_km) VALUES ('Bangalore', 'Chennai', 350);
INSERT INTO buses (route_id, bus_number, bus_type, total_seats, departure_time, arrival_time) VALUES (2, 'TN02AC9999', 'SLEEPER', 24, '22:00:00', '05:00:00');
INSERT INTO seats (bus_id, seat_number, is_available) VALUES
(2, 'L1', TRUE), (2, 'L2', TRUE), (2, 'U1', TRUE), (2, 'U2', TRUE);
