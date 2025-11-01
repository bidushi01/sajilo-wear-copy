image.png<?php
session_start();
require_once 'db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST']) {
    $action = $_POST['action'];
    
    try {
        switch ($action) {
            case 'add':
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                
                if (empty($name)) {
                    $error = 'Category name is required.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                    $stmt->execute([$name, $description]);
                    $message = 'Category added successfully.';
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                
                if (empty($name)) {
                    $error = 'Category name is required.';
                } else {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $id]);
                    $message = 'Category updated successfully.';
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'Category deleted successfully.';
                break;
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Get all categories
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(cl.id) as item_count
        FROM categories c 
        LEFT JOIN clothes cl ON c.id = cl.category_id
        GROUP BY c.id
        ORDER BY c.name
    ");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: #f8f9fa;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }
        .admin-navbar {
            background: #800000;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-navbar h1 {
            margin: 0;
            font-size: 24px;
        }
        .admin-navbar .nav-links {
            display: flex;
            gap: 20px;
        }
        .admin-navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .admin-navbar a:hover {
            background: rgba(255,255,255,0.1);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .page-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .page-header h1 {
            color: #800000;
            margin: 0 0 10px 0;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-section h2 {
            color: #800000;
            margin: 0 0 20px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #800000;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #800000;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #a00000;
            color: white;
            text-decoration: none;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .categories-grid {
            display: grid;
            gap: 20px;
        }
        .category-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 4px solid #800000;
        }
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .category-header h3 {
            margin: 0;
            color: #333;
        }
        .item-count {
            background: #800000;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .category-info {
            margin-bottom: 15px;
        }
        .category-info p {
            margin: 5px 0;
            color: #666;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .edit-form {
            display: none;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="admin-navbar">
        <div class="container">
            <h1>🏷️ Manage Categories</h1>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_users.php">Manage Users</a>
                <a href="admin_listings.php">Manage Listings</a>
                <a href="admin_categories.php">Categories</a>
                <a href="home.php">View Site</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>Category Management</h1>
            <p>Manage clothing categories for better organization and filtering</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Add New Category Form -->
        <div class="form-section">
            <h2>Add New Category</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="name">Category Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" placeholder="Optional description for this category"></textarea>
                </div>
                <button type="submit" class="btn">Add Category</button>
            </form>
        </div>

        <!-- Categories List -->
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <div class="category-card">
                    <div class="category-header">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <span class="item-count"><?php echo $category['item_count']; ?> items</span>
                    </div>
                    
                    <div class="category-info">
                        <?php if ($category['description']): ?>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($category['description']); ?></p>
                        <?php endif; ?>
                        <p><strong>Created:</strong> <?php echo date('M j, Y', strtotime($category['created_at'])); ?></p>
                    </div>
                    
                    <div class="action-buttons">
                        <button onclick="toggleEditForm(<?php echo $category['id']; ?>)" class="btn">Edit</button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                    
                    <!-- Edit Form -->
                    <div id="edit-form-<?php echo $category['id']; ?>" class="edit-form">
                        <form method="POST">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                            <div class="form-group">
                                <label for="edit-name-<?php echo $category['id']; ?>">Category Name:</label>
                                <input type="text" id="edit-name-<?php echo $category['id']; ?>" name="name" 
                                       value="<?php echo htmlspecialchars($category['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-description-<?php echo $category['id']; ?>">Description:</label>
                                <textarea id="edit-description-<?php echo $category['id']; ?>" name="description"><?php echo htmlspecialchars($category['description']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Update</button>
                            <button type="button" onclick="toggleEditForm(<?php echo $category['id']; ?>)" class="btn">Cancel</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function toggleEditForm(categoryId) {
            const form = document.getElementById('edit-form-' + categoryId);
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>
</body>
</html>
