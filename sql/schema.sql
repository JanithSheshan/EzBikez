-- Create database
CREATE DATABASE IF NOT EXISTS ezbikez;
USE ezbikez;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    terms TINYINT(1) DEFAULT 0,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bikes table (bike models)
CREATE TABLE bikes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model VARCHAR(100) NOT NULL,
    category ENUM('A', 'B', 'C') NOT NULL,
    price_per_day DECIMAL(10, 2) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bike items table (individual bikes)
CREATE TABLE bike_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bike_id INT NOT NULL,
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bike_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'confirmed', 'completed', 'rejected', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE,
    slip VARCHAR(255) DEFAULT NULL
);

-- Booking items table (links bookings to specific bike items)
CREATE TABLE booking_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    bike_item_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (bike_item_id) REFERENCES bike_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_bike (booking_id, bike_item_id)
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (name, email, password, is_admin) VALUES 
('Admin User', 'admin@ezbikez.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert sample bikes
INSERT INTO bikes (model, category, price_per_day, description) VALUES 
('Honda Activa 125', 'A', 2500.00, 'Comfortable and reliable scooter with great fuel efficiency. Perfect for city riding.'),
('Yamaha NMAX', 'A', 3000.00, 'Premium maxi-scooter with advanced features and comfortable ride.'),
('TVS Jupiter', 'B', 1500.00, 'Reliable and economical scooter with good storage space.'),
('Bajaj Pulsar 150', 'B', 2000.00, 'Popular motorcycle with good performance and fuel economy.'),
('Hero Splendor', 'C', 1000.00, 'Basic but reliable motorcycle with excellent fuel efficiency.'),
('Honda CD 110', 'C', 1200.00, 'Simple and economical motorcycle for everyday use.');

-- Insert bike items
INSERT INTO bike_items (bike_id) VALUES 
(1), (1), (1), 
(2), (2), 
(3), (3), (3), (3), 
(4), (4), (4), 
(5), (5), (5), (5), (5), 
(6), (6), (6), (6);