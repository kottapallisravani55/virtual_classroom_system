<<<<<<< HEAD
<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Get user details from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$profile_photo = isset($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : $user_id . '-avatar.jpg'; // Default avatar if no profile photo
$role = $_SESSION['role']; // Assuming the user's role is stored in the session
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Virtual Classroom</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/calendar.css">
    <link rel="stylesheet" href="./navbar.css">
    <link rel="stylesheet" href="./sidebar.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to right, #1A1A72, #4A4A90);
            overflow: hidden;
            /* Prevent scroll bars */
            min-height: 100vh;
            /* Ensure body covers viewport */
            min-width: 100vw;
            /* Ensure body width covers viewport */
            color: #FFF;
        }



        /* Main Content */
        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Particle Background */
        #particles {
            position: fixed;
            /* Change from absolute to fixed */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            /* Keep it below other content but interactive */
            pointer-events: auto;
            /* Ensure it registers mouse events */
        }

        /* Chatbot Icon */
        .chatbot-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            cursor: pointer;
            background-color: transparent;
        }

        .chatbot-icon img {
            width: 150px;
            height: auto;
            transition: transform 0.3s ease;
        }

        .chatbot-icon:hover img {
            transform: scale(1.2);
        }

        /* Slide up animation */
        .chatbot-icon {
            animation: slideUp 1s forwards ease;
        }

        @keyframes slideUp {
            0% {
                bottom: -150px;
            }

            100% {
                bottom: 20px;
            }
        }

        /* Custom Card Styling */
        .card {
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        /* Calendar Customization */
        .fc {
            color: #fff;
        }

        .fc-toolbar-title {
            color: #fff !important;
        }

        .fc-col-header-cell {
            background: rgba(255, 255, 255, 0.1) !important;
        }

        .fc-daygrid-day {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        /* List Group Customization */
        .list-group-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 8px;
            border-radius: 8px !important;
            transition: all 0.3s ease;
        }

        .list-group-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        /* Form Control Styling */
        .form-control {
            background: rgba(0, 0, 0, 0.3) !important;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff !important;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
        }
    </style>
</head>

<body>
    <!-- Top Navbar -->
    <div class="navbar">
        <span>Welcome, <?php echo $username; ?></span>
        <div class="icon-container">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
            <i class="fas fa-calendar-alt" onclick="toggleCalendar()"></i>
        </div>
    </div>
    <div class="calendar-popup" id="calendarPopup">
        <header class="modal-header">
            <h5 class="modal-title" id="calendarMonthYear">EVENT Calendar</h5>
        </header>
        <div class="modal-body">
            <div class="calendar-header">
                <div id="calendarControls">
                    <button id="prevMonth">Prev</button>
                    <span id="monthYearDisplay"></span>
                    <button id="nextMonth">Next</button>
                </div>
                <div class="calendar-grid"></div>

            </div>
        </div>
    </div>


    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Profile Photo -->
        <a href="profile.php" class="inactive"><img src="../assets/avatars/<?php echo $profile_photo; ?>" alt="Profile Photo" class="profile-photo"></a>

        <h3 class="text-center mt-3"><?php echo $username; ?></h3>

        <!-- Sidebar Links -->
        <a href="dashboard.php" class="active">
            <i class="fas fa-user-circle"></i>
            <span>Home</span>
        </a>
        <a href="study_materials.php">
            <i class="fas fa-book"></i>
            <span>Study Materials</span>
        </a>
        <a href="view_live_classes.php">
            <i class="fas fa-video"></i>
            <span>Live Classes</span>
        </a>
        <a href="activities.php">
            <i class="fas fa-tasks"></i>
            <span>Activity Hub</span>
        </a>
        <a href="class_attendance.php">
            <i class="fas fa-list"></i>
            <span>Attendance</span>
        </a>
        <?php if ($role == 'student'): ?>
            <a href="complaint_form.php">
                <i class="fas fa-exclamation-circle"></i>
                <span>Complaints</span>
            </a>
        <?php endif; ?>
        <a href="chat.php">
            <i class="fas fa-comments"></i>
            <span>Chat</span>
        </a>
        <?php if ($role == 'teacher'): ?>
            <a href="view_complaints.php">
                <i class="fas fa-exclamation-circle"></i>
                <span>View Complaints</span>
            </a>
        <?php endif; ?>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

    <!-- Main Content -->
    <!-- Main Content -->
    <div class="main-content">
        <!-- Particle Background -->
        <div id="particles"></div>

        <div class="container-fluid py-4">
            <div class="row g-4">
                <!-- Calendar Section -->

                <div class="col-12 col-lg-8">
                    <?php if ($role === 'student'): ?>
                        <!-- Upcoming Classes Section -->
                        <div class="card shadow-lg bg-transparent text-white" style="backdrop-filter: blur(10px);">
                            <div class="card-header">
                                <h4><i class="fas fa-video me-2"></i>Upcoming Classes</h4>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <!-- Sample Class Entries -->
                                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5>Mathematics - Algebra Basics</h5>
                                                <p class="mb-0">10:00 AM - 11:30 AM</p>
                                                <small>Prof. John Smith</small>
                                            </div>
                                            <button class="btn btn-primary align-self-center">Join Now</button>
                                        </div>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5>Science - Introduction to Physics</h5>
                                                <p class="mb-0">02:00 PM - 03:30 PM</p>
                                                <small>Dr. Sarah Johnson</small>
                                            </div>
                                            <button class="btn btn-outline-light align-self-center">Starts in 1h 30m</button>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($role === 'teacher'): ?>
                        <!-- Teacher Functionalities Section -->
                        <div class="card shadow-lg bg-transparent text-white" style="backdrop-filter: blur(10px);">
                            <div class="card-header">
                                <h4><i class="fas fa-tasks me-2"></i>Assignment Deadlines</h4>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="view_submissions.php" class="list-group-item list-group-item-action bg-transparent text-white">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5>Track Assignment Deadlines</h5>
                                                <p class="mb-0">Monitor all deadlines in one place</p>
                                            </div>
                                            <button class="btn btn-primary align-self-center">View</button>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card shadow-lg bg-transparent text-white mt-3" style="backdrop-filter: blur(10px);">
                            <div class="card-header">
                                <h4><i class="fas fa-poll me-2"></i>Create Polls</h4>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5>Engage with Live Polls</h5>
                                                <p class="mb-0">Interact with students in real time</p>
                                            </div>
                                            <button class="btn btn-success align-self-center">Create</button>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card shadow-lg bg-transparent text-white mt-3" style="backdrop-filter: blur(10px);">
                            <div class="card-header">
                                <h4><i class="fas fa-file-export me-2"></i>Export Attendance status</h4>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="attendance_history.php" class="list-group-item list-group-item-action bg-transparent text-white">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5>Download attendance sheet</h5>
                                                <p class="mb-0">explore attendance percentage</p>
                                            </div>
                                            <button class="btn btn-secondary align-self-center">Export</button>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card shadow-lg bg-transparent text-white" style="backdrop-filter: blur(10px);">
                        <div class="card-header">
                            <h4><i class="fas fa-tasks me-2"></i>To-Do List</h4>
                        </div>
                        <div class="card-body">
                            <!-- Add New Task -->
                            <div class="input-group mb-3">
                                <input type="text" id="new-task" class="form-control bg-dark text-white"
                                    placeholder="Add new task" aria-label="Add new task" required>
                                <button class="btn btn-primary" onclick="addTask()">
                                    <i class="fas fa-plus"></i>
                                </button>

                            </div>

                            <!-- Task List -->
                            <ul id="todo-list" class="list-group">
                                <!-- Dynamically added tasks will appear here -->
                            </ul>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <?php if ($role == 'student'): ?>
            <div class="chatbot-icon" data-bs-toggle="modal" data-bs-target="#chatbotModal">
                <img src="giphy-unscreen.gif" alt="Chatbot">
            </div>
        <?php endif; ?>

        <!-- Chatbot Modal -->
        <div class="modal fade" id="chatbotModal" tabindex="-1" aria-labelledby="chatbotModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="chatbotModalLabel">Chatbot</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Embed bot.php content -->
                        <iframe src="bot.php" frameborder="0" style="width: 100%; height: 500px;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- Particle JS Script -->
        <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

        <script>
            /* Adjust Particle.js settings */
            particlesJS("particles", {
                particles: {
                    number: {
                        value: 80,
                        density: {
                            enable: true,
                            value_area: 800
                        }
                    },
                    color: {
                        value: "#ffffff"
                    },
                    shape: {
                        type: "circle",
                        stroke: {
                            width: 0
                        },
                        polygon: {
                            nb_sides: 5
                        }
                    },
                    opacity: {
                        value: 0.5,
                        random: false
                    },
                    size: {
                        value: 3,
                        random: true
                    },
                    line_linked: {
                        enable: true,
                        distance: 150,
                        color: "#ffffff",
                        opacity: 0.4,
                        width: 1
                    },
                    move: {
                        enable: true,
                        speed: 2,
                        direction: "none",
                        random: false,
                        straight: false,
                        out_mode: "out",
                        attract: {
                            enable: false
                        }
                    },
                },
                interactivity: {
                    detect_on: "window",
                    /* Ensure particles are interactive across the window */
                    events: {
                        onhover: {
                            enable: true,
                            mode: "repulse"
                        },
                        onclick: {
                            enable: true,
                            mode: "push"
                        }
                    },
                    modes: {
                        repulse: {
                            distance: 100,
                            duration: 0.4
                        },
                        push: {
                            particles_nb: 4
                        }
                    },
                },
                retina_detect: true,
            });
        </script>
        <script>
            const userId = "<?php echo $user_id; ?>";
        </script>
        <script src="../assets/js/todo.js"></script>
        <!-- FullCalendar CSS & JS -->
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script>
            // Pass PHP variable to JS
            const role = "<?php echo $role; ?>";
        </script>
        <script src="../assets/js/calendar.js">
        </script>
        <!-- Bootstrap JS -->

        <!-- FullCalendar CSS & JS -->
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
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
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$profile_photo = isset($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : $user_id . '-avatar.jpg'; // Default avatar if no profile photo
$role = $_SESSION['role']; // Assuming the user's role is stored in the session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Virtual Classroom</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/calendar.css">
    <style>body {
    margin: 0;
    padding: 0;
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(to right, #1A1A72, #4A4A90);
    overflow: hidden; /* Prevent scroll bars */
    min-height: 100vh; /* Ensure body covers viewport */
    min-width: 100vw; /* Ensure body width covers viewport */
    color: #FFF;
}

.sidebar {
    background: linear-gradient(to bottom,rgb(75, 75, 233), #00C9FF);
    height: 100vh;
    width: 250px;
    position: fixed; /* Fixed position ensures no layout overflow */
    top: 0;
    left: 0;
    overflow-y: auto; /* Allow scrolling only for sidebar content */
    padding-top: 20px;
    z-index: 1000;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
}
        /* Sidebar Profile Photo */
        .sidebar .profile-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 10px auto 0;
            border: 3px solid #FFF;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.7); /* Glowing effect */
            transition: box-shadow 0.3s ease;
        }

        /* Hover effect for glowing effect */
        .sidebar .profile-photo:hover {
            box-shadow: 0 0 30px rgba(255, 255, 255, 1), 0 0 50px rgba(0, 255, 255, 0.5); /* Glowing effect on hover */
        }

        /* Sidebar Links */
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: #FFF;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .sidebar a i {
            margin-right: 15px;
            font-size: 18px;
        }

        /* Hover Effect with Glow */
        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left: 5px solid #FFF;
            padding-left: 25px;
            box-shadow: 0 0 10px #FFF, 0 0 20px #FFF, 0 0 30px #2575fc; /* Glow effect */
            transition: all 0.3s ease;
        }

        /* Sidebar Text Centering */
        .sidebar h3 {
            color: #FFF;
            font-size: 18px;
            text-align: center;
            margin-top: 10px;
        }

        .navbar {
            background: linear-gradient(to right, #00C9FF,rgb(75, 75, 233));
            padding: 10px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .notification {
            position: relative;
            color: #FFF;
            font-size: 24px;
        }

        .navbar .notification .badge {
            position: absolute;
            top: 0;
            right: -10px;
            background: red;
            color: #FFF;
            border-radius: 50%;
            padding: 5px 8px;
            font-size: 12px;
        }

        /* Profile Photo */
        .profile-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto;
            display: block;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Particle Background */
        #particles {
        position: fixed; /* Change from absolute to fixed */
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1; /* Keep it below other content but interactive */
        pointer-events: auto; /* Ensure it registers mouse events */
    }

        /* Chatbot Icon */
        .chatbot-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            cursor: pointer;
            background-color: transparent;
        }

        .chatbot-icon img {
            width: 150px;
            height: auto;
            transition: transform 0.3s ease;
        }

        .chatbot-icon:hover img {
            transform: scale(1.2);
        }

        /* Slide up animation */
        .chatbot-icon {
            animation: slideUp 1s forwards ease;
        }

        @keyframes slideUp {
            0% {
                bottom: -150px;
            }
            100% {
                bottom: 20px;
            }
        }
        
        /* Custom Card Styling */
.card {
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

/* Calendar Customization */
.fc {
    color: #fff;
}

.fc-toolbar-title {
    color: #fff !important;
}

.fc-col-header-cell {
    background: rgba(255, 255, 255, 0.1) !important;
}

.fc-daygrid-day {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

/* List Group Customization */
.list-group-item {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 8px;
    border-radius: 8px !important;
    transition: all 0.3s ease;
}

.list-group-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

/* Form Control Styling */
.form-control {
    background: rgba(0, 0, 0, 0.3) !important;
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff !important;
}

.form-control:focus {
    box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
}
        </style>
</head>
<body>
<!-- Top Navbar -->
<div class="navbar">
    <span >Welcome, <?php echo $username; ?></span>
    <div class="icon-container">
        <i class="fas fa-bell"></i>
        <span class="notification-badge">3</span>
        <i class="fas fa-calendar-alt" onclick="toggleCalendar()"></i>
    </div>
</div>
<div class="calendar-popup" id="calendarPopup">
    <header class="modal-header">
        <h5 class="modal-title" id="calendarMonthYear">EVENT Calendar</h5>
    </header>
    <div class="modal-body">
        <div class="calendar-header">
        <div id="calendarControls">
    <button id="prevMonth">Prev</button>
    <span id="monthYearDisplay"></span>
    <button id="nextMonth">Next</button>
</div>
<div class="calendar-grid"></div>

        </div>
    </div>
</div>


<!-- Sidebar -->
<div class="sidebar">
    <!-- Profile Photo -->
    <img src="../assets/avatars/<?php echo $profile_photo; ?>" alt="Profile Photo" class="profile-photo">

    <h3 class="text-center mt-3"><?php echo $username; ?></h3>

    <!-- Sidebar Links -->
    <a href="profile.php">
        <i class="fas fa-user-circle"></i>
        <span>Profile</span>
    </a>
    <a href="study_materials.php">
        <i class="fas fa-book"></i>
        <span>Study Materials</span>
    </a>
    <a href="view_live_classes.php">
        <i class="fas fa-video"></i>
        <span>Live Classes</span>
    </a>
    <a href="activities.php">
        <i class="fas fa-tasks"></i>
        <span>Activity Hub</span>
    </a>
    <a href="class_attendance.php">
        <i class="fas fa-list"></i>
        <span>Attendance</span>
    </a>
    <?php if ($role == 'student'): ?>
        <a href="complaint_form.php">
            <i class="fas fa-exclamation-circle"></i>
            <span>Complaints</span>
        </a>
    <?php endif; ?>
    <a href="chat.php">
        <i class="fas fa-comments"></i>
        <span>Chat</span>
    </a>
    <?php if ($role == 'teacher'): ?>
        <a href="view_complaints.php">
            <i class="fas fa-exclamation-circle"></i>
            <span>View Complaints</span>
        </a>
    <?php endif; ?>
    <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div>

<!-- Main Content -->
<!-- Main Content -->
<div class="main-content">
    <!-- Particle Background -->
    <div id="particles"></div>
    
    <div class="container-fluid py-4">
        <div class="row g-4">
            <!-- Calendar Section -->

            <div class="col-12 col-lg-8">
    <?php if ($role === 'student'): ?>
        <!-- Upcoming Classes Section -->
        <div class="card shadow-lg bg-transparent text-white" style="backdrop-filter: blur(10px);">
            <div class="card-header">
                <h4><i class="fas fa-video me-2"></i>Upcoming Classes</h4>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <!-- Sample Class Entries -->
                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>Mathematics - Algebra Basics</h5>
                                <p class="mb-0">10:00 AM - 11:30 AM</p>
                                <small>Prof. John Smith</small>
                            </div>
                            <button class="btn btn-primary align-self-center">Join Now</button>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>Science - Introduction to Physics</h5>
                                <p class="mb-0">02:00 PM - 03:30 PM</p>
                                <small>Dr. Sarah Johnson</small>
                            </div>
                            <button class="btn btn-outline-light align-self-center">Starts in 1h 30m</button>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    <?php elseif ($role === 'teacher'): ?>
        <!-- Teacher Functionalities Section -->
        <div class="card shadow-lg bg-transparent text-white" style="backdrop-filter: blur(10px);">
            <div class="card-header">
                <h4><i class="fas fa-tasks me-2"></i>Assignment Deadlines</h4>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="view_submissions.php" class="list-group-item list-group-item-action bg-transparent text-white">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>Track Assignment Deadlines</h5>
                                <p class="mb-0">Monitor all deadlines in one place</p>
                            </div>
                            <button class="btn btn-primary align-self-center">View</button>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="card shadow-lg bg-transparent text-white mt-3" style="backdrop-filter: blur(10px);">
            <div class="card-header">
                <h4><i class="fas fa-poll me-2"></i>Create Polls</h4>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>Engage with Live Polls</h5>
                                <p class="mb-0">Interact with students in real time</p>
                            </div>
                            <button class="btn btn-success align-self-center">Create</button>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="card shadow-lg bg-transparent text-white mt-3" style="backdrop-filter: blur(10px);">
            <div class="card-header">
                <h4><i class="fas fa-file-export me-2"></i>Export Attendance status</h4>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="attendance_history.php" class="list-group-item list-group-item-action bg-transparent text-white">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>Download attendance sheet</h5>
                                <p class="mb-0">explore attendance percentage</p>
                            </div>
                            <button class="btn btn-secondary align-self-center">Export</button>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

            <div class="col-12 col-lg-4">
            <div class="card shadow-lg bg-transparent text-white" style="backdrop-filter: blur(10px);">
    <div class="card-header">
        <h4><i class="fas fa-tasks me-2"></i>To-Do List</h4>
    </div>
    <div class="card-body">
        <!-- Add New Task -->
        <div class="input-group mb-3">
            <input type="text" id="new-task" class="form-control bg-dark text-white" 
                   placeholder="Add new task" aria-label="Add new task" required>
                   <button class="btn btn-primary" onclick="addTask()">
    <i class="fas fa-plus"></i>
</button>

        </div>

        <!-- Task List -->
        <ul id="todo-list" class="list-group">
            <!-- Dynamically added tasks will appear here -->
        </ul>
    </div>
</div>


        </div>
    </div>
</div>

<?php if ($role == 'student'): ?>
    <div class="chatbot-icon" data-bs-toggle="modal" data-bs-target="#chatbotModal">
        <img src="giphy-unscreen.gif" alt="Chatbot">
    </div>
<?php endif; ?>

<!-- Chatbot Modal -->
<div class="modal fade" id="chatbotModal" tabindex="-1" aria-labelledby="chatbotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chatbotModalLabel">Chatbot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Embed bot.php content -->
                <iframe src="bot.php" frameborder="0" style="width: 100%; height: 500px;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Particle JS Script -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

<script>
    /* Adjust Particle.js settings */
    particlesJS("particles", {
        particles: {
            number: { value: 80, density: { enable: true, value_area: 800 } },
            color: { value: "#ffffff" },
            shape: { type: "circle", stroke: { width: 0 }, polygon: { nb_sides: 5 } },
            opacity: { value: 0.5, random: false },
            size: { value: 3, random: true },
            line_linked: { enable: true, distance: 150, color: "#ffffff", opacity: 0.4, width: 1 },
            move: { enable: true, speed: 2, direction: "none", random: false, straight: false, out_mode: "out", attract: { enable: false } },
        },
        interactivity: {
            detect_on: "window", /* Ensure particles are interactive across the window */
            events: { 
                onhover: { enable: true, mode: "repulse" }, 
                onclick: { enable: true, mode: "push" } 
            },
            modes: { 
                repulse: { distance: 100, duration: 0.4 }, 
                push: { particles_nb: 4 } 
            },
        },
        retina_detect: true,
    });
</script>
<script>
    const userId = "<?php echo $user_id; ?>";
    </script>
<script src="../assets/js/todo.js"></script>
<!-- FullCalendar CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
        // Pass PHP variable to JS
        const role = "<?php echo $role; ?>";
    </script>
<script src="../assets/js/calendar.js">
</script>
<!-- Bootstrap JS -->

<!-- FullCalendar CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
>>>>>>> ee7c9565c28e3f015817e1645a6e2d0b3b949065
