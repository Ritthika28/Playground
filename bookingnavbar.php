<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turf Management System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
         body {
                        font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #ebffc9,white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
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
        .navbar {
            background: linear-gradient(90deg, #6c757d, #343a40);
            padding: 30px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            margin:auto;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
        }
        .navbar-brand {
            font-weight: bold;
            color: #fff;
            position: absolute;
            left: 50%;
            transform: translateX(-50%); /* Center align the brand */
        }
        .navbar-nav.ml-auto {
            position: absolute;
            right: 30px; /* Align the logout link to the right corner */
            margin: auto;
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
                /* The sidebar menu */
        .sidebar {
          height: 100%; /* 100% Full-height */
          width: 0; /* 0 width - change this with JavaScript */
          position: fixed; /* Stay in place */
          z-index: 1; /* Stay on top */
          top: 0;
          left: 0;
          background-color: #444; /* Black*/
          overflow-x: hidden; /* Disable horizontal scroll */
          padding-top: 60px; /* Place content 60px from the top */
          transition: 0.5s; /* 0.5 second transition effect to slide in the sidebar */
        }

        /* The sidebar links */
        .sidebar a {
          padding: 8px 8px 8px 32px;
          text-decoration: none;
          font-size: 20px;
          color: white;
          display: block;
          transition: 0.3s;
        }

        /* When you mouse over the navigation links, change their color */
        .sidebar a:hover {
          color: grey;
        }

        /* Position and style the close button (top right corner) */
        .sidebar .closebtn {
          position: absolute;
          top: 0;
          right: 25px;
          font-size: 36px;
          margin-left: 50px;
        }

        /* The button used to open the sidebar */
        .openbtn {
        font-size: 16px; /* Font size remains */
        padding: 5px; /* Reduce padding for a compact look */
        width: 120px; /* Set a smaller fixed width */
        border: none;
        background-color: #6c757d; /* Match the navbar background */
        color: white;
        cursor: pointer;
        position: absolute; /* Place it in the corner */
        top: 10px; /* Adjust position from the top */
        left: 10px; /* Adjust position from the left */
        text-align: center; /* Center-align text */
    }

        .openbtn:hover {
          background-color: #5c5b5c;
        }

        /* Style page content - use this if you want to push the page content to the right when you open the side navigation */
        #main {
          transition: margin-left .5s; /* If you want a transition effect */
        }

        /* On smaller screens, where height is less than 450px, change the style of the sidenav (less padding and a smaller font size) */
        @media screen and (max-height: 450px) {
          .sidebar {padding-top: 15px;}
          .sidebar a {font-size: 18px;}
        }
    </style>
</head>
<body>
    <div id="mySidebar" class="sidebar">
      <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
      <h3 class="text-center py-3" style="color: white;">User Page</h3>
        <a href='book_turf.php'>List</a>
        <a href="user_booking_history.php">Bookings History</a>
        <a href="logout.php">Logout</a>
    </div>

    <div id="main">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <button class="openbtn" onclick="openNav()">&#9776; User Page</button>
    <h5 class="navbar-brand ml-3">Hello, <?= htmlspecialchars($_SESSION['username']); ?>!</h5>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <button onclick="location.href='logout.php'" class="btn btn-custom mx-3">Logout</button>
            </li>
        </ul>
    </div>
</nav>

    
    <script>
        const toggleBtn = document.querySelector('.toggle-sidebar-btn');
        const sidebar = document.querySelector('.sidebar');
        const content = document.querySelector('.content');
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
        });
    </script>

    <script>
                /* Set the width of the sidebar to 250px and the left margin of the page content to 250px */
        function openNav() {
          document.getElementById("mySidebar").style.width = "250px";
          document.getElementById("main").style.marginLeft = "250px";
        }

        /* Set the width of the sidebar to 0 and the left margin of the page content to 0 */
        function closeNav() {
          document.getElementById("mySidebar").style.width = "0";
          document.getElementById("main").style.marginLeft = "0";
        }
    </script>
</body>
</html>