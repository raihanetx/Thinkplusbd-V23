<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$code = isset($input['code']) ? trim($input['code']) : '';
$discount_type = isset($input['discount_type']) ? trim($input['discount_type']) : '';
$discount_value = isset($input['discount_value']) ? (float)$input['discount_value'] : 0;
$start_date = isset($input['start_date']) ? trim($input['start_date']) : '';
$end_date = isset($input['end_date']) ? trim($input['end_date']) : '';
$applicable_to = isset($input['applicable_to']) ? trim($input['applicable_to']) : '';
$product_ids = isset($input['product_ids']) ? $input['product_ids'] : null;
$category = isset($input['category']) ? trim($input['category']) : null;
$usage_limit = isset($input['usage_limit']) ? (int)$input['usage_limit'] : 0;
$status = isset($input['status']) ? trim($input['status']) : '';

if (!empty($code) && !empty($discount_type) && $discount_value > 0 && !empty($start_date) && !empty($end_date) && !empty($applicable_to) && $usage_limit > 0) {
    $coupons_file_path = __DIR__ . '/coupons.json';
    $coupons = [];
    if (file_exists($coupons_file_path)) {
        $coupons_json = file_get_contents($coupons_file_path);
        $coupons = json_decode($coupons_json, true);
    }

    $new_coupon = [
        'code' => $code,
        'discount_type' => $discount_type,
        'discount_value' => $discount_value,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'applicable_to' => $applicable_to,
        'product_ids' => $product_ids,
        'category' => $category,
        'usage_limit' => $usage_limit,
        'status' => $status,
    ];

    $coupons[] = $new_coupon;
    $json_data = json_encode($coupons, JSON_PRETTY_PRINT);
    file_put_contents($coupons_file_path, $json_data);

    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false]);
?>
