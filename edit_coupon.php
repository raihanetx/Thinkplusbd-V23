<?php
session_start();
// Dummy admin check
if (!isset($_SESSION['admin'])) {
    // header('Location: admin_login.php');
    // exit();
}

$coupon_code = isset($_GET['code']) ? $_GET['code'] : '';
if (empty($coupon_code)) {
    die('Coupon code is required.');
}

$coupons_file_path = __DIR__ . '/coupons.json';
$coupons = [];
if (file_exists($coupons_file_path)) {
    $coupons_json = file_get_contents($coupons_file_path);
    $coupons = json_decode($coupons_json, true);
}

$coupon_to_edit = null;
foreach ($coupons as $coupon) {
    if ($coupon['code'] === $coupon_code) {
        $coupon_to_edit = $coupon;
        break;
    }
}

if ($coupon_to_edit === null) {
    die('Coupon not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Coupon</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Edit Coupon</h1>
        <form id="edit-coupon-form">
            <input type="hidden" id="original-coupon-code" value="<?php echo htmlspecialchars($coupon_to_edit['code']); ?>">
            <div class="form-group">
                <label for="coupon-code">Coupon Code</label>
                <input type="text" id="coupon-code" value="<?php echo htmlspecialchars($coupon_to_edit['code']); ?>" required>
            </div>
            <div class="form-group">
                <label for="discount-type">Discount Type</label>
                <select id="discount-type" required>
                    <option value="percentage" <?php echo $coupon_to_edit['discount_type'] === 'percentage' ? 'selected' : ''; ?>>Percentage</option>
                    <option value="fixed" <?php echo $coupon_to_edit['discount_type'] === 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
                </select>
            </div>
            <div class="form-group">
                <label for="discount-value">Discount Value</label>
                <input type="number" id="discount-value" value="<?php echo htmlspecialchars($coupon_to_edit['discount_value']); ?>" required>
            </div>
            <div class="form-group">
                <label for="start-date">Start Date</label>
                <input type="date" id="start-date" value="<?php echo htmlspecialchars($coupon_to_edit['start_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="end-date">End Date</label>
                <input type="date" id="end-date" value="<?php echo htmlspecialchars($coupon_to_edit['end_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="applicable-to">Applicable To</label>
                <select id="applicable-to" required>
                    <option value="entire-order" <?php echo $coupon_to_edit['applicable_to'] === 'entire-order' ? 'selected' : ''; ?>>Entire Order</option>
                    <option value="specific-product" <?php echo $coupon_to_edit['applicable_to'] === 'specific-product' ? 'selected' : ''; ?>>Specific Product(s)</option>
                    <option value="specific-category" <?php echo $coupon_to_edit['applicable_to'] === 'specific-category' ? 'selected' : ''; ?>>Specific Category</option>
                </select>
            </div>
            <div class="form-group" id="specific-product-container" style="display: <?php echo $coupon_to_edit['applicable_to'] === 'specific-product' ? 'block' : 'none'; ?>;">
                <label for="product-ids">Product IDs</label>
                <div id="product-checkboxes"></div>
            </div>
            <div class="form-group" id="specific-category-container" style="display: <?php echo $coupon_to_edit['applicable_to'] === 'specific-category' ? 'block' : 'none'; ?>;">
                <label for="category">Category</label>
                <div id="category-checkboxes"></div>
            </div>
            <div class="form-group">
                <label for="usage-limit">Usage Limit (per user)</label>
                <input type="number" id="usage-limit" value="<?php echo htmlspecialchars($coupon_to_edit['usage_limit']); ?>" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" required>
                    <option value="active" <?php echo $coupon_to_edit['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $coupon_to_edit['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit">Update Coupon</button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const coupon = <?php echo json_encode($coupon_to_edit); ?>;

            // Fetch products for the product selection
            fetch('get_products.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('product-checkboxes');
                    if (data.error) {
                        container.innerHTML = `<p>${data.error}</p>`;
                        return;
                    }
                    let html = '';
                    data.forEach(product => {
                        const isChecked = coupon.product_ids && coupon.product_ids.includes(product.id);
                        html += `
                            <label>
                                <input type="checkbox" name="product_ids" value="${product.id}" ${isChecked ? 'checked' : ''}>
                                ${product.name}
                            </label>
                        `;
                    });
                    container.innerHTML = html;
                });

            // Fetch categories for the category selection
            fetch('get_categories.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('category-checkboxes');
                    if (data.error) {
                        container.innerHTML = `<p>${data.error}</p>`;
                        return;
                    }
                    let html = '';
                    data.forEach(category => {
                        const isChecked = coupon.category && coupon.category.includes(category.name);
                        html += `
                            <label>
                                <input type="checkbox" name="category" value="${category.name}" ${isChecked ? 'checked' : ''}>
                                ${category.name}
                            </label>
                        `;
                    });
                    container.innerHTML = html;
                });
        });

        document.getElementById('applicable-to').addEventListener('change', function() {
            const specificProductContainer = document.getElementById('specific-product-container');
            const specificCategoryContainer = document.getElementById('specific-category-container');
            if (this.value === 'specific-product') {
                specificProductContainer.style.display = 'block';
                specificCategoryContainer.style.display = 'none';
            } else if (this.value === 'specific-category') {
                specificProductContainer.style.display = 'none';
                specificCategoryContainer.style.display = 'block';
            } else {
                specificProductContainer.style.display = 'none';
                specificCategoryContainer.style.display = 'none';
            }
        });

        document.getElementById('edit-coupon-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const original_code = document.getElementById('original-coupon-code').value;
            const code = document.getElementById('coupon-code').value;
            const discount_type = document.getElementById('discount-type').value;
            const discount_value = document.getElementById('discount-value').value;
            const start_date = document.getElementById('start-date').value;
            const end_date = document.getElementById('end-date').value;
            const applicable_to = document.getElementById('applicable-to').value;
            const product_ids = Array.from(document.querySelectorAll('input[name="product_ids"]:checked')).map(el => el.value);
            const category = Array.from(document.querySelectorAll('input[name="category"]:checked')).map(el => el.value);
            const usage_limit = document.getElementById('usage-limit').value;
            const status = document.getElementById('status').value;

            fetch('update_coupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    original_code,
                    code,
                    discount_type,
                    discount_value,
                    start_date,
                    end_date,
                    applicable_to,
                    product_ids: product_ids.length > 0 ? product_ids : null,
                    category: category.length > 0 ? category : null,
                    usage_limit,
                    status
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'admin_coupons.php';
                } else {
                    alert('Failed to update coupon.');
                }
            });
        });
    </script>
</body>
</html>
