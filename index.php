<?php
// Start the session (if needed for login checks)
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Classroom System</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        /* Inline CSS for simplicity, move this to main.css in assets/css/ */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f8fc;
            color: #333;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        nav {
            display: flex;
            justify-content: center;
            background-color: #0056b3;
            padding: 10px 0;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 1.2rem;
        }
        nav a:hover {
            text-decoration: underline;
        }
        section {
            padding: 20px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
            font-size: 1rem;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        footer {
            text-align: center;
            padding: 10px;
            background-color: #0056b3;
            color: white;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <h1>Welcome to the Virtual Classroom System</h1>
        <p>Your gateway to seamless online education</p>
    </header>

    <!-- Navigation Bar -->
    <nav>
        <a href="pages/login.php">Login</a>
        <a href="pages/signup.php">Sign Up</a>
        <a href="pages/study_materials.php">Study Materials</a>
        <a href="pages/live_classes.php">Live Classes</a>
    </nav>

    <!-- Main Section -->
    <section>
        <h2>Empowering Online Learning</h2>
        <p>Access live classes, study materials, assignments, and much more!</p>
        <a href="pages/login.php" class="btn">Get Started</a>
        <a href="pages/signup.php" class="btn">Create an Account</a>
    </section>

    <!-- Footer Section -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Virtual Classroom System. All rights reserved.</p>
    </footer>
</body>
</html>
