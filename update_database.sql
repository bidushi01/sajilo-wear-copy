-- Update existing rentals table to add new fields
-- Run this in phpMyAdmin if you already have the database set up

USE sajilowear;

-- Add new columns to rentals table
ALTER TABLE rentals 
ADD COLUMN borrower_location VARCHAR(100) AFTER total_amount,
ADD COLUMN delivery_option ENUM('delivery', 'pickup') NOT NULL DEFAULT 'pickup' AFTER borrower_location,
ADD COLUMN borrower_phone VARCHAR(20) AFTER delivery_option,
ADD COLUMN special_notes TEXT AFTER borrower_phone;
