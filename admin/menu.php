<?php
require_once '../config.php';

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('Location: ../login.php');
//     exit;
// }

include '../includes/header.php';

// Handle Add New Item with Image Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name        = trim($_POST['name']);
    $category    = $_POST['category'];
    $description = trim($_POST['description']);
    $price       = (float)$_POST['price'];
    $image_path  = 'assets/images/default-food.jpg'; // Default image

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../assets/images/';

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name   = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;
        $file_type   = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allowed file types
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_type, $allowed_types)) {
            // Check file size (max 5MB)
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $error = "Image is too large. Maximum size is 5MB.";
            } else if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = 'assets/images/' . $file_name;
            } else {
                $error = "Failed to upload image. Please try again.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
        }
    }

    if (!isset($error)) {
        $stmt = $pdo->prepare("INSERT INTO menu_items (name, category, description, price, image) 
                               VALUES (?, ?, ?, ?, ?)");

        if ($stmt->execute([$name, $category, $description, $price, $image_path])) {
            $success = "Menu item added successfully with image!";
        } else {
            $error = "Failed to add menu item to database.";
        }
    }
}

// Handle Edit Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
    $id          = (int)$_POST['item_id'];
    $name        = trim($_POST['name']);
    $category    = $_POST['category'];
    $description = trim($_POST['description']);
    $price       = (float)$_POST['price'];
    $image_path  = $_POST['current_image']; // Keep current image by default

    // Handle Image Upload for edit
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../assets/images/';

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name   = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;
        $file_type   = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allowed file types
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_type, $allowed_types)) {
            // Check file size (max 5MB)
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $error = "Image is too large. Maximum size is 5MB.";
            } else if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = 'assets/images/' . $file_name;
            } else {
                $error = "Failed to upload image. Please try again.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
        }
    }

    if (!isset($error)) {
        $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, category = ?, description = ?, price = ?, image = ? WHERE id = ?");

        if ($stmt->execute([$name, $category, $description, $price, $image_path, $id])) {
            $success = "Menu item updated successfully!";
        } else {
            $error = "Failed to update menu item.";
        }
    }
}

// Check if editing an item
$editing_item = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$edit_id]);
    $editing_item = $stmt->fetch();
}

// Handle Delete Menu Item
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$delete_id]);
    $success = "Menu item deleted successfully.";
}

// Handle Add New Add-on
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_addon'])) {
    $addon_name = trim($_POST['addon_name']);
    $addon_type = $_POST['addon_type'];
    $addon_price = (float)$_POST['addon_price'];

    if ($addon_name === '') {
        $error = "Add-on name cannot be empty.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO addons (name, type, price) VALUES (?, ?, ?)");
        if ($stmt->execute([$addon_name, $addon_type, $addon_price])) {
            $success = "Add-on added successfully.";
        } else {
            $error = "Failed to add add-on.";
        }
    }
}

// Handle Edit Add-on
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_addon'])) {
    $addon_id = (int)$_POST['addon_id'];
    $addon_name = trim($_POST['addon_name']);
    $addon_type = $_POST['addon_type'];
    $addon_price = (float)$_POST['addon_price'];

    if ($addon_name === '') {
        $error = "Add-on name cannot be empty.";
    } else {
        $stmt = $pdo->prepare("UPDATE addons SET name = ?, type = ?, price = ? WHERE id = ?");
        if ($stmt->execute([$addon_name, $addon_type, $addon_price, $addon_id])) {
            $success = "Add-on updated successfully.";
        } else {
            $error = "Failed to update add-on.";
        }
    }
}

// Handle Delete Add-on
if (isset($_GET['delete_addon'])) {
    $delete_addon_id = (int)$_GET['delete_addon'];
    $pdo->prepare("DELETE FROM addons WHERE id = ?")->execute([$delete_addon_id]);
    $success = "Add-on deleted successfully.";
}

$editing_addon = null;
if (isset($_GET['edit_addon'])) {
    $edit_addon_id = (int)$_GET['edit_addon'];
    $stmt = $pdo->prepare("SELECT * FROM addons WHERE id = ?");
    $stmt->execute([$edit_addon_id]);
    $editing_addon = $stmt->fetch();
}

$items = $pdo->query("SELECT * FROM menu_items ORDER BY category, name")->fetchAll();
$addons = $pdo->query("SELECT * FROM addons ORDER BY type, name")->fetchAll();
?>
<style>
    .menu-management-header {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: white;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
    }

    .menu-management-header h1 {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0;
    }

    .form-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .form-card .card-header {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: white;
        border-radius: 10px 10px 0 0;
        padding: 1.5rem;
    }

    .form-card .card-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 1.2rem;
    }

    .form-card .card-body {
        padding: 2rem;
    }

    .form-label {
        font-weight: 600;
        color: #0f766e;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        padding: 0.75rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0f766e;
        box-shadow: 0 0 0 0.2rem rgba(15, 118, 110, 0.15);
    }

    .btn-add-item {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 0.75rem 2rem;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-add-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(15, 118, 110, 0.3);
        color: white;
    }

    .items-table-container {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .items-table-header {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: white;
        font-weight: 600;
    }

    .items-table-header th {
        border: none;
        padding: 1.25rem;
        vertical-align: middle;
    }

    .items-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s ease;
    }

    .items-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .items-table td {
        padding: 1.25rem;
        vertical-align: middle;
    }

    .btn-edit-item {
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: white;
        border: none;
        padding: 0.5rem 0.75rem;
        border-radius: 6px 0 0 6px;
        transition: all 0.3s ease;
    }

    .btn-edit-item:hover {
        background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        color: white;
    }

    .btn-delete-item {
        background: #ef4444;
        color: white;
        border: none;
        padding: 0.5rem 0.75rem;
        border-radius: 0 6px 6px 0;
        transition: all 0.3s ease;
    }

    .btn-delete-item:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
</style>

<div class="menu-management-header">
    <h1><i class="fas fa-utensils me-2"></i>Manage Menu Items</h1>
    <!-- add a button to take me to the the order management page -->
    <a href="orders.php" class="btn btn-primary">
        <i class="fas fa-list me-2"></i>View Orders
    </a>
</div>

<!-- Add/Edit Item Form with Image Upload -->
<div class="card form-card">
    <div class="card-header">
        <h4><i class="fas fa-<?= $editing_item ? 'edit' : 'plus-circle' ?> me-2"></i><?= $editing_item ? 'Edit Menu Item' : 'Add New Menu Item' ?></h4>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <?php if ($editing_item): ?>
                <input type="hidden" name="item_id" value="<?= $editing_item['id'] ?>">
                <input type="hidden" name="current_image" value="<?= htmlspecialchars($editing_item['image']) ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= $editing_item ? htmlspecialchars($editing_item['name']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <optgroup label="🍽️ Core Categories (Main Meals)">
                                <option value="Pizza" <?= $editing_item && $editing_item['category'] == 'Pizza' ? 'selected' : '' ?>>Pizza</option>
                                <option value="Pasta" <?= $editing_item && $editing_item['category'] == 'Pasta' ? 'selected' : '' ?>>Pasta</option>
                                <option value="Burgers" <?= $editing_item && $editing_item['category'] == 'Burgers' ? 'selected' : '' ?>>Burgers</option>
                                <option value="Sandwiches" <?= $editing_item && $editing_item['category'] == 'Sandwiches' ? 'selected' : '' ?>>Sandwiches</option>
                                <option value="Rice Dishes" <?= $editing_item && $editing_item['category'] == 'Rice Dishes' ? 'selected' : '' ?>>Rice Dishes</option>
                                <option value="Noodles" <?= $editing_item && $editing_item['category'] == 'Noodles' ? 'selected' : '' ?>>Noodles</option>
                            </optgroup>
                            <optgroup label="🥗 Light & Healthy Options">
                                <option value="Salad" <?= $editing_item && $editing_item['category'] == 'Salad' ? 'selected' : '' ?>>Salad</option>
                                <option value="Vegan Dishes" <?= $editing_item && $editing_item['category'] == 'Vegan Dishes' ? 'selected' : '' ?>>Vegan Dishes</option>
                                <option value="Vegetarian Meals" <?= $editing_item && $editing_item['category'] == 'Vegetarian Meals' ? 'selected' : '' ?>>Vegetarian Meals</option>
                                <option value="Low-Calorie Meals" <?= $editing_item && $editing_item['category'] == 'Low-Calorie Meals' ? 'selected' : '' ?>>Low-Calorie Meals</option>
                                <option value="Gluten-Free Options" <?= $editing_item && $editing_item['category'] == 'Gluten-Free Options' ? 'selected' : '' ?>>Gluten-Free Options</option>
                            </optgroup>
                            <optgroup label="🍟 Starters & Sides">
                                <option value="Appetizers" <?= $editing_item && $editing_item['category'] == 'Appetizers' ? 'selected' : '' ?>>Appetizers</option>
                                <option value="Fries & Chips" <?= $editing_item && $editing_item['category'] == 'Fries & Chips' ? 'selected' : '' ?>>Fries & Chips</option>
                                <option value="Soups" <?= $editing_item && $editing_item['category'] == 'Soups' ? 'selected' : '' ?>>Soups</option>
                                <option value="Garlic Bread" <?= $editing_item && $editing_item['category'] == 'Garlic Bread' ? 'selected' : '' ?>>Garlic Bread</option>
                                <option value="Side Dishes" <?= $editing_item && $editing_item['category'] == 'Side Dishes' ? 'selected' : '' ?>>Side Dishes</option>
                            </optgroup>
                            <optgroup label="🍗 Protein-Based Dishes">
                                <option value="Chicken Dishes" <?= $editing_item && $editing_item['category'] == 'Chicken Dishes' ? 'selected' : '' ?>>Chicken Dishes</option>
                                <option value="Beef Dishes" <?= $editing_item && $editing_item['category'] == 'Beef Dishes' ? 'selected' : '' ?>>Beef Dishes</option>
                                <option value="Seafood" <?= $editing_item && $editing_item['category'] == 'Seafood' ? 'selected' : '' ?>>Seafood</option>
                                <option value="Grilled Items" <?= $editing_item && $editing_item['category'] == 'Grilled Items' ? 'selected' : '' ?>>Grilled Items</option>
                                <option value="BBQ Specials" <?= $editing_item && $editing_item['category'] == 'BBQ Specials' ? 'selected' : '' ?>>BBQ Specials</option>
                            </optgroup>
                            <optgroup label="🍰 Desserts">
                                <option value="Cakes" <?= $editing_item && $editing_item['category'] == 'Cakes' ? 'selected' : '' ?>>Cakes</option>
                                <option value="Ice Cream" <?= $editing_item && $editing_item['category'] == 'Ice Cream' ? 'selected' : '' ?>>Ice Cream</option>
                                <option value="Pastries" <?= $editing_item && $editing_item['category'] == 'Pastries' ? 'selected' : '' ?>>Pastries</option>
                                <option value="Puddings" <?= $editing_item && $editing_item['category'] == 'Puddings' ? 'selected' : '' ?>>Puddings</option>
                                <option value="Chocolates" <?= $editing_item && $editing_item['category'] == 'Chocolates' ? 'selected' : '' ?>>Chocolates</option>
                            </optgroup>
                            <optgroup label="🥤 Beverages">
                                <option value="Soft Drinks" <?= $editing_item && $editing_item['category'] == 'Soft Drinks' ? 'selected' : '' ?>>Soft Drinks</option>
                                <option value="Juices" <?= $editing_item && $editing_item['category'] == 'Juices' ? 'selected' : '' ?>>Juices</option>
                                <option value="Smoothies" <?= $editing_item && $editing_item['category'] == 'Smoothies' ? 'selected' : '' ?>>Smoothies</option>
                                <option value="Coffee" <?= $editing_item && $editing_item['category'] == 'Coffee' ? 'selected' : '' ?>>Coffee</option>
                                <option value="Tea" <?= $editing_item && $editing_item['category'] == 'Tea' ? 'selected' : '' ?>>Tea</option>
                                <option value="Milkshakes" <?= $editing_item && $editing_item['category'] == 'Milkshakes' ? 'selected' : '' ?>>Milkshakes</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Price (UGX)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required
                               value="<?= $editing_item ? htmlspecialchars($editing_item['price']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Allowed: JPG, JPEG, PNG, GIF, WEBP (Max 5MB)</small>
                        <?php if ($editing_item && !empty($editing_item['image'])): ?>
                            <div class="mt-2">
                                <small class="text-muted">Current image:</small><br>
                                <img src="<?= BASE_URL . ltrim(htmlspecialchars($editing_item['image']), '/') ?>" width="100" height="75" style="object-fit: cover; border-radius: 4px;" alt="Current image">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= $editing_item ? htmlspecialchars($editing_item['description']) : '' ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="<?= $editing_item ? 'edit_item' : 'add_item' ?>" class="btn btn-add-item">
                    <i class="fas fa-<?= $editing_item ? 'save' : 'plus' ?> me-2"></i><?= $editing_item ? 'Update Item' : 'Add Item' ?>
                </button>
                <?php if ($editing_item): ?>
                    <a href="menu.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel Edit
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Menu Items Table -->
<div class="items-table-container">
<table class="table items-table table-hover mb-0">
    <thead class="items-table-header">
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?= $item['id'] ?></td>
            <td>
                <?php if (!empty($item['image'])): ?>
                    <img src="<?= BASE_URL . ltrim(htmlspecialchars($item['image']), '/') ?>" 
                         width="80" height="60" 
                         style="object-fit: cover; border-radius: 4px;" 
                         alt="<?= htmlspecialchars($item['name']) ?>">
                <?php else: ?>
                    <span class="text-muted">No image</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= htmlspecialchars($item['category']) ?></td>
            <td>UGX <?= number_format($item['price'], 0) ?></td>
            <td>
                <div class="btn-group" role="group">
                    <a href="?edit=<?= $item['id'] ?>" class="btn btn-sm btn-edit-item" title="Edit Item">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="?delete=<?= $item['id'] ?>" 
                       class="btn btn-delete-item"
                       onclick="return confirm('Are you sure you want to delete this item?')"
                       title="Delete Item">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<!-- Add-ons Management -->
<div class="menu-management-header mt-5">
    <h1><i class="fas fa-wine-bottle me-2"></i>Manage Add-ons</h1>
</div>

<div class="card form-card mb-4">
    <div class="card-header">
        <h4><i class="fas fa-<?= $editing_addon ? 'edit' : 'plus-circle' ?> me-2"></i><?= $editing_addon ? 'Edit Add-on' : 'Add New Add-on' ?></h4>
    </div>
    <div class="card-body">
        <form method="POST">
            <?php if ($editing_addon): ?>
                <input type="hidden" name="addon_id" value="<?= $editing_addon['id'] ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-5">
                    <div class="mb-3">
                        <label class="form-label">Add-on Name</label>
                        <input type="text" name="addon_name" class="form-control" required
                               value="<?= $editing_addon ? htmlspecialchars($editing_addon['name']) : '' ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="addon_type" class="form-select" required>
                            <option value="accompaniment" <?= $editing_addon && $editing_addon['type'] === 'accompaniment' ? 'selected' : '' ?>>Accompaniment</option>
                            <option value="drink" <?= $editing_addon && $editing_addon['type'] === 'drink' ? 'selected' : '' ?>>Drink</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Price (UGX)</label>
                        <input type="number" step="0.01" name="addon_price" class="form-control" required
                               value="<?= $editing_addon ? htmlspecialchars($editing_addon['price']) : '' ?>">
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="<?= $editing_addon ? 'edit_addon' : 'add_addon' ?>" class="btn btn-add-item">
                    <i class="fas fa-<?= $editing_addon ? 'save' : 'plus' ?> me-2"></i><?= $editing_addon ? 'Update Add-on' : 'Add Add-on' ?>
                </button>
                <?php if ($editing_addon): ?>
                    <a href="menu.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel Edit
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="items-table-container">
<table class="table items-table table-hover mb-0">
    <thead class="items-table-header">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Type</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($addons as $addon): ?>
        <tr>
            <td><?= $addon['id'] ?></td>
            <td><?= htmlspecialchars($addon['name']) ?></td>
            <td><?= ucfirst($addon['type']) ?></td>
            <td>UGX <?= number_format($addon['price'], 0) ?></td>
            <td>
                <div class="btn-group" role="group">
                    <a href="?edit_addon=<?= $addon['id'] ?>" class="btn btn-sm btn-edit-item" title="Edit Add-on">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="?delete_addon=<?= $addon['id'] ?>" 
                       class="btn btn-delete-item"
                       onclick="return confirm('Are you sure you want to delete this add-on?')"
                       title="Delete Add-on">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php include '../includes/footer.php'; ?>