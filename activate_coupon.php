<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$coupon_code = isset($input['code']) ? trim($input['code']) : '';

if (!empty($coupon_code)) {
    $coupons_file_path = __DIR__ . '/coupons.json';
    $coupons = [];
    if (file_exists($coupons_file_path)) {
        $coupons_json = file_get_contents($coupons_file_path);
        $coupons = json_decode($coupons_json, true);
    }

    $coupon_activated = false;
    foreach ($coupons as &$coupon) {
        if ($coupon['code'] === $coupon_code) {
            $coupon['status'] = 'active';
            $coupon_activated = true;
            break;
        }
    }

    if ($coupon_activated) {
        $json_data = json_encode($coupons, JSON_PRETTY_PRINT);
        file_put_contents($coupons_file_path, $json_data);
        echo json_encode(['success' => true]);
        exit();
    }
}

echo json_encode(['success' => false]);
?>
