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
            </div>
            <div class="form-group">
                <label for="discount-percentage">Discount Percentage</label>
                <input type="number" id="discount-percentage" required>
            </div>
            <div class="form-group">
                <label for="product-ids">Apply to Product IDs (comma-separated)</label>
                <input type="text" id="product-ids">
            </div>
            <div class="form-group">
                <label for="category">Apply to Category</label>
                <input type="text" id="category">
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
                    html += '<tr><th>Code</th><th>Discount</th><th>Product IDs</th><th>Category</th><th>Action</th></tr>';
                    data.forEach(coupon => {
                        html += `
                            <tr>
                                <td>${coupon.code}</td>
                                <td>${coupon.discount_percentage}%</td>
                                <td>${coupon.product_ids ? coupon.product_ids.join(', ') : 'All'}</td>
                                <td>${coupon.category || 'All'}</td>
                                <td>
                                    <button onclick="deleteCoupon('${coupon.code}')">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                    html += '</table>';
                    container.innerHTML = html;
                });

            document.getElementById('create-coupon-form').addEventListener('submit', function(event) {
                event.preventDefault();
                const code = document.getElementById('coupon-code').value;
                const discount_percentage = document.getElementById('discount-percentage').value;
                const product_ids = document.getElementById('product-ids').value.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
                const category = document.getElementById('category').value;

                fetch('create_coupon.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        code,
                        discount_percentage,
                        product_ids: product_ids.length > 0 ? product_ids : null,
                        category: category || null
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
