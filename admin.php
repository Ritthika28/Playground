<?php
// admin.php
require 'config.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'];
        $booking_id = $_POST['booking_id'];

        // Fetch booking details
        $stmt = $pdo->prepare("SELECT turf_id, time_slot, status FROM bookings WHERE booking_id = :booking_id");
        $stmt->execute([':booking_id' => $booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            throw new Exception("Invalid booking ID.");
        }

        if ($action === 'cancel' && $booking['status'] === 'Pending') {
            // Cancel booking
            $pdo->beginTransaction();

            $update_booking = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = :booking_id");
            $update_booking->execute([':booking_id' => $booking_id]);

            $update_slot = $pdo->prepare("
                UPDATE time_slots 
                SET status = 'Available' 
                WHERE turf_id = :turf_id AND CONCAT(start_time, ' - ', end_time) = :time_slot
            ");
            $update_slot->execute([
                ':turf_id' => $booking['turf_id'],
                ':time_slot' => $booking['time_slot']
            ]);

            $pdo->commit();
        } elseif ($action === 'approve' && $booking['status'] === 'Pending') {
            // Approve booking
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'Approved' WHERE booking_id = :booking_id");
            $stmt->execute([':booking_id' => $booking_id]);
        } elseif ($action === 'delete') {
            // Delete booking
            $pdo->beginTransaction();

            $delete_booking = $pdo->prepare("DELETE FROM bookings WHERE booking_id = :booking_id");
            $delete_booking->execute([':booking_id' => $booking_id]);

            $update_slot = $pdo->prepare("
                UPDATE time_slots 
                SET status = 'Available' 
                WHERE turf_id = :turf_id AND CONCAT(start_time, ' - ', end_time) = :time_slot
            ");
            $update_slot->execute([
                ':turf_id' => $booking['turf_id'],
                ':time_slot' => $booking['time_slot']
            ]);

            $pdo->commit();
        } else {
            throw new Exception("Invalid action or booking status.");
        }

        header('Location: admin.php');
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Fetch bookings
$bookings = [];
try {
    $stmt = $pdo->query("SELECT b.booking_id, u.username AS user_name, t.name AS turf_name, b.booking_date, b.time_slot, b.total_cost, b.status 
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN turfs t ON b.turf_id = t.turf_id");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error fetching bookings: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="adminstyle.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
    <body>
        <main>
        <div id="main">
    	<?php include 'adminnavbar.php'; ?>
    	<!-- Alerts -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Bookings Section -->
            <div id="bookings" class="container-fluid my-5 p-4 border rounded bg-light shadow-lg">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">Bookings</h2>
            </div>
            <div class="card-body">
                <?php if (empty($bookings)): ?>
                    <p class="text-center text-muted fs-5">No bookings available.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>User</th>
                                    <th>Turf</th>
                                    <th>Date</th>
                                    <th>Time Slot</th>
                                    <th>Total Cost</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                                        <td><?= htmlspecialchars($booking['user_name']) ?></td>
                                        <td><?= htmlspecialchars($booking['turf_name']) ?></td>
                                        <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                                        <td><?= htmlspecialchars($booking['time_slot']) ?></td>
                                        <td class="text-success">₹<?= htmlspecialchars($booking['total_cost']) ?></td>
                                        <td class="<?= $booking['status'] === 'Pending' ? 'text-warning' : ($booking['status'] === 'Approved' ? 'text-success' : 'text-danger') ?>">
                                            <?= htmlspecialchars($booking['status']) ?>
                                        </td>
                                        <td>
                                            <?php if ($booking['status'] === 'Pending'): ?>
                                                <form method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
                                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
                                                    <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
                                                <button type="submit" name="action" value="delete" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
			<script src="adminscript.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

			<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
		    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
		    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</div>
</main>
	<footer class="bg-dark text-white text-center py-3 ">
        &copy; <?php echo date("Y"); ?> Turf Management System
    </footer>

</body>
</html>