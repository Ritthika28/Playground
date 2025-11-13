<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="adminstyle.css">
</head>
    <body>
        <div id="mySidebar" class="sidebar">
      <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
      <h3 class="text-center py-3" style="color: white;">Admin Panel</h3>
        <a href="admin.php">Bookings</a>
        <a href="adminadd.php">Add Turf</a>
        <a href="adminedit.php">Edit Turf</a>
        <a href="logout.php">Logout</a>
    </div>

    <div id="main">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <button class="openbtn" onclick="openNav()">&#9776; Admin Panel</button>
    <a class="navbar-brand ml-3" href="#">Hello, <?= htmlspecialchars($_SESSION['username']); ?>!</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <button onclick="location.href='logout.php'" class="btn btn-custom mx-3">Logout</button>
            </li>
        </ul>
    </div>
</nav>
</div>
</body>
</html>