<<<<<<< HEAD
<?php
// Start the session
session_start();

// Check if user details exist in the session
if (!isset($_SESSION['username'], $_SESSION['role'])) {
    echo "Error: User is not logged in!";
    exit();
}

// Safely retrieve session variables
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities Hub - Virtual Classroom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #6a11cb, #2575fc); /* Gradient background */
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .activities-hub {
            width: 90%;
            max-width: 1200px;
            position: relative;
        }

        .title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            animation: slideInFromLeft 1s ease-in-out;
            text-align: center;
        }

        .card {
            background: #1c1f4f; /* Dark blue card */
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            transition: transform 0.5s, box-shadow 0.5s;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-15px) scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5), 0 0 30px 5px rgba(255, 255, 255, 0.6);
        }

        .card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            background: linear-gradient(90deg, #6a11cb, #2575fc); /* Gradient button */
            color: #fff;
            cursor: pointer;
            font-size: 1rem;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .btn:hover {
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.8), 0 0 15px rgba(37, 117, 252, 0.9); /* Glow effect */
            transform: scale(1.1);
        }

        @keyframes slideInFromLeft {
            0% {
                opacity: 0;
                transform: translateX(-100px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .boy {
            position: absolute;
            bottom: 10%;
            left: -100px;
            width: 80px;
            animation: walk 6s linear infinite;
        }

        @keyframes walk {
            0% {
                left: -100px;
            }
            33% {
                left: 30%;
            }
            66% {
                left: 65%;
            }
            100% {
                left: 100%;
            }
        }

        .knock {
            animation: knock 1s ease-in-out infinite;
        }

        @keyframes knock {
            0%, 100% {
                transform: rotate(0deg);
            }
            50% {
                transform: rotate(-10deg);
            }
        }
    </style>
</head>
<body>
    <div class="activities-hub">
        <h1 class="title">Welcome to the Activities Hub, <?php echo htmlspecialchars($username); ?>!</h1>
        <br>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <!-- Assignments Card -->
            <div class="col">
                <div class="card text-center p-4">
                    <img src="../assets/images/assign.jpg" alt="assignments">
                    <h5 class="card-title">Assignments</h5>
                    <p>Track, submit, and manage your assignments.</p>
                    <a href="assignments_hub.php" class="btn">Go to Assignments</a>
                </div>
            </div>
            <!-- Quizzes Card -->
            <div class="col">
                <div class="card text-center p-4">
                    <img src="../assets/images/quiz.jpg" alt="Quizzes">
                    <h5 class="card-title">Quizzes</h5>
                    <p>Test your knowledge and improve your skills.</p>
                    <a href="quizzes.php" class="btn">Take a Quiz</a>
                </div>
            </div>
            <!-- Polls/Surveys Card -->
            <div class="col">
                <div class="card text-center p-4">
                    <img src="../assets/images/polls.jpg" alt="polls">
                    <h5 class="card-title">Polls/Surveys</h5>
                    <p>Participate in live polls and surveys.</p>
                    <a href="polls_list.php" class="btn">View Polls</a>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
        
    </div>
</body>
</html>
=======
<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Get user details from session
$username = $_SESSION['username'];
$role = $_SESSION['role']; // Either 'teacher' or 'student'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities Hub - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f8fc;
            margin: 0;
            padding: 0;
        }
        .activities-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .activities-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="activities-container">
        <h2>Welcome to the Activities Hub, <?php echo htmlspecialchars($username); ?>!</h2>

        <div class="btn-container">
            <a href="assignment.php" class="btn">Assignments</a>
            <a href="quizzes.php" class="btn">Quizzes</a>
            <a href="polls_list.php" class="btn">Polls/Surveys</a>
            <div class="btn-container">
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
        </div>
    </div>
</body>
</html>
>>>>>>> ee7c9565c28e3f015817e1645a6e2d0b3b949065
