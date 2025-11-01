<?php
session_start();
require_once 'db_connect.php';

// Get user ID from URL parameter
$view_user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$view_user_id) {
    header('Location: index.html');
    exit();
}

try {
    // Get user information
    $stmt = $pdo->prepare("SELECT id, fullname, profile_photo, location FROM users WHERE id = ?");
    $stmt->execute([$view_user_id]);
    $view_user = $stmt->fetch();
    
    if (!$view_user) {
        header('Location: index.html');
        exit();
    }
    
    // Get user's clothes
    $stmt = $pdo->prepare("SELECT * FROM clothes WHERE user_id = ? AND is_available = 1 ORDER BY posted_date DESC");
    $stmt->execute([$view_user_id]);
    $user_clothes = $stmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: index.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($view_user['fullname']); ?> - SajiloWear</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .profile-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 4px solid #007bff;
        }
        .profile-info h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .profile-info p {
            color: #666;
            margin: 5px 0;
        }
        .clothes-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .clothes-section h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .clothes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .cloth-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .cloth-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        .cloth-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .cloth-card-content {
            padding: 20px;
        }
        .cloth-card h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .cloth-card p {
            color: #666;
            margin: 5px 0;
        }
        .rent-button {
            width: 100%;
            padding: 12px;
            background: #800000;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(128,0,0,0.3);
        }
        .rent-button:hover {
            background: #a00000;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(128,0,0,0.4);
        }
        .rent-button:disabled {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
        .no-clothes {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        /* Reviews Section Styles */
        .reviews-section {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .reviews-section h2 {
            color: #800000;
            margin-bottom: 20px;
        }
        
        .rating-summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .average-rating {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .rating-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #800000;
        }
        
        .stars {
            font-size: 1.5em;
        }
        
        .review-count {
            color: #666;
            font-size: 1.1em;
        }
        
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .review-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .review-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .reviewer-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .reviewer-info {
            flex: 1;
        }
        
        .reviewer-info strong {
            display: block;
            color: #333;
            margin-bottom: 5px;
        }
        
        .reviewer-info strong a {
            color: #800000;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .reviewer-info strong a:hover {
            color: #a00000;
            text-decoration: underline;
        }
        
        .review-stars {
            font-size: 1.2em;
        }
        
        .review-date {
            color: #666;
            font-size: 0.9em;
        }
        
        .review-comment {
            color: #333;
            line-height: 1.5;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div><b>SajiloWear</b></div>
        <div>
            <a href="home.php">Home</a>
            <a href="home.php#featured">Browse</a>
            <a href="home.php#about">About</a>
            <a href="home.php#contact">Contact</a>
            <?php if (isLoggedIn()): ?>
                <a href="profile.php">Profile</a>
                <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="profile-container">
        <div class="back-link">
            <a href="home.php">← Back to Home</a>
        </div>

        <div class="profile-header">
            <img src="uploads/profile_photos/<?php echo htmlspecialchars($view_user['profile_photo']); ?>" 
                 alt="Profile Photo" class="profile-photo" 
                 onerror="this.src='uploads/profile_photos/default_profile.jpg'">
            
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($view_user['fullname']); ?></h1>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($view_user['location']); ?></p>
            </div>
        </div>

        <div class="clothes-section">
            <h2>Available Dresses</h2>
            
            <?php if (empty($user_clothes)): ?>
                <div class="no-clothes">
                    <p>This user hasn't listed any dresses yet.</p>
                </div>
            <?php else: ?>
                <div class="clothes-grid">
                    <?php foreach ($user_clothes as $cloth): ?>
                        <div class="cloth-card">
                            <img src="uploads/dress_images/<?php echo htmlspecialchars($cloth['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($cloth['name']); ?>">
                            <div class="cloth-card-content">
                                <h3><?php echo htmlspecialchars($cloth['name']); ?></h3>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($cloth['type']); ?></p>
                                <p><strong>Rent:</strong> NPR <?php echo number_format($cloth['rent_per_day']); ?>/day</p>
                                <p><strong>Deposit:</strong> NPR <?php echo number_format($cloth['fixed_deposit']); ?></p>
                                <p><strong>Condition:</strong> <?php echo htmlspecialchars($cloth['condition_status']); ?></p>
                                <?php if (!empty($cloth['description'])): ?>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars(substr($cloth['description'], 0, 100)) . (strlen($cloth['description']) > 100 ? '...' : ''); ?></p>
                                <?php endif; ?>
                                
                                <?php if (isLoggedIn()): ?>
                                    <button class="rent-button" onclick="requestRental(<?php echo $cloth['id']; ?>)">
                                        Request Rental
                                    </button>
                                <?php else: ?>
                                    <button class="rent-button" onclick="showLoginAlert()">
                                        Login to Rent
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Reviews Section -->
        <div class="reviews-section">
            <h2>⭐ Reviews for <?php echo htmlspecialchars($view_user['fullname']); ?></h2>
            <?php
            // Get reviews for this user
            try {
                $stmt = $pdo->prepare("
                    SELECT r.*, u.fullname as reviewer_name, u.profile_photo as reviewer_photo
                    FROM reviews r
                    JOIN users u ON r.reviewer_id = u.id
                    WHERE r.reviewee_id = ?
                    ORDER BY r.created_at DESC
                ");
                $stmt->execute([$view_user_id]);
                $reviews = $stmt->fetchAll();
                
                if (empty($reviews)) {
                    echo "<p style='text-align: center; color: #666; padding: 20px;'>No reviews yet.</p>";
                } else {
                    // Calculate average rating
                    $total_rating = 0;
                    foreach ($reviews as $review) {
                        $total_rating += $review['rating'];
                    }
                    $average_rating = count($reviews) > 0 ? round($total_rating / count($reviews), 1) : 0;
                    
                    echo "<div class='rating-summary'>";
                    echo "<div class='average-rating'>";
                    echo "<span class='rating-number'>{$average_rating}</span>";
                    echo "<div class='stars'>";
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $average_rating) {
                            echo "⭐";
                        } else {
                            echo "☆";
                        }
                    }
                    echo "</div>";
                    echo "<span class='review-count'>(" . count($reviews) . " reviews)</span>";
                    echo "</div>";
                    echo "</div>";
                    
                    echo "<div class='reviews-list'>";
                    foreach ($reviews as $review) {
                        echo "<div class='review-item'>";
                        echo "<div class='review-header'>";
                        echo "<img src='uploads/profile_photos/" . htmlspecialchars($review['reviewer_photo']) . "' alt='Reviewer' class='reviewer-photo'>";
                        echo "<div class='reviewer-info'>";
                        echo "<strong><a href='view_user.php?id=" . $review['reviewer_id'] . "' style='color: #800000; text-decoration: none;'>" . htmlspecialchars($review['reviewer_name']) . "</a></strong>";
                        echo "<div class='review-stars'>";
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $review['rating']) {
                                echo "⭐";
                            } else {
                                echo "☆";
                            }
                        }
                        echo "</div>";
                        echo "</div>";
                        echo "<div class='review-date'>" . date('M d, Y', strtotime($review['created_at'])) . "</div>";
                        echo "</div>";
                        if (!empty($review['comment'])) {
                            echo "<div class='review-comment'>" . htmlspecialchars($review['comment']) . "</div>";
                        }
                        echo "</div>";
                    }
                    echo "</div>";
                }
            } catch (PDOException $e) {
                echo "<p style='text-align: center; color: #666; padding: 20px;'>Error loading reviews.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        function requestRental(dressId) {
            if (confirm('Do you want to request this dress for rental?')) {
                // Here you would implement the rental request functionality
                alert('Rental request functionality will be implemented in the next phase!');
            }
        }

        function showLoginAlert() {
            alert('Please log in to rent or borrow this dress.');
        }
    </script>
</body>
</html>
