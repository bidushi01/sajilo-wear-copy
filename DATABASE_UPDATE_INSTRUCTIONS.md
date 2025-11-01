# Database Update Instructions

## 🗄️ Update Your Database

Since you already have the database set up, you need to add the new fields to your existing `rentals` table.

### Step 1: Go to phpMyAdmin
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click on your `sajilowear` database

### Step 2: Run the Update SQL
1. Click on the "SQL" tab at the top
2. Copy and paste this SQL code:

```sql
-- Add new columns to rentals table
ALTER TABLE rentals 
ADD COLUMN borrower_location VARCHAR(100) AFTER total_amount,
ADD COLUMN delivery_option ENUM('delivery', 'pickup') NOT NULL DEFAULT 'pickup' AFTER borrower_location,
ADD COLUMN borrower_phone VARCHAR(20) AFTER delivery_option,
ADD COLUMN special_notes TEXT AFTER borrower_phone;
```

3. Click "Go" to execute the SQL

### Step 3: Verify the Update
1. Click on the `rentals` table
2. You should see the new columns:
   - `borrower_location`
   - `delivery_option` 
   - `borrower_phone`
   - `special_notes`

## ✅ What's New

Now when users request to rent a dress, they can provide:
- **Location**: Where they are located
- **Phone Number**: Contact number for the owner
- **Delivery Option**: Choose between "I will come to pickup" or "Please deliver to me"
- **Special Notes**: Any additional requests or notes

The owner will see all this information when reviewing rental requests in their dashboard!

## 🎯 Test the New Features

1. **Request a Rental**: Go to any dress and click "Rent Now"
2. **Fill the Form**: You'll now see the new fields
3. **Check Owner Dashboard**: The owner will see all the new information
4. **View Your Activity**: Your rental requests will show the details you provided

Everything is now ready to use! 🚀
