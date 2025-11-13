<?php
require 'config.php';

$error_message = ""; // Variable to hold error messages
$success_message = ""; // Variable to hold success messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email']; // Get the email address
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // Get the selected role

    try {
        // Insert the user into the database
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
            ':role' => $role,
        ]);

        $success_message = "Registration successful! You can now log in.";
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) { // MySQL error code for duplicate entry
            $error_message = "Email already exists. Please try with another email address.";
        } else {
            $error_message = "An error occurred. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        .register-container {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            margin: auto;
        }
        .register-container h2 {
            margin-bottom: 1.5rem;
            font-weight: 600;
            color: #0d6efd;
        }
        #register {
            background-color: #0d6efd;
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        #register:hover {
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
                <button class="btn btn-custom mx-2" onclick="location.href='login.php'">Login</button>
            </div>
        </div>
    </nav>

    <div class="register-container">
        <h2>Create an Account</h2>
        <!-- Error or Success Messages -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php elseif (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="">
            <div class="mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="mb-3">
                <select class="form-control" id="role" name="role" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" id="register" class="btn btn-custom">Register</button>
        </form>
    </div>

    <footer>
        &copy; <?= date("Y"); ?> Turf Management System
    </footer>
</body>
</html>
