# SajiloWear - Complete Backend Setup Instructions

## 🚀 Quick Setup Guide

### 1. Database Setup
1. Open **phpMyAdmin** in your browser (usually `http://localhost/phpmyadmin`)
2. Create a new database called `sajilowear`
3. Import the SQL file: Go to **Import** tab → Choose `database_setup.sql` → Click **Go**

### 2. File Structure
Your project should now have these files:
```
Sajilo___Wear/
├── index.php (redirects to home.php)
├── home.php (main homepage)
├── signup.php
├── login.php
├── logout.php
├── profile.php
├── list_cloth.php
├── view_user.php
├── db_connect.php
├── database_setup.sql
├── uploads/
│   ├── profile_photos/
│   └── dress_images/
└── styles.css (your existing CSS)
```

### 3. Access Your Website
- Open browser and go to: `http://localhost/Sajilo___Wear/`
- The site will automatically redirect to the dynamic homepage

## 🎯 Features Implemented

### ✅ User Authentication
- **Sign Up**: Full registration with profile photo upload
- **Login**: Secure password verification
- **Logout**: Session destruction with confirmation
- **Session Management**: Automatic login state handling

### ✅ User Profile System
- **Profile Page**: Shows user info, photo, and dashboard
- **Change Profile Photo**: Upload and update profile pictures
- **User Dashboard**: Three sections:
  - Your Dress Listings
  - Order Requests
  - Order Activity

### ✅ Dress Listing System
- **List Cloth**: Complete form with all required fields
- **Image Upload**: Dress photos with validation
- **Success Messages**: Confirmation after listing
- **Form Validation**: All fields required and validated

### ✅ Dynamic Homepage
- **Welcome Message**: Personalized for logged-in users
- **Dynamic Content**: All dresses loaded from database
- **Search Functionality**: Filter dresses by name/type
- **Public Browsing**: Anyone can browse without login

### ✅ Public User Profiles
- **View Profile**: Click on any dress owner to see their profile
- **Public Info**: Only shows name, location, and their dresses
- **Private Info**: Email and other details are hidden

## 🔧 Database Tables Created

### `users` Table
- id, fullname, email, password, location, profile_photo, last_login, created_at

### `clothes` Table
- id, user_id, name, type, location, rent_per_day, condition_status, fixed_deposit, description, image, posted_date, is_available

### `rentals` Table
- id, dress_id, borrower_id, owner_id, status, start_date, end_date, total_amount, created_at

## 🎨 User Experience Features

### For Logged-in Users:
- **Navbar**: Shows "Profile" and "Logout" instead of "Login/Sign Up"
- **Welcome Message**: "👋 Welcome, [Name]!" on homepage
- **Direct Access**: "List Your Cloth" button always visible
- **Profile Dashboard**: Complete user management

### For Guests:
- **Public Browsing**: Can view all dresses without login
- **Login Prompt**: Alert when trying to rent without login
- **Sign Up Encouragement**: Clear path to registration

## 🔒 Security Features
- **Password Hashing**: All passwords securely hashed
- **Session Management**: Proper login/logout handling
- **File Upload Validation**: Only image files allowed
- **SQL Injection Protection**: Prepared statements used
- **Input Validation**: All user inputs sanitized

## 📱 How to Test

### 1. Create Your First Account
1. Go to `http://localhost/Sajilo___Wear/`
2. Click "Sign Up"
3. Fill in all details and upload a profile photo
4. You'll be redirected to your profile page

### 2. List Your First Dress
1. From your profile, click "List Your Traditional Cloth"
2. Fill in all dress details
3. Upload a dress image
4. Submit and see success message

### 3. Browse as Guest
1. Logout from your account
2. Browse the homepage to see your listed dress
3. Click "View Profile" to see your public profile
4. Try to rent without login (should show login alert)

### 4. Test Login/Logout
1. Login with your credentials
2. Notice the navbar changes
3. See personalized welcome message
4. Logout with confirmation dialog

## 🚨 Important Notes

### File Permissions
Make sure the `uploads/` directory and subdirectories have write permissions:
- `uploads/profile_photos/`
- `uploads/dress_images/`

### Default Profile Photo
The system uses a default profile photo for users who don't upload one. Make sure the file exists or update the code to handle missing default images.

### Database Connection
If you get database connection errors, check:
1. XAMPP MySQL is running
2. Database name is `sajilowear`
3. Username is `root` (default)
4. Password is empty (default)

## 🎉 You're All Set!

Your SajiloWear backend is now complete with:
- ✅ User registration and authentication
- ✅ Profile management with photo uploads
- ✅ Dress listing system
- ✅ Dynamic homepage
- ✅ Public user profiles
- ✅ Session management
- ✅ Security features

The system is ready for users to sign up, list their traditional clothes, and browse/rent from others!
