<?php
session_start();
// Require our OOP backend to fetch the data
require_once 'classes/Inventory.php';

// Instantiate the Inventory object
$inventoryManager = new Inventory();

// Fetch ALL products (not just featured ones)
$products = $inventoryManager->getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Store | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/auth-styles-append.css" rel="stylesheet" />
</head>
<body>
    <?php include_once __DIR__ . '/includes\cookie_notif.php'; ?>

    <!-- INJECT NAVBAR -->
    <?php 
    $currentPage = 'store';
    include 'includes\navbar.php'; 
    ?>

    <header class="inner-header">
        <div class="container">
            <h1>Complete Catalog</h1>
            <p>Equip your setup with industry-leading hardware.</p>
        </div>
    </header>

    <section class="inner-page">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
                <span class="text-muted fw-bold"><?php echo count($products); ?> Products Available</span>
                <select class="form-select w-auto bg-white border-1 fw-bold text-muted shadow-sm">
                    <option>Sort by: Featured</option>
                    <option>Price: Low to High</option>
                    <option>Price: High to Low</option>
                </select>
            </div>

            <div class="row g-4">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <h3 class="text-muted">Inventory is currently empty.</h3>
                        <p>Head over to the <a href="admin\apex26admin.php" class="text-apex-accent">Admin Panel</a> to add some gadgets.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-sm-6 col-md-4 col-lg-3 fade-up">
                            <div class="product-card h-100 position-relative">

                                <?php if (isset($product['badge']) && $product['badge']): ?>
                                    <div class="product-badge <?php echo isset($product['badge_type']) && $product['badge_type'] === 'sale' ? 'sale' : ''; ?>">
                                        <?php echo htmlspecialchars($product['badge']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="product-img bg-white">
                                    <?php
                                    // Handle both image URLs and SVG snippets gracefully
                                    if (strpos($product['image'], '<svg') !== false) {
                                        echo $product['image'];
                                    } else {
                                        echo '<img src="' . htmlspecialchars($product['image']) . '" alt="img">';
                                    }
                                    ?>
                                </div>

                                <div class="product-body">
                                    <div class="product-tags">
                                        <span class="filter-tag brand">
                                            <i class="fas fa-tag me-1" style="opacity: 0.6;"></i> <?php echo isset($product['brand']) ? htmlspecialchars($product['brand']) : 'Unknown Brand'; ?>
                                        </span>
                                        <span class="filter-tag category">
                                            <i class="fas fa-layer-group me-1" style="opacity: 0.6;"></i> <?php echo isset($product['category']) ? htmlspecialchars(ucfirst($product['category'])) : 'Accessories'; ?>
                                        </span>
                                    </div>
                                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="product-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                        <span>(<?php echo isset($product['rating']) ? $product['rating'] : rand(50, 400); ?>)</span>
                                    </div>
                                    <?php $stock = isset($product['stock']) ? (int)$product['stock'] : 0; ?>
                                    <div class="product-price">
                                        ₱<?php echo number_format($product['price'], 2); ?>
                                        <?php if (isset($product['old_price']) && $product['old_price']): ?>
                                            <span class="old">₱<?php echo number_format($product['old_price'], 2); ?></span>
                                        <?php endif; ?>
                                        <span class="d-block small fw-bold mt-1" style="font-size: 0.75rem; color: <?php echo $stock > 0 ? 'var(--apex-muted)' : '#ff3b5c'; ?>;">
                                            <?php echo $stock > 0 ? "Stock: " . $stock : "Out of Stock"; ?>
                                        </span>
                                    </div>
                                    <div class="product-actions">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-shop stretched-link">View Details</a>
                                        <form method="POST" action="includes\favorites_action.php" class="m-0 position-relative" style="display: inline-block; z-index: 2;">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                            <button type="submit" class="btn-wish border-0" title="Add to Favorites">
                                                <?php if (isset($_SESSION['favorites'][$product['id']])): ?>
                                                    <i class="fas fa-heart" style="color: #ff3b5c;"></i>
                                                <?php else: ?>
                                                    <i class="far fa-heart"></i>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- INJECT FOOTER, MODALS, AND SCRIPTS -->
    <?php include 'includes\footer.php'; ?>

</body>
</html>