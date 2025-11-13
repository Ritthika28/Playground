<?php
require 'config.php';
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate input
    if (!empty($email) && !empty($password)) {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } elseif ($user['role'] === 'user') {
                    header("Location: booking.php");
                }
                exit;
            } else {
                $error = "Invalid username or password!";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #ebffc9,white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
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
        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin: auto;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 1.5rem;
            font-weight: 600;
            color: #0d6efd;
        }
        #login {
            background-color: #0d6efd;
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        #login:hover {
            background-color: #0056b3;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #ccc;
        }
        .alert {
            text-align: left;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        footer {
            background: #343a40;
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Turf Management System</a>
            <div class="d-flex">
                <button class="btn btn-custom mx-2" onclick="location.href='index.php'">Home</button>
                <button class="btn btn-custom mx-2" onclick="location.href='register.php'">Register</button>
            </div>
        </div>
    </nav>

    <div class="login-container">
        <h2>Welcome Back!</h2>
        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="login.php">
            <div class="mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" id="login" class="btn btn-custom">Login</button>
        </form>
    </div>

    <footer>
        &copy; <?= date("Y"); ?> Turf Management System
    </footer>

    <script>
        $(document).ready(function () {
            $('#loginForm').on('submit', function (e) {
                const email = $('#email').val().trim();
                const password = $('#password').val().trim();

                if (!email || !password) {
                    e.preventDefault();
                    alert('Please fill in all fields.');
                }
            });
        });
    </script>
</body>
</html>
