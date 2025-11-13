<?php
require 'config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Fetch locations for the dropdown
$locations = [];
$query = "SELECT DISTINCT location FROM turfs";
$stmt = $pdo->query($query);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $locations[] = $row['location'];
}

// Fetch selected location and turfs
$selected_location = $_GET['location'] ?? '';
$turf_query = "SELECT * FROM turfs";
if ($selected_location) {
    $turf_query .= " WHERE location = :location";
    $stmt = $pdo->prepare($turf_query);
    $stmt->execute([':location' => $selected_location]);
} else {
    $stmt = $pdo->query($turf_query);
}
$turfs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getAmenities($turf_id, $pdo) {
    $stmt = $pdo->prepare("SELECT name, price FROM amenities WHERE turf_id = :turf_id");
    $stmt->execute([':turf_id' => $turf_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSports($turf_id, $pdo) {
    $stmt = $pdo->prepare("SELECT sport_name, cost FROM sports_cost WHERE turf_id = :turf_id");
    $stmt->execute([':turf_id' => $turf_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Turf</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #ebffc9,white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
        }
        .dropdown-container {
            max-width: 400px;
            margin: auto;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .card-img-top {
            border-radius: 12px 12px 0 0;
            max-height: 200px;
            object-fit: cover;
        }
        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .btn-primary {
            border-radius: 8px;
        }
        .truncate {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        #content{
            margin-top: 100px;
        }
    </style>
</head>
<body>
    <?php include 'bookingnavbar.php'; ?>
    <div class="container mt-5">
        <div class="text-center mb-4" id="content">
            <h2 class="fw-bold">Explore and Book Your Favorite Turf</h2>
            <p class="text-muted">Choose a location and find the best turf for your activities.</p>
        </div>
        <form method="GET" action="" class="dropdown-container mt-4" style="padding-bottom: 20px;">
            <select name="location" class="form-select" onchange="this.form.submit()">
                <option value="" <?= !$selected_location ? 'selected' : '' ?>>All Locations</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= htmlspecialchars($location) ?>" <?= $selected_location === $location ? 'selected' : '' ?>>
                        <?= htmlspecialchars($location) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <div class="text-center mb-4">
        <h2 class="fw-bold">
            <?= $selected_location ? "Turfs in " . htmlspecialchars($selected_location) : "Available Turfs" ?>
        </h2>
    </div>
        <div class="row mt-4">
            <?php foreach ($turfs as $turf): ?>
                <?php
                    $imagePath = htmlspecialchars($turf['image']);
                    if (!file_exists($imagePath) || empty($turf['image'])) {
                        $imagePath = "uploads/Default.jpg"; // Replace with your default image path
                    }
                ?>
                <div class="col-md-4 mb-4 d-flex">
                    <div class="card w-100">
                        <img src="<?= $imagePath ?>" class="card-img-top" alt="<?= htmlspecialchars($turf['name']) ?> Turf Image">
                        <div class="card-body">
                            <h5 class="card-title truncate"><?= htmlspecialchars($turf['name']) ?></h5>
                            <p class="truncate"><strong>Location:</strong> <?= htmlspecialchars($turf['location']) ?></p>
                            <p class="truncate"><strong>Area:</strong> <?= htmlspecialchars($turf['area']) ?></p>
                            <p class="truncate"><strong>Capacity:</strong> <?= htmlspecialchars($turf['capacity']) ?> players</p>
                            <p class="truncate"><strong>Address:</strong> <?= htmlspecialchars($turf['address']) ?></p>
                            <h6><strong>Sports and Costs:</strong></h6>
                            <ul>
                                <?php foreach (getSports($turf['turf_id'], $pdo) as $sport): ?>
                                    <li><?= htmlspecialchars($sport['sport_name']) ?>: ₹<?= htmlspecialchars($sport['cost']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <h6><strong>Amenities:</strong></h6>
                            <ul>
                                <?php foreach (getAmenities($turf['turf_id'], $pdo) as $amenity): ?>
                                    <li><?= htmlspecialchars($amenity['name']) ?> (₹<?= htmlspecialchars($amenity['price']) ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                            <p class="text-primary fw-bold">₹<?= htmlspecialchars($turf['booking_cost']) ?> / Slot</p>
                            <a href="book_turf.php?turf_id=<?= $turf['turf_id'] ?>" class="btn btn-primary mt-3">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($turfs)): ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No turfs found for the selected location.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'bookingFooter.php'; ?>
</body>
</html>
