-- Add missing is_completed column to rentals table
ALTER TABLE rentals ADD COLUMN is_completed BOOLEAN DEFAULT FALSE;

-- Update existing completed rentals
UPDATE rentals SET is_completed = 1 WHERE status = 'completed';
