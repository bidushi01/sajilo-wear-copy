<?php
session_start();
require_once 'db_connect.php';

// Require login
requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dress_id = intval($_POST['dress_id']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $borrower_location = trim($_POST['borrower_location']);
    $delivery_option = $_POST['delivery_option'];
    $borrower_phone = trim($_POST['borrower_phone']);
    $special_notes = trim($_POST['special_notes']);
    
    // Validation
    if (empty($start_date) || empty($end_date)) {
        $error = 'Please select both start and end dates.';
    } elseif (empty($borrower_location)) {
        $error = 'Please provide your location.';
    } elseif (empty($borrower_phone)) {
        $error = 'Please provide your phone number.';
    } elseif (strtotime($start_date) < strtotime('today')) {
        $error = 'Start date cannot be in the past.';
    } elseif (strtotime($end_date) <= strtotime($start_date)) {
        $error = 'End date must be after start date.';
    } elseif (!preg_match('/^[0-9+\-\s()]{10,20}$/', $borrower_phone)) {
        $error = 'Please provide a valid phone number.';
    } else {
        try {
            // Get dress details
            $stmt = $pdo->prepare("SELECT * FROM clothes WHERE id = ? AND is_available = 1");
            $stmt->execute([$dress_id]);
            $dress = $stmt->fetch();
            
            if (!$dress) {
                $error = 'Dress not found or not available for rent.';
            } elseif ($dress['user_id'] == $user['id']) {
                $error = 'You cannot rent your own dress.';
            } else {
                // Check if user already has a pending request for this dress (not declined)
                $stmt = $pdo->prepare("SELECT id FROM rentals WHERE dress_id = ? AND borrower_id = ? AND status IN ('requested', 'approved')");
                $stmt->execute([$dress_id, $user['id']]);
                
                if ($stmt->fetch()) {
                    $error = 'You already have a pending request for this dress. Please wait for the owner to respond.';
                } else {
                    // Calculate total amount
                    $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
                    $total_amount = $dress['rent_per_day'] * $days;
                    
                    // Insert rental request
                    $stmt = $pdo->prepare("INSERT INTO rentals (dress_id, borrower_id, owner_id, start_date, end_date, total_amount, borrower_location, delivery_option, borrower_phone, special_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$dress_id, $user['id'], $dress['user_id'], $start_date, $end_date, $total_amount, $borrower_location, $delivery_option, $borrower_phone, $special_notes]);
                    
                    // Show success alert and redirect to home
                    echo "<script>
                        alert('Rental request submitted successfully! The owner will review your request.');
                        window.location.href = 'home.php';
                    </script>";
                    exit();
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get dress details for display
$dress_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$dress = null;

if ($dress_id) {
    try {
        $stmt = $pdo->prepare("SELECT c.*, u.fullname as owner_name FROM clothes c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
        $stmt->execute([$dress_id]);
        $dress = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

if (!$dress) {
    header('Location: home.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Rental - SajiloWear</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .request-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .dress-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .dress-info img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #800000;
        }
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
            background: white;
        }
        .form-group select:focus {
            outline: none;
            border-color: #800000;
        }
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
            resize: vertical;
            min-height: 80px;
        }
        .form-group textarea:focus {
            outline: none;
            border-color: #800000;
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
        }
        .btn:hover {
            background: #a00000;
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

    <div class="request-container">
        <div class="back-link">
            <a href="home.php">← Back to Home</a>
        </div>

        <div class="form-container">
            <h2>Request Rental</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="dress-info">
                <img src="uploads/dress_images/<?php echo htmlspecialchars($dress['image']); ?>" 
                     alt="<?php echo htmlspecialchars($dress['name']); ?>">
                <h3><?php echo htmlspecialchars($dress['name']); ?></h3>
                <p><strong>Owner:</strong> <?php echo htmlspecialchars($dress['owner_name']); ?></p>
                <p><strong>Rent per day:</strong> NPR <?php echo number_format($dress['rent_per_day']); ?></p>
                <p><strong>Deposit:</strong> NPR <?php echo number_format($dress['fixed_deposit']); ?></p>
            </div>

            <form method="POST">
                <input type="hidden" name="dress_id" value="<?php echo $dress['id']; ?>">
                
                <div class="form-group">
                    <label for="borrower_location">Your Location:</label>
                    <input type="text" id="borrower_location" name="borrower_location" 
                           value="<?php echo htmlspecialchars($borrower_location ?? ''); ?>" 
                           placeholder="e.g., Kathmandu, Nepal" required>
                </div>

                <div class="form-group">
                    <label for="borrower_phone">Your Phone Number:</label>
                    <input type="tel" id="borrower_phone" name="borrower_phone" 
                           value="<?php echo htmlspecialchars($borrower_phone ?? ''); ?>" 
                           placeholder="e.g., +977 98XXXXXXXX" required>
                </div>

                <div class="form-group">
                    <label for="delivery_option">Delivery Option:</label>
                    <select id="delivery_option" name="delivery_option" required>
                        <option value="">Select Option</option>
                        <option value="pickup" <?php echo (($delivery_option ?? '') == 'pickup') ? 'selected' : ''; ?>>I will come to pickup</option>
                        <option value="delivery" <?php echo (($delivery_option ?? '') == 'delivery') ? 'selected' : ''; ?>>Please deliver to me</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" 
                           value="<?php echo htmlspecialchars($start_date ?? ''); ?>" 
                           min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" 
                           value="<?php echo htmlspecialchars($end_date ?? ''); ?>" 
                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                </div>

                <div class="form-group">
                    <label for="special_notes">Special Notes (Optional):</label>
                    <textarea id="special_notes" name="special_notes" 
                              placeholder="Any special requests or notes for the owner..."
                              rows="3"><?php echo htmlspecialchars($special_notes ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn">Submit Rental Request</button>
            </form>
        </div>
    </div>

    <script>
        // Update end date minimum when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const nextDay = new Date(startDate);
            nextDay.setDate(nextDay.getDate() + 1);
            document.getElementById('end_date').min = nextDay.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
