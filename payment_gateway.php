<?php
require 'config.php';
session_start();

if (!isset($_SESSION['booking_data'])) {
    header('Location: book_turf.php');
    exit;
}

$booking_data = $_SESSION['booking_data'];

try {
    // Simulate payment process
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payment_status = $_POST['payment_status'];

        if ($payment_status === 'success') {
            // Process booking
            $pdo->beginTransaction();

            // Insert booking into the database
            $insert_booking_query = "
                INSERT INTO bookings (user_id, turf_id, sport_id, booking_date, time_slot, total_cost, status)
                VALUES (:user_id, :turf_id, :sport_id, CURDATE(), :time_slot, :total_cost, 'Paid')
            ";
            $stmt = $pdo->prepare($insert_booking_query);
            $stmt->execute([
                ':user_id' => $booking_data['user_id'],
                ':turf_id' => $booking_data['turf_id'],
                ':sport_id' => $booking_data['sport_id'],
                ':time_slot' => $booking_data['time_slot'],
                ':total_cost' => $booking_data['total_cost']
            ]);
            $booking_id = $pdo->lastInsertId();

            // Insert selected amenities into booking_amenities table
            foreach ($booking_data['amenities'] as $amenity_id) {
                $insert_amenities_query = "INSERT INTO booking_amenities (booking_id, amenity_id) VALUES (:booking_id, :amenity_id)";
                $stmt = $pdo->prepare($insert_amenities_query);
                $stmt->execute([
                    ':booking_id' => $booking_id,
                    ':amenity_id' => $amenity_id
                ]);
            }

            $pdo->commit();

            // Clear booking session data
            unset($_SESSION['booking_data']);

            // Redirect to confirmation page
            header('Location: confirmation.php?booking_id=' . $booking_id);
            exit;
        } else {
            echo "<script>alert('Payment failed! Please try again.');</script>";
        }
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway</title>
</head>
<body>
    <h1>Payment Gateway</h1>
    <p>Total Amount: ₹<?php echo htmlspecialchars($booking_data['total_cost']); ?></p>
    <form method="POST">
        <button type="submit" name="payment_status" value="success">Simulate Successful Payment</button>
        <button type="submit" name="payment_status" value="failure">Simulate Failed Payment</button>
    </form>
</body>
</html>
