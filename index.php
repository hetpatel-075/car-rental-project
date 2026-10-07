<?php
session_start();
require_once __DIR__ . '/config/db.php';

function getCarImage(array $car): string
{
    $imageDir = __DIR__ . '/images/';
    $image = basename(trim((string)($car['image'] ?? '')));

    if ($image !== '' && is_file($imageDir . $image)) {
        return 'images/' . $image;
    }

    $search = strtolower(trim(
        ($car['car_name'] ?? '') . ' ' .
        ($car['brand'] ?? '') . ' ' .
        ($car['model'] ?? '')
    ));

    $aliases = [
        'innova' => 'innova.jpg', 'endeavour' => 'endeavour.jpg',
        'fortuner' => 'fortuner.jpg', 'creta' => 'creta.jpg',
        'venue' => 'venue.jpg', 'swift' => 'swift.jpg',
        'city' => 'city.jpg', 'nexon' => 'nexon.jpg',
        'm340i' => 'bmw.jpg', 'bmw' => 'bmw.jpg',
        'mercedes' => 'mercedes.jpg', 'urus' => 'bmw.jpg'
    ];

    foreach ($aliases as $keyword => $file) {
        if (strpos($search, $keyword) !== false && is_file($imageDir . $file)) {
            return 'images/' . $file;
        }
    }

    return 'images/bmw.jpg';
}

$featured = [];
$result = mysqli_query($conn, "SELECT * FROM cars ORDER BY car_id DESC LIMIT 6");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $featured[] = $row;
    }
}

$totalCars = 0;
$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM cars");
if ($countResult) {
    $totalCars = (int) mysqli_fetch_assoc($countResult)['total'];
}
$types = [];
foreach ($featured as $c) {
    $t = ucfirst(strtolower(trim($c['car_type'] ?? '')));
    if ($t !== '') $types[$t] = ($types[$t] ?? 0) + 1;
}
ksort($types);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drive-Me | Premium Car Rental</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar modern-nav">
    <a class="logo" href="index.php"><span>🚘</span> Car<b>Rental</b></a>
    <div class="nav-links">
        <a class="active" href="index.php">Home</a>
        <a href="cars.php">Cars</a>
        <a href="#about">About</a>
        <a href="#contact">Contact</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="client/dashboard.php">Dashboard</a>
            <a href="logout.php" class="nav-register">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="nav-register">Register</a>
        <?php endif; ?>
    </div>
</nav>

<section class="hero upgraded-hero home-hero">
    <div class="hero-overlay"></div>
    <div class="home-hero-content">
        <p class="hero-small">SIMPLE &bull; FAST &bull; RELIABLE</p>
        <h1>Rent Your <span>Dream Car</span></h1>
        <p class="hero-description">Find the perfect car for your journey. Affordable prices, easy booking and a wide range of vehicles.</p>
        <a href="cars.php" class="hero-browse-btn">🚘 Browse Cars &nbsp;&rsaquo;</a>
    </div>
    <div class="hero-features">
        <div class="hf-item"><span class="hf-icon">🏷️</span><div><strong>Best Prices</strong><small>Great deals, every day</small></div></div>
        <div class="hf-item"><span class="hf-icon">🛡️</span><div><strong>Verified Cars</strong><small>Safe &amp; reliable</small></div></div>
        <div class="hf-item"><span class="hf-icon">📅</span><div><strong>Easy Booking</strong><small>Quick &amp; hassle-free</small></div></div>
        <div class="hf-item"><span class="hf-icon">🎧</span><div><strong>24/7 Support</strong><small>We're always here</small></div></div>
    </div>
</section>

<section class="choose-car">
    <div class="choose-box">
        <div class="choose-head">
            <div><h2>Choose Your Car</h2><p>Pick a category or search by name &mdash; results update instantly.</p></div>
            <a href="cars.php" class="choose-viewall">View All Cars &rarr;</a>
        </div>
        <div class="filter-row">
            <input type="text" id="carSearch" placeholder="🔍 Search car, brand or model..." autocomplete="off">
            <select id="carSort">
                <option value="new">Newest</option>
                <option value="low">Price: Low to High</option>
                <option value="high">Price: High to Low</option>
            </select>
        </div>
        <div class="category-grid">
            <button type="button" class="category-card cat" data-type="sedan" data-label="Sedan"><img src="images/cat-sedan.png" alt="Sedan"><strong>Sedan</strong><small>Comfort &amp; Style</small></button>
            <button type="button" class="category-card cat" data-type="suv" data-label="SUV"><img src="images/cat-suv.png" alt="SUV"><strong>SUV</strong><small>Space &amp; Power</small></button>
            <button type="button" class="category-card cat" data-type="bike,2 wheeler,2-wheeler,two wheeler,scooter,motorcycle" data-label="2 Wheeler"><img src="images/cat-bike.png" alt="2 Wheeler"><strong>2 Wheeler</strong><small>Freedom on Wheels</small></button>
            <button type="button" class="category-card cat" data-type="sports,sport" data-label="Sports"><img src="images/cat-sports.png" alt="Sports"><strong>Sports</strong><small>Thrill &amp; Performance</small></button>
            <button type="button" class="category-card cat" data-type="luxury" data-label="Luxury"><img src="images/cat-luxury.png" alt="Luxury"><strong>Luxury</strong><small>Premium Experience</small></button>
        </div>
        <div class="chip-row" id="chipRow">
            <button type="button" class="chip active" data-type="all">All <em><?php echo count($featured); ?></em></button>
            <?php foreach ($types as $t => $n): if (in_array(strtolower($t), ["sedan","suv"])) continue; ?>
                <button type="button" class="chip" data-type="<?php echo htmlspecialchars(strtolower($t)); ?>"><?php echo htmlspecialchars($t); ?> <em><?php echo $n; ?></em></button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="featured-section" id="cars">
    <div class="section-heading left-heading">
        <div><p>OUR COLLECTION</p><h2>Popular Cars</h2></div>
        <a href="cars.php" class="view-all">View All Cars →</a>
    </div>
    <div class="featured-grid" id="carGrid">
        <?php if ($featured): foreach ($featured as $car): ?>
            <article class="premium-car-card reveal" data-type="<?php echo htmlspecialchars(strtolower(trim($car['car_type'] ?? ''))); ?>" data-price="<?php echo (float)$car['price_per_day']; ?>" data-order="<?php echo (int)$car['car_id']; ?>" data-text="<?php echo htmlspecialchars(strtolower($car['car_name'].' '.$car['brand'].' '.$car['model'])); ?>">
                <div class="premium-car-image">
                    <?php $carImage = getCarImage($car); ?>
                    <img
                        src="<?php echo htmlspecialchars($carImage); ?>"
                        alt="<?php echo htmlspecialchars($car['car_name']); ?>"
                        onerror="this.onerror=null;this.src='images/bmw.jpg';"
                    >
                    <span class="status-pill available">Available</span>
                </div>
                <div class="premium-car-content">
                    <div class="car-topline"><span><?php echo htmlspecialchars($car['car_type']); ?></span><span><?php echo htmlspecialchars($car['fuel_type']); ?></span></div>
                    <h3><?php echo htmlspecialchars($car['car_name']); ?></h3>
                    <p><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?> • <?php echo (int)$car['seats']; ?> Seats</p>
                    <div class="price-row"><strong>₹<?php echo number_format((float)$car['price_per_day'], 0); ?></strong><span>/ day</span><a href="car_details.php?car_id=<?php echo (int)$car['car_id']; ?>">View →</a></div>
                </div>
            </article>
        <?php endforeach; else: ?>
            <div class="empty-state">No cars are available yet. Add cars from the admin panel.</div>
        <?php endif; ?>
    </div>
    <div class="empty-state" id="noResults" style="display:none">No cars match your selection right now. Try another category.</div>
</section>

<section class="stats-band">
    <div class="stat"><strong data-count="<?php echo $totalCars; ?>">0</strong><span>Cars Available</span></div>
    <div class="stat"><strong data-count="<?php echo max(count($types),1); ?>">0</strong><span>Categories</span></div>
    <div class="stat"><strong data-count="24">0</strong><span>Hours Support (24/7)</span></div>
    <div class="stat"><strong data-count="100" data-suffix="%">0</strong><span>Verified Cars</span></div>
</section>

<section class="about upgraded-about" id="about">
    <div class="section-heading"><p>WHY DRIVE-ME</p><h2>Everything You Need for a Better Journey</h2><span>Simple booking, transparent pricing and a vehicle for every kind of trip.</span></div>
    <div class="features upgraded-features">
        <div class="feature reveal"><div class="feature-icon">🚗</div><h3>Wide Range</h3><p>Choose from different cars, types, fuel options and price ranges.</p></div>
        <div class="feature reveal"><div class="feature-icon">💰</div><h3>Clear Pricing</h3><p>Daily rental pricing makes it easy to understand your total cost.</p></div>
        <div class="feature reveal"><div class="feature-icon">⚡</div><h3>Easy Booking</h3><p>Select a car, choose dates and confirm your booking quickly.</p></div>
        <div class="feature reveal"><div class="feature-icon">🔒</div><h3>Secure Login</h3><p>User accounts and passwords are handled with secure PHP practices.</p></div>
    </div>
</section>

<section class="how-it-works upgraded-how">
    <div class="section-heading"><p>HOW IT WORKS</p><h2>Rent in 3 Simple Steps</h2></div>
    <div class="steps">
        <div class="step reveal"><div class="step-number">01</div><h3>Choose a Car</h3><p>Browse available vehicles and open the car you like.</p></div>
        <div class="step reveal"><div class="step-number">02</div><h3>Select Dates</h3><p>Choose your pickup and return dates for your journey.</p></div>
        <div class="step reveal"><div class="step-number">03</div><h3>Confirm Booking</h3><p>Review the rental amount and submit your booking.</p></div>
    </div>
</section>

<section class="cta upgraded-cta">
    <div><span>READY WHEN YOU ARE</span><h2>Your next journey starts here.</h2><p>Find a car that fits your trip, your style and your budget.</p><a href="cars.php" class="btn">Explore Cars →</a></div>
</section>

<footer id="contact">
    <div class="footer-content">
        <div><h3>🚘 Drive-Me</h3><p>Easy, fast and reliable car rental for your next journey.</p></div>
        <div><h4>Quick Links</h4><a href="index.php">Home</a><a href="cars.php">Cars</a><a href="login.php">Login</a><a href="register.php">Register</a></div>
        <div><h4>Contact</h4><p>📧 support-drive-me@gmail.com</p><p>📞 +91 98765 43210</p></div>
    </div>
    <div class="footer-bottom"><p>© 2026 Drive-Me. All Rights Reserved.</p></div>
</footer>

<script src="js/home.js"></script>
</body>
</html>
