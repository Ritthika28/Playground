<?php
require 'config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$turf_id = $_GET['turf_id'] ?? null;
if (!$turf_id) {
    header("Location: booking.php");
    exit;
}

// Fetch turf details
$turf_query = "SELECT * FROM turfs WHERE turf_id = :turf_id";
$stmt = $pdo->prepare($turf_query);
$stmt->execute([':turf_id' => $turf_id]);
$turf_details = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turf_details) {
    header("Location: booking.php");
    exit;
}

// Fetch amenities and sports
$amenities_query = "SELECT * FROM amenities WHERE turf_id = :turf_id";
$sports_query = "SELECT * FROM sports_cost WHERE turf_id = :turf_id";

$stmt = $pdo->prepare($amenities_query);
$stmt->execute([':turf_id' => $turf_id]);
$amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare($sports_query);
$stmt->execute([':turf_id' => $turf_id]);
$sports = $stmt->fetchAll(PDO::FETCH_ASSOC);

$time_slots = [];

// Handle filtering time slots based on selected date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_date'])) {
    $selected_date = $_POST['booking_date'];
    $time_slots_query = "
        SELECT * FROM time_slots 
        WHERE turf_id = :turf_id AND (booking_date = :booking_date OR booking_date IS NULL) AND status = 'Available'
    ";

    $stmt = $pdo->prepare($time_slots_query);
    $stmt->execute([':turf_id' => $turf_id, ':booking_date' => $selected_date]);
    $time_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Turf</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Existing styles retained */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #ebffc9,white);
        }
        .navbar {
            background: linear-gradient(90deg, #6c757d, #343a40);
            padding: 15px 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: bold;
            color: #fff;
        }
        .navbar .btn-custom {
            background-color: #fff;
            color: #007bff;
            border: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .navbar .btn-custom:hover {
            background-color: #007bff;
            color: #fff;
        }
        .btn-custom {
            background-color: #6cbbd4;
            color: white;
            border: none;
            transition: background-color 0.3s;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .container {
            margin-top: 70px;
        }
        .form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-section {
            margin-bottom: 20px;
        }
        .form-section h4 {
            color: #0056b3;
            font-weight: bold;
        }
        .form-check-label {
            font-size: 1rem;
        }
        .cost-section {
            padding: 15px;
            background: #e7f3fe;
            border-radius: 8px;
        }
        .cost-section p {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
            transition: background-color 0.3s, transform 0.3s;
        }
        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        .btn {
            margin: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Turf Management System</a>
            <div class="d-flex">
                <button onclick="location.href='logout.php'" class="btn btn-custom mx-3">Logout</button>
            </div>
        </div>
    </nav>
<div class="container">
    <div class="form-container">
        <h2 class="text-center mb-4">Book <?= htmlspecialchars($turf_details['name']) ?></h2>
        <form method="POST" action="">
            <input type="hidden" name="turf_id" value="<?= $turf_id ?>">

            <!-- Date Selection -->
            <div class="form-section">
                <h4>Select Date</h4>
                <input type="date" class="form-control" name="booking_date" value="<?= htmlspecialchars($_POST['booking_date'] ?? '') ?>" required>
            </div>

            <!-- Refresh Time Slots -->
            <div class="form-section text-center">
                <button type="submit" class="btn btn-primary">Check Availability</button>
            </div>
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_date'])): ?>
            <form method="POST" action="process_booking.php">
                <input type="hidden" name="turf_id" value="<?= $turf_id ?>">
                <input type="hidden" name="booking_date" value="<?= htmlspecialchars($selected_date) ?>">

                <!-- Time Slots Section -->
                <div class="form-section">
                    <h4>Time Slots</h4>
                    <select class="form-select" name="time_slot" required>
                        <?php if (empty($time_slots)): ?>
                            <option value="" disabled selected>No slots available for the selected date</option>
                        <?php else: ?>
                            <?php foreach ($time_slots as $slot): ?>
                                <option value="<?= $slot['start_time'] . ' - ' . $slot['end_time'] ?>">
                                    <?= $slot['start_time'] . ' - ' . $slot['end_time'] ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Sports Section -->
                <div class="form-section">
                    <h4>Sports</h4>
                    <?php foreach ($sports as $sport): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sport_id" value="<?= $sport['sport_id'] ?>" data-price="<?= $sport['cost'] ?>" required>
                            <label class="form-check-label"><?= htmlspecialchars($sport['sport_name']) ?> (₹<?= $sport['cost'] ?>)</label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Amenities Section -->
                <div class="form-section">
                    <h4>Amenities</h4>
                    <?php foreach ($amenities as $amenity): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="<?= $amenity['amenity_id'] ?>" data-price="<?= $amenity['price'] ?>">
                            <label class="form-check-label"><?= htmlspecialchars($amenity['name']) ?> (₹<?= $amenity['price'] ?>)</label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cost Section -->
                <div class="cost-section">
                    <p>Base Booking Cost: ₹<span id="baseCost" data-base-cost="<?= htmlspecialchars($turf_details['booking_cost']) ?>">
                        <?= htmlspecialchars($turf_details['booking_cost']) ?>
                    </span></p>
                    <p>Total Cost: ₹<span id="totalCost">0</span></p>
                </div>

                <!-- Payment Options -->
                <div class="form-section">
                    <h4>Payment Options</h4>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_option" value="Pay Now" id="payNow" required>
                        <label class="form-check-label" for="payNow">Pay Now</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_option" value="Pay After Play" id="payLater">
                        <label class="form-check-label" for="payLater">Pay After Play</label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center">
                    <button type="submit" class="btn btn-success">Confirm Booking</button>
                    <a href="booking.php" class="btn btn-danger ms-2">Cancel Booking</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<button onclick="window.location.href='booking.php';" class="btn btn-secondary">Back to Turf List</button>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const baseCostElement = document.getElementById('baseCost');
        const totalCostElement = document.getElementById('totalCost');
        const amenitiesCheckboxes = document.querySelectorAll('input[name="amenities[]"]');
        const sportsRadios = document.querySelectorAll('input[name="sport_id"]');

        const baseCost = parseFloat(baseCostElement.dataset.baseCost);

        function calculateTotalCost() {
            let totalCost = baseCost;

            amenitiesCheckboxes.forEach((checkbox) => {
                if (checkbox.checked) {
                    totalCost += parseFloat(checkbox.dataset.price);
                }
            });

            sportsRadios.forEach((radio) => {
                if (radio.checked) {
                    totalCost += parseFloat(radio.dataset.price);
                }
            });

            totalCostElement.textContent = totalCost.toFixed(2);
        }

        amenitiesCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', calculateTotalCost));
        sportsRadios.forEach((radio) => radio.addEventListener('change', calculateTotalCost));

        calculateTotalCost();
    });
</script>

<footer class="bg-dark text-white text-center py-3 mt-5">
    &copy; <?= date("Y") ?> Turf Management System
</footer>

</body>
</html>
