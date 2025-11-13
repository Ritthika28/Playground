<?php
require 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get user input
        $user_id = $_SESSION['user_id'];
        $turf_id = $_POST['turf_id'];
        $sport_id = $_POST['sport_id'] ?? null;
        $amenities_selected = $_POST['amenities'] ?? [];
        $time_slot = $_POST['time_slot'];
        $booking_date = $_POST['booking_date']; // New field for booking date
        $payment_option = $_POST['payment_option'];

        // Validate the booking date
        $current_date = date('Y-m-d');
        if (strtotime($booking_date) < strtotime($current_date)) {
            throw new Exception("Booking date cannot be in the past.");
        }

        // Fetch turf base cost
        $turf_query = "SELECT booking_cost FROM turfs WHERE turf_id = :turf_id";
        $stmt = $pdo->prepare($turf_query);
        $stmt->execute([':turf_id' => $turf_id]);
        $turf_details = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$turf_details) {
            throw new Exception("Invalid turf selected.");
        }

        $total_cost = $turf_details['booking_cost'];

        // Calculate total cost for selected amenities
        if (!empty($amenities_selected)) {
            $placeholders = implode(',', array_fill(0, count($amenities_selected), '?'));
            $amenity_query = "SELECT SUM(price) AS total FROM amenities WHERE amenity_id IN ($placeholders)";
            $stmt = $pdo->prepare($amenity_query);
            $stmt->execute($amenities_selected);
            $total_cost += $stmt->fetchColumn();
        }

        // Add the cost of the selected sport
        if ($sport_id) {
            $sport_query = "SELECT cost FROM sports_cost WHERE sport_id = :sport_id";
            $stmt = $pdo->prepare($sport_query);
            $stmt->execute([':sport_id' => $sport_id]);
            $sport = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_cost += $sport['cost'];
        }

        // Check payment option
        $pdo->beginTransaction();

        // Update time slot status for the selected date
        $update_time_slot_query = "
            UPDATE time_slots 
            SET status = 'Booked' 
            WHERE turf_id = :turf_id 
              AND booking_date = :booking_date 
              AND CONCAT(start_time, ' - ', end_time) = :time_slot
        ";

        if ($payment_option === 'Pay Now') {
            // Store booking details in session for payment gateway
            $_SESSION['booking_data'] = [
                'user_id' => $user_id,
                'turf_id' => $turf_id,
                'sport_id' => $sport_id,
                'amenities' => $amenities_selected,
                'time_slot' => $time_slot,
                'booking_date' => $booking_date,
                'total_cost' => $total_cost
            ];

            // Mark the time slot as booked for the selected date
            $stmt = $pdo->prepare($update_time_slot_query);
            $stmt->execute([
                ':turf_id' => $turf_id,
                ':booking_date' => $booking_date,
                ':time_slot' => $time_slot
            ]);

            $pdo->commit();

            // Redirect to payment gateway
            header('Location: payment_gateway.php?amount=' . $total_cost);
            exit;
        } else {
            // Insert booking into the database for 'Pay After Play'
            $status = 'Pending';

            // Insert into bookings table
            $insert_booking_query = "
                INSERT INTO bookings (user_id, turf_id, sport_id, booking_date, time_slot, total_cost, status)
                VALUES (:user_id, :turf_id, :sport_id, :booking_date, :time_slot, :total_cost, :status)
            ";
            $stmt = $pdo->prepare($insert_booking_query);
            $stmt->execute([
                ':user_id' => $user_id,
                ':turf_id' => $turf_id,
                ':sport_id' => $sport_id,
                ':booking_date' => $booking_date,
                ':time_slot' => $time_slot,
                ':total_cost' => $total_cost,
                ':status' => $status
            ]);
            $booking_id = $pdo->lastInsertId();

            // Insert selected amenities into booking_amenities table
            foreach ($amenities_selected as $amenity_id) {
                $insert_amenities_query = "INSERT INTO booking_amenities (booking_id, amenity_id) VALUES (:booking_id, :amenity_id)";
                $stmt = $pdo->prepare($insert_amenities_query);
                $stmt->execute([
                    ':booking_id' => $booking_id,
                    ':amenity_id' => $amenity_id
                ]);
            }

            // Mark the time slot as booked for the selected date
            $stmt = $pdo->prepare($update_time_slot_query);
            $stmt->execute([
                ':turf_id' => $turf_id,
                ':booking_date' => $booking_date,
                ':time_slot' => $time_slot
            ]);

            $pdo->commit();

            // Redirect to confirmation page
            header('Location: confirmation.php?booking_id=' . $booking_id);
            exit;
        }
    } catch (Exception $e) {
        // Handle exceptions and rollback if necessary
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
    }
}
?>
