<?php
require 'config.php';
session_start();

if (!isset($_GET['booking_id'])) {
    header('Location: book_turf.php');
    exit;
}

$booking_id = $_GET['booking_id'];

try {
    // Fetch booking details
    $booking_query = "
        SELECT b.*, u.username, t.name AS turf_name
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN turfs t ON b.turf_id = t.turf_id
        WHERE b.booking_id = :booking_id
    ";
    $stmt = $pdo->prepare($booking_query);
    $stmt->execute([':booking_id' => $booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception("Booking not found.");
    }

    // Fetch booked sport
    $sport_query = "
        SELECT sc.sport_name, sc.cost
        FROM sports_cost sc
        JOIN bookings b ON sc.sport_id = b.sport_id AND sc.turf_id = b.turf_id
        WHERE b.booking_id = :booking_id
    ";
    $stmt = $pdo->prepare($sport_query);
    $stmt->execute([':booking_id' => $booking_id]);
    $booked_sport = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch selected amenities
    $amenities_query = "
        SELECT a.name, a.price
        FROM booking_amenities ba
        JOIN amenities a ON ba.amenity_id = a.amenity_id
        WHERE ba.booking_id = :booking_id
    ";
    $stmt = $pdo->prepare($amenities_query);
    $stmt->execute([':booking_id' => $booking_id]);
    $amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch time slot details (if needed)
    $time_slot_query = "
        SELECT CONCAT(ts.start_time, ' - ', ts.end_time) AS time_slot, ts.booking_date
        FROM time_slots ts
        JOIN bookings b ON ts.turf_id = b.turf_id AND ts.booking_date = b.booking_date
        WHERE b.booking_id = :booking_id
    ";
    $stmt = $pdo->prepare($time_slot_query);
    $stmt->execute([':booking_id' => $booking_id]);
    $time_slot_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($time_slot_details) {
        $booking['time_slot'] = $time_slot_details['time_slot'];
        $booking['booking_date'] = $time_slot_details['booking_date'];
    }
} catch (Exception $e) {
    echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to bottom right, #ebffc9,white);
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h1 {
            color: #007bff;
            margin-bottom: 20px;
            text-align: center;
        }
        .details p {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .details strong {
            color: #333;
        }
        .amenities-list {
            margin-top: 20px;
            padding-left: 20px;
        }
        .amenities-list li {
            margin-bottom: 5px;
        }
        .btn-container {
            margin-top: 30px;
            text-align: center;
        }
        .btn {
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Booking Confirmation</h1>
        <div class="details">
            <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['booking_id']); ?></p>
            <p><strong>User:</strong> <?php echo htmlspecialchars($booking['username']); ?></p>
            <p><strong>Turf:</strong> <?php echo htmlspecialchars($booking['turf_name']); ?></p>
            <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
            <p><strong>Time Slot:</strong> <?php echo htmlspecialchars($booking['time_slot']); ?></p>
            <p><strong>Total Cost:</strong> ₹<?php echo htmlspecialchars($booking['total_cost']); ?></p>
            <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($booking['status']); ?></p>
        </div>

        <h2 class="mt-4">Booked Sport</h2>
        <?php if ($booked_sport): ?>
            <p><strong><?php echo htmlspecialchars($booked_sport['sport_name']); ?></strong> - ₹<?php echo htmlspecialchars($booked_sport['cost']); ?></p>
        <?php else: ?>
            <p>No sport selected.</p>
        <?php endif; ?>
        
        <h2 class="mt-4">Selected Amenities</h2>
        <?php if (count($amenities) > 0): ?>
            <ul class="amenities-list">
                <?php foreach ($amenities as $amenity): ?>
                    <li><?php echo htmlspecialchars($amenity['name']); ?> - ₹<?php echo htmlspecialchars($amenity['price']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No amenities selected.</p>
        <?php endif; ?>

        <div class="btn-container">
    <?php if ($booking['status'] === 'Paid' || $booking['status'] === 'Approved'): ?>
        <form method="POST" action="generate_invoice.php" style="display: inline;">
            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
            <button type="submit" class="btn btn-primary">Download Invoice</button>
        </form>
    <?php elseif ($booking['status'] === 'Cancelled'): ?>
        <p class="text-danger">Your booking has been cancelled.</p>
    <?php else: ?>
        <p class="text-danger">Please wait for the booking to be approved before downloading the invoice.</p>
    <?php endif; ?>

    <button onclick="window.location.href='booking.php';" class="btn btn-secondary">Back to Turf List</button>
    <button onclick="window.location.href='user_booking_history.php';" class="btn btn-success">Go to Bookings History</button>
</div>

    </div>
</body>
</html>
