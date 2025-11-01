-- SajiloWear Database Setup
-- Run this in phpMyAdmin to create the database and tables

CREATE DATABASE IF NOT EXISTS sajilowear;
USE sajilowear;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    location VARCHAR(100) NOT NULL,
    profile_photo VARCHAR(255) DEFAULT 'default_profile.jpg',
    last_login DATETIME,
    user_type ENUM('user', 'admin') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Clothes table
CREATE TABLE clothes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    location VARCHAR(100) NOT NULL,
    rent_per_day DECIMAL(10,2) NOT NULL,
    condition_status VARCHAR(20) NOT NULL,
    fixed_deposit DECIMAL(10,2) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    posted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_available BOOLEAN DEFAULT TRUE,
    is_approved BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Rentals table
CREATE TABLE rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dress_id INT NOT NULL,
    borrower_id INT NOT NULL,
    owner_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'requested',
    start_date DATE,
    end_date DATE,
    total_amount DECIMAL(10,2),
    borrower_location VARCHAR(100),
    delivery_option ENUM('delivery', 'pickup') NOT NULL,
    borrower_phone VARCHAR(20),
    special_notes TEXT,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dress_id) REFERENCES clothes(id) ON DELETE CASCADE,
    FOREIGN KEY (borrower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rental_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewee_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES rentals(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Saree', 'Traditional Indian sarees with various styles and fabrics'),
('Lehenga', 'Traditional Indian lehenga choli sets'),
('Kurta', 'Traditional Indian kurtas and kurtis'),
('Sherwani', 'Traditional Indian sherwani for men'),
('Daura Suruwal', 'Traditional Nepali daura suruwal for men'),
('Gunyu Cholo', 'Traditional Nepali gunyu cholo for women'),
('Jewelry', 'Traditional ornaments and jewelry pieces'),
('Accessories', 'Traditional accessories like bangles, bindis, etc.'),
('Other', 'Other traditional clothing items');

-- Create default admin user (password: admin123)
INSERT INTO users (fullname, email, password, location, user_type) VALUES
('Admin User', 'admin@sajilowear.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kathmandu', 'admin');

-- No other sample data - all data will be added through the application features
