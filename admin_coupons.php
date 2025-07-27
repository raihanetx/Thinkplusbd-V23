<?php
session_start();
// Dummy admin check
if (!isset($_SESSION['admin'])) {
    // header('Location: admin_login.php');
    // exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Coupons</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Manage Coupons</h1>
        <div id="coupons-container"></div>
        <h2>Create Coupon</h2>
        <form id="create-coupon-form">
            <div class="form-group">
                <label for="coupon-code">Coupon Code</label>
                <input type="text" id="coupon-code" required>
                <button type="button" id="generate-code">Generate Random Code</button>
            </div>
            <div class="form-group">
                <label for="discount-type">Discount Type</label>
                <select id="discount-type" required>
                    <option value="percentage">Percentage</option>
                    <option value="fixed">Fixed Amount</option>
                </select>
            </div>
            <div class="form-group">
                <label for="discount-value">Discount Value</label>
                <input type="number" id="discount-value" required>
            </div>
            <div class="form-group">
                <label for="start-date">Start Date</label>
                <input type="date" id="start-date" required>
            </div>
            <div class="form-group">
                <label for="end-date">End Date</label>
                <input type="date" id="end-date" required>
            </div>
            <div class="form-group">
                <label for="applicable-to">Applicable To</label>
                <select id="applicable-to" required>
                    <option value="entire-order">Entire Order</option>
                    <option value="specific-product">Specific Product(s)</option>
                    <option value="specific-category">Specific Category</option>
                </select>
            </div>
            <div class="form-group" id="specific-product-container" style="display: none;">
                <label for="product-ids">Product IDs (comma-separated)</label>
                <input type="text" id="product-ids">
            </div>
            <div class="form-group" id="specific-category-container" style="display: none;">
                <label for="category">Category</label>
                <input type="text" id="category">
            </div>
            <div class="form-group">
                <label for="usage-limit">Usage Limit (per user)</label>
                <input type="number" id="usage-limit" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit">Create Coupon</button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('get_coupons.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('coupons-container');
                    if (data.error) {
                        container.innerHTML = `<p>${data.error}</p>`;
                        return;
                    }
                    if (data.length === 0) {
                        container.innerHTML = '<p>No coupons to display.</p>';
                        return;
                    }
                    let html = '<table>';
                    html += '<tr><th>Code</th><th>Discount</th><th>Validity</th><th>Applicable To</th><th>Usage Limit</th><th>Status</th><th>Action</th></tr>';
                    data.forEach(coupon => {
                        html += `
                            <tr>
                                <td>${coupon.code}</td>
                                <td>${coupon.discount_type === 'percentage' ? coupon.discount_value + '%' : 'BDT ' + coupon.discount_value}</td>
                                <td>${coupon.start_date} to ${coupon.end_date}</td>
                                <td>${coupon.applicable_to.replace('-', ' ')}</td>
                                <td>${coupon.usage_limit}</td>
                                <td>${coupon.status}</td>
                                <td>
                                    <button onclick="deleteCoupon('${coupon.code}')">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                    html += '</table>';
                    container.innerHTML = html;
                });

            document.getElementById('generate-code').addEventListener('click', function() {
                const randomCode = Math.random().toString(36).substring(2, 10).toUpperCase();
                document.getElementById('coupon-code').value = randomCode;
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

            document.getElementById('create-coupon-form').addEventListener('submit', function(event) {
                event.preventDefault();
                const code = document.getElementById('coupon-code').value;
                const discount_type = document.getElementById('discount-type').value;
                const discount_value = document.getElementById('discount-value').value;
                const start_date = document.getElementById('start-date').value;
                const end_date = document.getElementById('end-date').value;
                const applicable_to = document.getElementById('applicable-to').value;
                const product_ids = document.getElementById('product-ids').value.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
                const category = document.getElementById('category').value;
                const usage_limit = document.getElementById('usage-limit').value;
                const status = document.getElementById('status').value;

                fetch('create_coupon.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        code,
                        discount_type,
                        discount_value,
                        start_date,
                        end_date,
                        applicable_to,
                        product_ids: product_ids.length > 0 ? product_ids : null,
                        category: category || null,
                        usage_limit,
                        status
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to create coupon.');
                    }
                });
            });
        });

        function deleteCoupon(couponCode) {
            fetch('delete_coupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ code: couponCode }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete coupon.');
                }
            });
        }
    </script>
</body>
</html>
