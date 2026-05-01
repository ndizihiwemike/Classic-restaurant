<?php
require_once '../config.php';

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('Location: ../login.php');
//     exit;
// }

include '../includes/header.php';

// Update order status
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    
    echo "<div class='alert alert-success'>Order status updated successfully!</div>";
}

// Fetch all orders with new columns
$orders = $pdo->query("SELECT o.*, u.name as customer_name 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       ORDER BY o.created_at DESC")->fetchAll();
?>

<style>
    .orders-header {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: white;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
    }

    .orders-header h1 {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0;
    }

    .orders-table-container {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .orders-table-header {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: white;
        font-weight: 600;
    }

    .orders-table-header th {
        border: none;
        padding: 1.25rem;
        vertical-align: middle;
    }

    .orders-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s ease;
    }

    .orders-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .orders-table td {
        padding: 1.25rem;
        vertical-align: middle;
    }

    .items-dropdown select {
        max-width: 290px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .items-dropdown select:focus {
        border-color: #0f766e;
        box-shadow: 0 0 0 0.2rem rgba(15, 118, 110, 0.15);
    }

    .badge-status {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .badge-pending     { background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); color: white; }
    .badge-processing  { background: linear-gradient(135deg, #3b82f6 0%, #0ea5e9 100%); color: white; }
    .badge-completed   { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); color: white; }
    .badge-cancelled   { background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); color: white; }
    .badge-paid        { background: #d1fae5; color: #065f46; font-weight: 600; }
    .badge-unpaid      { background: #fee2e2; color: #991b1b; font-weight: 600; }

    .order-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .form-select-status {
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        padding: 0.5rem;
        font-size: 0.875rem;
    }

    .btn-update-status {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-update-status:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);
        color: white;
    }
</style>

<div class="orders-header">
    <h1><i class="fas fa-receipt me-2"></i>All Orders</h1>
</div>

<div class="orders-table-container">
    <table class="table orders-table table-hover mb-0">
        <thead class="orders-table-header">
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Total</th>
                <th>Order Items</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): 
                // Safely decode JSON fields
                $items  = json_decode($order['order_items'] ?? '[]', true);
                $addons = json_decode($order['addons'] ?? '[]', true);

                if (!is_array($items))  $items  = [];
                if (!is_array($addons)) $addons = [];
            ?>
            <tr>
                <td><strong>#<?= $order['id'] ?></strong></td>
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td><strong><?= htmlspecialchars($order['phone_number'] ?? 'N/A') ?></strong></td>
                
                <td>
                    <strong>UGX <?= number_format($order['total'], 0) ?></strong>
                </td>

                <!-- Order Items + Addons -->
                <td>
                    <div class="items-dropdown">
                        <select class="form-select">
                            <option value="" disabled selected>
                                <?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?>
                                <?php if (!empty($addons)): ?> 
                                    + <?= count($addons) ?> addon<?= count($addons) !== 1 ? 's' : '' ?>
                                <?php endif; ?>
                            </option>
                            
                            <!-- Main Items -->
                            <?php foreach ($items as $item): 
                                $name  = $item['name'] ?? $item['product_name'] ?? 'Unknown Item';
                                $qty   = $item['quantity'] ?? 1;
                                $price = $item['price'] ?? 0;
                            ?>
                                <option value="" disabled>
                                    <?= htmlspecialchars($name) ?> × <?= (int)$qty ?> 
                                    - UGX <?= number_format($price * $qty, 0) ?>
                                </option>
                            <?php endforeach; ?>

                            <!-- Addons -->
                            <?php if (!empty($addons)): ?>
                                <?php foreach ($addons as $addon): 
                                    $addon_name = $addon['name'] ?? $addon['addon_name'] ?? 'Addon';
                                    $addon_type = $addon['type'] ?? '';
                                ?>
                                    <option value="" disabled style="color: #0f766e; font-style: italic;">
                                        ↳ <?= htmlspecialchars($addon_name) ?>
                                        <?php if ($addon_type): ?>(<?= htmlspecialchars($addon_type) ?>)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </td>

                <td>
                    <span class="badge-status badge-<?= $order['status'] === 'completed' ? 'completed' : 
                        ($order['status'] === 'pending' ? 'pending' : 
                        ($order['status'] === 'processing' ? 'processing' : 'cancelled')) ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </td>
                
                <td>
                    <span class="badge-status badge-<?= $order['payment_status'] === 'paid' ? 'paid' : 'unpaid' ?>">
                        <?= ucfirst($order['payment_status']) ?>
                    </span>
                </td>
                
                <td><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                
                <td>
                    <form method="POST" class="order-actions">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status" class="form-select-status">
                            <option value="pending"     <?= $order['status']=='pending'?'selected':'' ?>>Pending</option>
                            <option value="processing"  <?= $order['status']=='processing'?'selected':'' ?>>Processing</option>
                            <option value="completed"   <?= $order['status']=='completed'?'selected':'' ?>>Completed</option>
                            <option value="cancelled"   <?= $order['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn-update-status">
                            <i class="fas fa-check me-1"></i> Update
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>