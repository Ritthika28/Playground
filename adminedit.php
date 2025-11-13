<?php
// admin.php
require 'config.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch turfs for editing
$turfs = [];
try {
    $turfsStmt = $pdo->query("SELECT * FROM turfs");
    $turfs = $turfsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error fetching turfs: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Handle editing a specific turf
$turf_to_edit = null;
$time_slots = [];
if (isset($_GET['edit_turf_id'])) {
    $turf_id = (int)$_GET['edit_turf_id'];
    $stmt = $pdo->prepare("SELECT * FROM turfs WHERE turf_id = :turf_id");
    $stmt->execute(['turf_id' => $turf_id]);
    $turf_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch sports and their costs
    $sports_cost_stmt = $pdo->prepare("SELECT sport_name, cost FROM sports_cost WHERE turf_id = :turf_id");
    $sports_cost_stmt->execute(['turf_id' => $turf_id]);
    $sports_costs = $sports_cost_stmt->fetchAll(PDO::FETCH_ASSOC);
    // Combine sports and costs into a structured format
    $sports = [];
    $costs = [];
    foreach ($sports_costs as $sport) {
        $sports[] = $sport['sport_name'];
        $costs[] = $sport['cost'];
    }
    // Convert arrays to comma-separated strings for editing
    $turf_to_edit['sports'] = implode(',', $sports);
    $turf_to_edit['sports_costs'] = implode(',', $costs);

    // Fetch amenities for the turf
    $amenitiesStmt = $pdo->prepare("SELECT name, price FROM amenities WHERE turf_id = :turf_id");
    $amenitiesStmt->execute(['turf_id' => $turf_id]);
    $amenities = $amenitiesStmt->fetchAll(PDO::FETCH_ASSOC);
    // Combine amenities and their prices into a structured format
    $amenity_names = [];
    $amenity_prices = [];
    foreach ($amenities as $amenity) {
        $amenity_names[] = $amenity['name'];
        $amenity_prices[] = $amenity['price'];
    }
    // Convert arrays to comma-separated strings for editing
    $turf_to_edit['amenities'] = implode(',', $amenity_names);
    $turf_to_edit['amenities_prices'] = implode(',', $amenity_prices);

    // Fetch existing time slots for the turf
    $timeSlotsStmt = $pdo->prepare("SELECT * FROM time_slots WHERE turf_id = :turf_id");
    $timeSlotsStmt->execute(['turf_id' => $turf_id]);
    $time_slots = $timeSlotsStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
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
    <div class="content">
        <div class="container">
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

            <!-- Edit Turf Section -->
<div id="edit-turf" class="container my-5 p-4 border rounded bg-light">
    <h2 class="text-center mb-4">Edit Turf</h2>
    <?php if ($turf_to_edit): ?>
        <form method="POST" action="process_turf.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="turf_id" value="<?= $turf_to_edit['turf_id'] ?>">
            <div class="form-group">
                <label for="name">Turf Name:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($turf_to_edit['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" class="form-control" value="<?= htmlspecialchars($turf_to_edit['location']) ?>" required>
            </div>
            <div class="form-group">
                <label for="capacity">Capacity:</label>
                <input type="number" id="capacity" name="capacity" class="form-control" value="<?= htmlspecialchars($turf_to_edit['capacity']) ?>" required>
            </div>
            <div class="form-group">
                <label for="booking_cost">Booking Cost:</label>
                <input type="number" id="booking_cost" name="booking_cost" class="form-control" value="<?= htmlspecialchars($turf_to_edit['booking_cost']) ?>" required>
            </div>
            <div class="form-group">
                <label for="area">Area:</label>
                <input type="text" id="area" name="area" class="form-control" value="<?= htmlspecialchars($turf_to_edit['area']) ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($turf_to_edit['address']) ?>" required>
            </div>
            <div class="form-group">
                <label for="services">Services:</label>
                <input type="text" id="services" name="services" class="form-control" value="<?= htmlspecialchars($turf_to_edit['services']) ?>">
            </div>
            <div class="form-group">
                <label for="image">Turf Image:(If not inserted,Default image will be placed)</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($turf_to_edit['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="sports">Sports Available (comma-separated):</label>
                <input type="text" id="sports" name="sports" class="form-control" value="<?= htmlspecialchars($turf_to_edit['sports']) ?>">
            </div>
            <div class="form-group">
                <label for="sports_costs">Cost for Each Sport (comma-separated, in order):</label>
                <input type="text" id="sports_costs" name="sports_costs" class="form-control" value="<?= htmlspecialchars($turf_to_edit['sports_costs']) ?>">
            </div>
            <!-- Amenities -->
            <div class="form-group">
                <label for="amenities">Amenities (comma-separated names):</label>
                <input type="text" id="amenities" name="amenities" value="<?= htmlspecialchars($turf_to_edit['amenities'] ?? '') ?>" placeholder="Parking,Lighting,Water Facility">
            </div>
            <div class="form-group">
                <label for="amenities_prices">Amenity Prices (comma-separated prices):</label>
                <input type="text" id="amenities_prices" name="amenities_prices" value="<?= htmlspecialchars($turf_to_edit['amenities_prices'] ?? '') ?>" placeholder="50,100,30">
            </div>

            <div id="edit-time-slots" class="mb-3">
                <h5>Time Slots</h5>
                <?php foreach ($time_slots as $index => $slot): ?>
                    <div class="time-slot d-flex align-items-center mb-2">
                        <label class="mr-2">Booking Date:</label>
                        <input type="date" name="time_slots[<?= $index ?>][booking_date]" class="form-control mr-2" value="<?= htmlspecialchars($slot['booking_date']) ?>" required>
                        <label class="mr-2">Start Time:</label>
                        <input type="time" name="time_slots[<?= $index ?>][start_time]" class="form-control mr-2" value="<?= htmlspecialchars($slot['start_time']) ?>" required>
                        <label class="mr-2">End Time:</label>
                        <input type="time" name="time_slots[<?= $index ?>][end_time]" class="form-control mr-2" value="<?= htmlspecialchars($slot['end_time']) ?>" required>
                        <input type="hidden" name="time_slots[<?= $index ?>][slot_id]" value="<?= $slot['slot_id'] ?>">
                        <button type="button" class="btn btn-danger remove-slot-button" data-slot-id="<?= $slot['slot_id'] ?>">Remove</button>
                        <input type="hidden" name="remove_slots[]" id="remove_slot_<?= $slot['slot_id'] ?>" value="">
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="edit-slot-button" class="btn btn-secondary">Add Another Time Slot</button>
            <button type="submit" class="btn btn-primary">Update Turf</button>
            <a href="adminedit.php" class="btn btn-danger">Cancel Edit</a>

        </form>
    <?php else: ?>
        <p>Select a turf to edit:</p>
        <ul>
            <?php foreach ($turfs as $turf): ?>
                <li><a href="?edit_turf_id=<?= $turf['turf_id'] ?>"><?= htmlspecialchars($turf['name']) ?> (<?= htmlspecialchars($turf['location']) ?>)</a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>


        </div>
    </div>
</div>
<script src="adminscript.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    //<--!script for remove button near timeslot--!>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.remove-slot-button').forEach(button => {
        button.addEventListener('click', function () {
            const slotId = this.getAttribute('data-slot-id');
            document.getElementById('remove_slot_' + slotId).value = slotId;
            this.closest('.time-slot').remove();
            alert('Time slot removed successfully.');
        });
    });
});
</script>
</div>
</main>
    <footer class="bg-dark text-white text-center py-3 ">
        &copy; <?php echo date("Y"); ?> Turf Management System
    </footer>
</body>
</html>