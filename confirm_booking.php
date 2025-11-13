<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $turfId = $_POST['turf'];
    $slot = $_POST['slot'];
    $amenities = $_POST['amenities'] ?? [];

    // Fetch turf and amenities details
    $stmt = $pdo->prepare("SELECT * FROM turfs WHERE id = :id");
    $stmt->execute(['id' => $turfId]);
    $turf = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalFare = $turf['base_price'];
    $amenityDetails = [];

    if (!empty($amenities)) {
        $inQuery = implode(',', array_fill(0, count($amenities), '?'));
        $amenitiesStmt = $pdo->prepare("SELECT * FROM amenities WHERE id IN ($inQuery)");
        $amenitiesStmt->execute($amenities);
        $amenityDetails = $amenitiesStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($amenityDetails as $amenity) {
            $totalFare += $amenity['price'];
        }
    }

    // Save booking to the database
    $bookingStmt = $pdo->prepare("INSERT INTO bookings (user_id, turf_id, slot, total_fare) VALUES (:user_id, :turf_id, :slot, :total_fare)");
    $bookingStmt->execute([
        'user_id' => $_SESSION['user_id'],
        'turf_id' => $turfId,
        'slot' => $slot,
        'total_fare' => $totalFare,
    ]);

    $bookingId = $pdo->lastInsertId();

    // Generate invoice
    header("Location: invoice.php?booking_id=" . $bookingId);
    exit;
}
