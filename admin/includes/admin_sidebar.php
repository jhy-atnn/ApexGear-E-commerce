<?php
$currentAdminPage = $currentAdminPage ?? basename($_SERVER['PHP_SELF']);
$pendingOrderBadge = (int)($pendingOrders ?? $pendingCount ?? 0);

function adminNavActive(string $page, string $currentAdminPage): string
{
    return $currentAdminPage === $page ? ' class="active"' : '';
}
?>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
    <a href="../index.php" class="sidebar-brand">
        <img src="../assets/images/ApeX Logo.png" alt="ApeX Gear">
        <div class="sidebar-brand-text"><span class="t1">ApeX </span><span class="t2">Gear</span></div>
        <span class="sidebar-badge">Admin</span>
    </a>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Main</div>
        <a href="apex26admin.php"<?php echo adminNavActive('apex26admin.php', $currentAdminPage); ?>><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="manage_orders.php"<?php echo adminNavActive('manage_orders.php', $currentAdminPage); ?>>
            <i class="fas fa-shopping-cart"></i> Orders
            <?php if ($pendingOrderBadge > 0): ?><span class="nav-badge"><?php echo $pendingOrderBadge; ?></span><?php endif; ?>
        </a>
        <a href="manage_products.php"<?php echo adminNavActive('manage_products.php', $currentAdminPage); ?>><i class="fas fa-boxes"></i> Manage Products</a>
        <a href="manage_archives.php"<?php echo adminNavActive('manage_archives.php', $currentAdminPage); ?>><i class="fas fa-archive"></i> Archives</a>
        <a href="manage_users.php"<?php echo adminNavActive('manage_users.php', $currentAdminPage); ?>><i class="fas fa-users"></i> Users</a>
        <a href="report.php"<?php echo adminNavActive('report.php', $currentAdminPage); ?>><i class="fas fa-chart-pie"></i> Reports &amp; Analytics</a>
        <a href="manage_deals.php"<?php echo adminNavActive('manage_deals.php', $currentAdminPage); ?>><i class="fas fa-percentage"></i> Deals &amp; Promos</a>
        <div class="sidebar-section-label">Store</div>
        <a href="../index.php" target="_blank"><i class="fas fa-store"></i> View Live Store</a>
        <a href="../store.php" target="_blank"><i class="fas fa-tags"></i> Product Catalog</a>
    </nav>
    <div class="sidebar-footer" style="display: flex; flex-direction: column; gap: 14px;">
        <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a>
        <a href="admin_logout.php" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>
<script>
    window.toggleSidebar = window.toggleSidebar || function() {
        document.getElementById('sidebar')?.classList.toggle('open');
        document.getElementById('sidebarOverlay')?.classList.toggle('show');
    };

    window.closeSidebar = window.closeSidebar || function() {
        document.getElementById('sidebar')?.classList.remove('open');
        document.getElementById('sidebarOverlay')?.classList.remove('show');
    };
</script>
