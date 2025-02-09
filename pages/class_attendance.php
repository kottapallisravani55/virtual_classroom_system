<<<<<<< HEAD
<?php
session_start();

// Debugging: Check if session contains the correct username
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    die("Session Error: Username is not set. Please log in again.");
}

$username = htmlspecialchars($_SESSION['username'] ?? 'Unknown User');
$profile_photo = htmlspecialchars($_SESSION['profile_photo'] ?? 'default.jpg');

// Check if the user is logged in as either teacher or student
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'student')) {
    header("Location: login.php");
    exit;
}

require '../database/db_config.php'; // Adjust path if necessary

// Fetch correct username from the database to prevent incorrect session values
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $_SESSION['username'] = $user_data['username']; // Update session with correct username
    $username = htmlspecialchars($user_data['username']);
} else {
    $username = "Unknown User"; // Fallback
}

$stmt->close();

// **For Teacher Role**
if ($_SESSION['role'] === 'teacher') {
    $sql = "SELECT id, username FROM students";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Database query error: " . $conn->error);
    }

    $students = $result->fetch_all(MYSQLI_ASSOC);
    $teacher_subject = $_SESSION['subject'];
    $teacher_id = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_date'], $_POST['attendance'])) {
        $attendance_date = $_POST['attendance_date'];
        $attendance = $_POST['attendance'];

        foreach ($attendance as $student_id => $status) {
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject, attendance_date, attendance_status, teacher_id) 
                                    VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $student_id, $teacher_subject, $attendance_date, $status, $teacher_id);
            $stmt->execute();
        }
        echo "<script>alert('Attendance submitted successfully!');</script>";
    }
}

// **For Student Role**
if ($_SESSION['role'] === 'student') {
    $student_id = $_SESSION['user_id'];
    $subject_query = "SELECT DISTINCT subject FROM attendance";
    $date_query = "SELECT DISTINCT attendance_date FROM attendance ORDER BY attendance_date DESC";
    $subjects = $conn->query($subject_query)->fetch_all(MYSQLI_ASSOC);
    $dates = $conn->query($date_query)->fetch_all(MYSQLI_ASSOC);

    $filter_subject = $_POST['subject'] ?? '';
    $filter_date = $_POST['attendance_date'] ?? '';

    $attendance_query = "SELECT subject, attendance_date, attendance_status FROM attendance WHERE student_id = ?";
    $params = [$student_id];
    $types = "i";

    if (!empty($filter_subject)) {
        $attendance_query .= " AND subject = ?";
        $params[] = $filter_subject;
        $types .= "s";
    }
    if (!empty($filter_date)) {
        $attendance_query .= " AND attendance_date = ?";
        $params[] = $filter_date;
        $types .= "s";
    }

    $attendance_query .= " ORDER BY attendance_date DESC";
    $stmt = $conn->prepare($attendance_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $attendance_result = $stmt->get_result();
    $attendance_records = $attendance_result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Attendance</title>
    <link rel="stylesheet" href="./navbar.css">
    <link rel="stylesheet" href="./sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .main-content {
            margin-top: -20px;
            margin-left: 250px;
            padding: 0px 20px 20px 20px;
            width: calc(100% - 250px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            background: #f8f9fa;
        }

        .attendance-table {
            width: 80%;
            max-width: 900px;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .attendance-table th,
        .attendance-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        .attendance-table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        .attendance-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .attendance-table tr:hover {
            background-color: #ddd;
        }

        .button-container {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .button-container button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            background-color: #28a745;
            color: white;
            transition: background 0.3s ease;
        }

        .button-container button:hover {
            background-color: #218838;
        }

        .table-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        input[type="date"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
            outline: none;
        }

        input[type="date"]:focus {
            border-color: #28a745;
        }
    </style>
    </style>
</head>

<body>

    <!-- Navbar -->
    <div class="navbar">
        <span>Welcome, <?php echo $username; ?></span>
        <div class="icon-container">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
            <i class="fas fa-calendar-alt" onclick="toggleCalendar()"></i>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="profile.php"> <img src="../assets/avatars/<?php echo $profile_photo; ?>" alt="Profile Photo" class="profile-photo"></a>

        <h3 class="text-center mt-3"><?php echo $username; ?></h3>

        <a href="dashboard.php"><i class="fas fa-user-circle"></i><span>Home</span></a>
        <a href="study_materials.php"><i class="fas fa-book"></i><span>Study Materials</span></a>
        <a href="view_live_classes.php"><i class="fas fa-video"></i><span>Live Classes</span></a>
        <a href="activities.php"><i class="fas fa-tasks"></i><span>Activity Hub</span></a>
        <a href="class_attendance.php" class="active"><i class="fas fa-list"></i><span>Attendance</span></a>

        <?php if ($_SESSION['role'] === 'student'): ?>
            <a href="complaint_form.php"><i class="fas fa-exclamation-circle"></i><span>Complaints</span></a>
        <?php endif; ?>

        <a href="chat.php"><i class="fas fa-comments"></i><span>Chat</span></a>

        <?php if ($_SESSION['role'] === 'teacher'): ?>
            <a href="view_complaints.php"><i class="fas fa-exclamation-circle"></i><span>View Complaints</span></a>
        <?php endif; ?>

        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Class Attendance</h1>

        <?php if ($_SESSION['role'] === 'teacher'): ?>
            <!-- Teacher View: Attendance Submission -->
            <form action="" method="POST">
                <label for="attendance_date">Select Date:</label>
                <input type="date" name="attendance_date" required>
                <div class="table-container">
                    <table class="attendance-table">
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Attendance</th>
                        </tr>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo $student['id']; ?></td>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td>
                                    <label><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" required> Present</label>
                                    <label><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent"> Absent</label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <div class="button-container">
                        <button type="submit">Submit Attendance</button>
                        <button type="button" onclick="window.location.href='attendance_history.php'">View Attendance History</button>
                        <button type="button" onclick="window.location.href='attendance_calculator.php'">Attendance Calculator</button>
                    </div>
                </div>

            </form>

        <?php elseif ($_SESSION['role'] === 'student'): ?>
            <!-- Student View: Attendance Records -->
            <h2>Your Attendance Records</h2>

            <!-- Attendance Filter Form -->
            <form action="" method="POST">
                <label for="subject">Select Subject:</label>
                <select name="subject">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo htmlspecialchars($subject['subject']); ?>" <?php echo ($subject['subject'] === $filter_subject) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['subject']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="attendance_date">Select Date:</label>
                <input type="date" name="attendance_date" value="<?php echo $filter_date; ?>">
                <button type="submit">Filter</button>
            </form>

            <!-- Attendance Table -->
            <table class="attendance-table">
                <tr>
                    <th>Date</th>
                    <th>Subject</th>
                    <th>Status</th>
                </tr>
                <?php if (count($attendance_records) > 0): ?>
                    <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><?php echo $record['attendance_date']; ?></td>
                            <td><?php echo htmlspecialchars($record['subject']); ?></td>
                            <td><?php echo ucfirst($record['attendance_status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No attendance records found.</td>
                    </tr>
                <?php endif; ?>
            </table>
        <?php endif; ?>
    </div>

</body>

</html>
=======
<?php
session_start();

// Check if the user is logged in as either teacher or student
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'student')) {
    header("Location: login.php");
    exit;
}

require '../database/db_config.php'; // Adjust path if necessary

// For Teacher Role
if ($_SESSION['role'] === 'teacher') {
    // Fetch all students from the students table
    $sql = "SELECT id, username FROM students";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Database query error: " . $conn->error);
    }

    $students = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch teacher's subject from session
    $teacher_subject = $_SESSION['subject'];  // Assuming teacher's subject is stored in session
    $teacher_id = $_SESSION['user_id']; // Assuming teacher's ID is stored in session

    // Handle form submission for attendance
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Insert or update attendance in the database
        $attendance_date = $_POST['attendance_date'];
        $attendance = $_POST['attendance']; // Array of attendance (student_id => status)

        foreach ($attendance as $student_id => $status) {
            // Insert attendance record for each student
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject, attendance_date, attendance_status, teacher_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $student_id, $teacher_subject, $attendance_date, $status, $teacher_id);
            $stmt->execute();
        }
        echo "Attendance submitted successfully!";
    }
}

// For Student Role
if ($_SESSION['role'] === 'student') {
    $student_id = $_SESSION['user_id'];  // Assuming student ID is stored in session

    // Fetch all distinct subjects and dates from the attendance table
    $distinct_subject_query = "SELECT DISTINCT subject FROM attendance";
    $distinct_date_query = "SELECT DISTINCT attendance_date FROM attendance ORDER BY attendance_date DESC";

    $subject_result = $conn->query($distinct_subject_query);
    $date_result = $conn->query($distinct_date_query);

    if ($subject_result === false || $date_result === false) {
        die("Database query error: " . $conn->error);
    }

    $subjects = $subject_result->fetch_all(MYSQLI_ASSOC);
    $dates = $date_result->fetch_all(MYSQLI_ASSOC);

    // Handle filtering
    $filter_subject = isset($_POST['subject']) ? $_POST['subject'] : '';
    $filter_date = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : '';

    $attendance_query = "SELECT subject, attendance_date, attendance_status FROM attendance WHERE student_id = ?";

    // Add filters to query if they are set
    if (!empty($filter_subject)) {
        $attendance_query .= " AND subject = ?";
    }
    if (!empty($filter_date)) {
        $attendance_query .= " AND attendance_date = ?";
    }
    $attendance_query .= " ORDER BY attendance_date DESC";

    $stmt = $conn->prepare($attendance_query);

    // Bind parameters dynamically based on filters
    if (!empty($filter_subject) && !empty($filter_date)) {
        $stmt->bind_param("iss", $student_id, $filter_subject, $filter_date);
    } elseif (!empty($filter_subject)) {
        $stmt->bind_param("is", $student_id, $filter_subject);
    } elseif (!empty($filter_date)) {
        $stmt->bind_param("is", $student_id, $filter_date);
    } else {
        $stmt->bind_param("i", $student_id);
    }

    $stmt->execute();
    $attendance_result = $stmt->get_result();
    $attendance_records = $attendance_result->fetch_all(MYSQLI_ASSOC);

    // Debugging: Check if we are fetching attendance records
    if ($attendance_result->num_rows === 0) {
        echo "<p>No attendance records found for student ID $student_id.</p>";  // Debugging message
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Attendance</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        button {
            margin: 10px 0;
        }
        select, input[type="date"] {
            margin: 10px;
        }
    </style>
</head>
<body>
    <h1>Class Attendance</h1>

    <?php if ($_SESSION['role'] === 'teacher'): ?>
        <!-- Teacher View -->
        <form action="" method="POST">
            <label for="attendance_date">Select Date:</label>
            <input type="date" name="attendance_date" id="attendance_date" required>
            
            <!-- Hidden input for subject -->
            <input type="hidden" name="subject" value="<?php echo htmlspecialchars($teacher_subject); ?>">

            <table>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Attendance</th>
                </tr>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['id']; ?></td>
                        <td><?php echo htmlspecialchars($student['username']); ?></td>
                        <td>
                            <label>
                                <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" required> Present
                            </label>
                            <label>
                                <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent"> Absent
                            </label>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <button type="submit">Submit Attendance</button>
            <a href="attendance_history.php">view attendance history</a>
            <a href="attendance_calculator.php">attendance calaculator</a>
        </form>

    <?php elseif ($_SESSION['role'] === 'student'): ?>
        <!-- Student View -->
        <h2>Your Attendance</h2>

        <!-- Filter Form -->
        <form action="" method="POST">
            <label for="subject">Select Subject:</label>
            <select name="subject" id="subject">
                <option value="">All Subjects</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?php echo htmlspecialchars($subject['subject']); ?>" <?php echo ($subject['subject'] === $filter_subject) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($subject['subject']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="attendance_date">Select Date:</label>
            <input type="date" name="attendance_date" id="attendance_date" value="<?php echo $filter_date; ?>">

            <button type="submit">Filter</button>
        </form>

        <table>
            <tr>
                <th>Attendance Date</th>
                <th>Subject</th>
                <th>Attendance Status</th>
            </tr>
            <?php if (count($attendance_records) > 0): ?>
                <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><?php echo $record['attendance_date']; ?></td>
                        <td><?php echo htmlspecialchars($record['subject']); ?></td>
                        <td><?php echo $record['attendance_status']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No attendance records found.</td>
                </tr>
            <?php endif; ?>
        </table>
    <?php endif; ?>
</body>
</html>
>>>>>>> ee7c9565c28e3f015817e1645a6e2d0b3b949065
