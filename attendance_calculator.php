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

    // Handle form submission for attendance
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Insert or update attendance in the database
        $attendance_date = $_POST['attendance_date'];
        $attendance = $_POST['attendance']; // Array of attendance (student_id => status)

        foreach ($attendance as $student_id => $status) {
            // Insert attendance record for each student
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject, attendance_date, attendance_status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $student_id, $teacher_subject, $attendance_date, $status);
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

    // Calculate Attendance Percentage for Student
    $total_classes_query = "SELECT DISTINCT attendance_date FROM attendance WHERE student_id = ?";
    $stmt = $conn->prepare($total_classes_query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $total_classes_result = $stmt->get_result();
    $total_classes_count = $total_classes_result->num_rows;

    // Count the number of classes attended by the student (where status is 'present')
    $attended_classes_query = "SELECT COUNT(*) as attended FROM attendance WHERE student_id = ? AND attendance_status = 'present'";
    $stmt = $conn->prepare($attended_classes_query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $attended_classes_result = $stmt->get_result();
    $attended_classes = $attended_classes_result->fetch_assoc()['attended'];

    if ($total_classes_count > 0) {
        $attendance_percentage = ($attended_classes / $total_classes_count) * 100;
    } else {
        $attendance_percentage = 0;  // No attendance records
    }

}

// Teacher's Attendance Calculation
if ($_SESSION['role'] === 'teacher') {
    $teacher_id = $_SESSION['user_id'];  // Assuming teacher ID is stored in session

    // Get Total Classes Conducted (distinct dates)
    $total_classes_query = "SELECT DISTINCT attendance_date FROM attendance WHERE subject = ?";
    $stmt = $conn->prepare($total_classes_query);
    $stmt->bind_param("s", $teacher_subject);
    $stmt->execute();
    $total_classes_result = $stmt->get_result();
    $total_classes_count = $total_classes_result->num_rows;

    // Calculate Attendance Percentage for Each Student
    $students_attendance_percentage = [];
    $students_attended_count = [];
    foreach ($students as $student) {
        $student_id = $student['id'];

        // Get Total Classes Attended for the student
        $attended_classes_query = "SELECT COUNT(*) as attended FROM attendance WHERE student_id = ? AND subject = ? AND attendance_status = 'present'";
        $stmt = $conn->prepare($attended_classes_query);
        $stmt->bind_param("is", $student_id, $teacher_subject);
        $stmt->execute();
        $attended_classes_result = $stmt->get_result();
        $attended_classes = $attended_classes_result->fetch_assoc()['attended'];

        // Calculate Attendance Percentage
        if ($total_classes_count > 0) {
            $attendance_percentage = ($attended_classes / $total_classes_count) * 100;
        } else {
            $attendance_percentage = 0;  // No attendance records
        }

        $students_attendance_percentage[$student_id] = $attendance_percentage;
        $students_attended_count[$student_id] = $attended_classes;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Calculator</title>
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
    <h1>Attendance Calculator</h1>

    <?php if ($_SESSION['role'] === 'teacher'): ?>
        <!-- Teacher's View -->
        <h2>Students' Attendance</h2>
        <table>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Total Classes Conducted</th>
                <th>Total Classes Attended</th>
                <th>Attendance Percentage</th>
            </tr>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                    <td><?php echo $total_classes_count; ?></td>
                    <td><?php echo $students_attended_count[$student['id']]; ?></td>
                    <td><?php echo number_format($students_attendance_percentage[$student['id']], 2); ?>%</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($_SESSION['role'] === 'student'): ?>
        <!-- Student's View -->
        <h2>Your Attendance</h2>
        <p><strong>Student ID:</strong> <?php echo $student_id; ?></p>
        <p><strong>Total Classes Conducted:</strong> <?php echo $total_classes_count; ?></p>
        <p><strong>Total Classes Attended:</strong> <?php echo $attended_classes; ?></p>
        <p><strong>Attendance Percentage:</strong> <?php echo number_format($attendance_percentage, 2); ?>%</p>

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
