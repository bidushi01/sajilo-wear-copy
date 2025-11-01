<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
requireLogin();

$user = getCurrentUser();
$message = '';
$error = '';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rental_id = intval($_POST['rental_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a valid rating (1-5 stars).';
    } else {
        try {
            // Check if user can review this rental
            $stmt = $pdo->prepare("
                SELECT r.*, c.name as dress_name, 
                       CASE 
                           WHEN r.borrower_id = ? THEN r.owner_id 
                           WHEN r.owner_id = ? THEN r.borrower_id 
                       END as reviewee_id
                FROM rentals r 
                JOIN clothes c ON r.dress_id = c.id
                WHERE r.id = ? AND (r.borrower_id = ? OR r.owner_id = ?) 
                AND r.status = 'completed'
            ");
            $stmt->execute([$user['id'], $user['id'], $rental_id, $user['id'], $user['id']]);
            $rental = $stmt->fetch();
            
            if (!$rental) {
                $error = 'You cannot review this rental.';
            } else {
                // Check if user already reviewed this rental
                $stmt = $pdo->prepare("SELECT id FROM reviews WHERE rental_id = ? AND reviewer_id = ?");
                $stmt->execute([$rental_id, $user['id']]);
                
                if ($stmt->fetch()) {
                    $error = 'You have already reviewed this rental.';
                } else {
                    // Insert review
                    $stmt = $pdo->prepare("
                        INSERT INTO reviews (rental_id, reviewer_id, reviewee_id, rating, comment) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$rental_id, $user['id'], $rental['reviewee_id'], $rating, $comment]);
                    
                    $message = 'Review submitted successfully!';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get rentals available for review
try {
    $stmt = $pdo->prepare("
        SELECT r.*, c.name as dress_name, c.image as dress_image,
               CASE 
                   WHEN r.borrower_id = ? THEN u_owner.fullname 
                   WHEN r.owner_id = ? THEN u_borrower.fullname 
               END as other_user_name,
               CASE 
                   WHEN r.borrower_id = ? THEN r.owner_id 
                   WHEN r.owner_id = ? THEN r.borrower_id 
               END as other_user_id
        FROM rentals r 
        JOIN clothes c ON r.dress_id = c.id
        JOIN users u_owner ON r.owner_id = u_owner.id
        JOIN users u_borrower ON r.borrower_id = u_borrower.id
        WHERE (r.borrower_id = ? OR r.owner_id = ?) 
        AND r.status = 'completed'
        AND r.id NOT IN (
            SELECT rental_id FROM reviews WHERE reviewer_id = ?
        )
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id']]);
    $reviewable_rentals = $stmt->fetchAll();
    
    // Get user's reviews
    $stmt = $pdo->prepare("
        SELECT rv.*, r.dress_id, c.name as dress_name, c.image as dress_image,
               u.fullname as reviewee_name
        FROM reviews rv
        JOIN rentals r ON rv.rental_id = r.id
        JOIN clothes c ON r.dress_id = c.id
        JOIN users u ON rv.reviewee_id = u.id
        WHERE rv.reviewer_id = ?
        ORDER BY rv.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $user_reviews = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review System - SajiloWear</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: #f8f9fa;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }
        .navbar {
            background: #800000;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            margin: 0;
            font-size: 24px;
        }
        .navbar .nav-links {
            display: flex;
            gap: 20px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .navbar a:hover {
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
        .section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section h2 {
            color: #800000;
            margin: 0 0 20px 0;
            border-bottom: 2px solid #800000;
            padding-bottom: 10px;
        }
        .rental-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #800000;
        }
        .rental-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .rental-header h3 {
            margin: 0;
            color: #333;
        }
        .rental-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        .rental-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .rental-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        .info-item {
            background: white;
            padding: 10px;
            border-radius: 5px;
        }
        .info-item strong {
            color: #800000;
        }
        .review-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
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
        .rating-input {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .star {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        .star:hover,
        .star.active {
            color: #ffc107;
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
        .review-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #800000;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .review-header h4 {
            margin: 0;
            color: #333;
        }
        .stars {
            color: #ffc107;
            font-size: 18px;
        }
        .review-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }
        .review-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
        .review-info {
            display: grid;
            gap: 10px;
        }
        .info-item {
            background: white;
            padding: 8px;
            border-radius: 5px;
        }
        .info-item strong {
            color: #800000;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h1>⭐ Review System</h1>
            <div class="nav-links">
                <a href="home.php">Home</a>
                <a href="profile.php">Profile</a>
                <a href="order_activity.php">My Activity</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>Review & Rating System</h1>
            <p>Share your experience and help others make informed decisions</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Rentals Available for Review -->
        <div class="section">
            <h2>Complete Rentals - Leave Reviews</h2>
            <?php if (empty($reviewable_rentals)): ?>
                <div class="empty-state">
                    <h3>No completed rentals to review</h3>
                    <p>When you complete a rental, you'll be able to review the other user here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviewable_rentals as $rental): ?>
                    <div class="rental-card">
                        <div class="rental-header">
                            <h3><?php echo htmlspecialchars($rental['dress_name']); ?></h3>
                            <span style="background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">Completed</span>
                        </div>
                        
                        <div class="rental-content">
                            <img src="uploads/dress_images/<?php echo htmlspecialchars($rental['dress_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($rental['dress_name']); ?>" 
                                 class="rental-image"
                                 onerror="this.src='https://via.placeholder.com/300x200'">
                            
                            <div class="rental-info">
                                <div class="info-item">
                                    <strong>Other User:</strong><br>
                                    <?php echo htmlspecialchars($rental['other_user_name']); ?>
                                </div>
                                <div class="info-item">
                                    <strong>Rental Period:</strong><br>
                                    <?php echo date('M j, Y', strtotime($rental['start_date'])); ?><br>
                                    to <?php echo date('M j, Y', strtotime($rental['end_date'])); ?>
                                </div>
                                <div class="info-item">
                                    <strong>Total Amount:</strong><br>
                                    NPR <?php echo number_format($rental['total_amount']); ?>
                                </div>
                                <div class="info-item">
                                    <strong>Completed:</strong><br>
                                    <?php echo date('M j, Y', strtotime($rental['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="review-form">
                            <form method="POST">
                                <input type="hidden" name="rental_id" value="<?php echo $rental['id']; ?>">
                                
                                <div class="form-group">
                                    <label>Rate your experience:</label>
                                    <div class="rating-input">
                                        <span class="star" data-rating="1">★</span>
                                        <span class="star" data-rating="2">★</span>
                                        <span class="star" data-rating="3">★</span>
                                        <span class="star" data-rating="4">★</span>
                                        <span class="star" data-rating="5">★</span>
                                        <input type="hidden" name="rating" id="rating-input" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="comment">Your Review (Optional):</label>
                                    <textarea id="comment" name="comment" placeholder="Share your experience with this rental..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn">Submit Review</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- User's Reviews -->
        <div class="section">
            <h2>Your Reviews</h2>
            <?php if (empty($user_reviews)): ?>
                <div class="empty-state">
                    <h3>No reviews submitted yet</h3>
                    <p>Your reviews will appear here once you submit them.</p>
                </div>
            <?php else: ?>
                <?php foreach ($user_reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <h4><?php echo htmlspecialchars($review['dress_name']); ?></h4>
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $review['rating']): ?>
                                        ★
                                    <?php else: ?>
                                        ☆
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="review-content">
                            <img src="uploads/dress_images/<?php echo htmlspecialchars($review['dress_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($review['dress_name']); ?>" 
                                 class="review-image"
                                 onerror="this.src='https://via.placeholder.com/300x200'">
                            
                            <div class="review-info">
                                <div class="info-item">
                                    <strong>Reviewed:</strong> <?php echo htmlspecialchars($review['reviewee_name']); ?>
                                </div>
                                <div class="info-item">
                                    <strong>Rating:</strong> <?php echo $review['rating']; ?>/5 stars
                                </div>
                                <?php if ($review['comment']): ?>
                                    <div class="info-item">
                                        <strong>Your Comment:</strong><br>
                                        <?php echo htmlspecialchars($review['comment']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="info-item">
                                    <strong>Submitted:</strong> <?php echo date('M j, Y g:i A', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Star rating functionality
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                const stars = this.parentElement.querySelectorAll('.star');
                
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
                
                document.getElementById('rating-input').value = rating;
            });
        });
    </script>
</body>
</html>
