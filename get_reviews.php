<?php
header('Content-Type: application/json');
$reviews_file_path = __DIR__ . '/reviews.json';
if (file_exists($reviews_file_path)) {
    $reviews_json = file_get_contents($reviews_file_path);
    $reviews = json_decode($reviews_json, true);
    // Add an id to each review for easier handling on the frontend
    foreach ($reviews as $i => &$review) {
        $review['id'] = $i;
    }
    echo json_encode($reviews);
} else {
    echo json_encode([]);
}
?>
