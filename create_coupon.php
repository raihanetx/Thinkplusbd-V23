<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$code = isset($input['code']) ? trim($input['code']) : '';
$discount_percentage = isset($input['discount_percentage']) ? (int)$input['discount_percentage'] : 0;
$product_ids = isset($input['product_ids']) ? $input['product_ids'] : null;
$category = isset($input['category']) ? trim($input['category']) : null;

if (!empty($code) && $discount_percentage > 0) {
    $coupons_file_path = __DIR__ . '/coupons.json';
    $coupons = [];
    if (file_exists($coupons_file_path)) {
        $coupons_json = file_get_contents($coupons_file_path);
        $coupons = json_decode($coupons_json, true);
    }

    $new_coupon = [
        'code' => $code,
        'discount_percentage' => $discount_percentage,
        'product_ids' => $product_ids,
        'category' => $category,
    ];

    $coupons[] = $new_coupon;
    $json_data = json_encode($coupons, JSON_PRETTY_PRINT);
    file_put_contents($coupons_file_path, $json_data);

    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false]);
?>
