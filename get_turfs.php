<?php
// Ensure $selected_location is always initialized
$selected_location = $_GET['location'] ?? null;

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "turf_management");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch turfs based on selected location or all turfs
$sql_turfs = $selected_location
    ? "SELECT * FROM turfs WHERE location = '" . mysqli_real_escape_string($con, $selected_location) . "'"
    : "SELECT * FROM turfs";
$result_turfs = mysqli_query($con, $sql_turfs);

// Prepare data for amenities and sports
$turf_data = [];
if ($result_turfs && mysqli_num_rows($result_turfs) > 0) {
    while ($row = mysqli_fetch_assoc($result_turfs)) {
        $turf_id = $row['turf_id'];

        // Fetch amenities for the turf
        $sql_amenities = "SELECT name FROM amenities WHERE turf_id = $turf_id";
        $result_amenities = mysqli_query($con, $sql_amenities);
        $amenities = [];
        if ($result_amenities && mysqli_num_rows($result_amenities) > 0) {
            while ($amenity = mysqli_fetch_assoc($result_amenities)) {
                $amenities[] = $amenity['name'];
            }
        }

        // Fetch sports and their costs for the turf
        $sql_sports = "SELECT sport_name, cost FROM sports_cost WHERE turf_id = $turf_id";
        $result_sports = mysqli_query($con, $sql_sports);
        $sports = [];
        if ($result_sports && mysqli_num_rows($result_sports) > 0) {
            while ($sport = mysqli_fetch_assoc($result_sports)) {
                $sports[] = $sport['sport_name'] . " (₹" . $sport['cost'] . ")";
            }
        }

        // Add data to turf_data
        $row['amenities'] = $amenities;
        $row['sports'] = $sports;
        $turf_data[] = $row;
    }
}

// Return turfs as JSON
header('Content-Type: application/json');
echo json_encode($turf_data);
?>
