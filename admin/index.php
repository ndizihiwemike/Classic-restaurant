<?php
require_once '../config.php';

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('Location: ../login.php');
//     exit;
// }

include '../includes/header.php';

// Statistics
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total) FROM orders WHERE payment_status = 'paid'")->fetchColumn() ?? 0;
?>

<style>
    .admin-header {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: white;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
    }

    .admin-header h1 {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .admin-header p {
        font-size: 1rem;
        opacity: 0.9;
    }

    .stat-card {
        border: none;
        border-radius: 10px;
        padding: 1.5rem;
        color: white;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .stat-card-teal {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
    }

    .stat-card-gold {
        background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
    }

    .stat-card-blue {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    }

    .stat-card-green {
        background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
    }

    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.95rem;
        opacity: 0.9;
        margin-bottom: 0.5rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: bold;
    }

    .quick-actions-section {
        margin-top: 3rem;
        margin-bottom: 2rem;
    }

    .quick-actions-title {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 1.5rem;
        color: #0f766e;
    }

    .action-btn {
        padding: 1rem;
        font-size: 1rem;
        border-radius: 8px;
        border: none;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .action-btn-primary {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: white;
    }

    .action-btn-primary:hover {
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(15, 118, 110, 0.3);
    }

    .action-btn-secondary {
        background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
        color: white;
    }

    .action-btn-secondary:hover {
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(217, 119, 6, 0.3);
    }
</style>

<div class="admin-header">
    <h1><i class="fas fa-chart-line me-2"></i>Admin Dashboard</h1>
    <p>Welcome to Classic Restaurant Management Center</p>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card stat-card-teal">
            <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-number"><?= $total_orders ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card stat-card-gold">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-label">Pending Orders</div>
            <div class="stat-number"><?= $pending_orders ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card stat-card-blue">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-label">Total Users</div>
            <div class="stat-number"><?= $total_users ?></div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card stat-card-green">
            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-number">UGX <?= number_format($total_revenue, 0) ?></div>
        </div>
    </div>
</div>

<div class="quick-actions-section">
    <h2 class="quick-actions-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h2>
    <div class="row g-3">
        <div class="col-md-6">
            <a href="menu.php" class="btn action-btn action-btn-primary w-100">
                <i class="fas fa-utensils me-2"></i>Manage Menu Items
            </a>
        </div>
        <div class="col-md-6">
            <a href="orders.php" class="btn action-btn action-btn-secondary w-100">
                <i class="fas fa-receipt me-2"></i>View All Orders
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>