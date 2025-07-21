<?php
session_start();
if (!isset($_SESSION['booking_data'])) {
    header('Location: room.php');
    exit();
}
$booking = $_SESSION['booking_data'];
$amount = $booking['total_price'];
$tax_amount = 0;
$service_charge = 0;
$delivery_charge = 0;
$total_amount = $amount + $tax_amount + $service_charge + $delivery_charge;
$booking_id = $booking['booking_id'];
$product_code = 'EPAYTEST';
$transaction_uuid = 'BOOKING-' . $booking_id . '-' . time();
$success_url = 'http://localhost/ProjectHRS/esewa_callback.php?q=su';
$failure_url = 'http://localhost/ProjectHRS/esewa_callback.php?q=fu';
$signed_field_names = 'total_amount,transaction_uuid,product_code';
$secret = '8gBm/:&EnhH.1/q';

function generateEsewaSignature($total_amount, $transaction_uuid, $product_code, $secret) {
    $data = "total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=$product_code";
    return base64_encode(hash_hmac('sha256', $data, $secret, true));
}
$signature = generateEsewaSignature($total_amount, $transaction_uuid, $product_code, $secret);

// Debug output
echo "<pre>";
echo "String to sign: total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=$product_code\n";
echo "Signature: $signature\n";
echo "Form values:\n";
echo "total_amount: $total_amount\n";
echo "transaction_uuid: $transaction_uuid\n";
echo "product_code: $product_code\n";
echo "</pre>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>eSewa Payment</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; }
        .container { max-width: 400px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem; }
        h2 { color: #2C3333; margin-bottom: 1rem; }
        .details { margin-bottom: 1.5rem; }
        .details div { margin-bottom: 0.5rem; }
        .label { color: #395B64; font-weight: bold; }
        .value { color: #222; }
        .pay-btn { background: #4cae4c; color: #fff; border: none; padding: 0.8rem 2rem; border-radius: 5px; font-size: 1.1rem; cursor: pointer; transition: background 0.2s; }
        .pay-btn:hover { background: #388e3c; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Booking Payment Details</h2>
        <div class="details">
            <div><span class="label">Booking ID:</span> <span class="value">#<?php echo $booking_id; ?></span></div>
            <div><span class="label">Room Type:</span> <span class="value"><?php echo htmlspecialchars($booking['room_type']); ?></span></div>
            <div><span class="label">Check-in:</span> <span class="value"><?php echo htmlspecialchars($booking['check_in']); ?></span></div>
            <div><span class="label">Check-out:</span> <span class="value"><?php echo htmlspecialchars($booking['check_out']); ?></span></div>
            <div><span class="label">Duration:</span> <span class="value"><?php echo $booking['days']; ?> days</span></div>
            <div><span class="label">Guests:</span> <span class="value"><?php echo $booking['persons']; ?></span></div>
            <div style="font-size:1.2rem; margin-top:1rem;"><span class="label">Total Amount:</span> <span class="value">Rs. <?php echo number_format($total_amount, 2); ?></span></div>
        </div>
        <form id="esewaPay" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="tax_amount" value="<?php echo $tax_amount; ?>">
            <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
            <input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>">
            <input type="hidden" name="product_code" value="<?php echo $product_code; ?>">
            <input type="hidden" name="product_service_charge" value="<?php echo $service_charge; ?>">
            <input type="hidden" name="product_delivery_charge" value="<?php echo $delivery_charge; ?>">
            <input type="hidden" name="success_url" value="<?php echo $success_url; ?>">
            <input type="hidden" name="failure_url" value="<?php echo $failure_url; ?>">
            <input type="hidden" name="signed_field_names" value="<?php echo $signed_field_names; ?>">
            <input type="hidden" name="signature" value="<?php echo $signature; ?>">
            <button type="submit" class="pay-btn">Pay with eSewa</button>
        </form>
    </div>
</body>
</html> 