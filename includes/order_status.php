<?php
$orders = [];
$order_items_map = [];

if (isset($_SESSION['user']['id'])) {
    require_once __DIR__ . '/../classes/Inventory.php';
    $userId = intval($_SESSION['user']['id']);

    /** @var Inventory $inv */
    $inv = new Inventory();

    if (method_exists($inv, 'getOrdersByUser')) {
        // Fetch orders, you might want to adjust this query in Inventory.php later to EXCLUDE 'Completed' orders if you want them fully hidden
        $allUserOrders = $inv->getOrdersByUser($userId);
        
        // Filter out completed/archived orders from the active view
        foreach ($allUserOrders as $ord) {
            if ($ord['order_status'] !== 'Completed') {
                $orders[] = $ord;
                if (method_exists($inv, 'getOrderItems')) {
                    $order_items_map[intval($ord['order_id'])] = $inv->getOrderItems($ord['order_id']);
                }
            }
        }
    }
}
?>

<div id="orderStatusModal" class="profile-modal-container">
    <div class="profile-modal-content" style="max-width: 650px;">
        <div class="profile-modal-header border-bottom pb-3">
            <button type="button" class="btn-close-modal"><i class="fas fa-arrow-left"></i></button>
            <h5 class="profile-modal-title mb-0"><i class="fas fa-box-open me-2 text-primary"></i> My Orders</h5>
        </div>
        
        <div class="profile-modal-body" style="background: #f8f9fa; padding: 20px;">
            <div id="orderAlert" class="alert d-none"></div>

            <?php if (empty($orders)): ?>
                <div class="text-center py-5">
                    <div style="width: 80px; height: 80px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                        <i class="fas fa-receipt fa-2x text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-dark fw-bold">No Active Orders</h6>
                    <p class="text-muted small">You don't have any orders currently in progress.</p>
                </div>
            <?php else: ?>
                
                <?php foreach ($orders as $order): 
                    $status = $order['order_status'] ?? 'Pending';
                    $isCanceled = ($status === 'Canceled');
                    
                    // Determine Timeline Progress
                    $step1 = true; // Placed is always true
                    $step2 = in_array($status, ['On Process', 'Shipped', 'Delivered']);
                    $step3 = in_array($status, ['Shipped', 'Delivered']);
                    $step4 = ($status === 'Delivered');
                    
                    // Calculate Progress Bar Width
                    $progressWidth = '0%';
                    if ($step4) $progressWidth = '100%';
                    elseif ($step3) $progressWidth = '66%';
                    elseif ($step2) $progressWidth = '33%';
                ?>
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                            <div>
                                <span class="badge <?php echo $isCanceled ? 'bg-danger' : ($step4 ? 'bg-success' : 'bg-primary'); ?> mb-1">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                                <h6 class="mb-0 fw-bold" style="font-family: monospace; font-size: 0.9rem;">
                                    Ref: <?php echo htmlspecialchars($order['reference_number'] ?? 'N/A'); ?>
                                </h6>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                                <div class="fw-bold text-dark">₱<?php echo number_format($order['total_amount'], 2); ?></div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="mb-4">
                                <?php if (!empty($order_items_map[$order['order_id']])): ?>
                                    <?php foreach ($order_items_map[$order['order_id']] as $item): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="<?php echo htmlspecialchars($item['image'] ?? 'https://via.placeholder.com/50'); ?>" alt="Item" style="width: 40px; height: 40px; object-fit: contain; background: #f4f4f4; border-radius: 6px; margin-right: 12px;">
                                            <div class="flex-grow-1 text-truncate">
                                                <div class="small fw-semibold text-truncate"><?php echo htmlspecialchars($item['name']); ?></div>
                                                <div class="small text-muted">Qty: <?php echo $item['qty']; ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <?php if (!$isCanceled): ?>
                                <div class="tracking-container position-relative px-2 mb-3">
                                    <div class="progress position-absolute" style="height: 4px; top: 14px; left: 10%; right: 10%; background: #e9ecef; z-index: 1;">
                                        <div class="progress-bar" style="background: var(--apex-blue, #00c2ff); width: <?php echo $progressWidth; ?>;"></div>
                                    </div>
                                    <div class="d-flex justify-content-between position-relative" style="z-index: 2;">
                                        
                                        <div class="text-center tracking-step <?php echo $step1 ? 'active' : ''; ?>" style="width: 25%;">
                                            <div class="tracking-icon"><i class="fas fa-clipboard-list"></i></div>
                                            <div class="tracking-text">Placed</div>
                                        </div>
                                        
                                        <div class="text-center tracking-step <?php echo $step2 ? 'active' : ''; ?>" style="width: 25%;">
                                            <div class="tracking-icon"><i class="fas fa-box-open"></i></div>
                                            <div class="tracking-text">Packed</div>
                                        </div>
                                        
                                        <div class="text-center tracking-step <?php echo $step3 ? 'active' : ''; ?>" style="width: 25%;">
                                            <div class="tracking-icon"><i class="fas fa-truck-fast"></i></div>
                                            <div class="tracking-text">Shipped</div>
                                        </div>
                                        
                                        <div class="text-center tracking-step <?php echo $step4 ? 'active' : ''; ?>" style="width: 25%;">
                                            <div class="tracking-icon"><i class="fas fa-house-circle-check"></i></div>
                                            <div class="tracking-text">Delivered</div>
                                        </div>

                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger text-center py-2 mb-0" style="font-size: 0.85rem;">
                                    <i class="fas fa-times-circle me-1"></i> This order was canceled.
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($status === 'Delivered'): ?>
                            <div class="card-footer bg-white border-top text-end py-3">
                                <p class="text-muted small mb-2 text-center text-sm-end"><i class="fas fa-info-circle"></i> Please confirm you have received the items to archive this order.</p>
                                <button class="btn btn-success btn-sm w-100 w-sm-auto px-4 py-2 fw-bold rounded-pill" onclick="confirmOrder(<?php echo $order['order_id']; ?>)">
                                    <i class="fas fa-check-double me-1"></i> Confirm Receipt
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    /* Tracking Timeline CSS */
    .tracking-icon {
        width: 32px;
        height: 32px;
        background: #e9ecef;
        color: #adb5bd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px auto;
        font-size: 0.85rem;
        border: 2px solid #fff;
        transition: all 0.3s ease;
    }
    .tracking-text {
        font-size: 0.75rem;
        color: #adb5bd;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .tracking-step.active .tracking-icon {
        background: var(--apex-blue, #00c2ff);
        color: #fff;
        box-shadow: 0 0 0 3px rgba(0, 194, 255, 0.2);
    }
    .tracking-step.active .tracking-text {
        color: #343a40;
    }
    @media (max-width: 576px) {
        .tracking-text { font-size: 0.65rem; }
        .tracking-icon { width: 28px; height: 28px; font-size: 0.75rem; }
    }
</style>

<script>
    // AJAX to handle confirming the order
    async function confirmOrder(orderId) {
        if (!confirm("Are you sure you want to confirm receipt? This will move the order to your archives.")) {
            return;
        }

        const alertBox = document.getElementById('orderAlert');
        const formData = new FormData();
        formData.append('action', 'confirm_order');
        formData.append('order_id', orderId);

        try {
            const response = await fetch('actions/confirm_order_action.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                alertBox.className = 'alert alert-success';
                alertBox.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + result.message;
                alertBox.classList.remove('d-none');
                
                // Reload the page to reflect the archived order
                setTimeout(() => window.location.reload(), 1200);
            } else {
                alertBox.className = 'alert alert-danger';
                alertBox.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + result.message;
                alertBox.classList.remove('d-none');
            }
        } catch (err) {
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i> Connection error.';
            alertBox.classList.remove('d-none');
        }
    }

    // Modal Opening/Closing Logic
    document.addEventListener('DOMContentLoaded', function() {
        const orderStatusModal = document.getElementById('orderStatusModal');
        const orderStatusLinks = document.querySelectorAll('.order-status-link');
        const closeButtons = orderStatusModal ? orderStatusModal.querySelectorAll('.btn-close-modal') : [];

        if (!orderStatusModal || orderStatusLinks.length === 0) return;

        orderStatusLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                orderStatusModal.classList.add('open');
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