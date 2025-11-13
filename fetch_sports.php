<?php
// fetch_sports.php
require 'config.php';

if (isset($_GET['turf_id'])) {
    $turf_id = (int)$_GET['turf_id'];
    $stmt = $conn->prepare("SELECT sport_id, name FROM sports WHERE turf_id = ?");
    $stmt->bind_param("i", $turf_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $sports = [];
    while ($row = $result->fetch_assoc()) {
        $sports[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($sports);
}
?>
