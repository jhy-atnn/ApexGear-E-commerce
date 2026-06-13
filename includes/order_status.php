<?php

$orders = [];
$order_items_map = [];
$order_ids = [];

if (isset($_SESSION['user']['id'])) {
    require_once __DIR__ . '/../classes/Inventory.php';
    $userId = intval($_SESSION['user']['id']);

    /** @var Inventory $inv */
    $inv = new Inventory();

    // Check if methods exist before calling to prevent fatal errors
    if (method_exists($inv, 'getOrdersByUser')) {
        $orders = $inv->getOrdersByUser($userId);
        foreach ($orders as $ord) {
            $order_ids[] = intval($ord['order_id']);
            if (method_exists($inv, 'getOrderItems')) {
                $order_items_map[intval($ord['order_id'])] = $inv->getOrderItems($ord['order_id']);
            }
        }
    }
}

/**
 * Normalize a raw payment method string to a display-friendly label.
 *
 * @param string|null $raw
 * @return string
 */
function formatPaymentMethod($raw)
{
    $payment_raw = strtolower(trim($raw ?? ''));
    if (strpos($payment_raw, 'cod') !== false || strpos($payment_raw, 'cash on delivery') !== false) {
        return 'Cash on Delivery';
    }
    if (strpos($payment_raw, 'card') !== false || strpos($payment_raw, 'credit') !== false || strpos($payment_raw, 'debit') !== false) {
        return 'Credit / Debit Card';
    }
    if (strpos($payment_raw, 'gcash') !== false) {
        return 'GCash';
    }
    if (strpos($payment_raw, 'paypal') !== false) {
        return 'PayPal';
    }
    if (strpos($payment_raw, 'maya') !== false) {
        return 'Maya';
    }
    return $raw ?: 'N/A';
}

/**
 * Return a short payment status label for a given method.
 *
 * @param string|null $method
 * @return string
 */
function paymentStatusLabel($method)
{
    $payment_raw = strtolower(trim($method ?? ''));
    return (strpos($payment_raw, 'cod') !== false || strpos($payment_raw, 'cash on delivery') !== false) ? 'COD' : 'Paid';
}

/**
 * Map an order status to a CSS class name.
 *
 * @param string|null $status
 * @return string
 */
function statusClass($status)
{
    $normalized = strtolower(trim($status ?? ''));
    if ($normalized === 'delivered') {
        return 'delivered';
    }
    if ($normalized === 'canceled') {
        return 'canceled';
    }
    if ($normalized === 'shipped') {
        return 'shipped';
    }
    if ($normalized === 'on process' || $normalized === 'processing' || $normalized === 'pending') {
        return 'process';
    }
    return 'pending';
}

/**
 * Get a human-friendly summary title from an order status.
 *
 * @param string|null $status
 * @return string
 */
function orderSummaryTitle($status)
{
    $normalized = strtolower(trim($status ?? ''));
    if ($normalized === 'delivered') {
        return 'Order Complete';
    }
    if ($normalized === 'canceled') {
        return 'Order Canceled';
    }
    if ($normalized === 'shipped') {
        return 'Order Shipped';
    }
    return 'Order In Progress';
}
?>

<style>
    #orderStatusModal .profile-modal-content {
        max-width: 400px;
        width: 100%;
    }

    #orderStatusModal .profile-modal-body {
        max-height: 72vh;
        overflow-y: auto;
        padding-right: 0.5rem;
    }

    #orderStatusModal .order-card {
        background: #17182f;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        padding: 1.4rem;
        margin-bottom: 1rem;
        color: #fff;
    }

    #orderStatusModal .order-card+.order-card {
        margin-top: 0.75rem;
    }

    #orderStatusModal .order-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    #orderStatusModal .order-card-header h6 {
        margin: 0;
        font-size: 1.05rem;
        letter-spacing: 0.02em;
    }

    #orderStatusModal .order-card-header .order-meta {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.86rem;
        line-height: 1.5;
    }

    #orderStatusModal .order-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.55rem 1rem;
        border-radius: 999px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.78rem;
        letter-spacing: 0.08em;
    }

    #orderStatusModal .order-status-pill.delivered {
        background: rgba(40, 167, 69, 0.15);
        color: #28a745;
    }

    #orderStatusModal .order-status-pill.canceled {
        background: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }

    #orderStatusModal .order-status-pill.shipped {
        background: rgba(13, 110, 253, 0.15);
        color: #0d6efd;
    }

    #orderStatusModal .order-status-pill.process {
        background: rgba(255, 193, 7, 0.15);
        color: #ffc107;
    }

    #orderStatusModal .order-status-pill.pending {
        background: rgba(94, 94, 94, 0.16);
        color: #ced4da;
    }

    #orderStatusModal .order-progress {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.6rem;
        margin-bottom: 1rem;
    }

    #orderStatusModal .order-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        text-align: center;
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.55);
    }

    #orderStatusModal .order-step .step-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.18);
    }

    #orderStatusModal .order-step.active .step-dot {
        background: #00c2ff;
        box-shadow: 0 0 0 5px rgba(0, 194, 255, 0.12);
    }

    #orderStatusModal .order-step.active {
        color: #fff;
    }

    #orderStatusModal .order-item-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    #orderStatusModal .summary-label {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.25rem;
    }

    #orderStatusModal .summary-value {
        font-size: 0.95rem;
        color: #fff;
    }

    #orderStatusModal .order-items-list {
        list-style: none;
        margin: 0;
        padding: 0;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    #orderStatusModal .order-items-list li {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.95rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        font-size: 0.93rem;
        align-items: center;
    }

    #orderStatusModal .order-item-preview {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        max-width: 70%;
    }

    #orderStatusModal .order-item-image {
        width: 58px;
        height: 58px;
        border-radius: 16px;
        overflow: hidden;
        flex-shrink: 0;
        background: rgba(255, 255, 255, 0.06);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #orderStatusModal .order-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #orderStatusModal .order-items-list li:last-child {
        border-bottom: none;
    }

    #orderStatusModal .order-detail-block {
        display: grid;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }

    #orderStatusModal .order-detail-block span {
        display: block;
        font-size: 0.92rem;
    }

    #orderStatusModal .order-detail-block strong {
        color: #fff;
    }

    #orderStatusModal .order-card-footer {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    #orderStatusModal .order-card-footer .footer-item {
        min-width: 160px;
    }

    #orderStatusModal .empty-state {
        text-align: center;
        padding: 4rem 1rem;
    }

    #orderStatusModal .empty-state i {
        font-size: 3rem;
        color: rgba(255, 255, 255, 0.4);
        margin-bottom: 1rem;
    }

    #orderStatusModal .order-status-title {
        color: #fff;
        margin: 0 0 0.25rem;
        font-size: 1rem;
    }

    #orderStatusModal .order-status-subtitle {
        color: rgba(255, 255, 255, 0.65);
        font-size: 0.82rem;
        margin: 0;
    }
</style>

<div id="orderStatusModal" class="profile-modal-container">
    <div class="profile-modal-content">
        <div class="profile-modal-header">
            <button type="button" class="btn-close-modal"><i class="fas fa-times"></i></button>
            <h5 class="profile-modal-title">Order Status</h5>
        </div>
        <div class="profile-modal-body">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h5>No orders yet</h5>
                    <p>You don't have any orders yet. Start shopping and check back here for order updates.</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <?php $orderId = intval($order['order_id']); ?>
                    <div class="order-card">
                        <div class="order-card-header">
                            <div>
                                <h6><?php echo htmlspecialchars(orderSummaryTitle($order['order_status'])); ?></h6>
                                <div class="order-meta">
                                    Order #<?php echo $orderId; ?> · <?php echo htmlspecialchars(date('F j, Y', strtotime($order['order_date'] ?? ''))); ?>
                                </div>
                            </div>
                            <span class="order-status-pill <?php echo statusClass($order['order_status']); ?>">
                                <?php echo htmlspecialchars($order['order_status'] ?? 'Pending'); ?>
                            </span>
                        </div>

                        <div class="order-item-summary">
                            <div>
                                <div class="summary-label">Payment</div>
                                <div class="summary-value"><?php echo htmlspecialchars(formatPaymentMethod($order['payment_method'] ?? '')); ?></div>
                            </div>
                            <div>
                                <div class="summary-label">Total Items</div>
                                <div class="summary-value"><?php echo count($order_items_map[$orderId] ?? []); ?></div>
                            </div>
                            <div>
                                <div class="summary-label">Total Amount</div>
                                <div class="summary-value">₱<?php echo number_format((float)($order['total_amount'] ?? 0), 2); ?></div>
                            </div>
                        </div>

                        <ul class="order-items-list">
                            <?php foreach ($order_items_map[$orderId] ?? [] as $item): ?>
                                <li>
                                    <div class="order-item-preview">
                                        <div class="order-item-image">
                                            <?php if (!empty($item['image']) && strpos($item['image'], '<svg') === false): ?>
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name'] ?? 'Product'); ?>">
                                            <?php elseif (!empty($item['image'])): ?>
                                                <?php echo $item['image']; ?>
                                            <?php else: ?>
                                                <span style="color:rgba(255,255,255,.55);">No image</span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['name'] ?? 'Product'); ?></strong>
                                            <span class="text-muted small"><?php echo htmlspecialchars($item['brand'] ?? 'ApeX'); ?> · x<?php echo intval($item['qty']); ?></span>
                                        </div>
                                    </div>
                                    <div><strong>₱<?php echo number_format((float)($item['price'] ?? 0), 2); ?></strong></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const orderStatusModal = document.getElementById('orderStatusModal');
        const orderStatusLinks = document.querySelectorAll('.order-status-link');
        const closeButtons = orderStatusModal ? orderStatusModal.querySelectorAll('.btn-close-modal') : [];

        if (!orderStatusModal || orderStatusLinks.length === 0) {
            return;
        }

        orderStatusLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                orderStatusModal.classList.add('open');
                const profilePanel = document.getElementById('profilePanel');
                if (profilePanel) {
                    profilePanel.classList.remove('open');
                }
            });
        });

        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                orderStatusModal.classList.remove('open');
            });
        });

        orderStatusModal.addEventListener('click', function(event) {
            if (event.target === orderStatusModal) {
                orderStatusModal.classList.remove('open');
            }
        });
    });
</script>