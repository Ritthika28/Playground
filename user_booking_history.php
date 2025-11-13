<?php
require 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch all bookings for the logged-in user
    $bookings_query = "
        SELECT b.booking_id, b.booking_date, b.time_slot, b.total_cost, b.status, t.name AS turf_name
        FROM bookings b
        JOIN turfs t ON b.turf_id = t.turf_id
        WHERE b.user_id = :user_id
        ORDER BY b.booking_date DESC, b.booking_id DESC
    ";
    $stmt = $pdo->prepare($bookings_query);
    $stmt->execute([':user_id' => $user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Booking History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #ebffc9,white);
        }
        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }
        table {
            width: 100%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        table th, table td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #ddd;
        }
        table th {
            background: #6cbbd4;
            color: white;
            font-weight: bold;
        }
        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        table tbody tr:hover {
            background: #f1f5ff;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 16px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .container {
            margin: 65px auto;
            max-width: 90%;
        }
        .no-bookings {
            text-align: center;
            color: #555;
            margin-top: 30px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <?php include 'bookingnavbar.php'; ?>

    <div class="container">
        <h1>Booking History</h1>

        <?php if (count($bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Booking Date</th>
                        <th>Time Slot</th>
                        <th>Turf</th>
                        <th>Total Cost (₹)</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['time_slot']); ?></td>
                            <td><?php echo htmlspecialchars($booking['turf_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['total_cost']); ?></td>
                            <td><?php echo htmlspecialchars($booking['status']); ?></td>
                            <td>
                                <a class="btn btn-custom" href="confirmation.php?booking_id=<?php echo htmlspecialchars($booking['booking_id']); ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-bookings">No bookings found.</p>
        <?php endif; ?>

        <div class="text-center mt-4">
            <button onclick="window.location.href='book_turf.php';" class="btn btn-custom">Back to Turf List</button>
        </div>
    </div>

    <?php include 'bookingFooter.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
