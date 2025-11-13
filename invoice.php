<?php
// invoice.php
require 'config.php';

if (!isset($_GET['booking_id'])) {
    die("Booking ID is required.");
}

$booking_id = (int)$_GET['booking_id'];
$stmt = $conn->prepare("
    SELECT b.booking_id, u.name AS user_name, t.name AS turf_name, s.name AS sport_name, b.booking_date, b.time_slot, b.total_cost 
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN turfs t ON b.turf_id = t.turf_id
    JOIN sports s ON b.sport_id = s.sport_id
    WHERE b.booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    die("Booking not found.");
}

// Fetch amenities
$amenities = $conn->query("SELECT a.name, ba.quantity, a.price FROM booking_amenities ba JOIN amenities a ON ba.amenity_id = a.amenity_id WHERE ba.booking_id = $booking_id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
</head>
<body>
    <h1>Invoice</h1>
    <p><strong>Booking ID:</strong> <?= $booking['booking_id'] ?></p>
    <p><strong>User:</strong> <?= $booking['user_name'] ?></p>
    <p><strong>Turf:</strong> <?= $booking['turf_name'] ?></p>
    <p><strong>Sport:</strong> <?= $booking['sport_name'] ?></p>
    <p><strong>Date:</strong> <?= $booking['booking_date'] ?></p>
    <p><strong>Time Slot:</strong> <?= $booking['time_slot'] ?></p>

    <h3>Amenities:</h3>
    <ul>
        <?php foreach ($amenities as $amenity): ?>
            <li><?= $amenity['name'] ?> x <?= $amenity['quantity'] ?> (₹<?= $amenity['price'] ?> each)</li>
        <?php endforeach; ?>
    </ul>

    <h3>Total Cost: ₹<?= $booking['total_cost'] ?></h3>
</body>
</html>
