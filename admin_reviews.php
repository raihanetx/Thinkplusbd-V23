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
    <title>Admin - Manage Reviews</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Manage Reviews</h1>
        <div id="reviews-container"></div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('get_reviews.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('reviews-container');
                    if (data.error) {
                        container.innerHTML = `<p>${data.error}</p>`;
                        return;
                    }
                    if (data.length === 0) {
                        container.innerHTML = '<p>No reviews to display.</p>';
                        return;
                    }
                    let html = '<table>';
                    html += '<tr><th>Product ID</th><th>Name</th><th>Rating</th><th>Comment</th><th>Timestamp</th><th>Status</th><th>Action</th></tr>';
                    data.forEach(review => {
                        html += `
                            <tr>
                                <td>${review.product_id}</td>
                                <td>${review.name}</td>
                                <td>${review.rating}</td>
                                <td>${review.comment}</td>
                                <td>${review.timestamp}</td>
                                <td>${review.status}</td>
                                <td>
                                    <button onclick="approveReview(${review.id})">Approve</button>
                                    <button onclick="deleteReview(${review.id})">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                    html += '</table>';
                    container.innerHTML = html;
                });
        });

        function approveReview(reviewId) {
            fetch('approve_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: reviewId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to approve review.');
                }
            });
        }

        function deleteReview(reviewId) {
            fetch('delete_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: reviewId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete review.');
                }
            });
        }
    </script>
</body>
</html>
