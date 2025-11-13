<?php
// Ensure $selected_location is always initialized
$selected_location = $_GET['location'] ?? null;

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "turf_management");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch unique locations
$locations = [];
$sql_locations = "SELECT DISTINCT location FROM turfs";
$result_locations = mysqli_query($con, $sql_locations);
if ($result_locations) {
    while ($row = mysqli_fetch_assoc($result_locations)) {
        $locations[] = $row['location'];
    }
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turf Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #ebffc9,white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(90deg, #6c757d, #343a40);
            padding: 15px 20px;
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

        /* Welcome Section */
        .welcome-section {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(to right, #343a40, #6c757d);
            color: white;
        }
        .welcome-section h1 {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .welcome-section p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        .welcome-section .btn-custom {
            padding: 10px 30px;
            font-size: 1rem;
            font-weight: bold;
        }
        .welcome-section .btn-custom {
            background-color: #fff;
            color: #007bff;
            border: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .welcome-section .btn-custom:hover {
            background-color: #007bff;
            color: #fff;
        }
        /* Browse Section */
        .browse-section {
            padding: 50px 20px;
            text-align: center;
        }
        .browse-section h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .browse-section select {
            width: 50%;
            margin: 0 auto;
            display: block;
            padding: 10px;
            border: 2px solid #007bff;
            border-radius: 5px;
            font-size: 1rem;
        }

        /* Turf Cards Section */
        .turfs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* Maintain consistent card size */
            gap: 20px;
            justify-content: center; /* Center align when fewer cards */
            align-items: start;
            padding: 50px 20px;
        }

        .turfs-grid .card {
            background: #fff;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            width: 370px; /* Ensures cards never exceed this size */
        }

        .turfs-grid .card:hover {
            transform: translateY(-10px);
        }

        .turfs-grid .card img {
            height: 200px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }

        .turfs-grid .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }


        /* Footer */
        footer{
            bottom: 0;
            width: 100%;
            
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">Turf Management</a>
        <div class="ml-auto">
            <button class="btn btn-custom mx-2" onclick="location.href='login.php'">Login</button>
            <button class="btn btn-custom mx-2" onclick="location.href='register.php'">Register</button>
        </div>
    </nav>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <h1>Welcome to Turf Management System</h1>
        <p>Your ultimate destination for hassle-free turf bookings and sports facilities!</p>
        <button class="btn btn-custom" onclick="location.href='about.html'">About us</button>
    </section>

<!-- Browse Section -->
    <section id="browse-section" class="browse-section">
        <h2>Browse Turfs by Location</h2>
        <form id="location-form">
            <select name="location" id="location-select" onchange="fetchTurfs()">
                <option value="">All Locations</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= htmlspecialchars($location) ?>">
                        <?= htmlspecialchars($location) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </section>
    
    <!-- Turf Cards Section -->
    <section id="turfs-section" class="turfs-grid">
        <?php if (!empty($turf_data)): ?>
            <?php foreach ($turf_data as $turf): ?>
                <?php
                $imagePath = htmlspecialchars($turf['image']);
                if (!file_exists($imagePath) || empty($turf['image'])) {
                    $imagePath = "uploads/Default.jpg";
                }
                ?>
                <div class="card">
                    <img src="<?= $imagePath ?>" class="card-img-top" alt="<?= htmlspecialchars($turf['name']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($turf['name']) ?></h5>
                        <p class="card-text"><strong>Location:</strong> <?= htmlspecialchars($turf['location']) ?></p>
                        <p class="card-text"><strong>Sports:</strong> <?= implode(', ', $turf['sports']) ?: 'Not Available' ?></p>
                        <p class="card-text"><strong>Amenities:</strong> <?= implode(', ', $turf['amenities']) ?: 'Not Available' ?></p>
                        <p class="card-text"><strong>Capacity:</strong> <?= htmlspecialchars($turf['capacity']) ?> people</p>
                        <p class="card-text"><strong>Cost:</strong> ₹<?= htmlspecialchars($turf['booking_cost']) ?></p>
                        <a href="booking.php?turf_id=<?= urlencode($turf['turf_id']) ?>" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No turfs available.</p>
        <?php endif; ?>
    </section>

    <script>
        function fetchTurfs() {
            const location = document.getElementById('location-select').value;
            const turfsSection = document.getElementById('turfs-section');

            // Fetch turfs dynamically
            fetch(`get_turfs.php?location=${encodeURIComponent(location)}`)
                .then(response => response.json())
                .then(data => {
                    // Clear existing turfs
                    turfsSection.innerHTML = '';

                    // Populate new turfs
                    if (data.length > 0) {
                        data.forEach(turf => {
                            const card = `
                                <div class="card">
                                    <img src="${turf.image || 'uploads/Default.jpg'}" class="card-img-top" alt="${turf.name}">
                                    <div class="card-body">
                                        <h5 class="card-title">${turf.name}</h5>
                                        <p class="card-text"><strong>Location:</strong> ${turf.location}</p>
                                        <p class="card-text"><strong>Sports:</strong> ${turf.sports.join(', ') || 'Not Available'}</p>
                                        <p class="card-text"><strong>Amenities:</strong> ${turf.amenities.join(', ') || 'Not Available'}</p>
                                        <p class="card-text"><strong>Capacity:</strong> ${turf.capacity} people</p>
                                        <p class="card-text"><strong>Cost:</strong> ₹${turf.booking_cost}</p>
                                        <a href="booking.php?turf_id=${encodeURIComponent(turf.turf_id)}" class="btn btn-primary">Book Now</a>
                                    </div>
                                </div>
                            `;
                            turfsSection.innerHTML += card;
                        });
                    } else {
                        turfsSection.innerHTML = `<p>No turfs available in the selected location.</p>`;
                    }
                })
                .catch(error => console.error('Error fetching turfs:', error));
        }
    </script>
    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        &copy; <?php echo date("Y"); ?> Turf Management System
    </footer>
</body>
</html>
