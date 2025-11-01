<?php
session_start();
require_once 'db_connect.php';

// Require login
requireLogin();

// Prevent admins from listing clothes
if (isAdmin()) {
    header('Location: admin_dashboard.php');
    exit();
}

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $type = trim($_POST['type']);
    $location = trim($_POST['location']);
    $rent_per_day = floatval($_POST['rent_per_day']);
    $condition_status = $_POST['condition_status'];
    $fixed_deposit = floatval($_POST['fixed_deposit']);
    $description = trim($_POST['description']);
    
    // Validation
    if (empty($name) || empty($type) || empty($location) || $rent_per_day <= 0 || empty($condition_status) || $fixed_deposit <= 0) {
        $error = 'All fields are required and must have valid values.';
    } elseif (!isset($_FILES['dress_image']) || $_FILES['dress_image']['error'] != 0) {
        $error = 'Please upload a dress image.';
    } else {
        try {
            // Handle dress image upload
            $upload_dir = 'uploads/dress_images/';
            $file_extension = strtolower(pathinfo($_FILES['dress_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['dress_image']['tmp_name'], $upload_path)) {
                    // Insert dress into database
                    $stmt = $pdo->prepare("INSERT INTO clothes (user_id, category_id, name, type, location, rent_per_day, condition_status, fixed_deposit, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$user['id'], $category_id, $name, $type, $location, $rent_per_day, $condition_status, $fixed_deposit, $description, $new_filename]);
                    
                    $success = '✅ Your dress has been listed successfully!';
                    
                    // Clear form data
                    $name = $type = $location = $rent_per_day = $condition_status = $fixed_deposit = $description = '';
                } else {
                    $error = 'Failed to upload dress image.';
                }
            } else {
                $error = 'Please upload a valid image file (JPG, PNG, GIF).';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Your Cloth - SajiloWear</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .list-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: #800000;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(128,0,0,0.3);
        }
        .btn:hover {
            background: #a00000;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(128,0,0,0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            margin-right: 10px;
            width: auto;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
            transform: translateY(-2px);
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .back-link {
            margin-bottom: 20px;
        }
        .back-link a {
            color: #800000;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link a:hover {
            text-decoration: underline;
            color: #a00000;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div><b>SajiloWear</b></div>
        <div>
            <a href="home.php">Home</a>
            <a href="home.php#featured">Browse</a>
            <a href="list_cloth.php">List Your Cloth</a>
            <a href="home.php#about">About</a>
            <a href="home.php#contact">Contact</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </div>

    <div class="list-container">
        <div class="back-link">
            <a href="profile.php">← Back to Profile</a>
        </div>

        <div class="form-container">
            <h2>👗 List Your Traditional Cloth</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Dress Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" 
                           placeholder="e.g., Beautiful Red Saree" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category:</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                            $categories = $stmt->fetchAll();
                            foreach ($categories as $category) {
                                $selected = (($category_id ?? '') == $category['id']) ? 'selected' : '';
                                echo "<option value='{$category['id']}' $selected>" . htmlspecialchars($category['name']) . "</option>";
                            }
                        } catch (PDOException $e) {
                            // Handle error silently
                        }
                        ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="type">Dress Type:</label>
                        <input type="text" id="type" name="type" 
                               value="<?php echo htmlspecialchars($type ?? ''); ?>" 
                               placeholder="e.g., Red Saree, Blue Lehenga, Wedding Sherwani" required>
                    </div>

                    <div class="form-group">
                        <label for="condition_status">Condition:</label>
                        <select id="condition_status" name="condition_status" required>
                            <option value="">Select Condition</option>
                            <option value="New" <?php echo (($condition_status ?? '') == 'New') ? 'selected' : ''; ?>>New</option>
                            <option value="Good" <?php echo (($condition_status ?? '') == 'Good') ? 'selected' : ''; ?>>Good</option>
                            <option value="Fair" <?php echo (($condition_status ?? '') == 'Fair') ? 'selected' : ''; ?>>Fair</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location ?? ''); ?>" 
                           placeholder="e.g., Kathmandu, Nepal" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rent_per_day">Rent per Day (NPR):</label>
                        <input type="number" id="rent_per_day" name="rent_per_day" 
                               value="<?php echo htmlspecialchars($rent_per_day ?? ''); ?>" 
                               placeholder="2000" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="fixed_deposit">Fixed Deposit (NPR):</label>
                        <input type="number" id="fixed_deposit" name="fixed_deposit" 
                               value="<?php echo htmlspecialchars($fixed_deposit ?? ''); ?>" 
                               placeholder="5000" min="0" step="0.01" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" 
                              placeholder="Describe your dress, its features, and any special notes..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="dress_image">Upload Dress Image:</label>
                    <input type="file" id="dress_image" name="dress_image" accept="image/*" required>
                    <small style="color: #666;">Supported formats: JPG, PNG, GIF (Max size: 5MB)</small>
                </div>

                <button type="submit" class="btn">List My Dress</button>
            </form>
        </div>
    </div>

    <script>
        // Show success message with animation
        <?php if ($success): ?>
            setTimeout(function() {
                const successDiv = document.querySelector('.success');
                if (successDiv) {
                    successDiv.style.background = '#28a745';
                    successDiv.style.color = 'white';
                    successDiv.style.fontWeight = 'bold';
                }
            }, 100);
        <?php endif; ?>
    </script>
</body>
</html>
