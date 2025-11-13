<?php
require 'config.php';
session_start();

// Ensure the admin is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    $name = htmlspecialchars(trim($_POST['name']));
    $location = htmlspecialchars(trim($_POST['location']));
    $capacity = (int)$_POST['capacity'];
    $booking_cost = (float)$_POST['booking_cost'];
    $area = htmlspecialchars(trim($_POST['area']));
    $address = htmlspecialchars(trim($_POST['address']));
    $services = htmlspecialchars(trim($_POST['services']));
    $description = htmlspecialchars(trim($_POST['description']));
    $sports = array_map('trim', explode(',', $_POST['sports']));
    $sports_costs = array_map('trim', explode(',', $_POST['sports_costs']));
    $amenities = array_map('trim', explode(',', $_POST['amenities']));
    $amenities_prices = array_map('trim', explode(',', $_POST['amenities_prices']));
    $submitted_time_slots = $_POST['time_slots'] ?? [];
    $slots_to_remove = $_POST['remove_slots'] ?? [];

    if (count($sports) !== count($sports_costs)) {
        $_SESSION['error_message'] = "Mismatch between sports and their costs.";
        $redirect_page = ($action === 'add') ? 'adminadd.php' : 'adminedit.php';
        header("Location: $redirect_page");
        exit;
    }
    if (count($amenities) !== count($amenities_prices)) {
        $_SESSION['error_message'] = "Mismatch between amenities and their prices.";
        $redirect_page = ($action === 'add') ? 'adminadd.php' : 'adminedit.php';
        header("Location: $redirect_page");
        exit;
    }

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_dir = 'uploads/';
        if (!is_dir($image_dir)) {
            mkdir($image_dir, 0777, true);
        }
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $image_path = $image_dir . $image_name;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $_SESSION['error_message'] = "Failed to upload image.";
            $redirect_page = ($action === 'add') ? 'adminadd.php' : 'adminedit.php';
            header("Location: $redirect_page");
            exit;
        }
    }

    try {
        $pdo->beginTransaction();

        if ($action === 'add') {
            // Add new turf
            $stmt = $pdo->prepare("INSERT INTO turfs (name, location, capacity, booking_cost, area, address, services, description, image) VALUES (:name, :location, :capacity, :booking_cost, :area, :address, :services, :description, :image)");
            $stmt->execute([
                'name' => $name,
                'location' => $location,
                'capacity' => $capacity,
                'booking_cost' => $booking_cost,
                'area' => $area,
                'address' => $address,
                'services' => $services,
                'description' => $description,
                'image' => $image_path
            ]);

            $turf_id = $pdo->lastInsertId();

            // Insert sports costs
            $sportStmt = $pdo->prepare("INSERT INTO sports_cost (turf_id, sport_name, cost) VALUES (:turf_id, :sport_name, :cost)");
            foreach ($sports as $index => $sport) {
                $sportStmt->execute([
                    'turf_id' => $turf_id,
                    'sport_name' => $sport,
                    'cost' => (float)$sports_costs[$index]
                ]);
            }

            // Insert amenities
            $amenityStmt = $pdo->prepare("INSERT INTO amenities (turf_id, name, price) VALUES (:turf_id, :name, :price)");
            foreach ($amenities as $index => $amenity) {
                $amenityStmt->execute([
                    'turf_id' => $turf_id,
                    'name' => $amenity,
                    'price' => (float)$amenities_prices[$index]
                ]);
            }

            // Insert time slots with date
            $timeSlotStmt = $pdo->prepare("INSERT INTO time_slots (turf_id, booking_date, start_time, end_time, status) VALUES (:turf_id, :booking_date, :start_time, :end_time, 'Available')");
            foreach ($submitted_time_slots as $slot) {
                $timeSlotStmt->execute([
                    'turf_id' => $turf_id,
                    'booking_date' => $slot['booking_date'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time']
                ]);
            }
            $pdo->commit();
            $_SESSION['success_message'] = "Turf added successfully!";
            header('Location: adminadd.php');
           exit;
        } elseif ($action === 'edit') {
            $turf_id = (int)$_POST['turf_id'];

            // Update turf details
            $stmt = $pdo->prepare("UPDATE turfs SET name = :name, location = :location, capacity = :capacity, booking_cost = :booking_cost, area = :area, address = :address, services = :services, description = :description, image = :image WHERE turf_id = :turf_id");
            $stmt->execute([
                'name' => $name,
                'location' => $location,
                'capacity' => $capacity,
                'booking_cost' => $booking_cost,
                'area' => $area,
                'address' => $address,
                'services' => $services,
                'description' => $description,
                'image' => $image_path ?: $_POST['existing_image'],
                'turf_id' => $turf_id
            ]);

            // Update sports and their costs
            $pdo->prepare("DELETE FROM sports_cost WHERE turf_id = :turf_id")->execute(['turf_id' => $turf_id]);
            $sportStmt = $pdo->prepare("INSERT INTO sports_cost (turf_id, sport_name, cost) VALUES (:turf_id, :sport_name, :cost)");
            foreach ($sports as $index => $sport) {
                $sportStmt->execute([
                    'turf_id' => $turf_id,
                    'sport_name' => $sport,
                    'cost' => (float)$sports_costs[$index]
                ]);
            }

            // Update amenities and their prices
            $pdo->prepare("DELETE FROM amenities WHERE turf_id = :turf_id")->execute(['turf_id' => $turf_id]);
            $amenityStmt = $pdo->prepare("INSERT INTO amenities (turf_id, name, price) VALUES (:turf_id, :name, :price)");
            foreach ($amenities as $index => $amenity) {
                $amenityStmt->execute([
                    'turf_id' => $turf_id,
                    'name' => $amenity,
                    'price' => (float)$amenities_prices[$index]
                ]);
            }

            // Remove selected time slots
            foreach ($slots_to_remove as $slot_id) {
                $deleteStmt = $pdo->prepare("DELETE FROM time_slots WHERE slot_id = :slot_id");
                $deleteStmt->execute(['slot_id' => $slot_id]);
            }
            
            // Fetch existing time slots
            $existingSlotsStmt = $pdo->prepare("SELECT * FROM time_slots WHERE turf_id = :turf_id");
            $existingSlotsStmt->execute(['turf_id' => $turf_id]);
            $existing_slots = $existingSlotsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Prepare slots for update/insert/delete
            $submitted_slot_ids = array_column($submitted_time_slots, 'slot_id');
            $existing_slot_ids = array_column($existing_slots, 'slot_id');

            // Identify slots to delete
            $slots_to_delete = array_diff($existing_slot_ids, $submitted_slot_ids);
            foreach ($slots_to_delete as $slot_id) {
                $deleteStmt = $pdo->prepare("DELETE FROM time_slots WHERE slot_id = :slot_id");
                $deleteStmt->execute(['slot_id' => $slot_id]);
            }

            // Insert or update submitted slots
            foreach ($submitted_time_slots as $slot) {
                if (in_array($slot['slot_id'], $existing_slot_ids)) {
                    // Update existing slot
                    $updateStmt = $pdo->prepare("UPDATE time_slots SET booking_date = :booking_date, start_time = :start_time, end_time = :end_time, status = 'Available' WHERE slot_id = :slot_id");
                    $updateStmt->execute([
                        'booking_date' => $slot['booking_date'],
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'slot_id' => $slot['slot_id']
                    ]);
                } else {
                    // Insert new slot
                    $insertStmt = $pdo->prepare("INSERT INTO time_slots (turf_id, booking_date, start_time, end_time, status) VALUES (:turf_id, :booking_date, :start_time, :end_time, 'Available')");
                    $insertStmt->execute([
                        'turf_id' => $turf_id,
                        'booking_date' => $slot['booking_date'],
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time']
                    ]);
                }
            }
        }

        $pdo->commit();
        $_SESSION['success_message'] = "Turf updated successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
}
header('Location: adminedit.php');
exit;
?>
