<?php
session_start();
require_once __DIR__ . '/../database/db_connect.php';
require_once __DIR__ . '/../classes/Inventory.php';

$inventoryManager = new Inventory();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add' || $action === 'edit') {
        $name       = $_POST['name']       ?? '';
        $brand      = $_POST['brand']      ?? '';
        $category   = $_POST['category']   ?? '';
        $price      = $_POST['price']      ?? 0;
        $old_price  = $_POST['old_price']  ?? '';
        $stock      = $_POST['stock']      ?? 0;
        $rating     = $_POST['rating']     ?? '';
        $badge      = $_POST['badge']      ?? '';
        $badge_type = $_POST['badge_type'] ?? '';
        $desc       = $_POST['desc']       ?? '';

        $image_source = $_POST['image_source'] ?? 'upload';
        $image = '';

        if ($image_source === 'upload' && isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === 0) {
            $target_dir = __DIR__ . "/../assets/images/products/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $clean_file_name  = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["image_upload"]["name"]));
            $unique_file_name = time() . "_" . $clean_file_name;
            $target_file      = $target_dir . $unique_file_name;
            if (move_uploaded_file($_FILES["image_upload"]["tmp_name"], $target_file)) {
                $image = "assets/images/products/" . $unique_file_name;
            }
        } elseif ($image_source === 'url' && !empty($_POST['image'])) {
            $image = $_POST['image'];
        }

        if ($action === 'add') {
            $inventoryManager->addProduct($name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $image, $desc);
            header("Location: apex26admin.php?success=added");
            exit;
        } else {
            $product_id = $_POST['product_id'] ?? 0;
            if (empty($image)) {
                $products_temp = $inventoryManager->getAllProducts();
                if (isset($products_temp[$product_id])) $image = $products_temp[$product_id]['image'];
            }
            $inventoryManager->editProduct($product_id, $name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $image, $desc);
            header("Location: apex26admin.php?success=edited");
            exit;
        }
    } elseif ($action === 'delete') {
        $product_id = $_POST['product_id'] ?? 0;
        $inventoryManager->deleteProduct($product_id);
        header("Location: apex26admin.php?success=deleted");
        exit;
    }
}

$products = $inventoryManager->getAllProducts();
$totalProducts = count($products);
$totalStock    = array_sum(array_column($products, 'stock'));
$lowStock      = count(array_filter($products, fn($p) => isset($p['stock']) && $p['stock'] < 5));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin-style.css" rel="stylesheet">

</head>

<body>

    <!-- ── SIDEBAR OVERLAY (mobile) ── -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- ══════════════════════════ SIDEBAR ══════════════════════════ -->
    <aside class="sidebar" id="sidebar">
        <a href="../index.php" class="sidebar-brand">
            <img src="../assets/images/ApeX Logo.png" alt="ApeX Gear">
            <div class="sidebar-brand-text">
                <span class="t1">ApeX </span><span class="t2">Gear</span>
            </div>
            <span class="sidebar-badge">Admin</span>
        </a>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>

            <a href="apex26admin.php" class="active">
                <i class="fas fa-th-large"></i>
                Dashboard
            </a>
            <a href="manage_products.php">
                <i class="fas fa-box-open"></i>
                Manage Products
            </a>
            <a href="manage_orders.php">
                <i class="fas fa-shopping-cart"></i>
                Orders
                <?php if ($lowStock > 0): ?>
                    <span class="nav-badge"><?php echo $lowStock; ?></span>
                <?php endif; ?>
            </a>
            <a href="manage_archives.php">
                <i class="fas fa-archive"></i>
                Archives
            </a>

            <div class="sidebar-section-label">Store</div>

            <a href="../index.php" target="_blank">
                <i class="fas fa-store"></i>
                View Live Store
            </a>
            <a href="../index.php?page=products" target="_blank">
                <i class="fas fa-tags"></i>
                Product Catalog
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php">
                <i class="fas fa-arrow-left"></i>
                Back to Site
            </a>
        </div>
    </aside>

    <!-- ══════════════════════════ MAIN WRAP ══════════════════════════ -->
    <div class="main-wrap">

        <!-- ── TOP BAR ── -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="topbar-title">Dashboard</span>
                <div class="topbar-divider"></div>
                <span class="topbar-crumb">Inventory &amp; Products</span>
            </div>
            <div class="topbar-right">
                <a href="../index.php" target="_blank" class="btn-topbar">
                    <i class="fas fa-external-link-alt"></i> Live Store
                </a>
                <button class="btn-topbar btn-topbar-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>
        </header>

        <!-- ── PAGE BODY ── -->
        <main class="page-body">

            <!-- Alerts -->
            <?php if (isset($_GET['success']) && $_GET['success'] === 'added'): ?>
                <div class="apex-alert success">
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Success!</strong> New gadget added to inventory.</span>
                    <button class="close-btn" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
                <div class="apex-alert danger">
                    <i class="fas fa-trash-alt"></i>
                    <span><strong>Removed.</strong> The gadget was deleted from inventory.</span>
                    <button class="close-btn" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'edited'): ?>
                <div class="apex-alert info">
                    <i class="fas fa-edit"></i>
                    <span><strong>Updated.</strong> The gadget details were successfully changed.</span>
                    <button class="close-btn" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?>

            <!-- Stat Cards -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-box-open"></i></div>
                    <div>
                        <div class="stat-num"><?php echo $totalProducts; ?></div>
                        <div class="stat-lbl">Total Products</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon cyan"><i class="fas fa-cubes"></i></div>
                    <div>
                        <div class="stat-num"><?php echo number_format($totalStock); ?></div>
                        <div class="stat-lbl">Units in Stock</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                    <div>
                        <div class="stat-num"><?php echo $lowStock; ?></div>
                        <div class="stat-lbl">Low Stock Items</div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="content-grid">

                <!-- ── ADD PRODUCT PANEL ── -->
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title">Add New Gadget</span>
                    </div>
                    <div class="panel-body">
                        <form method="POST" action="apex26admin.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add">

                            <div class="field-group">
                                <label class="field-label">Product Name</label>
                                <input type="text" name="name" class="field-control" required placeholder="e.g. Razer DeathAdder V3">
                            </div>

                            <div class="field-row">
                                <div class="field-group">
                                    <label class="field-label">Brand</label>
                                    <select name="brand" class="field-control" required>
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
                                <div class="field-group">
                                    <label class="field-label">Category</label>
                                    <select name="category" class="field-control" required>
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

                            <div class="field-row">
                                <div class="field-group">
                                    <label class="field-label">Price (₱)</label>
                                    <input type="number" step="0.01" name="price" class="field-control" required placeholder="0.00">
                                </div>
                                <div class="field-group">
                                    <label class="field-label">Stock Qty</label>
                                    <input type="number" name="stock" class="field-control" required placeholder="10">
                                </div>
                            </div>

                            <div class="field-row">
                                <div class="field-group">
                                    <label class="field-label">Old Price (₱)</label>
                                    <input type="number" step="0.01" name="old_price" class="field-control" placeholder="Optional">
                                </div>
                                <div class="field-group">
                                    <label class="field-label">Rating Count</label>
                                    <input type="number" name="rating" class="field-control" placeholder="e.g. 210">
                                </div>
                            </div>

                            <div class="field-row">
                                <div class="field-group">
                                    <label class="field-label">Badge Text</label>
                                    <input type="text" name="badge" class="field-control" placeholder="e.g. Popular">
                                </div>
                                <div class="field-group">
                                    <label class="field-label">Badge Style</label>
                                    <select name="badge_type" class="field-control">
                                        <option value="">None</option>
                                        <option value="normal">Normal</option>
                                        <option value="new">New (Green)</option>
                                        <option value="sale">Sale (Red)</option>
                                        <option value="ribbon">Corner Ribbon</option>
                                    </select>
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Image Source</label>
                                <div class="radio-group">
                                    <label class="radio-item">
                                        <input type="radio" name="image_source" id="upload_radio" value="upload" checked>
                                        <span>Upload File</span>
                                    </label>
                                    <label class="radio-item">
                                        <input type="radio" name="image_source" id="url_radio" value="url">
                                        <span>Image URL / SVG</span>
                                    </label>
                                </div>
                            </div>

                            <div id="upload_section" class="field-group">
                                <label class="field-label">Upload Image</label>
                                <input type="file" name="image_upload" class="field-control" accept="image/*">
                                <small style="font-size:.7rem; color:var(--text-muted); margin-top:4px; display:block;">JPG, PNG, GIF, WebP — max 5 MB</small>
                            </div>
                            <div id="url_section" class="field-group" style="display:none;">
                                <label class="field-label">Image URL or SVG Code</label>
                                <input type="text" name="image" class="field-control" placeholder="<svg...> or https://...">
                            </div>

                            <div class="field-group">
                                <label class="field-label">Description</label>
                                <textarea name="desc" class="field-control" rows="3" placeholder="Specs and details..." style="resize:vertical;"></textarea>
                            </div>

                            <button type="submit" class="btn-submit">
                                <i class="fas fa-plus-circle" style="margin-right:7px;"></i>Publish to Store
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ── INVENTORY TABLE PANEL ── -->
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title">Current Inventory</span>
                        <span style="background:var(--accent);color:var(--sidebar-bg);font-size:.7rem;font-weight:800;padding:4px 12px;border-radius:20px;letter-spacing:.06em;">
                            <?php echo $totalProducts; ?> Items
                        </span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="inv-table">
                            <thead>
                                <tr>
                                    <th style="padding-left:20px;">ID</th>
                                    <th>Gadget</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th style="text-align:right; padding-right:20px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="fas fa-box-open"></i>
                                                <p>No gadgets in inventory yet.<br>Add your first product using the form.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <?php
                                        $s = isset($product['stock']) ? (int)$product['stock'] : 0;
                                        $stockClass = $s === 0 ? 'zero' : ($s < 5 ? 'low' : 'ok');
                                        $stockLabel = $s === 0 ? 'Out' : $s;
                                        $productImage = Inventory::getProductImageSrc($product['image'] ?? '', '../');
                                        ?>
                                        <tr>
                                            <td class="text-muted fw-bold" style="padding-left:20px; font-size:.8rem;">#<?php echo $product['id']; ?></td>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <div class="product-thumb">
                                                        <?php if (strpos($product['image'], '<svg') !== false): ?>
                                                            <?php echo $product['image']; ?>
                                                        <?php else: ?>
                                                            <img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                                        <div class="product-meta">
                                                            <?php echo isset($product['brand'])    ? htmlspecialchars($product['brand'])            : 'No Brand'; ?>
                                                            &bull;
                                                            <?php echo isset($product['category']) ? htmlspecialchars(ucfirst($product['category'])) : 'Uncategorized'; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="price-tag">₱<?php echo number_format($product['price'], 2); ?></td>
                                            <td>
                                                <span class="stock-badge <?php echo $stockClass; ?>"><?php echo $stockLabel; ?></span>
                                            </td>
                                            <td style="text-align:right; padding-right:20px; white-space:nowrap;">
                                                <button type="button" class="act-btn act-btn-edit me-1"
                                                    data-bs-toggle="modal" data-bs-target="#editModal"
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
                                                    <i class="fas fa-pen"></i> Edit
                                                </button>
                                                <form method="POST" action="apex26admin.php" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <button type="submit" class="act-btn act-btn-del"
                                                        onclick="return confirm('Delete this gadget permanently?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- /content-grid -->
        </main>

        <footer style="padding: 18px 32px; border-top: 1px solid var(--border); background: var(--card-bg); text-align:center;">
            <p style="font-size:.78rem; color:var(--text-muted); margin:0;">© 2026 ApeX Gear Admin Panel &mdash; All rights reserved.</p>
        </footer>
    </div><!-- /main-wrap -->


    <!-- ══════════════════════════ EDIT MODAL ══════════════════════════ -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header-custom">
                    <span class="modal-title"><i class="fas fa-pen me-2"></i>Edit Gadget</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="apex26admin.php" enctype="multipart/form-data">
                    <div class="modal-body-custom">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="product_id" id="edit_product_id">

                        <div class="field-group">
                            <label class="field-label">Product Name</label>
                            <input type="text" name="name" id="edit_name" class="field-control" required>
                        </div>

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Brand</label>
                                <select name="brand" id="edit_brand" class="field-control" required>
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
                            <div class="field-group">
                                <label class="field-label">Category</label>
                                <select name="category" id="edit_category" class="field-control" required>
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

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Price (₱)</label>
                                <input type="number" step="0.01" name="price" id="edit_price" class="field-control" required>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Stock Qty</label>
                                <input type="number" name="stock" id="edit_stock" class="field-control" required>
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Old Price (₱)</label>
                                <input type="number" step="0.01" name="old_price" id="edit_old_price" class="field-control">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Rating Count</label>
                                <input type="number" name="rating" id="edit_rating" class="field-control">
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Badge Text</label>
                                <input type="text" name="badge" id="edit_badge" class="field-control">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Badge Style</label>
                                <select name="badge_type" id="edit_badge_type" class="field-control">
                                    <option value="">None</option>
                                    <option value="normal">Normal</option>
                                    <option value="new">New (Green)</option>
                                    <option value="sale">Sale (Red)</option>
                                    <option value="ribbon">Corner Ribbon</option>
                                </select>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Image Source</label>
                            <div class="radio-group">
                                <label class="radio-item">
                                    <input type="radio" name="image_source" id="edit_upload_radio" value="upload" checked>
                                    <span>Upload File</span>
                                </label>
                                <label class="radio-item">
                                    <input type="radio" name="image_source" id="edit_url_radio" value="url">
                                    <span>Image URL / SVG</span>
                                </label>
                            </div>
                        </div>

                        <div id="edit_upload_section" class="field-group">
                            <label class="field-label">Upload Image</label>
                            <input type="file" name="image_upload" class="field-control" accept="image/*">
                            <small style="font-size:.7rem; color:var(--text-muted); margin-top:4px; display:block;">Leave blank to keep current image.</small>
                        </div>
                        <div id="edit_url_section" class="field-group" style="display:none;">
                            <label class="field-label">Image URL or SVG Code</label>
                            <input type="text" name="image" id="edit_image" class="field-control" placeholder="<svg...> or https://...">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Description</label>
                            <textarea name="desc" id="edit_desc" class="field-control" rows="3" style="resize:vertical;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-save"><i class="fas fa-check me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════ ADD MODAL (shortcut from topbar) ══════════════════════════ -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header-custom">
                    <span class="modal-title"><i class="fas fa-plus me-2"></i>Add New Gadget</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="apex26admin.php" enctype="multipart/form-data">
                    <div class="modal-body-custom">
                        <input type="hidden" name="action" value="add">

                        <div class="field-group">
                            <label class="field-label">Product Name</label>
                            <input type="text" name="name" class="field-control" required placeholder="e.g. Razer DeathAdder V3">
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Brand</label>
                                <select name="brand" class="field-control" required>
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
                            <div class="field-group">
                                <label class="field-label">Category</label>
                                <select name="category" class="field-control" required>
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
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Price (₱)</label>
                                <input type="number" step="0.01" name="price" class="field-control" required placeholder="0.00">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Stock Qty</label>
                                <input type="number" name="stock" class="field-control" required placeholder="10">
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Old Price (₱)</label>
                                <input type="number" step="0.01" name="old_price" class="field-control" placeholder="Optional">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Rating Count</label>
                                <input type="number" name="rating" class="field-control" placeholder="e.g. 210">
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Badge Text</label>
                                <input type="text" name="badge" class="field-control" placeholder="e.g. Popular">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Badge Style</label>
                                <select name="badge_type" class="field-control">
                                    <option value="">None</option>
                                    <option value="normal">Normal</option>
                                    <option value="new">New (Green)</option>
                                    <option value="sale">Sale (Red)</option>
                                    <option value="ribbon">Corner Ribbon</option>
                                </select>
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Image Source</label>
                            <div class="radio-group">
                                <label class="radio-item">
                                    <input type="radio" name="image_source" id="modal_upload_radio" value="upload" checked>
                                    <span>Upload File</span>
                                </label>
                                <label class="radio-item">
                                    <input type="radio" name="image_source" id="modal_url_radio" value="url">
                                    <span>Image URL / SVG</span>
                                </label>
                            </div>
                        </div>
                        <div id="modal_upload_section" class="field-group">
                            <label class="field-label">Upload Image</label>
                            <input type="file" name="image_upload" class="field-control" accept="image/*">
                        </div>
                        <div id="modal_url_section" class="field-group" style="display:none;">
                            <label class="field-label">Image URL or SVG Code</label>
                            <input type="text" name="image" class="field-control" placeholder="<svg...> or https://...">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Description</label>
                            <textarea name="desc" class="field-control" rows="3" placeholder="Specs and details..." style="resize:vertical;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-save"><i class="fas fa-plus me-1"></i>Publish to Store</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /* ── Sidebar Toggle (mobile) ── */
        function toggleSidebar() {
            const s = document.getElementById('sidebar');
            const o = document.getElementById('sidebarOverlay');
            s.classList.toggle('open');
            o.classList.toggle('show');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('show');
        }

        /* ── Populate Edit Modal ── */
        function populateEditForm(btn) {
            document.getElementById('edit_product_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_name').value = btn.getAttribute('data-name');
            document.getElementById('edit_brand').value = btn.getAttribute('data-brand');
            document.getElementById('edit_category').value = btn.getAttribute('data-category');
            document.getElementById('edit_price').value = btn.getAttribute('data-price');
            document.getElementById('edit_stock').value = btn.getAttribute('data-stock');
            document.getElementById('edit_old_price').value = btn.getAttribute('data-old_price');
            document.getElementById('edit_rating').value = btn.getAttribute('data-rating');
            document.getElementById('edit_badge').value = btn.getAttribute('data-badge');
            document.getElementById('edit_badge_type').value = btn.getAttribute('data-badge_type');
            document.getElementById('edit_image').value = btn.getAttribute('data-image');
            document.getElementById('edit_desc').value = btn.getAttribute('data-desc');
        }

        /* ── Image Source Toggles ── */
        document.addEventListener('DOMContentLoaded', function() {
            function bindToggle(radioUpId, radioUrlId, sectionUpId, sectionUrlId) {
                const up = document.getElementById(radioUpId);
                const url = document.getElementById(radioUrlId);
                const ups = document.getElementById(sectionUpId);
                const uls = document.getElementById(sectionUrlId);
                if (!up || !url) return;
                const toggle = () => {
                    ups.style.display = up.checked ? 'block' : 'none';
                    uls.style.display = url.checked ? 'block' : 'none';
                };
                up.addEventListener('change', toggle);
                url.addEventListener('change', toggle);
            }
            bindToggle('upload_radio', 'url_radio', 'upload_section', 'url_section');
            bindToggle('edit_upload_radio', 'edit_url_radio', 'edit_upload_section', 'edit_url_section');
            bindToggle('modal_upload_radio', 'modal_url_radio', 'modal_upload_section', 'modal_url_section');
        });
    </script>
</body>

</html>
