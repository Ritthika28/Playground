<?php
// admin.php
require 'config.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
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

            <!-- Add Turf Section -->
            <div id="add-turf" class="container my-5 p-4 border rounded bg-light">
                <h2 class="text-center mb-4">Add Turf</h2>
                <form method="POST" action="process_turf.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="name">Turf Name:</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter turf name" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Location:</label>
                        <input type="text" id="location" name="location" class="form-control" placeholder="Enter location" required>
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacity:</label>
                        <input type="number" id="capacity" name="capacity" class="form-control" placeholder="Enter capacity" required>
                    </div>
                    <div class="form-group">
                        <label for="booking_cost">Booking Cost:</label>
                        <input type="number" id="booking_cost" name="booking_cost" class="form-control" placeholder="Enter booking cost" required>
                    </div>
                    <div class="form-group">
                        <label for="area">Area:</label>
                        <input type="text" id="area" name="area" class="form-control" placeholder="Enter area" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" class="form-control" placeholder="Enter address" required>
                    </div>
                    <div class="form-group">
                        <label for="services">Services:</label>
                        <input type="text" id="services" name="services" class="form-control" placeholder="Enter services">
                    </div>
                    <div class="form-group">
                        <label for="image">Turf Image:</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Enter description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="sports">Sports Available (comma-separated):</label>
                        <input type="text" id="sports" name="sports" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="sports_costs">Cost for Each Sport (comma-separated, in order):</label>
                        <input type="text" id="sports_costs" name="sports_costs" class="form-control" required>
                    </div>
                    <!-- Amenities -->
                        <label for="amenities">Amenities (comma-separated names):</label>
                        <input type="text" id="amenities" name="amenities" placeholder="Parking,Lighting,Water Facility" class="form-control" required>

                        <label for="amenities_prices">Amenity Prices (comma-separated prices):</label>
                        <input type="text" id="amenities_prices" name="amenities_prices" placeholder="50,100,30" class="form-control" required>

                    <div id="add-time-slots" class="mb-3">
                        <h5>Time Slots</h5>
                        <div class="time-slot d-flex align-items-center mb-2">
                            <label class="mr-2">Booking Date:</label>
                            <input type="date" name="time_slots[0][booking_date]" class="form-control mr-2" required>
                            <label class="mr-2">Start Time:</label>
                            <input type="time" name="time_slots[0][start_time]" class="form-control mr-2" required>
                            <label class="mr-2">End Time:</label>
                            <input type="time" name="time_slots[0][end_time]" class="form-control" required>
                        </div>
                    </div>
                    <button type="button" id="add-slot-button" class="btn btn-secondary">Add Another Time Slot</button>
                    <button type="submit" class="btn btn-primary">Add Turf</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="adminscript.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</div>
</main>
    <footer class="bg-dark text-white text-center py-3 mt-5">
        &copy; <?php echo date("Y"); ?> Turf Management System
    </footer>
</body>
</html>