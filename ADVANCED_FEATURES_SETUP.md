# 🚀 Advanced Features Setup Instructions

## 🎯 **New Features Added:**

### 1. **Admin System**
- **Admin Login**: `admin@sajilowear.com` / `admin123`
- **Admin Dashboard**: Manage users, listings, categories
- **User Management**: Activate/deactivate users, view statistics
- **Listing Management**: Approve/disapprove listings, monitor content

### 2. **Review & Rating System**
- **User Reviews**: Rate and comment on completed rentals
- **5-Star Rating**: Visual star rating system
- **Review History**: Track all your reviews and ratings
- **Rental ID Tracking**: Reviews linked to specific rentals

### 3. **Category Management**
- **Pre-defined Categories**: Saree, Lehenga, Kurta, Sherwani, Jewelry, Accessories
- **Admin Category Control**: Add, edit, delete categories
- **Category Filtering**: Organize listings by categories

## 🗄️ **Database Updates Required:**

### **Step 1: Update Existing Database**

Run these SQL commands in phpMyAdmin:

```sql
-- Add new columns to users table
ALTER TABLE users 
ADD COLUMN user_type ENUM('user', 'admin') DEFAULT 'user' AFTER last_login,
ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER user_type;

-- Create categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add category_id to clothes table
ALTER TABLE clothes 
ADD COLUMN category_id INT AFTER user_id,
ADD COLUMN is_approved BOOLEAN DEFAULT TRUE AFTER is_available,
ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL;

-- Add is_completed to rentals table
ALTER TABLE rentals 
ADD COLUMN is_completed BOOLEAN DEFAULT FALSE AFTER special_notes;

-- Create reviews table
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
```

### **Step 2: Insert Sample Data**

```sql
-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Saree', 'Traditional Indian sarees with various styles and fabrics'),
('Lehenga', 'Traditional Indian lehenga choli sets'),
('Kurta', 'Traditional Indian kurtas and kurtis'),
('Sherwani', 'Traditional Indian sherwani for men'),
('Jewelry', 'Traditional ornaments and jewelry pieces'),
('Accessories', 'Traditional accessories like bangles, bindis, etc.');

-- Create default admin user (password: admin123)
INSERT INTO users (fullname, email, password, location, user_type) VALUES
('Admin User', 'admin@sajilowear.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kathmandu', 'admin');
```

## 🎮 **How to Use New Features:**

### **Admin Features:**
1. **Login as Admin**: Go to `admin_login.php`
2. **Email**: `admin@sajilowear.com`
3. **Password**: `admin123`
4. **Access Dashboard**: Manage users, listings, categories

### **Review System:**
1. **Complete a Rental**: Owner approves rental request
2. **Leave Review**: Go to `review_system.php`
3. **Rate & Comment**: 5-star rating with optional comments
4. **View Reviews**: See all your submitted reviews

### **Category System:**
1. **Admin Control**: Add/edit/delete categories in admin panel
2. **Listing Categories**: Assign categories when listing clothes
3. **Filter by Category**: Browse clothes by category

## 🔧 **Admin Dashboard Features:**

### **User Management:**
- View all registered users
- Activate/deactivate user accounts
- Delete user accounts
- View user statistics (listings, rentals)

### **Listing Management:**
- View all clothing listings
- Approve/disapprove listings
- Delete inappropriate listings
- Monitor listing content

### **Category Management:**
- Add new categories
- Edit existing categories
- Delete unused categories
- View category usage statistics

## ⭐ **Review System Features:**

### **For Users:**
- Rate completed rentals (1-5 stars)
- Write detailed comments
- View review history
- See ratings received from others

### **For System:**
- Prevent duplicate reviews
- Link reviews to specific rentals
- Track user reputation
- Build trust in the platform

## 🎯 **Category System:**

### **Pre-defined Categories:**
- **Saree**: Traditional Indian sarees
- **Lehenga**: Lehenga choli sets
- **Kurta**: Traditional kurtas and kurtis
- **Sherwani**: Men's traditional wear
- **Jewelry**: Traditional ornaments
- **Accessories**: Bangles, bindis, etc.

### **Admin Benefits:**
- Organize listings by category
- Add new categories as needed
- Monitor category popularity
- Improve user experience

## 🚀 **Ready to Use!**

After running the database updates:

1. **Admin Access**: Login with admin credentials
2. **User Reviews**: Complete rentals and leave reviews
3. **Category Management**: Organize listings by categories
4. **Enhanced Platform**: Professional rental platform with admin control

Your SajiloWear platform now has:
- ✅ **Admin Management System**
- ✅ **User Review & Rating System**
- ✅ **Category Management**
- ✅ **Enhanced User Experience**
- ✅ **Professional Platform Features**

Everything is ready for production use! 🎉
