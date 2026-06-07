<?php
session_start();
require_once '../classes/Inventory.php';

$inventoryManager = new Inventory();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- ADD NEW PRODUCT ---
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = $_POST['name'];
        $brand = $_POST['brand'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $old_price = $_POST['old_price'];
        $stock = $_POST['stock'];
        $rating = $_POST['rating'];
        $badge = $_POST['badge'];
        $badge_type = $_POST['badge_type'];
        $image = $_POST['image'];
        $desc = $_POST['desc'];

        $image_path = '';
        if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/images/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = uniqid() . '_' . basename($_FILES['image_upload']['name']);
            $target_file = $upload_dir . $file_name;

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($_FILES['image_upload']['type'], $allowed_types)) {
                if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $target_file)) {
                    $image_path = 'assets/images/uploads/' . $file_name;
                }
            }
        }

        $final_image = !empty($image_path) ? $image_path : $image;

        $inventoryManager->addProduct($name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $final_image, $desc);

        header("Location: apex26admin.php?success=added");
        exit();
    }

    // --- DELETE PRODUCT ---
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $idToDelete = (int)$_POST['product_id'];
        $inventoryManager->deleteProduct($idToDelete);

        header("Location: apex26admin.php?success=deleted");
        exit();
    }


    // --- EDIT PRODUCT ---
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = (int)$_POST['product_id'];
        $name = $_POST['name'];
        $brand = $_POST['brand'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $old_price = $_POST['old_price'];
        $stock = $_POST['stock'];
        $rating = $_POST['rating'];
        $badge = $_POST['badge'];
        $badge_type = $_POST['badge_type'];
        $image = $_POST['image'];
        $desc = $_POST['desc'];

        // Handle image upload
        $image_path = '';
        if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'assets/images/uploads/';
            $file_name = uniqid() . '_' . basename($_FILES['image_upload']['name']);
            $target_file = $upload_dir . $file_name;

            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($_FILES['image_upload']['type'], $allowed_types)) {
                if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $target_file)) {
                    $image_path = $target_file;
                }
            }
        }

        // Use uploaded image if available, otherwise use URL
        $final_image = !empty($image_path) ? $image_path : $image;

        $inventoryManager->editProduct($id, $name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $final_image, $desc);

        header("Location: admin\apex26admin.php?success=edited");
        exit();
    }
}

$products = $inventoryManager->getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <?php
    $userRole = $_SESSION['user']['role'] ?? 'guest';

    switch ($userRole) {
        case 'admin':
            echo "Welcome, Admin. Accessing Inventory Management.";
            break;
        case 'editor':
            echo "Welcome, Editor. Accessing Blog Content.";
            break;
        default:
            echo "Welcome, Customer. Browse our latest tech!";
            break;
    }
    ?>
    <nav class="main-nav navbar navbar-expand-lg bg-apex-dark" style="background: var(--apex-dark) !important; top: 0 !important; width: 100% !important; border-radius: 0 !important; position: static;">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="brand m-0">
                <img src="assets/images/ApeX Logo.png" alt="ApeX Gear Logo" class="brand-logo-img">
                <div class="brand-text" style="color: white; margin-left:-10px">ApeX</div>
                <div class="brand-text" style="color: #00c2ff; margin-left:-10px">Gear</div>

            </a>

            <a href="index.php" class="btn-apex-outline" style="padding: 6px 16px; font-size: .8rem;">View Live Store</a>
        </div>
    </nav>

    <section class="inner-page">
        <div class="container">

            <?php if (isset($_GET['success']) && $_GET['success'] == 'added'): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm bg-white" role="alert" style="border-left: 5px solid #00d68f !important;">
                    <i class="fas fa-check-circle text-success me-2"></i><strong>Success!</strong> New gadget added to inventory.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] == 'deleted'): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm bg-white" role="alert" style="border-left: 5px solid #ff3b5c !important;">
                    <i class="fas fa-trash-alt text-danger me-2"></i><strong>Removed.</strong> The gadget was deleted from inventory.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] == 'edited'): ?>
                <div class="alert alert-primary alert-dismissible fade show shadow-sm bg-white" role="alert" style="border-left: 5px solid var(--apex-blue) !important;">
                    <i class="fas fa-edit text-primary me-2"></i><strong>Updated.</strong> The gadget details were successfully changed.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">

                <div class="col-lg-4">
                    <div class="apex-card">
                        <h5 class="fw-bold mb-4 text-uppercase" style="font-family: 'Barlow Condensed', sans-serif; font-size: 1.5rem;">Add New Gadget</h5>

                        <form method="POST" action="admin\apex26admin.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add">

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Product Name</label>
                                <input type="text" name="name" class="form-control bg-light" required placeholder="e.g. Razer DeathAdder V3">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Brand</label>
                                    <select name="brand" class="form-select bg-light" required>
                                        <option value="" disabled selected>Select Brand</option>
                                        <option value="Apple">Apple</option>
                                        <option value="ASUS">ASUS</option>
                                        <option value="Corsair">Corsair</option>
                                        <option value="Dell">Dell</option>
                                        <option value="HP">HP</option>
                                        <option value="Lenovo">Lenovo</option>
                                        <option value="Intel">Intel</option>
                                        <option value="Logitech">Logitech</option>
                                        <option value="NVIDIA">NVIDIA</option>
                                        <option value="Samsung">Samsung</option>
                                        <option value="Sony">Sony</option>
                                        <option value="Razer">Razer</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Category</label>
                                    <select name="category" class="form-select bg-light" required>
                                        <option value="" disabled selected>Select Category</option>
                                        <option value="laptop">Laptop</option>
                                        <option value="desktop">Desktop / PC</option>
                                        <option value="tablet">Tablet</option>
                                        <option value="phone">Phone</option>
                                        <option value="audio">Headphones / Audio</option>
                                        <option value="peripheral">Accessories / Peripherals</option>
                                        <option value="cpu">CPU</option>
                                        <option value="gpu">GPU</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Price (₱)</label>
                                    <input type="number" step="0.01" name="price" class="form-control bg-light" required placeholder="0.00">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Stock Qty</label>
                                    <input type="number" name="stock" class="form-control bg-light" required placeholder="10">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Old Price (₱)</label>
                                    <input type="number" step="0.01" name="old_price" class="form-control bg-light" placeholder="Optional">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Rating Count</label>
                                    <input type="number" name="rating" class="form-control bg-light" placeholder="e.g. 210">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Badge Text</label>
                                    <input type="text" name="badge" class="form-control bg-light" placeholder="e.g. Popular">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Badge Style</label>
                                    <select name="badge_type" class="form-select bg-light">
                                        <option value="">None</option>
                                        <option value="normal">Normal</option>
                                        <option value="new">New (Green)</option>
                                        <option value="sale">Sale (Red)</option>
                                        <option value="ribbon">Corner Ribbon</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Image Source</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="image_source" id="upload_radio" value="upload" checked>
                                        <label class="form-check-label" for="upload_radio">
                                            Upload Image
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="image_source" id="url_radio" value="url">
                                        <label class="form-check-label" for="url_radio">
                                            Image URL
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="upload_section" class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Upload Image</label>
                                <input type="file" name="image_upload" class="form-control bg-light" accept="image/*">
                                <small class="text-muted">Supported formats: JPG, PNG, GIF, WebP. Max size: 5MB</small>
                            </div>

                            <div id="url_section" class="mb-3" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase">Image URL or SVG Code</label>
                                <input type="text" name="image" class="form-control bg-light" placeholder="<svg...> or https://...">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                <textarea name="desc" class="form-control bg-light" rows="3" placeholder="Specs and details..."></textarea>
                            </div>

                            <button type="submit" class="btn-apex w-100 text-center">Publish to Store</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="apex-card p-0 overflow-hidden">
                        <div class="p-4 bg-white d-flex justify-content-between align-items-center border-bottom">
                            <h5 class="fw-bold m-0 text-uppercase" style="font-family: 'Barlow Condensed', sans-serif; font-size: 1.5rem;">Current Inventory</h5>
                            <span class="badge" style="background: var(--apex-accent); color: var(--apex-dark); font-size: .8rem; padding: 6px 12px;"><?php echo count($products); ?> Items</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-apex-dark text-muted small text-uppercase">
                                    <tr>
                                        <th style="padding-left: 20px;">ID</th>
                                        <th>Gadget</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th class="text-end" style="padding-right: 20px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <?php if (empty($products)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">No gadgets in inventory.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td class="fw-bold text-muted" style="padding-left: 20px;">#<?php echo $product['id']; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div style="width: 40px; height: 40px; background: var(--apex-grey); border-radius: 4px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                                            <?php
                                                            if (strpos($product['image'], '<svg') !== false) {
                                                                echo $product['image'];
                                                            } else {
                                                                echo '<img src="' . htmlspecialchars($product['image']) . '" style="max-width: 100%; object-fit: contain;">';
                                                            }
                                                            ?>
                                                        </div>
                                                        <div>
                                                            <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($product['name']); ?></span>
                                                            <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.05em;">
                                                                <?php echo isset($product['brand']) ? htmlspecialchars($product['brand']) : 'No Brand'; ?> &bull;
                                                                <?php echo isset($product['category']) ? htmlspecialchars(ucfirst($product['category'])) : 'Uncategorized'; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="fw-bold text-apex-blue">₱<?php echo number_format($product['price'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo isset($product['stock']) ? $product['stock'] : 'N/A'; ?></span>
                                                </td>
                                                <td class="text-end" style="padding-right: 20px; white-space: nowrap;">
                                                    <button type="button" class="btn btn-sm btn-outline-primary me-2"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal"
                                                        data-id="<?php echo $product['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                        data-brand="<?php echo isset($product['brand']) ? htmlspecialchars($product['brand']) : ''; ?>"
                                                        data-category="<?php echo isset($product['category']) ? htmlspecialchars($product['category']) : ''; ?>"
                                                        data-price="<?php echo $product['price']; ?>"
                                                        data-old_price="<?php echo isset($product['old_price']) ? $product['old_price'] : ''; ?>"
                                                        data-rating="<?php echo isset($product['rating']) ? $product['rating'] : ''; ?>"
                                                        data-badge="<?php echo isset($product['badge']) ? htmlspecialchars($product['badge']) : ''; ?>"
                                                        data-badge_type="<?php echo isset($product['badge_type']) ? htmlspecialchars($product['badge_type']) : ''; ?>"
                                                        data-stock="<?php echo isset($product['stock']) ? $product['stock'] : ''; ?>"
                                                        data-image="<?php echo htmlspecialchars($product['image']); ?>"
                                                        data-desc="<?php echo isset($product['desc']) ? htmlspecialchars($product['desc']) : ''; ?>"
                                                        onclick="populateEditForm(this)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>

                                                    <form method="POST" action="admin\apex26admin.php" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this gadget permanently?');"><i class="fas fa-trash"></i> Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-bottom mt-0 border-0 pt-4 pb-4 bg-apex-dark text-center">
            <p>© 2026 ApeX Gear. All rights reserved. | High-Performance Tech</p>
        </div>
    </footer>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-apex-dark text-white rounded-top" style="border-radius: 0;">
                    <h5 class="modal-title fw-bold text-uppercase" style="font-family: 'Barlow Condensed', sans-serif;">Edit Gadget</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="admin\apex26admin.php" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="product_id" id="edit_product_id">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Product Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control bg-light" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Brand</label>
                                <select name="brand" id="edit_brand" class="form-select bg-light" required>
                                    <option value="Apple">Apple</option>
                                    <option value="ASUS">ASUS</option>
                                    <option value="Corsair">Corsair</option>
                                    <option value="Dell">Dell</option>
                                    <option value="HP">HP</option>
                                    <option value="Lenovo">Lenovo</option>
                                    <option value="Intel">Intel</option>
                                    <option value="Logitech">Logitech</option>
                                    <option value="NVIDIA">NVIDIA</option>
                                    <option value="Samsung">Samsung</option>
                                    <option value="Sony">Sony</option>
                                    <option value="Razer">Razer</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Category</label>
                                <select name="category" id="edit_category" class="form-select bg-light" required>
                                    <option value="laptop">Laptop</option>
                                    <option value="desktop">Desktop / PC</option>
                                    <option value="tablet">Tablet</option>
                                    <option value="phone">Phone</option>
                                    <option value="audio">Headphones / Audio</option>
                                    <option value="peripheral">Accessories / Peripherals</option>
                                    <option value="cpu">CPU</option>
                                    <option value="gpu">GPU</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Price (₱)</label>
                                <input type="number" step="0.01" name="price" id="edit_price" class="form-control bg-light" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Stock Qty</label>
                                <input type="number" name="stock" id="edit_stock" class="form-control bg-light" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Old Price (₱)</label>
                                <input type="number" step="0.01" name="old_price" id="edit_old_price" class="form-control bg-light">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Rating</label>
                                <input type="number" name="rating" id="edit_rating" class="form-control bg-light">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Badge Text</label>
                                <input type="text" name="badge" id="edit_badge" class="form-control bg-light">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Badge Style</label>
                                <select name="badge_type" id="edit_badge_type" class="form-select bg-light">
                                    <option value="">None</option>
                                    <option value="normal">Normal</option>
                                    <option value="new">New (Green)</option>
                                    <option value="sale">Sale (Red)</option>
                                    <option value="ribbon">Corner Ribbon</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Image Source</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="image_source" id="edit_upload_radio" value="upload" checked>
                                    <label class="form-check-label" for="edit_upload_radio">
                                        Upload Image
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="image_source" id="edit_url_radio" value="url">
                                    <label class="form-check-label" for="edit_url_radio">
                                        Image URL
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="edit_upload_section" class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Upload Image</label>
                            <input type="file" name="image_upload" class="form-control bg-light" accept="image/*">
                            <small class="text-muted">Supported formats: JPG, PNG, GIF, WebP. Max size: 5MB</small>
                        </div>

                        <div id="edit_url_section" class="mb-3" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase">Image URL or SVG Code</label>
                            <input type="text" name="image" id="edit_image" class="form-control bg-light" placeholder="<svg...> or https://...">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                            <textarea name="desc" id="edit_desc" class="form-control bg-light" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary text-uppercase fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-apex">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/navbar.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        function populateEditForm(button) {
            document.getElementById('edit_product_id').value = button.getAttribute('data-id');
            document.getElementById('edit_name').value = button.getAttribute('data-name');
            document.getElementById('edit_brand').value = button.getAttribute('data-brand');
            document.getElementById('edit_category').value = button.getAttribute('data-category');
            document.getElementById('edit_price').value = button.getAttribute('data-price');
            document.getElementById('edit_stock').value = button.getAttribute('data-stock');

            // New attribute pulls
            document.getElementById('edit_old_price').value = button.getAttribute('data-old_price');
            document.getElementById('edit_rating').value = button.getAttribute('data-rating');
            document.getElementById('edit_badge').value = button.getAttribute('data-badge');
            document.getElementById('edit_badge_type').value = button.getAttribute('data-badge_type');

            document.getElementById('edit_image').value = button.getAttribute('data-image');
            document.getElementById('edit_desc').value = button.getAttribute('data-desc');
        }

        // Handle image source toggling for add form
        document.addEventListener('DOMContentLoaded', function() {
            const uploadRadio = document.getElementById('upload_radio');
            const urlRadio = document.getElementById('url_radio');
            const uploadSection = document.getElementById('upload_section');
            const urlSection = document.getElementById('url_section');

            function toggleImageSource() {
                if (uploadRadio.checked) {
                    uploadSection.style.display = 'block';
                    urlSection.style.display = 'none';
                } else {
                    uploadSection.style.display = 'none';
                    urlSection.style.display = 'block';
                }
            }

            uploadRadio.addEventListener('change', toggleImageSource);
            urlRadio.addEventListener('change', toggleImageSource);

            // Handle image source toggling for edit form
            const editUploadRadio = document.getElementById('edit_upload_radio');
            const editUrlRadio = document.getElementById('edit_url_radio');
            const editUploadSection = document.getElementById('edit_upload_section');
            const editUrlSection = document.getElementById('edit_url_section');

            function toggleEditImageSource() {
                if (editUploadRadio.checked) {
                    editUploadSection.style.display = 'block';
                    editUrlSection.style.display = 'none';
                } else {
                    editUploadSection.style.display = 'none';
                    editUrlSection.style.display = 'block';
                }
            }

            editUploadRadio.addEventListener('change', toggleEditImageSource);
            editUrlRadio.addEventListener('change', toggleEditImageSource);
        });
    </script>
</body>

</html>