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
    <div id="google_translate_element"></div>
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
    <script src="script.js"></script>
      <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement(
                {pageLanguage: 'en'},
                'google_translate_element'
            );
        } 
  </script>
  <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>
</html>
