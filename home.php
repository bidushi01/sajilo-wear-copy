<?php
session_start();
require_once 'db_connect.php';

// Get all available clothes (not rented)
try {
    $stmt = $pdo->prepare("
        SELECT c.*, u.fullname as owner_name, u.profile_photo as owner_photo,
               cat.name as category_name
        FROM clothes c 
        JOIN users u ON c.user_id = u.id 
        LEFT JOIN categories cat ON c.category_id = cat.id
        WHERE c.is_available = 1 
        AND c.is_approved = 1
        AND c.id NOT IN (
            SELECT dress_id FROM rentals 
            WHERE status = 'approved' 
            AND (start_date <= CURDATE() AND end_date >= CURDATE())
        )
        ORDER BY c.posted_date DESC
    ");
    $stmt->execute();
    $clothes = $stmt->fetchAll();
} catch (PDOException $e) {
    $clothes = [];
}

$user = isLoggedIn() ? getCurrentUser() : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SajiloWear</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-radius: 15px;
      margin: 20px auto;
      max-width: 600px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    
    .empty-state h3 {
      color: #2c3e50;
      font-size: 1.8rem;
      margin-bottom: 15px;
      font-weight: bold;
    }
    
    .empty-state p {
      color: #6c757d;
      font-size: 1.1rem;
      margin-bottom: 30px;
      line-height: 1.6;
    }
    
    .empty-actions {
      margin-top: 30px;
    }
    
    .btn {
      display: inline-block;
      padding: 15px 30px;
      background: #800000;
      color: white;
      text-decoration: none;
      border-radius: 25px;
      font-weight: bold;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(128,0,0,0.3);
      border: none;
      cursor: pointer;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(128,0,0,0.4);
      background: #a00000;
      color: white;
      text-decoration: none;
    }
    
    .empty-subtext {
      margin-top: 15px;
      color: #6c757d;
      font-size: 0.9rem;
      font-style: italic;
    }
    
    .category-filter {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      margin: 20px 0;
      text-align: center;
    }
    
    .category-filter h3 {
      color: #800000;
      margin: 0 0 15px 0;
      font-size: 1.2rem;
    }
    
    .category-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
    }
    
    .category-btn {
      padding: 8px 16px;
      background: #f8f9fa;
      color: #800000;
      border: 2px solid #800000;
      border-radius: 25px;
      cursor: pointer;
      font-weight: bold;
      transition: all 0.3s ease;
    }
    
    .category-btn:hover {
      background: #800000;
      color: white;
    }
    
    .category-btn.active {
      background: #800000;
      color: white;
    }
    
    .featured {
      background: white;
      padding: 40px 20px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      margin: 20px auto;
      max-width: 1200px;
    }
    
    .featured h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 30px;
      font-size: 2.2rem;
      font-weight: bold;
    }
    
    .list-section {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 60px 20px;
      margin: 40px 0;
    }
    
    .list-container {
      max-width: 800px;
      margin: 0 auto;
      text-align: center;
    }
    
    .list-container h2 {
      color: #2c3e50;
      font-size: 2.2rem;
      margin-bottom: 15px;
      font-weight: bold;
    }
    
    .list-container p {
      color: #6c757d;
      font-size: 1.1rem;
      margin-bottom: 40px;
      line-height: 1.6;
    }
    
    .list-prompt, .list-ready {
      background: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      margin: 20px auto;
      max-width: 500px;
    }
    
    .list-prompt p, .list-ready p {
      color: #2c3e50;
      font-size: 1.1rem;
      margin-bottom: 25px;
    }
    
    .signup-link {
      margin-top: 20px;
      color: #6c757d;
    }
    
    .signup-link a {
      color: #800000;
      text-decoration: none;
      font-weight: bold;
    }
    
    .signup-link a:hover {
      text-decoration: underline;
      color: #a00000;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <div style="display: flex; align-items: center; gap: 10px;">
      <img src="logo.png" alt="SajiloWear Logo" style="height: 40px; width: auto;">
      <b>SajiloWear</b>
    </div>
    <div>
      <a href="home.php">Home</a>
      <a href="#featured">Browse</a>
      <?php if ($user && !isAdmin()): ?>
        <a href="list_cloth.php">List Your Cloth</a>
      <?php endif; ?>
      <a href="#about">About</a>
      <a href="#contact">Contact</a>
      <?php if ($user): ?>
        <?php if (!isAdmin()): ?>
          <a href="profile.php">Profile</a>
        <?php endif; ?>
        <?php if (isAdmin()): ?>
          <a href="admin_dashboard.php">Admin</a>
        <?php endif; ?>
        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="signup.php">Sign Up</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Hero -->
  <div class="hero">
    <?php if ($user): ?>
      <h1>👋 Welcome, <?php echo htmlspecialchars($user['fullname']); ?>!</h1>
      <p>Discover beautiful traditional clothes or list your own for others to enjoy!</p>
    <?php else: ?>
      <h1>Rent & Share Traditional Clothes</h1>
      <p>Give your beautiful traditional wear a new life. Rent or lend unique outfits for every occasion!</p>
    <?php endif; ?>
    <button onclick="document.getElementById('featured').scrollIntoView()">Browse Clothes</button>
    <?php if ($user): ?>
      <button onclick="window.location.href='list_cloth.php'">List Your Cloth</button>
    <?php else: ?>
      <button onclick="window.location.href='signup.php'">Get Started</button>
    <?php endif; ?>
  </div>

  <!-- Search and Filter -->
  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search for clothes (e.g., Saree, Lehenga, Dress)">
    <button onclick="filterOutfits()">Search</button>
  </div>

  <!-- Category Filter -->
  <div class="category-filter">
    <h3>Browse by Category:</h3>
    <div class="category-buttons">
      <button class="category-btn active" onclick="filterByCategory('all')">All</button>
      <?php
      try {
          $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
          $categories = $stmt->fetchAll();
          foreach ($categories as $category) {
              echo "<button class='category-btn' onclick='filterByCategory({$category['id']})'>" . htmlspecialchars($category['name']) . "</button>";
          }
      } catch (PDOException $e) {
          // Handle error silently
      }
      ?>
    </div>
  </div>

  <!-- Featured Outfits -->
  <div class="featured" id="featured">
    <h2>Available Traditional Clothes</h2>
    <div class="outfit-grid" id="outfitGrid">
      <?php if (empty($clothes)): ?>
        <div class="empty-state">
          <h3>No Traditional Clothes Available Yet</h3>
          <p>Be the first to share your beautiful traditional attire with the community!</p>
          <div class="empty-actions">
            <?php if ($user): ?>
              <a href="list_cloth.php" class="btn btn-primary">List Your First Dress</a>
              <p class="empty-subtext">Share your traditional clothes and earn money!</p>
            <?php else: ?>
              <a href="signup.php" class="btn btn-primary">Sign Up to List</a>
              <p class="empty-subtext">Join our community of traditional fashion lovers!</p>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($clothes as $cloth): ?>
          <div class="outfit-card" data-category-id="<?php echo $cloth['category_id'] ?? ''; ?>">
            <img src="uploads/dress_images/<?php echo htmlspecialchars($cloth['image']); ?>" 
                 alt="<?php echo htmlspecialchars($cloth['name']); ?>"
                 onerror="this.src='https://via.placeholder.com/300x250'">
            <h3><?php echo htmlspecialchars($cloth['name']); ?></h3>
            <p>
              <strong>Category:</strong> <?php echo htmlspecialchars($cloth['category_name'] ?? 'Uncategorized'); ?><br>
              <strong>Type:</strong> <?php echo htmlspecialchars($cloth['type']); ?><br>
              <strong>Rent:</strong> NPR <?php echo number_format($cloth['rent_per_day']); ?>/day<br>
              <strong>Deposit:</strong> NPR <?php echo number_format($cloth['fixed_deposit']); ?><br>
              <strong>Condition:</strong> <?php echo htmlspecialchars($cloth['condition_status']); ?><br>
              <strong>Owner:</strong> <?php echo htmlspecialchars($cloth['owner_name']); ?>
            </p>
            <?php if ($user && $user['id'] == $cloth['user_id']): ?>
              <!-- User owns this dress -->
              <button style="background: #6c757d; cursor: not-allowed;" disabled>You Own This Cloth</button>
            <?php else: ?>
              <!-- Other user's dress -->
              <button onclick="window.location.href='view_user.php?id=<?php echo $cloth['user_id']; ?>'">View Profile</button>
              <?php if ($user): ?>
                <?php
                // Check if user already has a pending request for this dress (not declined)
                $stmt = $pdo->prepare("SELECT id FROM rentals WHERE dress_id = ? AND borrower_id = ? AND status IN ('requested', 'approved')");
                $stmt->execute([$cloth['id'], $user['id']]);
                $has_pending_request = $stmt->fetch();
                ?>
                <?php if ($has_pending_request): ?>
                  <button style="background: #ffc107; cursor: not-allowed;" disabled>Request Pending</button>
                <?php else: ?>
                  <button onclick="window.location.href='request_rental.php?id=<?php echo $cloth['id']; ?>'">Rent Now</button>
                <?php endif; ?>
              <?php else: ?>
                <button onclick="showLoginAlert()">Login to Rent</button>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- List Your Cloth Section -->
  <section id="list" class="list-section">
    <div class="list-container">
      <h2>List Your Traditional Cloth</h2>
      <p>Have a beautiful outfit to share? List it for rent and earn!</p>
      <div id="listClothContainer">
        <?php if (!$user): ?>
          <!-- Login Prompt -->
          <div class="list-prompt">
            <p>You must be logged in to list your traditional dress for rent.</p>
            <a href="login.php" class="btn btn-primary">Login</a>
            <p class="signup-link">Don't have an account? <a href="signup.php">Sign up</a></p>
          </div>
        <?php else: ?>
          <!-- Direct link to list cloth -->
          <div class="list-ready">
            <p>Ready to list your traditional dress?</p>
            <a href="list_cloth.php" class="btn btn-primary">List Your Dress</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- About -->
  <section id="about" class="about-wrap">
    <div class="about-cards">
      <div class="about-card"><img src="photo1.png" alt="Saree"><h3>Saree</h3></div>
      <div class="about-card"><img src="photo2.png" alt="Lehenga"><h3>Lehenga</h3></div>
      <div class="about-card"><img src="photo3.png" alt="dress"><h3>Dress</h3></div>
      <div class="about-card"><img src="photo4.png" alt="Jewelry"><h3>Jewelry</h3></div>
    </div>
    <div class="about-panel">
      <h2>About SajiloWear</h2>
      <p>SajiloWear is a modern online platform that makes traditional fashion accessible and affordable for everyone. Our mission is to bring people together by sharing the beauty of cultural attire, giving outfits a second life, and making them available for rent to those who need them for weddings, parties, and special occasions.</p>
      <p>Whether you want to find the perfect saree, lehenga, daura suruwal, or unique jewelry, SajiloWear connects owners and renters in a safe and convenient way. By listing your outfit, you not only earn extra income but also help preserve and promote our cultural heritage.</p>
      <p>We believe fashion should be sustainable, affordable, and community-driven. SajiloWear is here to make dressing up for your special day simple, stylish, and meaningful.</p>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contact">
    <p>Email: support@sajilowear.com | Phone: +977 9866554320 | Location: Kathmandu, Nepal</p>
    <p>© 2025 SajiloWear. All rights reserved.</p>
  </footer>

  <!-- JS -->
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

    // ✅ Search filter function
    function filterOutfits() {
      let input = document.getElementById("searchInput").value.toLowerCase();
      let cards = document.querySelectorAll(".outfit-card");

      cards.forEach(card => {
        let title = card.querySelector("h3").innerText.toLowerCase();
        let desc = card.querySelector("p").innerText.toLowerCase();
        if (title.includes(input) || desc.includes(input)) {
          card.style.display = "block";
        } else {
          card.style.display = "none";
        }
      });
    }

    // ✅ Category filter function
    function filterByCategory(categoryId) {
      // Update active button
      document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      event.target.classList.add('active');
      
      // Filter outfits by category
      const outfitCards = document.querySelectorAll('.outfit-card');
      
      outfitCards.forEach(card => {
        if (categoryId === 'all') {
          card.style.display = 'block';
        } else {
          const cardCategoryId = card.getAttribute('data-category-id');
          if (cardCategoryId == categoryId) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        }
      });
    }

    // Show logout success message if redirected from logout
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('logout') === 'success') {
      alert('You have been logged out successfully!');
    }
  </script>
</body>
</html>
