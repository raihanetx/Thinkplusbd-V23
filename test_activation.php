<?php
// Test script for coupon activation

// 1. Create a test coupon
$coupons_file_path = __DIR__ . '/coupons.json';
$coupons = [];
if (file_exists($coupons_file_path)) {
    $coupons_json = file_get_contents($coupons_file_path);
    $coupons = json_decode($coupons_json, true);
}

$test_coupon_code = 'TEST_COUPON';
$test_coupon = [
    'code' => $test_coupon_code,
    'discount_type' => 'fixed',
    'discount_value' => 100,
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31',
    'applicable_to' => 'entire-order',
    'product_ids' => null,
    'category' => null,
    'usage_limit' => 1,
    'status' => 'inactive'
];

// Add the test coupon to the list
$coupons[] = $test_coupon;
file_put_contents($coupons_file_path, json_encode($coupons, JSON_PRETTY_PRINT));

// 2. Simulate a call to activate_coupon.php using cURL
$ch = curl_init('http://localhost:8000/activate_coupon.php');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['code' => $test_coupon_code]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$result = curl_exec($ch);
curl_close($ch);

// 3. Verify the coupon's status
$coupons_json = file_get_contents($coupons_file_path);
$coupons = json_decode($coupons_json, true);

$activated = false;
foreach ($coupons as $coupon) {
    if ($coupon['code'] === $test_coupon_code) {
        if ($coupon['status'] === 'active') {
            $activated = true;
        }
        break;
    }
}

// Clean up: remove the test coupon
$coupons = array_filter($coupons, function($coupon) use ($test_coupon_code) {
    return $coupon['code'] !== $test_coupon_code;
});
file_put_contents($coupons_file_path, json_encode($coupons, JSON_PRETTY_PRINT));

// Output the result
if ($activated) {
    echo "Test passed: Coupon activated successfully.";
} else {
    echo "Test failed: Coupon not activated.";
    echo "cURL result: " . $result;
}
?>
